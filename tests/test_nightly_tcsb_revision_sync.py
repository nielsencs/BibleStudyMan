import os
import shutil
import subprocess
import tempfile
import unittest
from pathlib import Path


REPO = Path(__file__).resolve().parents[1]
SCRIPT = REPO / "scripts" / "nightly-tcsb-revision-sync.py"
WRAPPER = REPO / "scripts" / "nightly-tcsb-revision-sync.sh"


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


def git(repo, *args):
    return run(["git", *args], cwd=repo)


def init_repo(path: Path, branch: str):
    path.mkdir(parents=True)
    git(path, "init", "-b", branch)
    git(path, "config", "user.name", "Test User")
    git(path, "config", "user.email", "test@example.invalid")


def commit(repo: Path, message: str):
    git(repo, "add", ".")
    git(repo, "commit", "-m", message)
    return git(repo, "rev-parse", "HEAD").stdout.strip()


def write_metadata(path: Path, revision="260701", bl_commit="old-bl", bsm_commit="old-bsm", tcsb_strongs_commit="old-strongs", tcsb_glossary_commit="old-glossary"):
    path.write_text(
        "DROP TABLE IF EXISTS `tcsb_text_metadata`;\n"
        "CREATE TABLE `tcsb_text_metadata` (\n"
        "  `metadataKey` varchar(40) NOT NULL,\n"
        "  `metadataValue` varchar(255) NOT NULL,\n"
        "  PRIMARY KEY (`metadataKey`)\n"
        ") ENGINE=MyISAM DEFAULT CHARSET=latin1;\n\n"
        f"INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision', '{revision}');\n"
        f"INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_version', '{revision}');\n"
        "INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision_date', '2026-07-01');\n"
        f"INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bl_bible_verses_commit', '{bl_commit}');\n"
        f"INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bsm_bible_schema_commit', '{bsm_commit}');\n"
        f"INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_bible_strongs_commit', '{tcsb_strongs_commit}');\n"
        f"INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_glossary_usfm_commit', '{tcsb_glossary_commit}');\n",
        encoding="utf-8",
    )


