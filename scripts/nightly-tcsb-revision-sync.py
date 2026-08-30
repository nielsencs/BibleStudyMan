#!/usr/bin/env python3
"""Nightly TCSB text revision sync.

Bumps the short TCSB text revision only when one of the revision-gate files
changed since the last recorded metadata:

- bookish-lamp/database/bibleVerses.sql
- BibleStudyMan/database/bibleSchema.sql
- the-cleanslate-bible/exports/bibleStrongs.sql
- the-cleanslate-bible/source/.../*GLO*.usfm
- the-cleanslate-bible/data/tcsb_promoted_usfm_books.txt and promoted book USFM files

Deliberately ignored for text-revision purposes:

- bookish-lamp/database/translationToDo.txt
- BibleStudyMan/database/bibleCompletedVerses.sql
"""
from __future__ import annotations

import argparse
import html
import importlib.util
import json
import os
import re
import shutil
import subprocess
import sys
from datetime import datetime
from pathlib import Path
from zoneinfo import ZoneInfo


SCRIPT_DIR = Path(__file__).resolve().parent
DEFAULT_BSM_ROOT = SCRIPT_DIR.parent
DEFAULT_TCSB_ROOT = DEFAULT_BSM_ROOT.parent / "the-cleanslate-bible"
PROMOTED_USFM_BOOKS_PATH = Path("data/tcsb_promoted_usfm_books.txt")
PROMOTED_USFM_SOURCE_ALIASES = {
    # Bookish Lamp / BSM uses JOE; the canonical TCSB USFM file/id is JOL.
    "JOE": "JOL",
}
VERSE_INSERT_RE = re.compile(
    r"^INSERT\s+INTO\s+`?verses`?\s*"
    r"\(`?bookCode`?,\s*`?chapter`?,\s*`?verseNumber`?,\s*`?verseText`?\)\s*"
    r"VALUES\s*\('([A-Z0-9]{3})',\s*(\d+),\s*(\d+),\s*'(.*)'\);\s*$",
    re.IGNORECASE,
)


