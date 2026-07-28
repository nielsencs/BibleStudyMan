import importlib.util
import tempfile
import unittest
from pathlib import Path


REPO = Path(__file__).resolve().parents[1]
SCRIPT = REPO / "scripts" / "generate-verse-plain.py"


def load_module():
    spec = importlib.util.spec_from_file_location("generate_verse_plain", SCRIPT)
    if spec is None or spec.loader is None:
        raise AssertionError(f"Could not load {SCRIPT}")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


class GenerateVersePlainTests(unittest.TestCase):
    def test_plain_text_removes_markup_and_strongs_but_keeps_reader_text(self):
        module = load_module()

        self.assertEqual(
            module.verse_plain("<p>At the beginning, God{H0430} created-from-nothing{H1254} the sky{H8064} and the ground{H0776}."),
            "At the beginning, God created-from-nothing the sky and the ground.",
        )
        self.assertEqual(
            module.verse_plain("The earth was wild and waste{H0922}. Darkness was on the surface of the deep and God{H0430}&apos;s Spirit{H7307} was hovering over the surface of the waters.</p>"),
            "The earth was wild and waste. Darkness was on the surface of the deep and God's Spirit was hovering over the surface of the waters.",
        )

    def test_rewrites_bible_verses_sql_with_verse_plain_column_and_values(self):
        module = load_module()
        source = """
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
""".lstrip()

        with tempfile.TemporaryDirectory() as tmp:
            path = Path(tmp) / "bibleVerses.sql"
            path.write_text(source, encoding="utf-8")

            changed = module.rewrite_file(path)

            self.assertTrue(changed)
            rewritten = path.read_text(encoding="utf-8")
            self.assertIn("  `versePlain` text NOT NULL,", rewritten)
            self.assertIn("`verseText`, `versePlain`", rewritten)
            self.assertIn(
                "'At the beginning, God created-from-nothing the sky and the ground.'",
                rewritten,
            )

    def test_rewrite_is_idempotent(self):
        module = load_module()
        with tempfile.TemporaryDirectory() as tmp:
            path = Path(tmp) / "bibleVerses.sql"
            path.write_text(
                """
DROP TABLE IF EXISTS `verses`;
CREATE TABLE `verses` (
  `verseID` int(11) NOT NULL AUTO_INCREMENT,
  `bookCode` varchar(3) NOT NULL,
  `chapter` smallint(4) NOT NULL,
  `verseNumber` smallint(4) NOT NULL,
  `verseText` text NOT NULL,
  `versePlain` text NOT NULL,
  PRIMARY KEY (`verseID`),
  UNIQUE KEY `book-chapter-verse` (`bookCode`,`chapter`,`verseNumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
INSERT INTO `verses` (`bookCode`, `chapter`, `verseNumber`, `verseText`, `versePlain`) VALUES ('GEN',   1,   1, '<p>At the beginning, God{H0430}.', 'At the beginning, God.');
""".lstrip(),
                encoding="utf-8",
            )
            before = path.read_text(encoding="utf-8")

            changed = module.rewrite_file(path)

            self.assertFalse(changed)
            self.assertEqual(path.read_text(encoding="utf-8"), before)


if __name__ == "__main__":
    unittest.main()
