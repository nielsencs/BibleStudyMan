DROP TABLE IF EXISTS `tcsb_text_metadata`;
CREATE TABLE `tcsb_text_metadata` (
  `metadataKey` varchar(40) NOT NULL,
  `metadataValue` text NOT NULL,
  PRIMARY KEY (`metadataKey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision', '260906');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_version', '260906');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision_date', '2026-09-06');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_repo', 'bookish-lamp');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_branch', 'master');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_file', 'database/bibleVerses.sql + promoted TCSB USFM books');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bl_bible_verses_commit', 'e491c3fbc4be7010dc374d00c21fc20fb39dee1e');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bsm_bible_schema_commit', '22e09c06e3a8aa1a8ec03d06c0e4e38db21692d7');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_bible_strongs_commit', '23e5f3c198db5e25324906453158b2a88696e6b7');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_glossary_usfm_commit', '0612fe63abcb9a0e4b31649434924345d09f88c7');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_promoted_usfm_books', 'PRO,JOL,OBA,HAB');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_promoted_usfm_commit', 'fa7a4207f25e47cc8d1a62de29423e356f7461e3');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('generated_at', '2026-09-07T03:30:54+01:00');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_disclaimer_html', '<p class="bibleDisclaimer"><a href="https://hope.biblestudyman.co.uk/TCSB/">The CleanSlate Bible</a> is an adaptation of the <a href="https://worldenglish.bible" target="_blank">WEB</a>
to include nuanced meanings of particular ancient words for placenames, God and others of special interest.
<br>In general square brackets:[] are used to indicate words not found in the original text.
<br>They also indicate the 5 books of the Psalms, and the letters in Psalm 119;
<br>and a few passages considered by some to be of questionable authenticity, marked with an asterisk(*).</p>');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_disclaimer_text', 'The CleanSlate Bible is an adaptation of the WEB
to include nuanced meanings of particular ancient words for placenames, God and others of special interest.
In general square brackets:[] are used to indicate words not found in the original text.
They also indicate the 5 books of the Psalms, and the letters in Psalm 119;
and a few passages considered by some to be of questionable authenticity, marked with an asterisk(*).');
