# Bookish Lamp to BibleStudyMan Sync

## 1. Purpose

Define the repeatable process for keeping BibleStudyMan's public Bible data aligned with the current TCSB text source. Normally rows come from Bookish Lamp. Books listed in `the-cleanslate-bible/data/tcsb_promoted_usfm_books.txt` are generated from canonical TCSB USFM instead, then merged into the BSM `bibleVerses.sql` output. The script must also rebuild BSM's compiled database upload file.

## 2. Background

- Bookish Lamp (BL) is the source-of-truth working repository for The CleanSlate Bible verse text.
- BibleStudyMan (BSM) is the public-facing website repository.
- BSM keeps its own `database/bibleVerses.sql` because the website database is deployed from the BSM repository.
- Therefore BL verse-text changes must be reflected in BSM promptly and exactly.

## 3. Scope

This note covers syncing:

- From: `bookish-lamp/database/bibleVerses.sql`
- To: `BibleStudyMan/database/bibleVerses.sql`

It also rebuilds and imports:

- `BibleStudyMan/database/bibleComplete.sql`

and then applies that compiled SQL to the configured MariaDB database.

from these logical components:

- `BibleStudyMan/database/bibleImportSettings.sql`
- `BibleStudyMan/database/tcsbMetadata.sql`
- `BibleStudyMan/database/bibleSchema.sql`
- `BibleStudyMan/database/bibleCompletedVerses.sql`
- `BibleStudyMan/database/bibleVerses.sql`

It also now feeds a downstream mobile SQLite distribution artefact:

- `BibleStudyMan/build/tcsb-mobile/tcsb-<revision>.sqlite`
- `BibleStudyMan/build/tcsb-mobile/tcsb-<revision>.sqlite.sha256`
- `BibleStudyMan/build/tcsb-mobile/latest.sqlite`
- `BibleStudyMan/build/tcsb-mobile/latest.json`

Those files are generated artefacts, not hand-edited source.

It does not cover:

- changing `bibleCompletedVerses.sql`
- paragraph data
- live-server / public website deployment after the artefacts are generated
- deciding whether a BL verse change is correct

## 4. Required Behaviour

### R1. Check both repositories first

Before syncing, the process must check both repositories:

1. In BL: `git status`, then if safe `git fetch --prune` and pull/inspect the relevant branch.
2. In BSM: `git status`, then if safe `git fetch --prune` and pull/inspect `develop`.

The sync must stop if either repository has unexpected local changes, unresolved conflicts, or suspicious remote divergence.

### R2. Build `bibleVerses.sql` from the active source per book

For ordinary books, Bookish Lamp remains the row source. For books listed in `the-cleanslate-bible/data/tcsb_promoted_usfm_books.txt`, canonical TCSB USFM is the row source: the sync generates SQL with `tools/plain_usfm_to_sql.py`, verifies the promoted book references match Bookish Lamp, and replaces only those book rows before generating BSM-only `versePlain` values.

### R3. Preserve row references and generated `versePlain`

The hybrid file is not byte-identical to Bookish Lamp once any book is promoted. The required invariant is now: same promoted-book reference set on both sides, non-promoted rows copied from Bookish Lamp, promoted rows generated from canonical USFM, then `versePlain` generated for every BSM row.

### R4. Rebuild `bibleComplete.sql`

After syncing `bibleVerses.sql` and copying the generated Strong's glossary SQL from `the-cleanslate-bible/exports/bibleStrongs.sql` to `BibleStudyMan/database/bibleStrongs.sql`, the process must reproduce the current database assembly order. `bibleComplete.sql` is a compiled upload/import artefact; its source parts remain split for readability:

```text
bibleImportSettings.sql + tcsbMetadata.sql + bibleSchema.sql + bibleStrongs.sql + bibleCompletedVerses.sql + bibleVerses.sql
```

On Unix-like systems this is equivalent to:

```sh
python3 scripts/nightly-tcsb-revision-sync.py --no-push --no-pull
```

`bibleComplete.sql` must put import settings first, then the `tcsb_text_metadata` table/current `text_revision` rows near the top before the main schema/data tables.

### R5. Import the rebuilt database into MariaDB

After `bibleComplete.sql` is rebuilt, the nightly sync imports it into MariaDB so the running BSM database matches the generated files. Configuration is read from environment variables:

