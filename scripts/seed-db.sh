#!/usr/bin/env bash
set -euo pipefail

db_container=$(docker compose ps -q db)
if [ -z "$db_container" ]; then
  echo "Database container is not running. Start it with: docker compose up -d" >&2
  exit 1
fi

docker compose exec -T db sh -lc 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < ./database/bibleComplete.sql
