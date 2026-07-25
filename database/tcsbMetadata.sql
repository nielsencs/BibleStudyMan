DROP TABLE IF EXISTS `tcsb_text_metadata`;
CREATE TABLE `tcsb_text_metadata` (
  `metadataKey` varchar(40) NOT NULL,
  `metadataValue` varchar(255) NOT NULL,
  PRIMARY KEY (`metadataKey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision', '260724');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_version', '260724');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_revision_date', '2026-07-24');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_repo', 'bookish-lamp');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_branch', 'master');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_source_file', 'database/bibleVerses.sql');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bl_bible_verses_commit', 'd113736512081422b706b404f559a011c39625c4');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('bsm_bible_schema_commit', '40b6916e97818f9440bbb9e5454528328d07f03a');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('generated_at', '2026-07-25T03:30:12+01:00');
