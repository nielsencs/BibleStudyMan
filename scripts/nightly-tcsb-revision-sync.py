#!/usr/bin/env python3
"""Nightly TCSB text revision sync.

Bumps the short TCSB text revision only when one of the revision-gate files
changed since the last recorded metadata:

- bookish-lamp/database/bibleVerses.sql
- BibleStudyMan/database/bibleSchema.sql

Deliberately ignored for text-revision purposes:

- bookish-lamp/database/translationToDo.txt
- BibleStudyMan/database/bibleCompletedVerses.sql
"""
from __future__ import annotations

import argparse
import html
import os
import re
import importlib.util
import shutil
import subprocess
import sys
from datetime import datetime
from pathlib import Path
from zoneinfo import ZoneInfo


SCRIPT_DIR = Path(__file__).resolve().parent
DEFAULT_BSM_ROOT = SCRIPT_DIR.parent


def run(cmd: list[str], cwd: Path | None = None, input_text: str | None = None, check: bool = True) -> subprocess.CompletedProcess[str]:
    result = subprocess.run(
        cmd,
        cwd=cwd,
        input=input_text,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        check=False,
    )
    if check and result.returncode != 0:
        raise SystemExit(result.stdout.rstrip() or f"command failed: {' '.join(cmd)}")
    return result


def git(repo: Path, *args: str, check: bool = True) -> subprocess.CompletedProcess[str]:
    return run(["git", *args], cwd=repo, check=check)


def git_stdout(repo: Path, *args: str) -> str:
    return git(repo, *args).stdout.strip()


def require_file(path: Path) -> None:
    if not path.is_file():
        raise SystemExit(f"Required file missing: {path}")


def ensure_git_repo(repo: Path, label: str) -> None:
    if not (repo / ".git").is_dir():
        raise SystemExit(f"{label} is not a git checkout: {repo}")


def ensure_clean_repo(repo: Path, label: str) -> None:
    status = git_stdout(repo, "status", "--porcelain")
    if status:
        raise SystemExit(f"{label} has uncommitted changes; stopping before sync.\n{status}")


def current_branch(repo: Path) -> str:
    branch = git_stdout(repo, "branch", "--show-current")
    if not branch:
        raise SystemExit(f"{repo} is not on a branch; stopping.")
    return branch


def pull_ff_current_branch(repo: Path, label: str) -> None:
    branch = current_branch(repo)
    git(repo, "fetch", "--prune", "origin")
    git(repo, "pull", "--ff-only", "origin", branch)


def metadata_value(path: Path, key: str) -> str:
    pattern = re.compile(rf"\('{re.escape(key)}', '([^']+)'\)")
    for line in path.read_text(encoding="utf-8").splitlines():
        match = pattern.search(line)
        if match:
            return match.group(1)
    return ""


def latest_commit_for_file(repo: Path, file_path: str) -> str:
    return git_stdout(repo, "log", "-n", "1", "--format=%H", "--", file_path)


def sql_insert(key: str, value: str) -> str:
    escaped = value.replace("'", "''")
    return f"INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('{key}', '{escaped}');\n"


def disclaimer_plain_text(disclaimer_html: str) -> str:
    text = re.sub(r"<\s*br\s*/?\s*>", "\n", disclaimer_html, flags=re.IGNORECASE)
    text = re.sub(r"<[^>]+>", "", text)
    text = html.unescape(text)
    lines = [re.sub(r"\s+", " ", line).strip() for line in text.splitlines()]
    return "\n".join(line for line in lines if line)


