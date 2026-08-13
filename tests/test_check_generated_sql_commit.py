import os
import subprocess
import tempfile
import unittest
from pathlib import Path


REPO = Path(__file__).resolve().parents[1]
SCRIPT = REPO / "scripts" / "check-generated-sql-commit.py"


def run(cmd, cwd=None, check=True, env=None):
    result = subprocess.run(
        cmd,
        cwd=cwd,
        check=False,
        text=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        env=env,
    )
    if check and result.returncode != 0:
        raise AssertionError(f"command failed ({result.returncode}): {cmd}\n{result.stdout}")
    return result


def git(repo, *args, check=True):
    return run(["git", *args], cwd=repo, check=check)


def init_repo(path: Path):
    path.mkdir(parents=True)
    git(path, "init", "-b", "develop")
    git(path, "config", "user.name", "Test User")
    git(path, "config", "user.email", "test@example.invalid")
    (path / "database").mkdir()
    (path / "scripts").mkdir()
    for file_path in [
        "database/bibleComplete.sql",
        "database/bibleVerses.sql",
        "database/bibleStrongs.sql",
        "database/tcsbMetadata.sql",
        "database/bibleSchema.sql",
        "database/bibleCompletedVerses.sql",
        "scripts/nightly-tcsb-revision-sync.py",
    ]:
        full_path = path / file_path
        full_path.parent.mkdir(parents=True, exist_ok=True)
        full_path.write_text(f"initial {file_path}\n", encoding="utf-8")
    git(path, "add", ".")
    git(path, "commit", "-m", "initial")


class GeneratedSqlCommitGuardTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.repo = Path(self.tmp.name) / "BibleStudyMan"
        init_repo(self.repo)

    def tearDown(self):
        self.tmp.cleanup()

    def run_guard(self, env=None):
        merged_env = os.environ.copy()
        if env:
            merged_env.update(env)
        return run(["python3", str(SCRIPT)], cwd=self.repo, check=False, env=merged_env)

    def stage_change(self, file_path: str, text: str = "changed\n"):
        (self.repo / file_path).write_text(text, encoding="utf-8")
        git(self.repo, "add", file_path)

    def test_blocks_direct_bible_complete_edit(self):
        self.stage_change("database/bibleComplete.sql")

        result = self.run_guard()

        self.assertNotEqual(result.returncode, 0)
        self.assertIn("generated BSM SQL", result.stdout)
        self.assertIn("database/bibleComplete.sql", result.stdout)

    def test_blocks_direct_bible_verses_edit(self):
        self.stage_change("database/bibleVerses.sql")

        result = self.run_guard()

        self.assertNotEqual(result.returncode, 0)
        self.assertIn("database/bibleVerses.sql", result.stdout)

    def test_blocks_direct_bible_strongs_edit(self):
        self.stage_change("database/bibleStrongs.sql")

        result = self.run_guard()

        self.assertNotEqual(result.returncode, 0)
        self.assertIn("database/bibleStrongs.sql", result.stdout)

    def test_allows_generated_sql_when_sync_metadata_is_staged(self):
        self.stage_change("database/bibleComplete.sql")
        self.stage_change("database/tcsbMetadata.sql", "metadata changed\n")

        result = self.run_guard()

        self.assertEqual(result.returncode, 0, result.stdout)

    def test_allows_generated_sql_with_explicit_override(self):
        self.stage_change("database/bibleComplete.sql")

        result = self.run_guard({"ALLOW_GENERATED_SQL_COMMIT": "1"})

        self.assertEqual(result.returncode, 0, result.stdout)

    def test_allows_normal_source_changes(self):
        self.stage_change("database/bibleSchema.sql", "schema changed\n")

        result = self.run_guard()

        self.assertEqual(result.returncode, 0, result.stdout)


if __name__ == "__main__":
    unittest.main()