```text
BSM_DB_NAME      database name; default bible
BSM_DB_USER      database user; default root
BSM_DB_PASSWORD  database password; passed via MYSQL_PWD, not command-line args
BSM_DB_HOST      host when using TCP
BSM_DB_PORT      optional TCP port
BSM_DB_SOCKET    socket path; takes precedence over host
BSM_DB_CLIENT    optional explicit mariadb/mysql client path
```

Use `--no-db-import` only for tests/dry runs where MariaDB should not be touched.

### R6. Commit and push the BSM update

If BSM changed, commit the update on BSM `develop` and push it.

Suggested commit message:

```text
Sync Bible verses from Bookish Lamp
```

Generated sync commits made by the nightly/file-generation scripts must use the automatic identity:

```text
TCSB Sync Bot <tcsb-sync@hermes.local>
```

Deliberate assistant-authored commits remain separate and use `Joel <joel@hermes.local>`.

### R7. Build mobile SQLite distribution artefacts

After a real TCSB text revision sync, build the mobile SQLite artefacts from BSM's synced database state. The mobile app may inspect/consume these artefacts, but BSM/TCSB owns producing them.

```sh
python3 scripts/build-tcsb-mobile-sqlite.py \
  --mobile-root ../tcsb-mobile \
  --out-dir build/tcsb-mobile
```

The generated `latest.json` must identify the revision, source commits, SQLite filename, checksum, size, verse count, and sample-verse verification. The SQLite itself must include a `tcsb_metadata` table copied from BSM's `tcsbMetadata.sql` plus mobile SQLite generation metadata.

### R8. Do nothing when already in sync

If the files already match and regenerating `bibleComplete.sql` produces no change, no BSM commit should be made. The process should simply report that BSM is already synced with BL.

## 5. Safety Rules

The sync must not proceed automatically if:

- BL has uncommitted changes that have not been intentionally included.
- BSM has unrelated local changes.
- BSM `develop` cannot be fast-forwarded safely.
- The source or target file is missing.
- The post-copy byte comparison fails.
- The diff appears to include secrets or unrelated database files.

In any of those cases, stop and ask Carl or investigate before committing.

## 6. Acceptance Criteria

A sync is successful when:

1. BL and BSM repository status have been checked.
2. Relevant remotes have been fetched/pulled or inspected safely.
3. `BibleStudyMan/database/bibleVerses.sql` has been rebuilt from Bookish Lamp plus any promoted canonical-USFM books and processed for BSM-only generated fields such as `versePlain`.
4. `BibleStudyMan/database/bibleStrongs.sql` has been copied from `the-cleanslate-bible/exports/bibleStrongs.sql`.
5. `BibleStudyMan/database/bibleComplete.sql` has been regenerated with `tcsbMetadata.sql` immediately after `bibleImportSettings.sql` and before `bibleSchema.sql`/`bibleStrongs.sql`/`bibleVerses.sql`.
6. The BSM change, if any, is committed and pushed to `origin/develop`.
7. Final report includes the BSM commit hash or says no commit was needed because the files already matched.

## 7. Local path overrides

The sync reads a real local file if present:

```text
local_paths.json
```

That file is ignored because it may contain Carl's actual machine paths. If it is missing, the sync falls back to the normal sibling-repo layout:

```text
../bookish-lamp
```

Carl's Windows layout is older and may use `D:/_WebSites` or another folder. Put that in `local_paths.json`, for example:

```json
{
  "bookish_lamp_repo": "D:/GitHub/bookish-lamp"
}
```

Precedence is:

1. explicit `--bl-root` argument;
2. ignored `local_paths.json`;
3. sibling fallback `../bookish-lamp`.

Back up real `local_paths.json` values in the private local-paths store, not in this public repo.

## 8. Future Automation

The implemented scripts are:

```text
scripts/sync-verses-from-bookish-lamp.py
scripts/sync-verses-from-bookish-lamp.sh
scripts/sync-verses-from-bookish-lamp.bat
```

The Python file is the implementation. The `.sh` and `.bat` files are only thin launchers. The `.bat` form is intentionally just:

```bat
py sync-verses-from-bookish-lamp.py
```

The old PowerShell long-version helper is obsolete; do not use `TCSB-YYYY.MM.DD.N` for this sync.
