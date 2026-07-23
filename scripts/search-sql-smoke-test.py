#!/usr/bin/env python3
from __future__ import annotations

import os
import shutil
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

NORMALISED_SQL = "TRIM(REGEXP_REPLACE(REGEXP_REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(LOWER(verses.verseText), '<[^>]*>', ' '), '[{][hg][0-9]+[}]', ''), '&apos;', ' '), '&quot;', ' '), '&nbsp;', ' '), '-all', ''), '[^[:alnum:]]+', ' '), '[[:space:]]+', ' '))"


def db_client() -> str:
    for name in ("mariadb", "mysql"):
        found = shutil.which(name)
        if found:
            return found
    print("Neither mariadb nor mysql client is available.", file=sys.stderr)
    raise SystemExit(127)


def client_args() -> list[str]:
    db_name = os.environ.get("BSM_DB_NAME", "bible")
    db_user = os.environ.get("BSM_DB_USER", "root")
    db_password = os.environ.get("BSM_DB_PASSWORD", "")
    db_host = os.environ.get("BSM_DB_HOST", "")
    db_socket = os.environ.get("BSM_DB_SOCKET", "")
    args = [f"-u{db_user}"]
    if db_password:
        args.append(f"-p{db_password}")
    if db_socket:
        args.append(f"--socket={db_socket}")
    elif db_host:
        args.append(f"-h{db_host}")
    args.extend(["--batch", "--skip-column-names", db_name])
    return args


def run_query(client: str, args: list[str], phrase: str) -> str:
    sql = f"""
SELECT CONCAT(books.bookName, ' ', verses.chapter, ':', verses.verseNumber)
FROM verses INNER JOIN books ON verses.bookCode=books.bookCode
WHERE CONCAT(' ', {NORMALISED_SQL}, ' ') LIKE '% {phrase} %'
ORDER BY books.orderChristian, verses.chapter, verses.verseNumber;
"""
    result = subprocess.run([client, *args], input=sql, text=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, check=True)
    return result.stdout


def assert_contains_line(expected: str, actual: str, label: str) -> None:
    if expected in actual.splitlines():
        print(f"PASS: {label}")
        return
    print(f"FAIL: {label}", file=sys.stderr)
    print(f"Expected line: {expected}", file=sys.stderr)
    print("Actual output:", file=sys.stderr)
    print(actual, file=sys.stderr)
    raise SystemExit(1)


def main() -> int:
    client = db_client()
    args = client_args()
    probe = subprocess.run([client, *args, "-e", "SELECT 1"], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    if probe.returncode != 0:
        print(
            "Cannot connect to the BSM database. Start and seed the Docker/local MySQL or MariaDB environment first, or set BSM_DB_NAME, BSM_DB_USER, BSM_DB_PASSWORD, BSM_DB_HOST, or BSM_DB_SOCKET as needed.",
            file=sys.stderr,
        )
        return 1

    assert_contains_line("Genesis 1:3", run_query(client, args, "god said light"), "issue #178 exact-ish search ignores punctuation")
    assert_contains_line("Matthew 5:14", run_query(client, args, "you are the light of the world"), "ordinary you finds TCSB you-all phrase")
    assert_contains_line("Matthew 5:43", run_query(client, args, "selflessly love your neighbor"), "hyphenated TCSB words are searchable as ordinary words")
    print("All search SQL smoke tests passed")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
