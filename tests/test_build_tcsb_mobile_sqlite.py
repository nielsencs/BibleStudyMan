import hashlib
import json
import sqlite3
import subprocess
import sys
import tempfile
import textwrap
import unittest
from pathlib import Path


SCRIPT = Path(__file__).resolve().parents[1] / "scripts" / "build-tcsb-mobile-sqlite.py"


def write_metadata(path: Path) -> None:
    path.write_text(
        """
DROP TABLE IF EXISTS `tcsb_text_metadata`;
CREATE TABLE `tcsb_text_metadata` (`metadataKey` varchar(40) NOT NULL, `metadataValue` varchar(255) NOT NULL);
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision', '260724');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision_date', '2026-07-24');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bl_bible_verses_commit', 'bookishcommit123');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bsm_bible_schema_commit', 'bsmcommit456');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('generated_at', '2026-07-25T03:30:12+01:00');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_disclaimer_text', 'TCSB disclaimer text.
Second line.');
""".strip()
        + "\n",
        encoding="utf-8",
    )


def write_fake_converter(path: Path) -> None:
    path.write_text(
        textwrap.dedent(
            """
            #!/usr/bin/env python3
            import argparse
            import sqlite3
            from pathlib import Path

            parser = argparse.ArgumentParser()
            parser.add_argument('--verses', required=True)
            parser.add_argument('--start', required=True)
            parser.add_argument('--out', required=True)
            args = parser.parse_args()
            assert Path(args.verses).name == 'bibleVerses.sql'
            assert Path(args.start).name == 'bibleComplete.sql'
            conn = sqlite3.connect(args.out)
            conn.executescript('''
              CREATE TABLE books (book_code TEXT PRIMARY KEY);
              CREATE TABLE verses (book_code TEXT, chapter INTEGER, verse_number INTEGER, verse_text_raw TEXT, verse_text_plain TEXT);
              CREATE TABLE plan_days (plan_day INTEGER PRIMARY KEY);
              CREATE TABLE plan_readings (plan_id INTEGER PRIMARY KEY);
              CREATE TABLE strongs (strongs_number TEXT PRIMARY KEY);
            ''')
            conn.executemany('INSERT INTO verses VALUES (?,?,?,?,?)', [
                ('GEN', 1, 1, '<p>In the beginning...', 'In the beginning...'),
                ('LUK', 8, 1, '<p>Soon afterwards...', 'Soon afterwards...'),
            ] + [('GEN', 1, i + 2, 'x', 'x') for i in range(31221)])
            conn.commit()
            conn.close()
            """
        ).lstrip(),
        encoding="utf-8",
    )
    path.chmod(0o755)


