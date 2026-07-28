# Script inventory

This repo keeps Python implementations plus thin launchers for Carl-friendly Windows/macOS/Linux use.

## Keep as implementations

| Script | Purpose | Notes |
|---|---|---|
| `scripts/nightly-tcsb-revision-sync.py` | Canonical Bookish Lamp → BibleStudyMan TCSB text sync. | Copies BL verses, generates BSM `versePlain`, rebuilds `bibleComplete.sql`, commits/pushes when enabled. Supports `local_paths.json` for non-sibling repo layouts. |
| `scripts/generate-verse-plain.py` | Regenerates `versePlain` in `database/bibleVerses.sql`. | Idempotent. Run after any raw BL verse copy. |
| `scripts/check-tcsb-database-integrity.py` | Safety checks for TCSB SQL files. | Accepts old 4-column BL rows and new 5-column BSM rows. |
| `scripts/build-tcsb-mobile-sqlite.py` | Builds mobile SQLite distribution artefacts. | Assumes sibling `../tcsb-mobile` unless `--mobile-root` is supplied. |
| `scripts/local-up.py` | Starts Docker local site and smoke-tests it. | `.ps1` / `.sh` wrappers are convenience launchers. |
| `scripts/reset-local-db.py` | Recreates local Docker DB volume from `bibleComplete.sql`. | `.ps1` / `.sh` wrappers are convenience launchers. |
| `scripts/seed-db.py` | Re-imports DB into an already-running container. | `.sh` wrapper is a convenience launcher. |
| `scripts/php-lint.py` | PHP syntax checks. | `.sh` wrapper is a convenience launcher. |
| `scripts/php-tests.py` | PHP smoke/regression tests. | `.sh` wrapper is a convenience launcher. |
| `scripts/search-sql-smoke-test.py` | DB-backed search SQL smoke tests. | Requires running seeded DB and `BSM_DB_*` env vars. |
| `scripts/smoke-test.py` | HTTP smoke tests against local site. | Used by local-up. |

## Compatibility launchers

| Script | Status |
|---|---|
| `scripts/sync-verses-from-bookish-lamp.py` | Compatibility entrypoint; delegates to `nightly-tcsb-revision-sync.py`. |
| `scripts/sync-verses-from-bookish-lamp.sh` | Thin launcher. |
| `scripts/sync-verses-from-bookish-lamp.bat` | Thin Windows launcher. |

## Local paths

Do not hard-code Carl's machine paths into scripts.

The real local file is:

```text
local_paths.json
```

It is gitignored. If missing, scripts fall back to committed defaults:

```text
local_paths.defaults.json
```

The defaults file keeps key names and normal sibling-path values recoverable in git. If Carl needs actual Windows paths preserved too, back up `local_paths.json` somewhere private; do not put private/local machine paths in public scripts.
