#!/usr/bin/env bash
set -euo pipefail

files=$(find site -name '*.php' -print; printf '%s\n' sqlCon.php)

if command -v php >/dev/null 2>&1; then
  while IFS= read -r file; do
    [ -n "$file" ] || continue
    php -l "$file" >/dev/null
    echo "OK $file"
  done <<< "$files"
else
  if ! command -v docker >/dev/null 2>&1; then
    echo "PHP is not installed and Docker is not available." >&2
    exit 127
  fi
  docker compose run --rm web sh -lc '
    set -e
    find site -name "*.php" -print | while read -r file; do
      php -l "$file" >/dev/null
      echo "OK $file"
    done
    php -l sqlCon.php >/dev/null
    echo "OK sqlCon.php"
  '
fi
