#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'USAGE'
Usage: scripts/sync-verses-from-bookish-lamp.sh [--no-push] [--bl-root PATH]

Copies bookish-lamp/database/bibleVerses.sql into this BSM checkout,
then rebuilds database/bibleComplete.sql using the same order as
Database/concatenate.bat:

  bibleStart.sql + bibleCompletedVerses.sql + bibleVerses.sql

By default, commits any resulting BSM changes and pushes develop.
Use --no-push to leave the commit local.
USAGE
}

push_changes=1
bl_root=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --no-push)
      push_changes=0
      shift
      ;;
    --bl-root)
      bl_root="${2:-}"
      if [[ -z "$bl_root" ]]; then
        echo "Missing path after --bl-root" >&2
        exit 2
      fi
      shift 2
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage >&2
      exit 2
      ;;
  esac
done

script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
bsm_root=$(cd -- "$script_dir/.." && pwd)

if [[ -z "$bl_root" ]]; then
  bl_root=$(cd -- "$bsm_root/../bookish-lamp" 2>/dev/null && pwd || true)
fi

if [[ -z "$bl_root" || ! -d "$bl_root/.git" ]]; then
  echo "Cannot find bookish-lamp checkout. Use --bl-root PATH." >&2
  exit 1
fi

require_file() {
  local path=$1
  if [[ ! -f "$path" ]]; then
    echo "Required file missing: $path" >&2
    exit 1
  fi
}

ensure_clean_repo() {
  local repo=$1
  local label=$2
  if [[ -n "$(git -C "$repo" status --porcelain)" ]]; then
    echo "$label has uncommitted changes; stopping before sync." >&2
    git -C "$repo" status --short
    exit 1
  fi
}

pull_ff_current_branch() {
  local repo=$1
  local label=$2
  local branch
  branch=$(git -C "$repo" branch --show-current)
  if [[ -z "$branch" ]]; then
    echo "$label is not on a branch; stopping." >&2
    exit 1
  fi
  git -C "$repo" fetch --prune origin
  git -C "$repo" pull --ff-only origin "$branch"
}

require_file "$bl_root/database/bibleVerses.sql"
require_file "$bsm_root/database/bibleStart.sql"
require_file "$bsm_root/database/bibleCompletedVerses.sql"
require_file "$bsm_root/database/bibleVerses.sql"

bsm_branch=$(git -C "$bsm_root" branch --show-current)
if [[ "$bsm_branch" != "develop" ]]; then
  echo "BSM must be on develop; currently on $bsm_branch" >&2
  exit 1
fi

ensure_clean_repo "$bl_root" "Bookish Lamp"
ensure_clean_repo "$bsm_root" "BibleStudyMan"

pull_ff_current_branch "$bl_root" "Bookish Lamp"
pull_ff_current_branch "$bsm_root" "BibleStudyMan"

cp "$bl_root/database/bibleVerses.sql" "$bsm_root/database/bibleVerses.sql"
cat \
  "$bsm_root/database/bibleStart.sql" \
  "$bsm_root/database/bibleCompletedVerses.sql" \
  "$bsm_root/database/bibleVerses.sql" \
  > "$bsm_root/database/bibleComplete.sql"

if ! cmp -s "$bl_root/database/bibleVerses.sql" "$bsm_root/database/bibleVerses.sql"; then
  echo "Post-copy verification failed: BSM bibleVerses.sql does not match BL." >&2
  exit 1
fi

if [[ -z "$(git -C "$bsm_root" status --porcelain)" ]]; then
  echo "BSM already matches BL; bibleComplete.sql regenerated with no tracked changes."
  exit 0
fi

git -C "$bsm_root" config user.name "Ezra"
git -C "$bsm_root" config user.email "ezra@openclaw.local"
git -C "$bsm_root" add database/bibleVerses.sql database/bibleComplete.sql
git -C "$bsm_root" commit -m "Sync Bible verses from Bookish Lamp"

if [[ "$push_changes" -eq 1 ]]; then
  git -C "$bsm_root" push origin develop
fi

git -C "$bsm_root" log -1 --format='%h %an <%ae> %s'
