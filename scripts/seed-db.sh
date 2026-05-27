#!/usr/bin/env bash
set -euo pipefail

compose_project=${COMPOSE_PROJECT_NAME:-biblestudyman}
db_container=$(docker compose ps -q db)
if [ -z "$db_container" ]; then
  echo "Database container is not running. Start it with: docker compose up -d" >&2
  exit 1
fi

docker exec -i "$db_container" mysql -uroot -p"${MYSQL_ROOT_PASSWORD:-myrootpass}" "${MYSQL_DATABASE:-bible}" < ./database/bibleComplete.sql