def write_metadata(
    path: Path,
    *,
    revision: str,
    revision_date: str,
    bl_root: Path,
    bl_commit: str,
    bsm_commit: str,
    generated_at: str,
    disclaimer_html: str,
) -> None:
    content = [
        "DROP TABLE IF EXISTS `tcsb_text_metadata`;\n",
        "CREATE TABLE `tcsb_text_metadata` (\n",
        "  `metadataKey` varchar(40) NOT NULL,\n",
        "  `metadataValue` text NOT NULL,\n",
        "  PRIMARY KEY (`metadataKey`)\n",
        ") ENGINE=MyISAM DEFAULT CHARSET=latin1;\n",
        "\n",
        sql_insert("text_revision", revision),
        sql_insert("text_version", revision),
        sql_insert("text_revision_date", revision_date),
        sql_insert("text_source_repo", "bookish-lamp"),
        sql_insert("text_source_branch", current_branch(bl_root)),
        sql_insert("text_source_file", "database/bibleVerses.sql"),
        sql_insert("bl_bible_verses_commit", bl_commit),
        sql_insert("bsm_bible_schema_commit", bsm_commit),
        sql_insert("generated_at", generated_at),
        sql_insert("tcsb_disclaimer_html", disclaimer_html.strip()),
        sql_insert("tcsb_disclaimer_text", disclaimer_plain_text(disclaimer_html)),
    ]
    path.write_text("".join(content), encoding="utf-8")


def commit_if_changed(repo: Path, message: str, paths: list[str]) -> bool:
    existing_paths = [path for path in paths if (repo / path).exists()]
    if not existing_paths:
        return False
    git(repo, "add", *existing_paths)
    if git(repo, "diff", "--cached", "--quiet", check=False).returncode == 0:
        return False
    git(repo, "config", "user.name", "Ezra H")
    git(repo, "config", "user.email", "ezra-h@hermes.local")
    git(repo, "commit", "-m", message)
    return True


def load_generate_verse_plain_module(bsm_root: Path):
    script = bsm_root / "scripts" / "generate-verse-plain.py"
    if not script.exists():
        script = Path(__file__).with_name("generate-verse-plain.py")
    spec = importlib.util.spec_from_file_location("generate_verse_plain", script)
    if spec is None or spec.loader is None:
        raise SystemExit(f"Could not load {script}")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


