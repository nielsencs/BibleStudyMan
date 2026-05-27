#!/usr/bin/env bash
set -euo pipefail

if [ ! -f .env ]; then
  cp .env.example .env
fi

docker compose down -v
docker compose up -d --build

echo "Database volume recreated. MySQL will import database/bibleComplete.sql on first startup."
echo "Run: python3 scripts/smoke-test.py"
