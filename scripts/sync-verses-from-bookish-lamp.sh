#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'USAGE'
Usage: scripts/sync-verses-from-bookish-lamp.sh [--no-push] [--bl-root PATH]

Copies bookish-lamp/database/bibleVerses.sql and tcsbVersion.sql into
this BSM checkout, then rebuilds database/bibleComplete.sql using:

  bibleStart.sql + bibleCompletedVerses.sql + tcsbVersion.sql + bibleVerses.sql

If BL verse text differs from BSM before the copy, the script first bumps
the canonical TCSB text version in Bookish Lamp and commits it there.

By default, commits any resulting BSM changes and pushes develop.
Use --no-push to leave commits local.
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

current_tcsb_version() {
  local path=$1
  sed -nE "s/.*'text_version', '([^']+)'.*/\1/p" "$path" | head -n1
}

write_next_tcsb_version() {
  local path=$1
  local today sql_date current current_date current_seq next_seq next_version
  today=$(TZ=Europe/London date +%Y.%m.%d)
  sql_date=$(TZ=Europe/London date +%Y-%m-%d)
  current=$(current_tcsb_version "$path" || true)
  if [[ "$current" =~ ^TCSB-([0-9]{4}\.[0-9]{2}\.[0-9]{2})\.([0-9]+)$ && "${BASH_REMATCH[1]}" == "$today" ]]; then
    current_seq=${BASH_REMATCH[2]}
    next_seq=$((current_seq + 1))
  else
    next_seq=1
  fi
  next_version="TCSB-${today}.${next_seq}"
  cat > "$path" <<SQL
DROP TABLE IF EXISTS \`tcsb_text_metadata\`;
CREATE TABLE \`tcsb_text_metadata\` (
  \`metadataKey\` varchar(40) NOT NULL,
  \`metadataValue\` varchar(255) NOT NULL,
  PRIMARY KEY (\`metadataKey\`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO \`tcsb_text_metadata\` (\`metadataKey\`, \`metadataValue\`) VALUES ('text_version', '$next_version');
INSERT INTO \`tcsb_text_metadata\` (\`metadataKey\`, \`metadataValue\`) VALUES ('version_date', '$sql_date');
INSERT INTO \`tcsb_text_metadata\` (\`metadataKey\`, \`metadataValue\`) VALUES ('version_source', 'bookish-lamp/database/bibleVerses.sql');
SQL
  printf '%s\n' "$next_version"
}

require_file "$bl_root/database/bibleVerses.sql"
require_file "$bl_root/database/tcsbVersion.sql"
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

verses_changed=0
if ! cmp -s "$bl_root/database/bibleVerses.sql" "$bsm_root/database/bibleVerses.sql"; then
  verses_changed=1
fi

if [[ "$verses_changed" -eq 1 ]]; then
  version=$(write_next_tcsb_version "$bl_root/database/tcsbVersion.sql")
  git -C "$bl_root" config user.name "Ezra"
  git -C "$bl_root" config user.email "ezra@openclaw.local"
  git -C "$bl_root" add database/tcsbVersion.sql
  git -C "$bl_root" commit -m "Bump TCSB text version to $version"
  if [[ "$push_changes" -eq 1 ]]; then
    bl_branch=$(git -C "$bl_root" branch --show-current)
    git -C "$bl_root" push origin "$bl_branch"
  fi
fi

cp "$bl_root/database/tcsbVersion.sql" "$bsm_root/database/tcsbVersion.sql"
cp "$bl_root/database/bibleVerses.sql" "$bsm_root/database/bibleVerses.sql"
cat \
  "$bsm_root/database/bibleStart.sql" \
  "$bsm_root/database/bibleCompletedVerses.sql" \
  "$bsm_root/database/tcsbVersion.sql" \
  "$bsm_root/database/bibleVerses.sql" \
  > "$bsm_root/database/bibleComplete.sql"

if ! cmp -s "$bl_root/database/bibleVerses.sql" "$bsm_root/database/bibleVerses.sql"; then
  echo "Post-copy verification failed: BSM bibleVerses.sql does not match BL." >&2
  exit 1
fi

if ! cmp -s "$bl_root/database/tcsbVersion.sql" "$bsm_root/database/tcsbVersion.sql"; then
  echo "Post-copy verification failed: BSM tcsbVersion.sql does not match BL." >&2
  exit 1
fi

if [[ -z "$(git -C "$bsm_root" status --porcelain)" ]]; then
  echo "BSM already matches BL; bibleComplete.sql regenerated with no tracked changes."
  exit 0
fi

git -C "$bsm_root" config user.name "Ezra"
git -C "$bsm_root" config user.email "ezra@openclaw.local"
git -C "$bsm_root" add database/tcsbVersion.sql database/bibleVerses.sql database/bibleComplete.sql scripts/bump-tcsb-version.ps1 scripts/sync-verses-from-bookish-lamp.bat scripts/sync-verses-from-bookish-lamp.sh
git -C "$bsm_root" commit -m "Version TCSB text sync metadata"

if [[ "$push_changes" -eq 1 ]]; then
  git -C "$bsm_root" push origin develop
fi

git -C "$bsm_root" log -1 --format='%h %an <%ae> %s'
