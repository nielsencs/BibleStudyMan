#!/usr/bin/env python3
"""Build versioned TCSB mobile SQLite distribution artefacts.

This is intentionally an upstream BibleStudyMan/TCSB pipeline script: tcsb-mobile
provides the SQLite converter and consumes the output, but BSM/TCSB owns deciding
what the latest text revision is.
"""
from __future__ import annotations

import argparse
import hashlib
import json
import re
import shutil
import sqlite3
import subprocess
import sys
import tempfile
from datetime import datetime, timezone
from pathlib import Path


SCRIPT_DIR = Path(__file__).resolve().parent
DEFAULT_BSM_ROOT = SCRIPT_DIR.parent


def run(cmd: list[str], cwd: Path | None = None) -> subprocess.CompletedProcess[str]:
    result = subprocess.run(
        cmd,
        cwd=cwd,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        check=False,
    )
    if result.returncode != 0:
        raise SystemExit(result.stdout.rstrip() or f"command failed: {' '.join(cmd)}")
    return result


def require_file(path: Path) -> None:
    if not path.is_file():
        raise SystemExit(f"Required file missing: {path}")


def metadata_value(metadata_sql: Path, key: str) -> str:
    pattern = re.compile(rf"\('{re.escape(key)}', '((?:''|[^'])*)'\)", re.DOTALL)
    match = pattern.search(metadata_sql.read_text(encoding="utf-8"))
    if match:
        return match.group(1).replace("''", "'")
    return ""


def read_bsm_metadata(metadata_sql: Path) -> dict[str, str]:
    keys = [
        "text_revision",
        "text_version",
        "text_revision_date",
        "text_source_repo",
        "text_source_branch",
        "text_source_file",
        "bl_bible_verses_commit",
        "bsm_bible_schema_commit",
        "generated_at",
        "tcsb_disclaimer_html",
        "tcsb_disclaimer_text",
    ]
    return {key: metadata_value(metadata_sql, key) for key in keys if metadata_value(metadata_sql, key)}


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def verify_and_enrich_sqlite(sqlite_path: Path, metadata: dict[str, str]) -> dict[str, object]:
    conn = sqlite3.connect(sqlite_path)
    try:
        integrity = conn.execute("PRAGMA integrity_check").fetchone()[0]
        if integrity != "ok":
            raise SystemExit(f"SQLite integrity check failed: {integrity}")
        tables = {row[0] for row in conn.execute("SELECT name FROM sqlite_master WHERE type='table'")}
        required = {"books", "verses", "plan_days", "plan_readings", "strongs"}
        missing = sorted(required - tables)
        if missing:
            raise SystemExit(f"Generated SQLite missing required table(s): {', '.join(missing)}")
        verse_count = int(conn.execute("SELECT COUNT(*) FROM verses").fetchone()[0])
        if verse_count < 30000:
            raise SystemExit(f"Generated SQLite verse count looks too low: {verse_count}")
        sample_refs = {
            "GEN 1:1": conn.execute(
                "SELECT verse_text_plain FROM verses WHERE book_code='GEN' AND chapter=1 AND verse_number=1"
            ).fetchone(),
            "LUK 8:1": conn.execute(
                "SELECT verse_text_plain FROM verses WHERE book_code='LUK' AND chapter=8 AND verse_number=1"
            ).fetchone(),
        }
        missing_samples = [ref for ref, value in sample_refs.items() if not value or not value[0]]
        if missing_samples:
            raise SystemExit(f"Generated SQLite missing sample verse(s): {', '.join(missing_samples)}")
        conn.execute(
            "CREATE TABLE IF NOT EXISTS tcsb_metadata (metadata_key TEXT PRIMARY KEY, metadata_value TEXT NOT NULL)"
        )
        enriched = dict(metadata)
        revision = enriched.get("text_revision", "")
        enriched["mobile_sqlite_revision"] = revision
        enriched["mobile_sqlite_generated_at"] = datetime.now(timezone.utc).isoformat(timespec="seconds")
        for key, value in enriched.items():
            conn.execute(
                "INSERT OR REPLACE INTO tcsb_metadata(metadata_key, metadata_value) VALUES (?, ?)",
                (key, value),
            )
        conn.commit()
        return {
            "integrity_check": integrity,
            "verse_count": verse_count,
            "sample_verses": {ref: value[0] for ref, value in sample_refs.items() if value},
        }
    finally:
        conn.close()


