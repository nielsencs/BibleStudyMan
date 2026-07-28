#!/usr/bin/env python3
from __future__ import annotations

import argparse
import re
import sys
from dataclasses import dataclass
from pathlib import Path

VERSE_INSERT_PREFIX_RE = re.compile(r"^INSERT\s+INTO\s+`?verses`?\b", re.IGNORECASE)
VERSE_INSERT_RE = re.compile(
    r"^INSERT\s+INTO\s+`?verses`?\s*"
    r"\(`?bookCode`?,\s*`?chapter`?,\s*`?verseNumber`?,\s*`?verseText`?\)\s*"
    r"VALUES\s*\('([A-Z0-9]{3})',\s*(\d+),\s*(\d+),\s*'(.*)'\);\s*$",
    re.IGNORECASE,
)
STRONGS_INSERT_RE = re.compile(
    r"^INSERT\s+INTO\s+`?strongs`?\s*"
    r"\(`?strongsNumber`?\s*,.*?\)\s*VALUES\s*\('([HG]\d{4})'\s*,",
    re.IGNORECASE,
)
STRONGS_MARKER_RE = re.compile(r"\{([HG]\d{4})\}")
ANY_STRONGS_LIKE_MARKER_RE = re.compile(r"\{[HG][0-9]+\}")


@dataclass(frozen=True)
class VerseRow:
    line_number: int
    book_code: str
    chapter: int
    verse_number: int
    verse_text: str


@dataclass(frozen=True)
class IntegrityIssue:
    code: str
    message: str


def read_lines(path: Path) -> list[str]:
    return path.read_text(encoding="utf-8", errors="replace").splitlines()


def parse_verses(path: Path) -> tuple[list[VerseRow], list[IntegrityIssue]]:
    rows: list[VerseRow] = []
    issues: list[IntegrityIssue] = []
    refs: dict[tuple[str, int, int], int] = {}

    for line_number, line in enumerate(read_lines(path), start=1):
        if not VERSE_INSERT_PREFIX_RE.match(line):
            continue
        match = VERSE_INSERT_RE.match(line)
        if not match:
            issues.append(
                IntegrityIssue(
                    "malformed-verse-insert",
                    f"{path}:{line_number}: verse INSERT line is not in the expected one-row format",
                )
            )
            continue

        book_code, chapter_text, verse_number_text, verse_text = match.groups()
        chapter = int(chapter_text)
        verse_number = int(verse_number_text)
        ref = (book_code, chapter, verse_number)
        if ref in refs:
            issues.append(
                IntegrityIssue(
                    "duplicate-verse-reference",
                    f"{path}:{line_number}: duplicate verse reference {book_code} {chapter}:{verse_number}; first seen on line {refs[ref]}",
                )
            )
        refs[ref] = line_number
        rows.append(VerseRow(line_number, book_code, chapter, verse_number, verse_text))

    if not rows:
        issues.append(IntegrityIssue("no-verses", f"{path}: no verse INSERT rows found"))
    return rows, issues


def parse_strongs(path: Path) -> tuple[set[str], list[IntegrityIssue]]:
    numbers: set[str] = set()
    issues: list[IntegrityIssue] = []

    for line_number, line in enumerate(read_lines(path), start=1):
        if not re.match(r"^INSERT\s+INTO\s+`?strongs`?\b", line, re.IGNORECASE):
            continue
        match = STRONGS_INSERT_RE.match(line)
        if not match:
            issues.append(
                IntegrityIssue(
                    "malformed-strongs-insert",
                    f"{path}:{line_number}: strongs INSERT line is not in the expected one-row format",
                )
            )
            continue
        number = match.group(1)
        if number in numbers:
            issues.append(IntegrityIssue("duplicate-strongs-number", f"{path}:{line_number}: duplicate strongs number {number}"))
        numbers.add(number)

    if not numbers:
        issues.append(IntegrityIssue("no-strongs", f"{path}: no strongs INSERT rows found"))
    return numbers, issues


def check_marker_format(verses_path: Path, rows: list[VerseRow]) -> list[IntegrityIssue]:
    issues: list[IntegrityIssue] = []
    for row in rows:
        for marker in ANY_STRONGS_LIKE_MARKER_RE.findall(row.verse_text):
            if not STRONGS_MARKER_RE.fullmatch(marker):
                issues.append(
                    IntegrityIssue(
                        "bad-strongs-marker-format",
                        f"{verses_path}:{row.line_number}: {row.book_code} {row.chapter}:{row.verse_number} uses non-standard marker {marker}; expected four digits like {{H0430}}",
                    )
                )
    return issues


def check_missing_strongs(verses_path: Path, rows: list[VerseRow], known_strongs: set[str]) -> list[IntegrityIssue]:
    used: dict[str, list[str]] = {}
    for row in rows:
        ref = f"{row.book_code} {row.chapter}:{row.verse_number}"
        for number in STRONGS_MARKER_RE.findall(row.verse_text):
            used.setdefault(number, [])
            if len(used[number]) < 5:
                used[number].append(ref)

    issues: list[IntegrityIssue] = []
    for number in sorted(set(used) - known_strongs):
        refs = ", ".join(used[number])
        issues.append(
            IntegrityIssue(
                "missing-strongs-definition",
                f"{verses_path}: {number} is used in bibleVerses.sql but is missing from the strongs table; examples: {refs}",
            )
        )
    return issues


def check_paths(verses_path: Path, complete_path: Path) -> list[IntegrityIssue]:
    issues: list[IntegrityIssue] = []
    if not verses_path.exists():
        issues.append(IntegrityIssue("missing-file", f"Missing verses file: {verses_path}"))
    if not complete_path.exists():
        issues.append(IntegrityIssue("missing-file", f"Missing complete/schema file: {complete_path}"))
    if issues:
        return issues

    rows, verse_issues = parse_verses(verses_path)
    strongs, strongs_issues = parse_strongs(complete_path)
    issues.extend(verse_issues)
    issues.extend(strongs_issues)
    if rows:
        issues.extend(check_marker_format(verses_path, rows))
    if rows and strongs:
        issues.extend(check_missing_strongs(verses_path, rows, strongs))
    return issues


def main(argv: list[str] | None = None) -> int:
    root = Path(__file__).resolve().parents[1]
    parser = argparse.ArgumentParser(description="Check TCSB SQL files for common hand-edit/database integrity mistakes.")
    parser.add_argument("--verses", type=Path, default=root / "database" / "bibleVerses.sql", help="Path to bibleVerses.sql")
    parser.add_argument("--complete", type=Path, default=root / "database" / "bibleComplete.sql", help="Path to bibleComplete.sql containing the strongs table")
    args = parser.parse_args(argv)

    issues = check_paths(args.verses, args.complete)
    if issues:
        print(f"TCSB database integrity check FAILED: {len(issues)} issue(s)", file=sys.stderr)
        for issue in issues:
            print(f"- [{issue.code}] {issue.message}", file=sys.stderr)
        return 1

    print("TCSB database integrity check passed")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
