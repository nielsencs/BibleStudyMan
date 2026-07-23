#!/usr/bin/env bash
exec python3 "$(dirname "$0")/seed-db.py" "$@"
