#!/usr/bin/env bash
set -euo pipefail

if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

docker compose up -d --build

echo "Waiting for local site..."
for i in {1..60}; do
  if python3 scripts/smoke-test.py >/tmp/bsm-smoke.log 2>&1; then
    cat /tmp/bsm-smoke.log
    echo "BibleStudyMan is running at http://localhost:8080/site/"
    exit 0
  fi
  sleep 2
done

cat /tmp/bsm-smoke.log || true
echo "Site did not pass smoke tests yet. Try: docker compose logs --tail=100" >&2
exit 1
