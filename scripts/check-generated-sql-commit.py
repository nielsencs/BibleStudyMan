#!/usr/bin/env python3
"""Pre-commit guard for generated/copied BSM SQL artefacts.

These files should normally be changed only by the TCSB sync pipeline, not by
hand edits in BibleStudyMan:

- database/bibleComplete.sql
- database/bibleVerses.sql
- database/bibleStrongs.sql

Set ALLOW_GENERATED_SQL_COMMIT=1 for a deliberate exceptional commit.
"""
from __future__ import annotations

import os
import subprocess
import sys


GENERATED_SQL = {
    "database/bibleComplete.sql",
    "database/bibleVerses.sql",
    "database/bibleStrongs.sql",
}

SYNC_CONTEXT = {
    "database/tcsbMetadata.sql",
    "database/bibleSchema.sql",
    "database/bibleCompletedVerses.sql",
    "scripts/nightly-tcsb-revision-sync.py",
    "scripts/generate-verse-plain.py",
}


def staged_files() -> set[str]:
    result = subprocess.run(
        ["git", "diff", "--cached", "--name-only", "--diff-filter=ACMR"],
        check=False,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
    )
    if result.returncode != 0:
        print(result.stdout, end="")
        raise SystemExit(result.returncode)
    return {line.strip() for line in result.stdout.splitlines() if line.strip()}


def main() -> int:
    if os.environ.get("ALLOW_GENERATED_SQL_COMMIT") == "1":
        return 0

    staged = staged_files()
    generated = sorted(staged & GENERATED_SQL)
    if not generated:
        return 0

    if staged & SYNC_CONTEXT:
        return 0

    print("Refusing commit: generated BSM SQL files are staged without sync/source context.")
    print("")
    print("These files should not be edited directly in BibleStudyMan:")
    for path in generated:
        print(f"  - {path}")
    print("")
    print("Edit the source instead, then run the TCSB sync pipeline.")
    print("If this is a deliberate generated-data commit, rerun with:")
    print("  ALLOW_GENERATED_SQL_COMMIT=1 git commit ...")
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
