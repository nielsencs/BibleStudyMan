#!/usr/bin/env python3
from __future__ import annotations

import argparse
import html
import re
from pathlib import Path

VERSE_INSERT_RE = re.compile(
    r"^INSERT\s+INTO\s+`?verses`?\s*"
    r"\(`?bookCode`?,\s*`?chapter`?,\s*`?verseNumber`?,\s*`?verseText`?\)\s*"
    r"VALUES\s*\('([A-Z0-9]{3})',\s*(\d+),\s*(\d+),\s*'(.*)'\);\s*$",
    re.IGNORECASE,
)


def sql_unescape(value: str) -> str:
    return value.replace(r"\'", "'").replace(r'\"', '"')


def sql_escape(value: str) -> str:
    return value.replace("\\", "\\\\").replace("'", r"\'")


def verse_plain(source: str) -> str:
    text = sql_unescape(source)
    text = html.unescape(text)
    text = re.sub(r"<br\s*/?>", " ", text, flags=re.IGNORECASE)
    text = re.sub(r"</?p>", "", text, flags=re.IGNORECASE)
    text = re.sub(r"<[^>]+>", "", text)
    text = re.sub(r"\{[HG]\d{4}\}", "", text)
    text = re.sub(r"[ \t\r\n]+", " ", text).strip()
    return text


def add_verse_plain_column(sql: str) -> str:
    if "`versePlain`" in sql:
        return sql
    return sql.replace("  `verseText` text NOT NULL,\n", "  `verseText` text NOT NULL,\n  `versePlain` text NOT NULL,\n", 1)


def rewrite_insert(line: str) -> str:
    match = VERSE_INSERT_RE.match(line)
    if not match:
        return line
    book_code, chapter, verse_number, verse_text = match.groups()
    plain = sql_escape(verse_plain(verse_text))
    return (
        "INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`, `versePlain`) "
        f"VALUES ('{book_code}', {int(chapter):3d}, {int(verse_number):3d}, '{verse_text}', '{plain}');"
    )


def rewrite_text(sql: str) -> str:
    if "`verseText`, `versePlain`" in sql:
        return sql
    sql = add_verse_plain_column(sql)
    return "\n".join(rewrite_insert(line) for line in sql.splitlines()) + ("\n" if sql.endswith("\n") else "")


def rewrite_file(path: Path) -> bool:
    before = path.read_text(encoding="utf-8")
    after = rewrite_text(before)
    if after == before:
        return False
    path.write_text(after, encoding="utf-8")
    return True


def main(argv: list[str] | None = None) -> int:
    root = Path(__file__).resolve().parents[1]
    parser = argparse.ArgumentParser(description="Generate verses.versePlain values in bibleVerses.sql from verses.verseText.")
    parser.add_argument("path", nargs="?", type=Path, default=root / "database" / "bibleVerses.sql")
    args = parser.parse_args(argv)
    changed = rewrite_file(args.path)
    print(f"{'Updated' if changed else 'Already up to date'}: {args.path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
