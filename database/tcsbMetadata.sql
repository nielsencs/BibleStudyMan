DROP TABLE IF EXISTS `tcsb_text_metadata`;
CREATE TABLE `tcsb_text_metadata` (
  `metadataKey` varchar(40) NOT NULL,
  `metadataValue` varchar(255) NOT NULL,
  PRIMARY KEY (`metadataKey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('text_version', 'TCSB-2026.06.26.1');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('version_date', '2026-06-26');
INSERT INTO `tcsb_text_metadata` (`metadataKey`, `metadataValue`) VALUES ('version_source', 'bookish-lamp/database/bibleVerses.sql');