def build_artifacts(*, bsm_root: Path, mobile_root: Path, out_dir: Path) -> dict[str, object]:
    bsm_root = bsm_root.resolve()
    mobile_root = mobile_root.resolve()
    out_dir = out_dir.resolve()
    metadata_sql = bsm_root / "database" / "tcsbMetadata.sql"
    verses_sql = bsm_root / "database" / "bibleVerses.sql"
    complete_sql = bsm_root / "database" / "bibleComplete.sql"
    converter = mobile_root / "scripts" / "convert_mysql_dumps_to_sqlite.py"
    for path in [metadata_sql, verses_sql, complete_sql, converter]:
        require_file(path)

    metadata = read_bsm_metadata(metadata_sql)
    revision = metadata.get("text_revision")
    if not revision:
        raise SystemExit(f"Could not read text_revision from {metadata_sql}")

    out_dir.mkdir(parents=True, exist_ok=True)
    versioned = out_dir / f"tcsb-{revision}.sqlite"
    tmp = out_dir / f".{versioned.name}.tmp"
    if tmp.exists():
        tmp.unlink()
    run(
        [
            sys.executable,
            str(converter),
            "--verses",
            str(verses_sql),
            "--start",
            str(complete_sql),
            "--out",
            str(tmp),
        ],
        cwd=mobile_root,
    )
    verification = verify_and_enrich_sqlite(tmp, metadata)
    tmp.replace(versioned)
    latest = out_dir / "latest.sqlite"
    shutil.copyfile(versioned, latest)
    digest = sha256_file(versioned)
    checksum = out_dir / f"{versioned.name}.sha256"
    checksum.write_text(f"{digest}  {versioned.name}\n", encoding="utf-8")
    manifest = {
        "revision": revision,
        "revision_date": metadata.get("text_revision_date", ""),
        "sqlite": versioned.name,
        "latest_sqlite": latest.name,
        "sha256": digest,
        "sha256_file": checksum.name,
        "size_bytes": versioned.stat().st_size,
        "generated_at": datetime.now(timezone.utc).isoformat(timespec="seconds"),
        "source": {
            "bookish_lamp_commit": metadata.get("bl_bible_verses_commit", ""),
            "bsm_bible_schema_commit": metadata.get("bsm_bible_schema_commit", ""),
            "text_source_repo": metadata.get("text_source_repo", ""),
            "text_source_file": metadata.get("text_source_file", ""),
        },
        **verification,
    }
    (out_dir / "latest.json").write_text(json.dumps(manifest, indent=2, sort_keys=True) + "\n", encoding="utf-8")
    return manifest