class NightlyTcsbRevisionSyncTest(unittest.TestCase):
    def setUp(self):
        self.tmp = Path(tempfile.mkdtemp())
        self.bl = self.tmp / "bookish-lamp"
        self.bsm = self.tmp / "BibleStudyMan"
        self.tcsb = self.tmp / "the-cleanslate-bible"
        init_repo(self.bl, "master")
        init_repo(self.tcsb, "main")
        init_repo(self.bsm, "develop")
        (self.bl / "database").mkdir()
        (self.tcsb / "exports").mkdir()
        (self.tcsb / "database-components").mkdir()
        (self.tcsb / "data").mkdir()
        (self.tcsb / "source" / "tcsb-usfm_2026-06-28").mkdir(parents=True)
        (self.tcsb / "tools").mkdir()
        (self.bsm / "database").mkdir()
        (self.bsm / "site").mkdir()
        (self.bsm / ".gitignore").write_text("/local_paths.json\n", encoding="utf-8")
        (self.bsm / "site" / "bibleDisclaimer.html").write_text(
            '<p class="bibleDisclaimer"><a href="https://hope.biblestudyman.co.uk/TCSB/">The CleanSlate Bible</a> is an adaptation of the <a href="https://worldenglish.bible" target="_blank">WEB</a><br>Square brackets mark words not found in the original text.</p>\n',
            encoding="utf-8",
        )
        (self.bl / "database" / "bibleVerses.sql").write_text("INSERT verse old;\n", encoding="utf-8")
        (self.bl / "database" / "translationToDo.txt").write_text("todo old\n", encoding="utf-8")
        write_metadata(self.bl / "database" / "tcsbMetadata.sql")
        (self.tcsb / "exports" / "bibleStrongs.sql").write_text("STRONGS source old;\n", encoding="utf-8")
        (self.tcsb / "source" / "tcsb-usfm_2026-06-28" / "97-GLOengtcsbp.usfm").write_text("\\id GLO\nold glossary\n", encoding="utf-8")
        (self.tcsb / "source" / "tcsb-usfm_2026-06-28" / "31-OBAengtcsbp.usfm").write_text("\\id OBA\nold Obadiah source\n", encoding="utf-8")
        (self.tcsb / "tools" / "plain_usfm_to_sql.py").write_text(
            r"""
import sys
from pathlib import Path

source_dir = Path(sys.argv[1])
output_sql = Path(sys.argv[2])
glossary = (source_dir / '97-GLOengtcsbp.usfm').read_text(encoding='utf-8')
output_sql.parent.mkdir(parents=True, exist_ok=True)
output_sql.write_text(
    "DROP TABLE IF EXISTS `verses`;\n"
    "CREATE TABLE `verses` (\n"
    "  `verseID` int(11) NOT NULL AUTO_INCREMENT,\n"
    "  `bookCode` varchar(3) NOT NULL,\n"
    "  `chapter` smallint(4) NOT NULL,\n"
    "  `verseNumber` smallint(4) NOT NULL,\n"
    "  `verseText` text NOT NULL,\n"
    "  PRIMARY KEY (`verseID`),\n"
    "  UNIQUE KEY `book-chapter-verse` (`bookCode`,`chapter`,`verseNumber`)\n"
    ") ENGINE=MyISAM DEFAULT CHARSET=latin1;\n"
    "INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('OBA', 1, 1, 'USFM Obadiah text{H5662}.');\n"
    "INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('JOL', 1, 1, 'USFM Joel text{H3068}.');\n",
    encoding='utf-8',
)
(output_sql.parent / 'bibleStrongs.sql').write_text('GENERATED STRONGS FROM: ' + glossary, encoding='utf-8')
""".lstrip(),
            encoding="utf-8",
        )
        (self.tcsb / "database-components" / "bibleCompletedVerses.sql").write_text("COMPLETED old;\n", encoding="utf-8")
        (self.bsm / "database" / "bibleImportSettings.sql").write_text("SETTINGS old;\n", encoding="utf-8")
        (self.bsm / "database" / "bibleSchema.sql").write_text("SCHEMA old;\n", encoding="utf-8")
        (self.bsm / "database" / "bibleStrongs.sql").write_text("STRONGS source old;\n", encoding="utf-8")
        (self.bsm / "database" / "bibleCompletedVerses.sql").write_text("COMPLETED old;\n", encoding="utf-8")
        (self.bsm / "database" / "bibleVerses.sql").write_text("INSERT verse old;\n", encoding="utf-8")
        write_metadata(self.bsm / "database" / "tcsbMetadata.sql")
        (self.bsm / "database" / "bibleComplete.sql").write_text("", encoding="utf-8")
        self.initial_bl_commit = commit(self.bl, "initial BL")
        self.initial_tcsb_commit = commit(self.tcsb, "initial TCSB")
        self.initial_bsm_commit = commit(self.bsm, "initial BSM")
        write_metadata(self.bl / "database" / "tcsbMetadata.sql", bl_commit=self.initial_bl_commit, bsm_commit=self.initial_bsm_commit, tcsb_strongs_commit=self.initial_tcsb_commit, tcsb_glossary_commit=self.initial_tcsb_commit)
        write_metadata(self.bsm / "database" / "tcsbMetadata.sql", bl_commit=self.initial_bl_commit, bsm_commit=self.initial_bsm_commit, tcsb_strongs_commit=self.initial_tcsb_commit, tcsb_glossary_commit=self.initial_tcsb_commit)
        commit(self.bl, "record initial metadata")
        commit(self.bsm, "record initial metadata")

    def tearDown(self):
        shutil.rmtree(self.tmp)

    def run_script(self, *extra, use_default_bl=False, db_import=False, env=None):
        bl_args = [] if use_default_bl else ["--bl-root", str(self.bl)]
        db_args = [] if db_import else ["--no-db-import"]
        return run([
            "python3",
            str(SCRIPT),
            "--no-push",
            "--no-pull",
            *db_args,
            *bl_args,
            "--bsm-root",
            str(self.bsm),
            "--tcsb-root",
            str(self.tcsb),
            "--revision",
            "260722",
            "--revision-date",
            "2026-07-22",
            *extra,
        ], env=env)

    def fake_mariadb_env(self):
        bin_dir = self.tmp / "fake-bin"
        bin_dir.mkdir()
        capture_sql = self.tmp / "mariadb-import.sql"
        capture_args = self.tmp / "mariadb-args.txt"
        capture_pwd = self.tmp / "mariadb-pwd.txt"
        fake_client = bin_dir / "mariadb"
        fake_client.write_text(
            "#!/usr/bin/env python3\n"
            "import os, sys\n"
            f"open({str(capture_args)!r}, 'w').write('\\n'.join(sys.argv[1:]))\n"
            f"open({str(capture_sql)!r}, 'w').write(sys.stdin.read())\n"
            f"open({str(capture_pwd)!r}, 'w').write(os.environ.get('MYSQL_PWD', ''))\n",
            encoding="utf-8",
        )
        fake_client.chmod(0o755)
        env = os.environ.copy()
        env.update({
            "PATH": f"{bin_dir}{os.pathsep}" + env.get("PATH", ""),
            "BSM_DB_NAME": "bible_test",
            "BSM_DB_USER": "bsm_user",
            "BSM_DB_PASSWORD": "secret-for-test",
            "BSM_DB_HOST": "127.0.0.1",
        })
        return env, capture_sql, capture_args, capture_pwd

    def test_rebuilds_bible_complete_with_metadata_between_start_and_verses(self):
        (self.bl / "database" / "bibleVerses.sql").write_text("INSERT verse changed;\n", encoding="utf-8")
        commit(self.bl, "change verses for complete rebuild")

        result = self.run_script()
        self.assertEqual(result.returncode, 0, result.stdout)
        expected = "\n".join(
            [
                (self.bsm / "database" / "bibleImportSettings.sql").read_text(encoding="utf-8"),
                (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8"),
                (self.bsm / "database" / "bibleSchema.sql").read_text(encoding="utf-8"),
                (self.bsm / "database" / "bibleStrongs.sql").read_text(encoding="utf-8"),
                (self.bsm / "database" / "bibleCompletedVerses.sql").read_text(encoding="utf-8"),
                (self.bsm / "database" / "bibleVerses.sql").read_text(encoding="utf-8"),
            ]
        )
        complete = (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
        self.assertEqual(complete, expected)
        self.assertIn("tcsb_text_metadata", complete)
        self.assertLess(complete.index("tcsb_text_metadata"), complete.index("SCHEMA old"))
        self.assertLess(complete.index("SCHEMA old"), complete.index("STRONGS source old"))
        self.assertLess(complete.index("STRONGS source old"), complete.index("COMPLETED old"))

    def test_shell_wrapper_is_only_a_python_launcher(self):
        wrapper = WRAPPER.read_text(encoding="utf-8").strip().splitlines()
        self.assertEqual(wrapper[0], "#!/usr/bin/env bash")
        self.assertEqual(wrapper[1], 'exec python3 "$(dirname "$0")/nightly-tcsb-revision-sync.py" "$@"')

    def test_can_use_ignored_local_paths_for_bookish_lamp_location(self):
        other = self.tmp / "not-a-sibling" / "bookish-lamp-real"
        shutil.copytree(self.bl, other)
        (self.bsm / "local_paths.json").write_text(
            '{"bookish_lamp_repo":"' + str(other).replace('\\\\', '/') + '"}\n',
            encoding="utf-8",
        )
        (other / "database" / "bibleVerses.sql").write_text("INSERT verse changed elsewhere;\n", encoding="utf-8")
        commit(other, "change verses through local paths")

        result = self.run_script(use_default_bl=True)

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        self.assertIn("INSERT verse changed elsewhere;", (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8"))

    def test_bumps_revision_when_bookish_lamp_bible_verses_changed(self):
        (self.bl / "database" / "bibleVerses.sql").write_text("INSERT verse changed;\n", encoding="utf-8")
        new_bl_commit = commit(self.bl, "change verses")

        result = self.run_script()

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        bl_metadata = (self.bl / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        bsm_metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn("('text_revision', '260722')", bl_metadata)
        self.assertIn("`metadataValue` text NOT NULL", bl_metadata)
        self.assertIn("('tcsb_disclaimer_html', '<p class=\"bibleDisclaimer\">", bl_metadata)
        self.assertIn("('tcsb_disclaimer_text', 'The CleanSlate Bible is an adaptation of the WEB", bl_metadata)
        self.assertIn(f"('bl_bible_verses_commit', '{new_bl_commit}')", bl_metadata)
        self.assertIn("('tcsb_bible_strongs_commit', '", bl_metadata)
        self.assertEqual(bl_metadata, bsm_metadata)
        self.assertEqual(
            (self.bl / "database" / "bibleVerses.sql").read_text(encoding="utf-8"),
            (self.bsm / "database" / "bibleVerses.sql").read_text(encoding="utf-8"),
        )
        latest_author = run(["git", "log", "-1", "--format=%an <%ae>"], cwd=self.bsm).stdout.strip()
        self.assertEqual(latest_author, "TCSB Sync Bot <tcsb-sync@hermes.local>")
        complete = (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
        self.assertIn("SETTINGS old;", complete)
        self.assertIn("SCHEMA old;", complete)
        self.assertIn("STRONGS source old;", complete)
        self.assertIn("COMPLETED old;", complete)
        self.assertIn("('text_revision', '260722')", complete)
        self.assertIn("INSERT verse changed;", complete)

    def test_imports_bible_complete_to_mariadb_after_text_revision_sync(self):
        env, capture_sql, capture_args, capture_pwd = self.fake_mariadb_env()
        (self.bl / "database" / "bibleVerses.sql").write_text("INSERT verse changed;\n", encoding="utf-8")
        commit(self.bl, "change verses for database import")

        result = self.run_script(db_import=True, env=env)

        self.assertIn("Imported BSM bibleComplete.sql into MariaDB", result.stdout)
        self.assertEqual(
            (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8"),
            capture_sql.read_text(encoding="utf-8"),
        )
        args = capture_args.read_text(encoding="utf-8").splitlines()
        self.assertIn("-ubsm_user", args)
        self.assertIn("-h127.0.0.1", args)
        self.assertIn("bible_test", args)
        self.assertFalse(any("secret-for-test" in arg for arg in args))
        self.assertEqual("secret-for-test", capture_pwd.read_text(encoding="utf-8"))

    def test_generates_verse_plain_when_bookish_lamp_verses_are_copied(self):
        (self.bl / "database" / "bibleVerses.sql").write_text(
            """
DROP TABLE IF EXISTS `verses`;
CREATE TABLE `verses` (
  `verseID` int(11) NOT NULL AUTO_INCREMENT,
  `bookCode` varchar(3) NOT NULL,
  `chapter` smallint(4) NOT NULL,
  `verseNumber` smallint(4) NOT NULL,
  `verseText` text NOT NULL,
  PRIMARY KEY (`verseID`),
  UNIQUE KEY `book-chapter-verse` (`bookCode`,`chapter`,`verseNumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('GEN',   1,   1, '<p>At the beginning, God{H0430} created-from-nothing{H1254} the sky{H8064} and the ground{H0776}.');
""".lstrip(),
            encoding="utf-8",
        )
        commit(self.bl, "change verses to real insert")

        result = self.run_script()

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        bsm_verses = (self.bsm / "database" / "bibleVerses.sql").read_text(encoding="utf-8")
        self.assertIn("`versePlain` text NOT NULL", bsm_verses)
        self.assertIn("`verseText`, `versePlain`", bsm_verses)
        self.assertIn("'At the beginning, God created-from-nothing the sky and the ground.'", bsm_verses)
        self.assertNotEqual((self.bl / "database" / "bibleVerses.sql").read_text(encoding="utf-8"), bsm_verses)
        complete = (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
        self.assertIn("`versePlain` text NOT NULL", complete)

    def test_promoted_usfm_book_replaces_only_that_book_in_hybrid_verses(self):
        (self.bl / "database" / "bibleVerses.sql").write_text(
            """
DROP TABLE IF EXISTS `verses`;
CREATE TABLE `verses` (
  `verseID` int(11) NOT NULL AUTO_INCREMENT,
  `bookCode` varchar(3) NOT NULL,
  `chapter` smallint(4) NOT NULL,
  `verseNumber` smallint(4) NOT NULL,
  `verseText` text NOT NULL,
  PRIMARY KEY (`verseID`),
  UNIQUE KEY `book-chapter-verse` (`bookCode`,`chapter`,`verseNumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('GEN', 1, 1, 'BL Genesis text.');
INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('OBA', 1, 1, 'BL Obadiah text.');
""".lstrip(),
            encoding="utf-8",
        )
        commit(self.bl, "change verses with Obadiah")
        promoted = self.tcsb / "data" / "tcsb_promoted_usfm_books.txt"
        promoted.write_text("OBA\n", encoding="utf-8")
        promoted_commit = commit(self.tcsb, "promote Obadiah USFM")

        result = self.run_script()

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        self.assertIn("TCSB promoted USFM books: OBA", result.stdout)
        bsm_verses = (self.bsm / "database" / "bibleVerses.sql").read_text(encoding="utf-8")
        self.assertIn("'GEN',   1,   1, 'BL Genesis text.'", bsm_verses)
        self.assertIn("'OBA',   1,   1, 'USFM Obadiah text{H5662}.'", bsm_verses)
        self.assertIn("'USFM Obadiah text.'", bsm_verses)
        self.assertNotIn("BL Obadiah text", bsm_verses)
        metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn("('tcsb_promoted_usfm_books', 'OBA')", metadata)
        self.assertIn(f"('tcsb_promoted_usfm_commit', '{promoted_commit}')", metadata)

    def test_promoted_usfm_book_code_alias_replaces_bookish_lamp_code(self):
        (self.bl / "database" / "bibleVerses.sql").write_text(
            """
DROP TABLE IF EXISTS `verses`;
CREATE TABLE `verses` (
  `verseID` int(11) NOT NULL AUTO_INCREMENT,
  `bookCode` varchar(3) NOT NULL,
  `chapter` smallint(4) NOT NULL,
  `verseNumber` smallint(4) NOT NULL,
  `verseText` text NOT NULL,
  PRIMARY KEY (`verseID`),
  UNIQUE KEY `book-chapter-verse` (`bookCode`,`chapter`,`verseNumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('JOE', 1, 1, 'BL Joel text.');
""".lstrip(),
            encoding="utf-8",
        )
        commit(self.bl, "change verses with Joel")
        (self.tcsb / "source" / "tcsb-usfm_2026-06-28" / "30-JOLengtcsbp.usfm").write_text(
            "\\id JOL\nold Joel source\n",
            encoding="utf-8",
        )
        promoted = self.tcsb / "data" / "tcsb_promoted_usfm_books.txt"
        promoted.write_text("JOE\n", encoding="utf-8")
        commit(self.tcsb, "promote Joel USFM")

        result = self.run_script()

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        self.assertIn("TCSB promoted USFM books: JOE", result.stdout)
        bsm_verses = (self.bsm / "database" / "bibleVerses.sql").read_text(encoding="utf-8")
        self.assertIn("'JOE',   1,   1, 'USFM Joel text{H3068}.'", bsm_verses)
        self.assertIn("'USFM Joel text.'", bsm_verses)
        self.assertNotIn("'JOL'", bsm_verses)
        self.assertNotIn("BL Joel text", bsm_verses)

    def test_copies_strongs_from_tcsb_and_bumps_revision_when_tcsb_bible_strongs_changed(self):
        (self.tcsb / "exports" / "bibleStrongs.sql").write_text("STRONGS source changed;\n", encoding="utf-8")
        new_tcsb_strongs_commit = commit(self.tcsb, "change exported bible strongs")

        result = self.run_script()

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn(f"('tcsb_bible_strongs_commit', '{new_tcsb_strongs_commit}')", metadata)
        self.assertIn("STRONGS source changed;", (self.bsm / "database" / "bibleStrongs.sql").read_text(encoding="utf-8"))
        self.assertIn("STRONGS source changed;", (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8"))

    def test_regenerates_strongs_and_bumps_revision_when_tcsb_glossary_usfm_changed(self):
        glossary = self.tcsb / "source" / "tcsb-usfm_2026-06-28" / "97-GLOengtcsbp.usfm"
        glossary.write_text("\\id GLO\nchanged glossary source\n", encoding="utf-8")
        new_glossary_commit = commit(self.tcsb, "change glossary source")

        result = self.run_script()

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        self.assertIn("Generated TCSB bibleStrongs.sql from glossary source", result.stdout)
        self.assertIn("changed glossary source", (self.tcsb / "exports" / "bibleStrongs.sql").read_text(encoding="utf-8"))
        self.assertIn("changed glossary source", (self.bsm / "database" / "bibleStrongs.sql").read_text(encoding="utf-8"))
        self.assertIn("changed glossary source", (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8"))
        metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn(f"('tcsb_glossary_usfm_commit', '{new_glossary_commit}')", metadata)
        self.assertIn("('text_revision', '260722')", metadata)

    def test_bumps_revision_when_bsm_bible_schema_changed(self):
        (self.bsm / "database" / "bibleSchema.sql").write_text("SCHEMA changed;\n", encoding="utf-8")
        new_bsm_commit = commit(self.bsm, "change bible schema")

        result = self.run_script()

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn(f"('bsm_bible_schema_commit', '{new_bsm_commit}')", metadata)
        self.assertIn("SCHEMA changed;", (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8"))

    def test_rebuilds_stale_bible_complete_when_completed_component_already_matches(self):
        (self.bsm / "database" / "bibleComplete.sql").write_text("STALE COMPLETE\n", encoding="utf-8")
        commit(self.bsm, "make complete stale")

        result = self.run_script()

        self.assertIn("Synced TCSB completed verses without text revision change", result.stdout)
        self.assertEqual("COMPLETED old;\n", (self.bsm / "database" / "bibleCompletedVerses.sql").read_text(encoding="utf-8"))
        complete = (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
        self.assertIn("COMPLETED old;", complete)
        self.assertNotIn("STALE COMPLETE", complete)
        metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn("('text_revision', '260701')", metadata)
        self.assertNotIn("260722", metadata)

    def test_imports_bible_complete_to_mariadb_after_completed_verses_sync(self):
        env, capture_sql, _, _ = self.fake_mariadb_env()
        (self.tcsb / "database-components" / "bibleCompletedVerses.sql").write_text("COMPLETED changed;\n", encoding="utf-8")
        commit(self.tcsb, "change completed verses for database import")

        result = self.run_script(db_import=True, env=env)

        self.assertIn("Imported BSM bibleComplete.sql into MariaDB", result.stdout)
        self.assertEqual(
            (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8"),
            capture_sql.read_text(encoding="utf-8"),
        )

    def test_ignores_translation_todo_and_completed_verses_for_revision_gate(self):
        (self.bl / "database" / "translationToDo.txt").write_text("todo changed\n", encoding="utf-8")
        commit(self.bl, "change todo only")
        (self.tcsb / "database-components" / "bibleCompletedVerses.sql").write_text("COMPLETED changed;\n", encoding="utf-8")
        commit(self.tcsb, "change completed only")

        result = self.run_script()

        self.assertIn("Synced TCSB completed verses without text revision change", result.stdout)
        metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn("('text_revision', '260701')", metadata)
        self.assertNotIn("260722", metadata)
        self.assertEqual(
            "COMPLETED changed;\n",
            (self.bsm / "database" / "bibleCompletedVerses.sql").read_text(encoding="utf-8"),
        )
        self.assertIn("COMPLETED changed;", (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8"))


if __name__ == "__main__":
    unittest.main()
