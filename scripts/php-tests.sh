#!/usr/bin/env bash
set -euo pipefail

if ! command -v php >/dev/null 2>&1; then
  echo "PHP is not installed or not on PATH." >&2
  exit 127
fi

php site/tests/SearchStrategyTest.php
php site/tests/ProcessStrongsTest.php >/dev/null

echo "All PHP smoke tests passed"
