#!/usr/bin/env python3
from __future__ import annotations

import shutil
import subprocess
import sys
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def run(cmd: list[str], *, stdout=None, stderr=None, check: bool = True) -> subprocess.CompletedProcess[str]:
    return subprocess.run(cmd, cwd=ROOT, text=True, stdout=stdout, stderr=stderr, check=check)


def main() -> int:
    env = ROOT / ".env"
    if not env.exists():
        shutil.copyfile(ROOT / ".env.example", env)
        print("Created .env from .env.example")

    run(["docker", "compose", "up", "-d", "--build"])

    print("Waiting for local site...")
    smoke_log = Path("/tmp/bsm-smoke.log")
    for _ in range(60):
        with smoke_log.open("w", encoding="utf-8") as log:
            result = subprocess.run(
                ["python3", "scripts/smoke-test.py"],
                cwd=ROOT,
                text=True,
                stdout=log,
                stderr=subprocess.STDOUT,
                check=False,
            )
        if result.returncode == 0:
            print(smoke_log.read_text(encoding="utf-8"), end="")
            print("BibleStudyMan is running at http://localhost:8080/site/")
            return 0
        time.sleep(2)

    if smoke_log.exists():
        print(smoke_log.read_text(encoding="utf-8"), end="")
    print("Site did not pass smoke tests yet. Try: docker compose logs --tail=100", file=sys.stderr)
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