class BuildTcsbMobileSqliteTests(unittest.TestCase):
    def test_builds_versioned_sqlite_checksum_latest_manifest_and_latest_copy(self):
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            bsm = root / "BibleStudyMan"
            mobile = root / "tcsb-mobile"
            out_dir = root / "public" / "tcsb-mobile"
            (bsm / "database").mkdir(parents=True)
            (mobile / "scripts").mkdir(parents=True)
            write_metadata(bsm / "database" / "tcsbMetadata.sql")
            (bsm / "database" / "bibleVerses.sql").write_text("-- verses\n", encoding="utf-8")
            (bsm / "database" / "bibleComplete.sql").write_text("-- complete\n", encoding="utf-8")
            write_fake_converter(mobile / "scripts" / "convert_mysql_dumps_to_sqlite.py")

            result = subprocess.run(
                [
                    sys.executable,
                    str(SCRIPT),
                    "--bsm-root",
                    str(bsm),
                    "--mobile-root",
                    str(mobile),
                    "--out-dir",
                    str(out_dir),
                ],
                text=True,
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                check=False,
            )

            self.assertEqual(result.returncode, 0, result.stdout)
            versioned = out_dir / "tcsb-260724.sqlite"
            latest = out_dir / "latest.sqlite"
            checksum = out_dir / "tcsb-260724.sqlite.sha256"
            manifest_path = out_dir / "latest.json"
            self.assertTrue(versioned.is_file())
            self.assertTrue(latest.is_file())
            self.assertTrue(checksum.is_file())
            self.assertTrue(manifest_path.is_file())
            digest = hashlib.sha256(versioned.read_bytes()).hexdigest()
            self.assertEqual(latest.read_bytes(), versioned.read_bytes())
            self.assertIn(digest, checksum.read_text(encoding="utf-8"))
            manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
            self.assertEqual(manifest["revision"], "260724")
            self.assertEqual(manifest["sqlite"], "tcsb-260724.sqlite")
            self.assertEqual(manifest["latest_sqlite"], "latest.sqlite")
            self.assertEqual(manifest["sha256"], digest)
            self.assertEqual(manifest["verse_count"], 31223)
            self.assertEqual(manifest["source"]["bookish_lamp_commit"], "bookishcommit123")
            self.assertEqual(manifest["source"]["bsm_bible_schema_commit"], "bsmcommit456")
            conn = sqlite3.connect(versioned)
            try:
                metadata = dict(conn.execute("SELECT metadata_key, metadata_value FROM tcsb_metadata"))
            finally:
                conn.close()
            self.assertEqual(metadata["text_revision"], "260724")
            self.assertEqual(metadata["mobile_sqlite_revision"], "260724")
            self.assertEqual(metadata["tcsb_disclaimer_text"], "TCSB disclaimer text.\nSecond line.")

    def test_commit_mode_tracks_generated_artefacts_in_bsm_repo(self):
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            bsm = root / "BibleStudyMan"
            mobile = root / "tcsb-mobile"
            out_dir = bsm / "build" / "tcsb-mobile"
            (bsm / "database").mkdir(parents=True)
            (mobile / "scripts").mkdir(parents=True)
            write_metadata(bsm / "database" / "tcsbMetadata.sql")
            (bsm / "database" / "bibleVerses.sql").write_text("-- verses\n", encoding="utf-8")
            (bsm / "database" / "bibleComplete.sql").write_text("-- complete\n", encoding="utf-8")
            (bsm / ".gitignore").write_text(
                "/build/*\n!/build/tcsb-mobile/\n!/build/tcsb-mobile/latest.json\n!/build/tcsb-mobile/latest.sqlite\n!/build/tcsb-mobile/tcsb-*.sqlite\n!/build/tcsb-mobile/tcsb-*.sqlite.sha256\n",
                encoding="utf-8",
            )
            write_fake_converter(mobile / "scripts" / "convert_mysql_dumps_to_sqlite.py")
            subprocess.run(["git", "init", "-b", "develop"], cwd=bsm, check=True, stdout=subprocess.PIPE)
            subprocess.run(["git", "config", "user.name", "Test"], cwd=bsm, check=True)
            subprocess.run(["git", "config", "user.email", "test@example.invalid"], cwd=bsm, check=True)
            subprocess.run(["git", "add", "."], cwd=bsm, check=True)
            subprocess.run(["git", "commit", "-m", "initial"], cwd=bsm, check=True, stdout=subprocess.PIPE)

            result = subprocess.run(
                [
                    sys.executable,
                    str(SCRIPT),
                    "--bsm-root",
                    str(bsm),
                    "--mobile-root",
                    str(mobile),
                    "--out-dir",
                    str(out_dir),
                    "--commit",
                ],
                text=True,
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                check=False,
            )

            self.assertEqual(result.returncode, 0, result.stdout)
            tracked = subprocess.run(
                ["git", "ls-files", "build/tcsb-mobile"],
                cwd=bsm,
                text=True,
                stdout=subprocess.PIPE,
                check=True,
            ).stdout.splitlines()
            self.assertEqual(
                sorted(tracked),
                [
                    "build/tcsb-mobile/latest.json",
                    "build/tcsb-mobile/latest.sqlite",
                    "build/tcsb-mobile/tcsb-260724.sqlite",
                    "build/tcsb-mobile/tcsb-260724.sqlite.sha256",
                ],
            )
            latest_commit = subprocess.run(
                ["git", "log", "-1", "--format=%s"],
                cwd=bsm,
                text=True,
                stdout=subprocess.PIPE,
                check=True,
            ).stdout.strip()
            self.assertEqual(latest_commit, "Publish TCSB mobile SQLite revision 260724")


if __name__ == "__main__":
    unittest.main()