def rebuild_bible_complete(bsm_root: Path) -> None:
    parts = [
        bsm_root / "database" / "bibleImportSettings.sql",
        bsm_root / "database" / "tcsbMetadata.sql",
        bsm_root / "database" / "bibleSchema.sql",
        bsm_root / "database" / "bibleCompletedVerses.sql",
        bsm_root / "database" / "bibleVerses.sql",
    ]
    with (bsm_root / "database" / "bibleComplete.sql").open("w", encoding="utf-8") as out:
        for index, part in enumerate(parts):
            if index:
                out.write("\n")
            out.write(part.read_text(encoding="utf-8"))


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description="Nightly TCSB text revision sync")
    parser.add_argument("--bl-root", type=Path)
    parser.add_argument("--bsm-root", type=Path, default=DEFAULT_BSM_ROOT)
    parser.add_argument("--no-push", action="store_true")
    parser.add_argument("--no-pull", action="store_true")
    parser.add_argument("--revision")
    parser.add_argument("--revision-date")
    args = parser.parse_args(argv)

    bsm_root = args.bsm_root.resolve()
    bl_root = args.bl_root.resolve() if args.bl_root else (bsm_root.parent / "bookish-lamp").resolve()

    ensure_git_repo(bl_root, "Bookish Lamp")
    ensure_git_repo(bsm_root, "BibleStudyMan")
    for path in [
        bl_root / "database" / "bibleVerses.sql",
        bl_root / "database" / "tcsbMetadata.sql",
        bsm_root / "database" / "bibleImportSettings.sql",
        bsm_root / "database" / "bibleSchema.sql",
        bsm_root / "database" / "bibleCompletedVerses.sql",
        bsm_root / "database" / "bibleVerses.sql",
        bsm_root / "database" / "tcsbMetadata.sql",
        bsm_root / "site" / "bibleDisclaimer.html",
    ]:
        require_file(path)

    if current_branch(bsm_root) != "develop":
        raise SystemExit(f"BSM must be on develop; currently on {current_branch(bsm_root)}")

    ensure_clean_repo(bl_root, "Bookish Lamp")
    ensure_clean_repo(bsm_root, "BibleStudyMan")

    if not args.no_pull:
        pull_ff_current_branch(bl_root, "Bookish Lamp")
        pull_ff_current_branch(bsm_root, "BibleStudyMan")

    bl_commit = latest_commit_for_file(bl_root, "database/bibleVerses.sql")
    bsm_commit = latest_commit_for_file(bsm_root, "database/bibleSchema.sql")
    recorded_bl_commit = metadata_value(bsm_root / "database" / "tcsbMetadata.sql", "bl_bible_verses_commit")
    recorded_bsm_commit = metadata_value(bsm_root / "database" / "tcsbMetadata.sql", "bsm_bible_schema_commit")

    if bl_commit == recorded_bl_commit and bsm_commit == recorded_bsm_commit:
        print("No TCSB text revision change: tracked source commits already recorded.")
        return 0

    now = datetime.now(ZoneInfo("Europe/London"))
    revision = args.revision or now.strftime("%y%m%d")
    revision_date = args.revision_date or now.strftime("%Y-%m-%d")
    generated_at = now.isoformat(timespec="seconds")
    disclaimer_html = (bsm_root / "site" / "bibleDisclaimer.html").read_text(encoding="utf-8")

    write_metadata(
        bl_root / "database" / "tcsbMetadata.sql",
        revision=revision,
        revision_date=revision_date,
        bl_root=bl_root,
        bl_commit=bl_commit,
        bsm_commit=bsm_commit,
        generated_at=generated_at,
        disclaimer_html=disclaimer_html,
    )

    bl_commit_created = commit_if_changed(bl_root, f"Set TCSB text revision {revision}", ["database/tcsbMetadata.sql"])
    if bl_commit_created and not args.no_push:
        git(bl_root, "push", "origin", current_branch(bl_root))

    shutil.copyfile(bl_root / "database" / "tcsbMetadata.sql", bsm_root / "database" / "tcsbMetadata.sql")
    shutil.copyfile(bl_root / "database" / "bibleVerses.sql", bsm_root / "database" / "bibleVerses.sql")
    load_generate_verse_plain_module(bsm_root).rewrite_file(bsm_root / "database" / "bibleVerses.sql")
    rebuild_bible_complete(bsm_root)

    if (bl_root / "database" / "tcsbMetadata.sql").read_bytes() != (bsm_root / "database" / "tcsbMetadata.sql").read_bytes():
        raise SystemExit("Post-copy verification failed: BSM tcsbMetadata.sql does not match BL.")
    complete = (bsm_root / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
    if f"('text_revision', '{revision}')" not in complete:
        raise SystemExit(f"Post-copy verification failed: bibleComplete.sql lacks text revision {revision}.")

    bsm_paths = [
        "database/bibleImportSettings.sql",
        "database/tcsbMetadata.sql",
        "database/bibleSchema.sql",
        "database/bibleCompletedVerses.sql",
        "database/bibleVerses.sql",
        "database/bibleComplete.sql",
        "scripts/generate-verse-plain.py",
        "scripts/nightly-tcsb-revision-sync.py",
        "scripts/nightly-tcsb-revision-sync.sh",
        "tests/test_nightly_tcsb_revision_sync.py",
    ]
    bsm_commit_created = commit_if_changed(bsm_root, f"Sync TCSB text revision {revision}", bsm_paths)
    if bsm_commit_created and not args.no_push:
        git(bsm_root, "push", "origin", "develop")

    print(f"Synced TCSB text revision {revision}")
    print(f"BL bibleVerses commit: {bl_commit}")
    print(f"BSM bibleSchema commit: {bsm_commit}")
    print(f"Bookish Lamp metadata commit created: {int(bl_commit_created)}")
    print(f"BibleStudyMan sync commit created: {int(bsm_commit_created)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