def publish_github_release(*, bsm_root: Path, out_dir: Path, manifest: dict[str, object]) -> None:
    revision = str(manifest["revision"])
    digest = str(manifest["sha256"])
    tag = "tcsb-mobile-db-latest"
    branch = run(["git", "branch", "--show-current"], cwd=bsm_root).stdout.strip() or "develop"
    sqlite_path = out_dir / str(manifest["sqlite"])
    checksum_path = out_dir / str(manifest["sha256_file"])
    require_file(sqlite_path)
    require_file(checksum_path)
    release_notes = f"""# TCSB mobile SQLite database

Download the `.sqlite` asset below, then import it in the TCSB mobile app.

- Revision: `{revision}`
- Verse count: `{manifest.get('verse_count')}`
- SHA-256: `{digest}`

Use this asset for Android import:

```text
TCSB-mobile-latest.sqlite
```

The versioned source artefact is also in BibleStudyMan at:

```text
build/tcsb-mobile/tcsb-{revision}.sqlite
```
"""
    with tempfile.TemporaryDirectory() as tmp_dir:
        tmp = Path(tmp_dir)
        latest_asset = tmp / "TCSB-mobile-latest.sqlite"
        versioned_asset = tmp / f"TCSB-mobile-{revision}.sqlite"
        checksum_asset = tmp / f"TCSB-mobile-{revision}.sqlite.sha256"
        notes_file = tmp / "release-notes.md"
        shutil.copyfile(sqlite_path, latest_asset)
        shutil.copyfile(sqlite_path, versioned_asset)
        shutil.copyfile(checksum_path, checksum_asset)
        notes_file.write_text(release_notes, encoding="utf-8")
        run(["git", "tag", "-f", tag], cwd=bsm_root)
        run(["git", "push", "-f", "origin", tag], cwd=bsm_root)
        title = f"Latest TCSB mobile SQLite database ({revision})"
        if subprocess.run(["gh", "release", "view", tag], cwd=bsm_root, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL).returncode == 0:
            run(["gh", "release", "edit", tag, "--title", title, "--notes-file", str(notes_file), "--latest"], cwd=bsm_root)
        else:
            run(["gh", "release", "create", tag, "--target", branch, "--title", title, "--notes-file", str(notes_file), "--latest"], cwd=bsm_root)
        run(
            [
                "gh",
                "release",
                "upload",
                tag,
                f"{latest_asset}#Download latest SQLite database",
                f"{versioned_asset}#Download revision {revision} SQLite database",
                f"{checksum_asset}#SHA-256 checksum",
                "--clobber",
            ],
            cwd=bsm_root,
        )


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description="Build TCSB mobile SQLite distribution artefacts")
    parser.add_argument("--bsm-root", type=Path, default=DEFAULT_BSM_ROOT)
    parser.add_argument("--mobile-root", type=Path, default=DEFAULT_BSM_ROOT.parent / "tcsb-mobile")
    parser.add_argument("--out-dir", type=Path, default=DEFAULT_BSM_ROOT / "build" / "tcsb-mobile")
    parser.add_argument("--commit", action="store_true", help="Commit generated artefacts to the BSM repository")
    parser.add_argument("--push", action="store_true", help="Push the commit to origin/current branch; implies --commit")
    parser.add_argument("--github-release", action="store_true", help="Create/update the latest GitHub release download page")
    args = parser.parse_args(argv)
    bsm_root = args.bsm_root.resolve()
    mobile_root = args.mobile_root.resolve()
    out_dir = args.out_dir.resolve()
    manifest = build_artifacts(bsm_root=bsm_root, mobile_root=mobile_root, out_dir=out_dir)
    if args.commit or args.push:
        rel_paths = [
            str((out_dir / manifest["sqlite"]).relative_to(bsm_root)),
            str((out_dir / manifest["sha256_file"]).relative_to(bsm_root)),
            str((out_dir / manifest["latest_sqlite"]).relative_to(bsm_root)),
            str((out_dir / "latest.json").relative_to(bsm_root)),
        ]
        run(["git", "add", ".gitignore", *rel_paths], cwd=bsm_root)
        diff = subprocess.run(["git", "diff", "--cached", "--quiet"], cwd=bsm_root, check=False)
        if diff.returncode != 0:
            run(["git", "config", "user.name", "TCSB Sync Bot"], cwd=bsm_root)
            run(["git", "config", "user.email", "tcsb-sync@hermes.local"], cwd=bsm_root)
            run(["git", "commit", "-m", f"Publish TCSB mobile SQLite revision {manifest['revision']}"], cwd=bsm_root)
            if args.push:
                branch = run(["git", "branch", "--show-current"], cwd=bsm_root).stdout.strip()
                run(["git", "push", "origin", branch], cwd=bsm_root)
        elif args.push:
            print("No generated artefact changes to commit.")
    if args.github_release:
        publish_github_release(bsm_root=bsm_root, out_dir=out_dir, manifest=manifest)
    print(f"Built TCSB mobile SQLite revision {manifest['revision']}")
    print(f"SQLite: {args.out_dir / manifest['sqlite']}")
    print(f"SHA-256: {manifest['sha256']}")
    print(f"Latest manifest: {args.out_dir / 'latest.json'}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
