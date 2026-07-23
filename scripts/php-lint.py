#!/usr/bin/env python3
from __future__ import annotations

import shutil
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def php_files() -> list[Path]:
    return sorted((ROOT / "site").rglob("*.php")) + [ROOT / "sqlCon.php"]


def main() -> int:
    php = shutil.which("php")
    if php:
        for file in php_files():
            subprocess.run([php, "-l", str(file.relative_to(ROOT))], cwd=ROOT, stdout=subprocess.DEVNULL, check=True)
            print(f"OK {file.relative_to(ROOT)}")
        return 0

    if not shutil.which("docker"):
        print("PHP is not installed and Docker is not available.", file=sys.stderr)
        return 127

    docker_script = """
set -e
find site -name "*.php" -print | while read -r file; do
  php -l "$file" >/dev/null
  echo "OK $file"
done
php -l sqlCon.php >/dev/null
echo "OK sqlCon.php"
""".strip()
    return subprocess.run(["docker", "compose", "run", "--rm", "web", "sh", "-lc", docker_script], cwd=ROOT).returncode


if __name__ == "__main__":
    raise SystemExit(main())
