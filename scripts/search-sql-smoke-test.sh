#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${BSM_DB_NAME:-bible}"
DB_USER="${BSM_DB_USER:-root}"
DB_PASSWORD="${BSM_DB_PASSWORD:-}"
DB_HOST="${BSM_DB_HOST:-}"
DB_SOCKET="${BSM_DB_SOCKET:-}"

if command -v mariadb >/dev/null 2>&1; then
  DB_CLIENT="mariadb"
elif command -v mysql >/dev/null 2>&1; then
  DB_CLIENT="mysql"
else
  echo "Neither mariadb nor mysql client is available." >&2
  exit 127
fi

client_args=("-u${DB_USER}")
if [ -n "$DB_PASSWORD" ]; then
  client_args+=("-p${DB_PASSWORD}")
fi
if [ -n "$DB_SOCKET" ]; then
  client_args+=("--socket=${DB_SOCKET}")
elif [ -n "$DB_HOST" ]; then
  client_args+=("-h${DB_HOST}")
fi
client_args+=("--batch" "--skip-column-names" "$DB_NAME")

normalised_sql="TRIM(REGEXP_REPLACE(REGEXP_REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(LOWER(verses.verseText), '<[^>]*>', ' '), '[{][hg][0-9]+[}]', ''), '&apos;', ' '), '&quot;', ' '), '&nbsp;', ' '), '-all', ''), '[^[:alnum:]]+', ' '), '[[:space:]]+', ' '))"

run_query() {
  local phrase="$1"
  "$DB_CLIENT" "${client_args[@]}" <<SQL
SELECT CONCAT(books.bookName, ' ', verses.chapter, ':', verses.verseNumber)
FROM verses INNER JOIN books ON verses.bookCode=books.bookCode
WHERE CONCAT(' ', $normalised_sql, ' ') LIKE '% $phrase %'
ORDER BY books.orderChristian, verses.chapter, verses.verseNumber;
SQL
}

assert_contains_line() {
  local expected="$1"
  local actual="$2"
  local label="$3"
  if grep -Fxq "$expected" <<< "$actual"; then
    echo "PASS: $label"
  else
    echo "FAIL: $label" >&2
    echo "Expected line: $expected" >&2
    echo "Actual output:" >&2
    echo "$actual" >&2
    exit 1
  fi
}

god_light_results="$(run_query "god said light")"
assert_contains_line "Genesis 1:3" "$god_light_results" "issue #178 exact-ish search ignores punctuation"

you_light_results="$(run_query "you are the light of the world")"
assert_contains_line "Matthew 5:14" "$you_light_results" "ordinary you finds TCSB you-all phrase"

selfless_results="$(run_query "selflessly love your neighbor")"
assert_contains_line "Matthew 5:43" "$selfless_results" "hyphenated TCSB words are searchable as ordinary words"

echo "All search SQL smoke tests passed"
