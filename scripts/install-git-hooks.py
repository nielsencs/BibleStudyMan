#!/usr/bin/env python3
"""Install this repo's tracked Git hooks."""
from __future__ import annotations

import subprocess
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
HOOKS = ROOT / ".githooks"


def main() -> int:
    if not HOOKS.is_dir():
        raise SystemExit(f"Hook directory missing: {HOOKS}")
    subprocess.run(["git", "config", "core.hooksPath", str(HOOKS.relative_to(ROOT))], cwd=ROOT, check=True)
    print(f"Installed Git hooks from {HOOKS.relative_to(ROOT)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
