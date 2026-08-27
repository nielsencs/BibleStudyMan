DROP TABLE IF EXISTS `tcsb_text_metadata`;
CREATE TABLE `tcsb_text_metadata` (
  `metadataKey` varchar(40) NOT NULL,
  `metadataValue` text NOT NULL,
  PRIMARY KEY (`metadataKey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision', '260826');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_version', '260826');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision_date', '2026-08-26');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_repo', 'bookish-lamp');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_branch', 'master');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_file', 'database/bibleVerses.sql + promoted TCSB USFM books');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bl_bible_verses_commit', 'fc223fbed879bdb2c9d15eeea2321feab4407d9c');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bsm_bible_schema_commit', 'ea7763fed0ad74c4055c39de1b4d8c43bfc917f2');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_bible_strongs_commit', '0101b9c882fc934f73dfa9212bda2884531a056c');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_glossary_usfm_commit', '696038d07c8588601969e7fc664a5ee3961de633');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_promoted_usfm_books', 'OBA');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('tcsb_promoted_usfm_commit', '886bf4ca81e4650a55b1e20902f93271c9c1af90');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('generated_at', '2026-08-27T03:30:31+01:00');
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
