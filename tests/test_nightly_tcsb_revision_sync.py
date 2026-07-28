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


def write_metadata(path: Path, revision="260701", bl_commit="old-bl", bsm_commit="old-bsm"):
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
        f"INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bsm_bible_schema_commit', '{bsm_commit}');\n",
        encoding="utf-8",
    )


class NightlyTcsbRevisionSyncTest(unittest.TestCase):
    def setUp(self):
        self.tmp = Path(tempfile.mkdtemp())
        self.bl = self.tmp / "bookish-lamp"
        self.bsm = self.tmp / "BibleStudyMan"
        init_repo(self.bl, "master")
        init_repo(self.bsm, "develop")
        (self.bl / "database").mkdir()
        (self.bsm / "database").mkdir()
        (self.bsm / "site").mkdir()
        (self.bsm / "site" / "bibleDisclaimer.html").write_text(
            '<p class="bibleDisclaimer"><a href="https://hope.biblestudyman.co.uk/TCSB/">The CleanSlate Bible</a> is an adaptation of the <a href="https://worldenglish.bible" target="_blank">WEB</a><br>Square brackets mark words not found in the original text.</p>\n',
            encoding="utf-8",
        )
        (self.bl / "database" / "bibleVerses.sql").write_text("INSERT verse old;\n", encoding="utf-8")
        (self.bl / "database" / "translationToDo.txt").write_text("todo old\n", encoding="utf-8")
        write_metadata(self.bl / "database" / "tcsbMetadata.sql")
        (self.bsm / "database" / "bibleImportSettings.sql").write_text("SETTINGS old;\n", encoding="utf-8")
        (self.bsm / "database" / "bibleSchema.sql").write_text("SCHEMA old;\n", encoding="utf-8")
        (self.bsm / "database" / "bibleCompletedVerses.sql").write_text("COMPLETED old;\n", encoding="utf-8")
        (self.bsm / "database" / "bibleVerses.sql").write_text("INSERT verse old;\n", encoding="utf-8")
        write_metadata(self.bsm / "database" / "tcsbMetadata.sql")
        (self.bsm / "database" / "bibleComplete.sql").write_text("", encoding="utf-8")
        self.initial_bl_commit = commit(self.bl, "initial BL")
        self.initial_bsm_commit = commit(self.bsm, "initial BSM")
        write_metadata(self.bl / "database" / "tcsbMetadata.sql", bl_commit=self.initial_bl_commit, bsm_commit=self.initial_bsm_commit)
        write_metadata(self.bsm / "database" / "tcsbMetadata.sql", bl_commit=self.initial_bl_commit, bsm_commit=self.initial_bsm_commit)
        commit(self.bl, "record initial metadata")
        commit(self.bsm, "record initial metadata")

    def tearDown(self):
        shutil.rmtree(self.tmp)

    def run_script(self, *extra):
        return run([
            "python3",
            str(SCRIPT),
            "--no-push",
            "--no-pull",
            "--bl-root",
            str(self.bl),
            "--bsm-root",
            str(self.bsm),
            "--revision",
            "260722",
            "--revision-date",
            "2026-07-22",
            *extra,
        ])

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
                (self.bsm / "database" / "bibleCompletedVerses.sql").read_text(encoding="utf-8"),
                (self.bsm / "database" / "bibleVerses.sql").read_text(encoding="utf-8"),
            ]
        )
        complete = (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
        self.assertEqual(complete, expected)
        self.assertIn("tcsb_text_metadata", complete)
        self.assertLess(complete.index("tcsb_text_metadata"), complete.index("SCHEMA old"))

    def test_shell_wrapper_is_only_a_python_launcher(self):
        wrapper = WRAPPER.read_text(encoding="utf-8").strip().splitlines()
        self.assertEqual(wrapper[0], "#!/usr/bin/env bash")
        self.assertEqual(wrapper[1], 'exec python3 "$(dirname "$0")/nightly-tcsb-revision-sync.py" "$@"')

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
        self.assertEqual(bl_metadata, bsm_metadata)
        self.assertEqual(
            (self.bl / "database" / "bibleVerses.sql").read_text(encoding="utf-8"),
            (self.bsm / "database" / "bibleVerses.sql").read_text(encoding="utf-8"),
        )
        complete = (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8")
        self.assertIn("SETTINGS old;", complete)
        self.assertIn("SCHEMA old;", complete)
        self.assertIn("COMPLETED old;", complete)
        self.assertIn("('text_revision', '260722')", complete)
        self.assertIn("INSERT verse changed;", complete)

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

    def test_bumps_revision_when_bsm_bible_schema_changed(self):
        (self.bsm / "database" / "bibleSchema.sql").write_text("SCHEMA changed;\n", encoding="utf-8")
        new_bsm_commit = commit(self.bsm, "change bible schema")

        result = self.run_script()

        self.assertIn("Synced TCSB text revision 260722", result.stdout)
        metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn(f"('bsm_bible_schema_commit', '{new_bsm_commit}')", metadata)
        self.assertIn("SCHEMA changed;", (self.bsm / "database" / "bibleComplete.sql").read_text(encoding="utf-8"))

    def test_ignores_translation_todo_and_completed_verses_for_revision_gate(self):
        (self.bl / "database" / "translationToDo.txt").write_text("todo changed\n", encoding="utf-8")
        commit(self.bl, "change todo only")
        (self.bsm / "database" / "bibleCompletedVerses.sql").write_text("COMPLETED changed;\n", encoding="utf-8")
        commit(self.bsm, "change completed only")

        result = self.run_script()

        self.assertIn("No TCSB text revision change", result.stdout)
        metadata = (self.bsm / "database" / "tcsbMetadata.sql").read_text(encoding="utf-8")
        self.assertIn("('text_revision', '260701')", metadata)
        self.assertNotIn("260722", metadata)


if __name__ == "__main__":
    unittest.main()
