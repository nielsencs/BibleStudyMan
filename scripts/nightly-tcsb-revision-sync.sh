#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'USAGE'
Usage: scripts/nightly-tcsb-revision-sync.sh [options]

Nightly TCSB text-revision sync. Bumps the short TCSB text revision only when
one of the revision-gate files changed since the last recorded metadata:

  - bookish-lamp/database/bibleVerses.sql
  - BibleStudyMan/database/bibleStart.sql

Deliberately ignored for text-revision purposes:

  - bookish-lamp/database/translationToDo.txt
  - BibleStudyMan/database/bibleCompletedVerses.sql

Options:
  --bl-root PATH        bookish-lamp checkout path
  --bsm-root PATH       BibleStudyMan checkout path; defaults to this script's repo
  --no-push             commit locally but do not push
  --no-pull             do not fetch/pull first; useful for tests
  --revision YYMMDD     override generated revision; useful for tests
  --revision-date DATE  override generated revision date YYYY-MM-DD; useful for tests
  -h, --help            show this help
USAGE
}

push_changes=1
pull_changes=1
bl_root=""
bsm_root=""
revision=""
revision_date=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --bl-root)
      bl_root="${2:-}"
      shift 2
      ;;
    --bsm-root)
      bsm_root="${2:-}"
      shift 2
      ;;
    --no-push)
      push_changes=0
      shift
      ;;
    --no-pull)
      pull_changes=0
      shift
      ;;
    --revision)
      revision="${2:-}"
      shift 2
      ;;
    --revision-date)
      revision_date="${2:-}"
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
if [[ -z "$bsm_root" ]]; then
  bsm_root=$(cd -- "$script_dir/.." && pwd)
fi
if [[ -z "$bl_root" ]]; then
  bl_root=$(cd -- "$bsm_root/../bookish-lamp" 2>/dev/null && pwd || true)
fi

require_file() {
  local path=$1
  if [[ ! -f "$path" ]]; then
    echo "Required file missing: $path" >&2
    exit 1
  fi
}

ensure_git_repo() {
  local repo=$1 label=$2
  if [[ ! -d "$repo/.git" ]]; then
    echo "$label is not a git checkout: $repo" >&2
    exit 1
  fi
}

ensure_clean_repo() {
  local repo=$1 label=$2
  if [[ -n "$(git -C "$repo" status --porcelain)" ]]; then
    echo "$label has uncommitted changes; stopping before sync." >&2
    git -C "$repo" status --short >&2
    exit 1
  fi
}

pull_ff_current_branch() {
  local repo=$1 label=$2 branch
  branch=$(git -C "$repo" branch --show-current)
  if [[ -z "$branch" ]]; then
    echo "$label is not on a branch; stopping." >&2
    exit 1
  fi
  git -C "$repo" fetch --prune origin
  git -C "$repo" pull --ff-only origin "$branch"
}

metadata_value() {
  local path=$1 key=$2
  sed -nE "s/.*\('$key', '([^']+)'\).*/\1/p" "$path" | head -n1
}

latest_commit_for_file() {
  local repo=$1 file=$2
  git -C "$repo" log -n 1 --format=%H -- "$file"
}

sql_insert() {
  local key=$1 value=$2
  printf "INSERT INTO \`tcsb_text_metadata\` (\`metadataKey\`, \`metadataValue\`) VALUES ('%s', '%s');\n" "$key" "$value"
}

