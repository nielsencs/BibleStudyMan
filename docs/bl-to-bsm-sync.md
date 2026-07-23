# Bookish Lamp to BibleStudyMan Sync

## 1. Purpose

Define the repeatable process for keeping BibleStudyMan's public Bible data aligned with Bookish Lamp. When `bookish-lamp/database/bibleVerses.sql` changes, the same file should be copied directly to `BibleStudyMan/database/bibleVerses.sql` so the public BibleStudyMan site data stays aligned with the canonical CleanSlate Bible text. The script must also rebuild BSM's compiled database upload file.

## 2. Background

- Bookish Lamp (BL) is the source-of-truth working repository for The CleanSlate Bible verse text.
- BibleStudyMan (BSM) is the public-facing website repository.
- BSM keeps its own `database/bibleVerses.sql` because the website database is deployed from the BSM repository.
- Therefore BL verse-text changes must be reflected in BSM promptly and exactly.

## 3. Scope

This note covers syncing only:

- From: `bookish-lamp/database/bibleVerses.sql`
- To: `BibleStudyMan/database/bibleVerses.sql`

It also rebuilds:

- `BibleStudyMan/database/bibleComplete.sql`

from these logical components:

- `BibleStudyMan/database/bibleImportSettings.sql`
- `BibleStudyMan/database/tcsbMetadata.sql`
- `BibleStudyMan/database/bibleSchema.sql`
- `BibleStudyMan/database/bibleCompletedVerses.sql`
- `BibleStudyMan/database/bibleVerses.sql`

It does not cover:

- changing `bibleCompletedVerses.sql`
- paragraph data
- generated mobile/app databases
- live-server deployment after the BSM repository is updated
- deciding whether a BL verse change is correct

## 4. Required Behaviour

### R1. Check both repositories first

Before syncing, the process must check both repositories:

1. In BL: `git status`, then if safe `git fetch --prune` and pull/inspect the relevant branch.
2. In BSM: `git status`, then if safe `git fetch --prune` and pull/inspect `develop`.

The sync must stop if either repository has unexpected local changes, unresolved conflicts, or suspicious remote divergence.

### R2. Treat BL as authoritative for this file

For `database/bibleVerses.sql`, BL is authoritative. BSM's copy should be replaced with BL's copy, not manually merged line-by-line, unless Carl explicitly asks for investigation.

### R3. Preserve exact file content

After copying, the process must verify that the two files are byte-for-byte identical.

Example check:

```sh
cmp -s ../bookish-lamp/database/bibleVerses.sql database/bibleVerses.sql
```

### R4. Rebuild `bibleComplete.sql`

After syncing `bibleVerses.sql`, the process must reproduce the current database assembly order. `bibleComplete.sql` is a compiled upload/import artefact; its source parts remain split for readability:

```text
bibleImportSettings.sql + tcsbMetadata.sql + bibleSchema.sql + bibleCompletedVerses.sql + bibleVerses.sql
```

On Unix-like systems this is equivalent to:

```sh
python3 scripts/nightly-tcsb-revision-sync.py --no-push --no-pull
```

`bibleComplete.sql` must put import settings first, then the `tcsb_text_metadata` table/current `text_revision` rows near the top before the main schema/data tables.

### R5. Commit and push the BSM update

If BSM changed, commit the update on BSM `develop` and push it.

Suggested commit message:

```text
Sync Bible verses from Bookish Lamp
```

Assistant-authored commits must use:

```text
Ezra H <ezra-h@hermes.local>
```

### R6. Do nothing when already in sync

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
3. `BibleStudyMan/database/bibleVerses.sql` exactly matches `bookish-lamp/database/bibleVerses.sql`.
4. `BibleStudyMan/database/bibleComplete.sql` has been regenerated with `tcsbMetadata.sql` immediately after `bibleImportSettings.sql` and before `bibleSchema.sql`/`bibleVerses.sql`.
5. The BSM change, if any, is committed and pushed to `origin/develop`.
6. Final report includes the BSM commit hash or says no commit was needed because the files already matched.

## 7. Future Automation

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
