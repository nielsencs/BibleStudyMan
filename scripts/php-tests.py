#!/usr/bin/env python3
from __future__ import annotations

import shutil
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def main() -> int:
    php = shutil.which("php")
    if not php:
        print("PHP is not installed or not on PATH.", file=sys.stderr)
        return 127

    tests = [
        [php, "site/tests/SearchStrategyTest.php"],
        [php, "site/tests/SearchIssueRegressionTest.php"],
        [php, "site/tests/ProcessStrongsTest.php"],
    ]
    for i, cmd in enumerate(tests):
        stdout = subprocess.DEVNULL if i == 2 else None
        subprocess.run(cmd, cwd=ROOT, stdout=stdout, check=True)
    print("All PHP smoke tests passed")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