write_metadata() {
  local path=$1 bl_commit=$2 bsm_commit=$3 generated_at=$4
  cat > "$path" <<SQL
DROP TABLE IF EXISTS \`tcsb_text_metadata\`;
CREATE TABLE \`tcsb_text_metadata\` (
  \`metadataKey\` varchar(40) NOT NULL,
  \`metadataValue\` varchar(255) NOT NULL,
  PRIMARY KEY (\`metadataKey\`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

SQL
  sql_insert "text_revision" "$revision" >> "$path"
  sql_insert "text_version" "$revision" >> "$path"
  sql_insert "text_revision_date" "$revision_date" >> "$path"
  sql_insert "text_source_repo" "bookish-lamp" >> "$path"
  sql_insert "text_source_branch" "$(git -C "$bl_root" branch --show-current)" >> "$path"
  sql_insert "text_source_file" "database/bibleVerses.sql" >> "$path"
  sql_insert "bl_bible_verses_commit" "$bl_commit" >> "$path"
  sql_insert "bsm_bible_start_commit" "$bsm_commit" >> "$path"
  sql_insert "generated_at" "$generated_at" >> "$path"
}

commit_if_changed() {
  local repo=$1 message=$2
  shift 2
  git -C "$repo" add "$@"
  if git -C "$repo" diff --cached --quiet; then
    return 1
  fi
  git -C "$repo" config user.name "Ezra H"
  git -C "$repo" config user.email "ezra-h@hermes.local"
  git -C "$repo" commit -m "$message"
  return 0
}

ensure_git_repo "$bl_root" "Bookish Lamp"
ensure_git_repo "$bsm_root" "BibleStudyMan"
require_file "$bl_root/database/bibleVerses.sql"
require_file "$bl_root/database/tcsbMetadata.sql"
require_file "$bsm_root/database/bibleStart.sql"
require_file "$bsm_root/database/bibleCompletedVerses.sql"
require_file "$bsm_root/database/bibleVerses.sql"
require_file "$bsm_root/database/tcsbMetadata.sql"

bsm_branch=$(git -C "$bsm_root" branch --show-current)
if [[ "$bsm_branch" != "develop" ]]; then
  echo "BSM must be on develop; currently on $bsm_branch" >&2
  exit 1
fi

ensure_clean_repo "$bl_root" "Bookish Lamp"
ensure_clean_repo "$bsm_root" "BibleStudyMan"

if [[ "$pull_changes" -eq 1 ]]; then
  pull_ff_current_branch "$bl_root" "Bookish Lamp"
  pull_ff_current_branch "$bsm_root" "BibleStudyMan"
fi

bl_commit=$(latest_commit_for_file "$bl_root" "database/bibleVerses.sql")
bsm_commit=$(latest_commit_for_file "$bsm_root" "database/bibleStart.sql")
recorded_bl_commit=$(metadata_value "$bsm_root/database/tcsbMetadata.sql" "bl_bible_verses_commit" || true)
recorded_bsm_commit=$(metadata_value "$bsm_root/database/tcsbMetadata.sql" "bsm_bible_start_commit" || true)

if [[ "$bl_commit" == "$recorded_bl_commit" && "$bsm_commit" == "$recorded_bsm_commit" ]]; then
  echo "No TCSB text revision change: tracked source commits already recorded."
  exit 0
fi

if [[ -z "$revision" ]]; then
  revision=$(TZ=Europe/London date +%y%m%d)
fi
if [[ -z "$revision_date" ]]; then
  revision_date=$(TZ=Europe/London date +%Y-%m-%d)
fi
generated_at=$(TZ=Europe/London date +%Y-%m-%dT%H:%M:%S%z)
generated_at="${generated_at:0:22}:${generated_at:22:2}"

write_metadata "$bl_root/database/tcsbMetadata.sql" "$bl_commit" "$bsm_commit" "$generated_at"
bl_commit_created=0
if commit_if_changed "$bl_root" "Set TCSB text revision $revision" database/tcsbMetadata.sql; then
  bl_commit_created=1
  if [[ "$push_changes" -eq 1 ]]; then
    git -C "$bl_root" push origin "$(git -C "$bl_root" branch --show-current)"
  fi
fi

cp "$bl_root/database/tcsbMetadata.sql" "$bsm_root/database/tcsbMetadata.sql"
cp "$bl_root/database/bibleVerses.sql" "$bsm_root/database/bibleVerses.sql"
{
  cat "$bsm_root/database/bibleStart.sql"
  printf '\n'
  cat "$bsm_root/database/bibleCompletedVerses.sql"
  printf '\n'
  cat "$bsm_root/database/tcsbMetadata.sql"
  printf '\n'
  cat "$bsm_root/database/bibleVerses.sql"
} > "$bsm_root/database/bibleComplete.sql"

cmp -s "$bl_root/database/bibleVerses.sql" "$bsm_root/database/bibleVerses.sql" || {
  echo "Post-copy verification failed: BSM bibleVerses.sql does not match BL." >&2
  exit 1
}
cmp -s "$bl_root/database/tcsbMetadata.sql" "$bsm_root/database/tcsbMetadata.sql" || {
  echo "Post-copy verification failed: BSM tcsbMetadata.sql does not match BL." >&2
  exit 1
}

grep -q "('text_revision', '$revision')" "$bsm_root/database/bibleComplete.sql" || {
  echo "Post-copy verification failed: bibleComplete.sql lacks text revision $revision." >&2
  exit 1
}

bsm_commit_created=0
if commit_if_changed "$bsm_root" "Sync TCSB text revision $revision" database/tcsbMetadata.sql database/bibleVerses.sql database/bibleComplete.sql scripts/nightly-tcsb-revision-sync.sh tests/test_nightly_tcsb_revision_sync.py; then
  bsm_commit_created=1
  if [[ "$push_changes" -eq 1 ]]; then
    git -C "$bsm_root" push origin develop
  fi
fi

echo "Synced TCSB text revision $revision"
echo "BL bibleVerses commit: $bl_commit"
echo "BSM bibleStart commit: $bsm_commit"
echo "Bookish Lamp metadata commit created: $bl_commit_created"
echo "BibleStudyMan sync commit created: $bsm_commit_created"
