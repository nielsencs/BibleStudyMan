#!/usr/bin/env python3
from __future__ import annotations

import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def main() -> int:
    result = subprocess.run(["docker", "compose", "ps", "-q", "db"], cwd=ROOT, text=True, stdout=subprocess.PIPE, check=True)
    if not result.stdout.strip():
        print("Database container is not running. Start it with: docker compose up -d", file=sys.stderr)
        return 1

    sql = (ROOT / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
    return subprocess.run(
        ["docker", "compose", "exec", "-T", "db", "sh", "-lc", 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'],
        cwd=ROOT,
        input=sql,
        text=True,
    ).returncode


if __name__ == "__main__":
    raise SystemExit(main())
