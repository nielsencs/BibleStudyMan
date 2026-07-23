#!/usr/bin/env python3
from __future__ import annotations

import shutil
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def main() -> int:
    env = ROOT / ".env"
    if not env.exists():
        shutil.copyfile(ROOT / ".env.example", env)

    subprocess.run(["docker", "compose", "down", "-v"], cwd=ROOT, check=True)
    subprocess.run(["docker", "compose", "up", "-d", "--build"], cwd=ROOT, check=True)
    print("Database volume recreated. MySQL will import database/bibleComplete.sql on first startup.")
    print("Run: python3 scripts/smoke-test.py")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
