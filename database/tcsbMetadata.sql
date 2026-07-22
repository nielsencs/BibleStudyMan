DROP TABLE IF EXISTS `tcsb_text_metadata`;
CREATE TABLE `tcsb_text_metadata` (
  `metadataKey` varchar(40) NOT NULL,
  `metadataValue` varchar(255) NOT NULL,
  PRIMARY KEY (`metadataKey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision', '260722');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_version', '260722');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision_date', '2026-07-22');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_repo', 'bookish-lamp');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_branch', 'master');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_file', 'database/bibleVerses.sql');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bl_bible_verses_commit', 'f8383fad5192ad580a0304eda545052dfb15e13d');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bsm_bible_start_commit', 'f87559b55852f39b13c346f3742e9dd576c3d4fc');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('generated_at', '2026-07-22T23:41:08+01:00');
