#!/usr/bin/env python3
"""Compatibility entrypoint for BL -> BSM TCSB sync.

The old shell implementation maintained the long TCSB-YYYY.MM.DD.N version.
The canonical implementation is now nightly-tcsb-revision-sync.py, which uses
short YYMMDD text revisions and only bumps when the agreed source gates changed.
"""
from __future__ import annotations

import subprocess
import sys
from pathlib import Path

SCRIPT = Path(__file__).resolve().with_name("nightly-tcsb-revision-sync.py")


def main() -> int:
    return subprocess.run(["python3", str(SCRIPT), *sys.argv[1:]]).returncode


if __name__ == "__main__":
    raise SystemExit(main())
