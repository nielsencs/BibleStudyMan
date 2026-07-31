import subprocess
import tempfile
import unittest
from pathlib import Path


REPO = Path(__file__).resolve().parents[1]
SCRIPT = REPO / "scripts" / "check-tcsb-database-integrity.py"


GOOD_VERSES = """
DROP TABLE IF EXISTS `verses`;
CREATE TABLE `verses` (
  `bookCode` varchar(3) NOT NULL,
  `chapter` smallint(4) NOT NULL,
  `verseNumber` smallint(4) NOT NULL,
  `verseText` text NOT NULL
);
INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('GEN',   1,   1, '<p>At the beginning, God{H0430} created-from-nothing{H1254} the sky{H8064} and the ground{H0776}.');
INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('GEN',  17,   1, '<p>When Abram was ninety-nine years old, ForeverOne{H3068} appeared to Abram and said to him, "I am God{H0410} Almighty{H7706}. Walk before me and be blameless.');
""".strip()

GOOD_STRONGS = """
DROP TABLE IF EXISTS strongs;
CREATE TABLE strongs (
  strongsNumber varchar(7) NOT NULL,
  strongsIsName tinyint(1) NOT NULL,
  strongsOriginal varchar(25) NOT NULL,
  strongsEnglish varchar(25) NOT NULL,
  strongsDefinition text NOT NULL,
  PRIMARY KEY (strongsNumber)
);
INSERT INTO strongs (strongsNumber, strongsIsName, strongsOriginal, strongsEnglish, strongsDefinition) VALUES('H0410', 0, 'El', 'God', 'Singular God.');
INSERT INTO strongs (strongsNumber, strongsIsName, strongsOriginal, strongsEnglish, strongsDefinition) VALUES('H0430', 0, 'Elohim', 'God', 'God.');
INSERT INTO strongs (strongsNumber, strongsIsName, strongsOriginal, strongsEnglish, strongsDefinition) VALUES('H0776', 0, 'erets', 'ground', 'Ground.');
INSERT INTO strongs (strongsNumber, strongsIsName, strongsOriginal, strongsEnglish, strongsDefinition) VALUES('H1254', 0, 'bara', 'created-from-nothing', 'Create.');
INSERT INTO strongs (strongsNumber, strongsIsName, strongsOriginal, strongsEnglish, strongsDefinition) VALUES('H3068', 1, 'YHWH', 'ForeverOne', 'Name.');
INSERT INTO strongs (strongsNumber, strongsIsName, strongsOriginal, strongsEnglish, strongsDefinition) VALUES('H7706', 0, 'Shaddai', 'Almighty', 'Almighty.');
INSERT INTO strongs (strongsNumber, strongsIsName, strongsOriginal, strongsEnglish, strongsDefinition) VALUES('H8064', 0, 'shamayim', 'sky', 'Sky.');
""".strip()


class TcsbDatabaseIntegrityTests(unittest.TestCase):
    def setUp(self):
        self.tmpdir = tempfile.TemporaryDirectory()
        self.root = Path(self.tmpdir.name)
        self.verses = self.root / "bibleVerses.sql"
        self.strongs = self.root / "bibleStrongs.sql"
        self.verses.write_text(GOOD_VERSES + "\n", encoding="utf-8")
        self.strongs.write_text(GOOD_STRONGS + "\n", encoding="utf-8")

    def tearDown(self):
        self.tmpdir.cleanup()

    def run_check(self):
        return subprocess.run(
            ["python3", str(SCRIPT), "--verses", str(self.verses), "--strongs", str(self.strongs)],
            check=False,
            text=True,
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
        )

    def test_accepts_well_formed_verses_and_known_strongs(self):
        result = self.run_check()
        self.assertEqual(result.returncode, 0, result.stdout)
        self.assertIn("integrity check passed", result.stdout)

    def test_accepts_new_verse_plain_column_and_values(self):
        self.verses.write_text(
            GOOD_VERSES.replace(
                "`verseText` text NOT NULL",
                "`verseText` text NOT NULL,\n  `versePlain` text NOT NULL",
            ).replace(
                "`verseText`) VALUES ('GEN',   1,   1, '<p>At the beginning, God{H0430} created-from-nothing{H1254} the sky{H8064} and the ground{H0776}.')",
                "`verseText`, `versePlain`) VALUES ('GEN',   1,   1, '<p>At the beginning, God{H0430} created-from-nothing{H1254} the sky{H8064} and the ground{H0776}.', 'At the beginning, God created-from-nothing the sky and the ground.')",
            ).replace(
                "`verseText`) VALUES ('GEN',  17,   1, '<p>When Abram was ninety-nine years old, ForeverOne{H3068} appeared to Abram and said to him, \"I am God{H0410} Almighty{H7706}. Walk before me and be blameless.')",
                "`verseText`, `versePlain`) VALUES ('GEN',  17,   1, '<p>When Abram was ninety-nine years old, ForeverOne{H3068} appeared to Abram and said to him, \"I am God{H0410} Almighty{H7706}. Walk before me and be blameless.', 'When Abram was ninety-nine years old, ForeverOne appeared to Abram and said to him, \"I am God Almighty. Walk before me and be blameless.')",
            ),
            encoding="utf-8",
        )

        result = self.run_check()

        self.assertEqual(result.returncode, 0, result.stdout)

    def test_fails_when_verse_uses_strongs_code_missing_from_table(self):
        self.strongs.write_text(
            GOOD_STRONGS.replace(
                "INSERT INTO strongs (strongsNumber, strongsIsName, strongsOriginal, strongsEnglish, strongsDefinition) VALUES('H7706', 0, 'Shaddai', 'Almighty', 'Almighty.');\n",
                "",
            ),
            encoding="utf-8",
        )

        result = self.run_check()

        self.assertNotEqual(result.returncode, 0, result.stdout)
        self.assertIn("missing-strongs-definition", result.stdout)
        self.assertIn("H7706", result.stdout)
        self.assertIn("GEN 17:1", result.stdout)

    def test_fails_on_badly_formatted_verse_sql(self):
        self.verses.write_text(
            GOOD_VERSES
            + "\nINSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`) VALUES ('GEN', 1, 2, 'Bad hand edited verse);\n",
            encoding="utf-8",
        )

        result = self.run_check()

        self.assertNotEqual(result.returncode, 0, result.stdout)
        self.assertIn("malformed-verse-insert", result.stdout)


if __name__ == "__main__":
    unittest.main()