def run(
    cmd: list[str],
    cwd: Path | None = None,
    input_text: str | None = None,
    check: bool = True,
    env: dict[str, str] | None = None,
) -> subprocess.CompletedProcess[str]:
    result = subprocess.run(
        cmd,
        cwd=cwd,
        input=input_text,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        env=env,
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


def tcsb_glossary_usfm_paths(tcsb_root: Path) -> list[str]:
    paths = sorted(tcsb_root.glob("source/**/*.usfm"))
    glossary_paths = [path.relative_to(tcsb_root).as_posix() for path in paths if "GLO" in path.name]
    if not glossary_paths:
        raise SystemExit(f"No TCSB glossary USFM file found under {tcsb_root / 'source'}")
    return glossary_paths


def latest_commit_for_files(repo: Path, file_paths: list[str]) -> str:
    return git_stdout(repo, "log", "-n", "1", "--format=%H", "--", *file_paths)


def tcsb_source_dir(tcsb_root: Path) -> Path:
    candidates = [path for path in sorted(tcsb_root.glob("source/tcsb-usfm_*")) if path.is_dir()]
    if not candidates:
        raise SystemExit(f"No TCSB USFM source directory found under {tcsb_root / 'source'}")
    return candidates[-1]


def load_promoted_usfm_books(tcsb_root: Path) -> list[str]:
    path = tcsb_root / PROMOTED_USFM_BOOKS_PATH
    if not path.exists():
        return []
    books = []
    seen = set()
    for line in path.read_text(encoding="utf-8").splitlines():
        book = line.split("#", 1)[0].strip().upper()
        if not book:
            continue
        if not re.fullmatch(r"[A-Z0-9]{3}", book):
            raise SystemExit(f"Invalid promoted USFM book code in {path}: {book!r}")
        if book in seen:
            raise SystemExit(f"Duplicate promoted USFM book code in {path}: {book}")
        seen.add(book)
        books.append(book)
    return books


def usfm_source_book_code(book: str) -> str:
    return PROMOTED_USFM_SOURCE_ALIASES.get(book, book)


def tcsb_usfm_book_paths(tcsb_root: Path, books: list[str]) -> list[str]:
    if not books:
        return []
    source_dir = tcsb_source_dir(tcsb_root)
    wanted = {usfm_source_book_code(book) for book in books}
    source_to_promoted = {usfm_source_book_code(book): book for book in books}
    found: dict[str, str] = {}
    for path in sorted(source_dir.glob("*.usfm")):
        for line in path.read_text(encoding="utf-8", errors="replace").splitlines():
            if line.startswith(r"\id "):
                code = line.split(maxsplit=2)[1].strip().upper()
                if code in wanted:
                    found[source_to_promoted[code]] = path.relative_to(tcsb_root).as_posix()
                break
    missing = [book for book in books if book not in found]
    if missing:
        raise SystemExit(f"Promoted USFM book(s) missing from {source_dir}: {', '.join(missing)}")
    return [found[book] for book in books]


def parse_verse_insert(line: str) -> tuple[str, str, str] | None:
    match = VERSE_INSERT_RE.match(line)
    if not match:
        return None
    book, chapter, verse, _ = match.groups()
    return book, str(int(chapter)), str(int(verse))


def rewrite_insert_book_code(line: str, book: str) -> str:
    return re.sub(r"VALUES\s*\('[A-Z0-9]{3}'", f"VALUES ('{book}'", line, count=1, flags=re.IGNORECASE)


def merge_promoted_usfm_books(bl_sql: str, usfm_sql: str, promoted_books: list[str]) -> str:
    if not promoted_books:
        return bl_sql
    promoted = set(promoted_books)
    source_to_promoted = {usfm_source_book_code(book): book for book in promoted_books}
    usfm_rows: dict[tuple[str, str, str], str] = {}
    for line in usfm_sql.splitlines():
        key = parse_verse_insert(line)
        if key and key[0] in source_to_promoted:
            promoted_book = source_to_promoted[key[0]]
            promoted_key = (promoted_book, key[1], key[2])
            usfm_rows[promoted_key] = rewrite_insert_book_code(line, promoted_book)
    bl_keys = [parse_verse_insert(line) for line in bl_sql.splitlines()]
    promoted_bl_keys = [key for key in bl_keys if key and key[0] in promoted]
    promoted_bl_key_set = set(promoted_bl_keys)
    usfm_key_set = set(usfm_rows)
    if promoted_bl_key_set != usfm_key_set:
        missing = sorted(promoted_bl_key_set - usfm_key_set)[:10]
        extra = sorted(usfm_key_set - promoted_bl_key_set)[:10]
        raise SystemExit(
            "Promoted USFM/Bookish Lamp reference mismatch: "
            f"missing_from_usfm={missing} extra_from_usfm={extra}"
        )
    lines = []
    replaced = 0
    for line in bl_sql.splitlines():
        key = parse_verse_insert(line)
        if key and key[0] in promoted:
            lines.append(usfm_rows[key])
            replaced += 1
        else:
            lines.append(line)
    if replaced != len(promoted_bl_keys):
        raise SystemExit(f"Expected to replace {len(promoted_bl_keys)} promoted row(s), replaced {replaced}")
    return "\n".join(lines) + ("\n" if bl_sql.endswith("\n") else "")


def promoted_usfm_export_sql(tcsb_root: Path) -> str:
    source_dir = tcsb_source_dir(tcsb_root)
    exporter = tcsb_root / "tools" / "plain_usfm_to_sql.py"
    require_file(exporter)
    tmp = tcsb_root / "generated" / "promoted-usfm-nightly-preview.sql"
    tmp.parent.mkdir(parents=True, exist_ok=True)
    run([sys.executable, str(exporter), str(source_dir), str(tmp)], cwd=tcsb_root)
    return tmp.read_text(encoding="utf-8")


def write_hybrid_bible_verses(bl_root: Path, tcsb_root: Path, bsm_root: Path, promoted_books: list[str]) -> None:
    bl_sql = (bl_root / "database" / "bibleVerses.sql").read_text(encoding="utf-8")
    hybrid = bl_sql
    if promoted_books:
        hybrid = merge_promoted_usfm_books(bl_sql, promoted_usfm_export_sql(tcsb_root), promoted_books)
    (bsm_root / "database" / "bibleVerses.sql").write_text(hybrid, encoding="utf-8")


def regenerate_tcsb_exports_from_usfm(tcsb_root: Path, *, no_push: bool) -> bool:
    source_dir = tcsb_source_dir(tcsb_root)
    exporter = tcsb_root / "tools" / "plain_usfm_to_sql.py"
    require_file(exporter)
    output_sql = tcsb_root / "exports" / f"{source_dir.name}.sql"
    run([sys.executable, str(exporter), str(source_dir), str(output_sql)], cwd=tcsb_root)
    changed = commit_if_changed(
        tcsb_root,
        "Regenerate TCSB SQL exports from USFM",
        [f"exports/{source_dir.name}.sql", "exports/bibleStrongs.sql"],
    )
    if changed and not no_push:
        git(tcsb_root, "push", "origin", current_branch(tcsb_root))
    return changed


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
    tcsb_strongs_commit: str,
    tcsb_glossary_commit: str,
    tcsb_promoted_usfm_commit: str,
    promoted_books: list[str],
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
        sql_insert("text_source_file", "database/bibleVerses.sql + promoted TCSB USFM books" if promoted_books else "database/bibleVerses.sql"),
        sql_insert("bl_bible_verses_commit", bl_commit),
        sql_insert("bsm_bible_schema_commit", bsm_commit),
        sql_insert("tcsb_bible_strongs_commit", tcsb_strongs_commit),
        sql_insert("tcsb_glossary_usfm_commit", tcsb_glossary_commit),
        sql_insert("tcsb_promoted_usfm_books", ",".join(promoted_books)),
        sql_insert("tcsb_promoted_usfm_commit", tcsb_promoted_usfm_commit),
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
    git(repo, "config", "user.name", "TCSB Sync Bot")
    git(repo, "config", "user.email", "tcsb-sync@hermes.local")
    git(repo, "commit", "-m", message)
    return True


def local_path_value(root: Path, key: str) -> Path | None:
    path_file = root / "local_paths.json"
    if not path_file.exists():
        return None
    try:
        data = json.loads(path_file.read_text(encoding="utf-8"))
    except json.JSONDecodeError as exc:
        raise SystemExit(f"Invalid JSON in {path_file}: {exc}") from exc
    value = data.get(key)
    if value:
        return Path(str(value)).expanduser()
    return None


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


def bible_complete_parts(bsm_root: Path) -> list[Path]:
    return [
        bsm_root / "database" / "bibleImportSettings.sql",
        bsm_root / "database" / "tcsbMetadata.sql",
        bsm_root / "database" / "bibleSchema.sql",
        bsm_root / "database" / "bibleStrongs.sql",
        bsm_root / "database" / "bibleCompletedVerses.sql",
        bsm_root / "database" / "bibleVerses.sql",
    ]


def bible_complete_text(bsm_root: Path) -> str:
    return "\n".join(part.read_text(encoding="utf-8") for part in bible_complete_parts(bsm_root))


def rebuild_bible_complete(bsm_root: Path) -> None:
    (bsm_root / "database" / "bibleComplete.sql").write_text(bible_complete_text(bsm_root), encoding="utf-8")


def mariadb_client() -> str:
    configured = os.environ.get("BSM_DB_CLIENT", "").strip()
    if configured:
        return configured
    for name in ("mariadb", "mysql"):
        found = shutil.which(name)
        if found:
            return found
    raise SystemExit(
        "Neither mariadb nor mysql client is available; cannot import BSM bibleComplete.sql. "
        "Install a client, set BSM_DB_CLIENT, or run with --no-db-import."
    )


def mariadb_import_args() -> tuple[list[str], dict[str, str]]:
    db_name = os.environ.get("BSM_DB_NAME", "bible")
    db_user = os.environ.get("BSM_DB_USER", "root")
    db_password = os.environ.get("BSM_DB_PASSWORD", "")
    db_host = os.environ.get("BSM_DB_HOST", "")
    db_port = os.environ.get("BSM_DB_PORT", "")
    db_socket = os.environ.get("BSM_DB_SOCKET", "")
    args = [f"-u{db_user}"]
    if db_socket:
        args.append(f"--socket={db_socket}")
    elif db_host:
        args.append(f"-h{db_host}")
    if db_port:
        args.append(f"--port={db_port}")
    args.append(db_name)
    env = os.environ.copy()
    if db_password:
        env["MYSQL_PWD"] = db_password
    return args, env


def import_bible_complete_to_mariadb(bsm_root: Path) -> None:
    sql_path = bsm_root / "database" / "bibleComplete.sql"
    require_file(sql_path)
    client = mariadb_client()
    args, env = mariadb_import_args()
    run([client, *args], cwd=bsm_root, input_text=sql_path.read_text(encoding="utf-8"), env=env)


def sync_completed_verses_from_tcsb(tcsb_root: Path, bsm_root: Path) -> bool:
    source = tcsb_root / "database-components" / "bibleCompletedVerses.sql"
    destination = bsm_root / "database" / "bibleCompletedVerses.sql"
    require_file(source)
    require_file(destination)
    changed = False
    if source.read_bytes() != destination.read_bytes():
        shutil.copyfile(source, destination)
        changed = True
    expected_complete = bible_complete_text(bsm_root)
    complete_path = bsm_root / "database" / "bibleComplete.sql"
    if complete_path.read_text(encoding="utf-8") != expected_complete:
        complete_path.write_text(expected_complete, encoding="utf-8")
        changed = True
    return changed


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description="Nightly TCSB text revision sync")
    parser.add_argument("--bl-root", type=Path)
    parser.add_argument("--bsm-root", type=Path, default=DEFAULT_BSM_ROOT)
    parser.add_argument("--tcsb-root", type=Path)
    parser.add_argument("--no-push", action="store_true")
    parser.add_argument("--no-pull", action="store_true")
    parser.add_argument("--no-db-import", action="store_true", help="Do not import rebuilt bibleComplete.sql into MariaDB")
    parser.add_argument("--revision")
    parser.add_argument("--revision-date")
    args = parser.parse_args(argv)

    bsm_root = args.bsm_root.resolve()
    bl_root = (
        args.bl_root
        or local_path_value(bsm_root, "bookish_lamp_repo")
        or (bsm_root.parent / "bookish-lamp")
    ).resolve()
    tcsb_root = (
        args.tcsb_root
        or local_path_value(bsm_root, "the_cleanslate_bible_repo")
        or local_path_value(bsm_root, "tcsb_repo")
        or DEFAULT_TCSB_ROOT
    ).resolve()

    ensure_git_repo(bl_root, "Bookish Lamp")
    ensure_git_repo(tcsb_root, "the-cleanslate-bible")
    ensure_git_repo(bsm_root, "BibleStudyMan")
    for path in [
        bl_root / "database" / "bibleVerses.sql",
        bl_root / "database" / "tcsbMetadata.sql",
        bsm_root / "database" / "bibleImportSettings.sql",
        bsm_root / "database" / "bibleSchema.sql",
        tcsb_root / "exports" / "bibleStrongs.sql",
        tcsb_root / "database-components" / "bibleCompletedVerses.sql",
        bsm_root / "database" / "bibleCompletedVerses.sql",
        bsm_root / "database" / "bibleVerses.sql",
        bsm_root / "database" / "tcsbMetadata.sql",
        bsm_root / "site" / "bibleDisclaimer.html",
    ]:
        require_file(path)

    if current_branch(bsm_root) != "develop":
        raise SystemExit(f"BSM must be on develop; currently on {current_branch(bsm_root)}")

    ensure_clean_repo(bl_root, "Bookish Lamp")
    ensure_clean_repo(tcsb_root, "the-cleanslate-bible")
    ensure_clean_repo(bsm_root, "BibleStudyMan")

    if not args.no_pull:
        pull_ff_current_branch(bl_root, "Bookish Lamp")
        pull_ff_current_branch(tcsb_root, "the-cleanslate-bible")
        pull_ff_current_branch(bsm_root, "BibleStudyMan")

    bl_commit = latest_commit_for_file(bl_root, "database/bibleVerses.sql")
    bsm_commit = latest_commit_for_file(bsm_root, "database/bibleSchema.sql")
    tcsb_strongs_commit = latest_commit_for_file(tcsb_root, "exports/bibleStrongs.sql")
    tcsb_glossary_paths = tcsb_glossary_usfm_paths(tcsb_root)
    promoted_books = load_promoted_usfm_books(tcsb_root)
    promoted_paths = [PROMOTED_USFM_BOOKS_PATH.as_posix(), *tcsb_usfm_book_paths(tcsb_root, promoted_books)] if promoted_books else []
    tcsb_glossary_commit = latest_commit_for_files(tcsb_root, tcsb_glossary_paths)
    tcsb_promoted_usfm_commit = latest_commit_for_files(tcsb_root, promoted_paths) if promoted_paths else ""
    recorded_bl_commit = metadata_value(bsm_root / "database" / "tcsbMetadata.sql", "bl_bible_verses_commit")
    recorded_bsm_commit = metadata_value(bsm_root / "database" / "tcsbMetadata.sql", "bsm_bible_schema_commit")
    recorded_tcsb_strongs_commit = metadata_value(bsm_root / "database" / "tcsbMetadata.sql", "tcsb_bible_strongs_commit")
    recorded_tcsb_glossary_commit = metadata_value(bsm_root / "database" / "tcsbMetadata.sql", "tcsb_glossary_usfm_commit")
    recorded_tcsb_promoted_usfm_commit = metadata_value(bsm_root / "database" / "tcsbMetadata.sql", "tcsb_promoted_usfm_commit")

    if tcsb_glossary_commit != recorded_tcsb_glossary_commit:
        if regenerate_tcsb_exports_from_usfm(tcsb_root, no_push=args.no_push):
            print("Generated TCSB bibleStrongs.sql from glossary source")
        tcsb_strongs_commit = latest_commit_for_file(tcsb_root, "exports/bibleStrongs.sql")

    if (
        bl_commit == recorded_bl_commit
        and bsm_commit == recorded_bsm_commit
        and tcsb_strongs_commit == recorded_tcsb_strongs_commit
        and tcsb_glossary_commit == recorded_tcsb_glossary_commit
        and tcsb_promoted_usfm_commit == recorded_tcsb_promoted_usfm_commit
    ):
        if sync_completed_verses_from_tcsb(tcsb_root, bsm_root):
            if not args.no_db_import:
                import_bible_complete_to_mariadb(bsm_root)
                print("Imported BSM bibleComplete.sql into MariaDB")
            bsm_commit_created = commit_if_changed(
                bsm_root,
                "Sync TCSB completed verses",
                ["database/bibleCompletedVerses.sql", "database/bibleComplete.sql"],
            )
            if bsm_commit_created and not args.no_push:
                git(bsm_root, "push", "origin", "develop")
            print("Synced TCSB completed verses without text revision change")
            return 0
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
        tcsb_strongs_commit=tcsb_strongs_commit,
        tcsb_glossary_commit=tcsb_glossary_commit,
        tcsb_promoted_usfm_commit=tcsb_promoted_usfm_commit,
        promoted_books=promoted_books,
        generated_at=generated_at,
        disclaimer_html=disclaimer_html,
    )

    bl_commit_created = commit_if_changed(bl_root, f"Set TCSB text revision {revision}", ["database/tcsbMetadata.sql"])
    if bl_commit_created and not args.no_push:
        git(bl_root, "push", "origin", current_branch(bl_root))

    shutil.copyfile(bl_root / "database" / "tcsbMetadata.sql", bsm_root / "database" / "tcsbMetadata.sql")
    shutil.copyfile(tcsb_root / "exports" / "bibleStrongs.sql", bsm_root / "database" / "bibleStrongs.sql")
    shutil.copyfile(tcsb_root / "database-components" / "bibleCompletedVerses.sql", bsm_root / "database" / "bibleCompletedVerses.sql")
    write_hybrid_bible_verses(bl_root, tcsb_root, bsm_root, promoted_books)
    load_generate_verse_plain_module(bsm_root).rewrite_file(bsm_root / "database" / "bibleVerses.sql")
    rebuild_bible_complete(bsm_root)

    if (bl_root / "database" / "tcsbMetadata.sql").read_bytes() != (bsm_root / "database" / "tcsbMetadata.sql").read_bytes():
        raise SystemExit("Post-copy verification failed: BSM tcsbMetadata.sql does not match BL.")
    complete = (bsm_root / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
    if f"('text_revision', '{revision}')" not in complete:
        raise SystemExit(f"Post-copy verification failed: bibleComplete.sql lacks text revision {revision}.")

    if not args.no_db_import:
        import_bible_complete_to_mariadb(bsm_root)
        print("Imported BSM bibleComplete.sql into MariaDB")

    bsm_paths = [
        "database/bibleImportSettings.sql",
        "database/tcsbMetadata.sql",
        "database/bibleSchema.sql",
        "database/bibleStrongs.sql",
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
    print(f"TCSB bibleStrongs commit: {tcsb_strongs_commit}")
    print(f"TCSB glossary USFM commit: {tcsb_glossary_commit}")
    print(f"TCSB promoted USFM books: {','.join(promoted_books) if promoted_books else 'none'}")
    print(f"TCSB promoted USFM commit: {tcsb_promoted_usfm_commit or 'none'}")
    print(f"Bookish Lamp metadata commit created: {int(bl_commit_created)}")
    print(f"BibleStudyMan sync commit created: {int(bsm_commit_created)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
