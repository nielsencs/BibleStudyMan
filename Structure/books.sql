-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 17, 2018 at 06:07 PM
-- Server version: 10.2.14-MariaDB-log
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `biblestu_dy`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--
DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `orderChristian` tinyint(2) NOT NULL,
  `bookChapters` smallint(6) NOT NULL,
  `bookCode` varchar(3) NOT NULL,
  `bookName` varchar(25) NOT NULL,
  `orderJewish` tinyint(2) NOT NULL,
  `orderChron1` tinyint(2) NOT NULL,
  `orderChron2` tinyint(2) NOT NULL,
  `testament` varchar(1) NOT NULL,
  `sectionCode` varchar(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`orderChristian`, `bookChapters`, `bookCode`, `bookName`, `orderJewish`, `orderChron1`, `orderChron2`, `testament`, `sectionCode`) VALUES
( 1, 50,  'GEN', 'Genesis',     1,  1, 2, 'O', '1TOR'),
( 2, 40,  'EXO', 'Exodus',      2,  2, 3, 'O', '1TOR'),
( 3, 27,  'LEV', 'Leviticus',   3,  3, 4, 'O', '1TOR'),
( 4, 36,  'NUM', 'Numbers',     4,  4, 5, 'O', '1TOR'),
( 5, 34,  'DEU', 'Deuteronomy', 5,  5, 6, 'O', '1TOR'),
( 6, 24,  'JOS', 'Joshua',      6,  6, 8, 'O', '2NV1'),
( 7, 21,  'JDG', 'Judges',      7,  7, 9, 'O', '2NV1'),
( 8, 4,   'RUT', 'Ruth',       31,  9, 10, 'O', '2NV1'),
( 9, 31,  '1SM', '1 Samuel',    8, 10, 14, 'O', '2NV1'),
(10, 24,  '2SM', '2 Samuel',    9, 11, 15, 'O', '2NV1'),
(11, 22,  '1KI', '1 Kings',    10, 26, 29, 'O', '2NV1'),
(12, 25,  '2KI', '2 Kings', 11, 27, 30, 'O', '2NV1'),
(13, 29,  '1CH', '1 Chronicles', 38, 28, 35, 'O', '3KTV'),
(14, 36,  '2CH', '2 Chronicles', 39, 29, 36, 'O', '3KTV'),
(15, 10,  'EZR', 'Ezra', 36, 34, 34, 'O', '3KTV'),
(16, 13,  'NEH', 'Nehemiah', 37, 35, 39, 'O', '3KTV'),
(17, 10,  'EST', 'Esther', 34, 36, 37, 'O', '3KTV'),
(18, 42,  'JOB', 'Job', 29, 12, 1, 'O', '3KTV'),
(19, 150, 'PSA', 'Psalms', 27, 8, 7, 'O', '3KTV'),
(20, 31,  'PRO', 'Proverbs', 28, 13, 12, 'O', '3KTV'),
(21, 12,  'ECC', 'Ecclesiastes', 33, 14, 13, 'O', '3KTV'),
(22, 8,   'SON', 'Song of Songs', 30, 15, 11, 'O', '3KTV'),
(23, 66,  'ISA', 'Isaiah', 12, 16, 22, 'O', '4NV2'),
(24, 52,  'JER', 'Jeremiah', 13, 30, 28, 'O', '4NV2'),
(25, 5,   'LAM', 'Lamentations', 32, 31, 27, 'O', '4NV2'),
(26, 48,  'EZE', 'Ezekiel', 14, 32, 26, 'O', '4NV2'),
(27, 12,  'DAN', 'Daniel', 35, 33, 31, 'O', '4NV2'),
(28, 14,  'HOS', 'Hosea', 15, 17, 20, 'O', '4NV2'),
(29, 3,   'JOE', 'Joel', 16, 18, 17, 'O', '4NV2'),
(30, 9,   'AMO', 'Amos', 17, 19, 19, 'O', '4NV2'),
(31, 1,   'OBA', 'Obadiah', 18, 20, 16, 'O', '4NV2'),
(32, 4,   'JON', 'Jonah', 19, 21, 18, 'O', '4NV2'),
(33, 7,   'MIC', 'Micah', 20, 22, 21, 'O', '4NV2'),
(34, 3,   'NAH', 'Nahum', 21, 23, 23, 'O', '4NV2'),
(35, 3,   'HAB', 'Habakkuk', 22, 24, 25, 'O', '4NV2'),
(36, 3,   'ZEP', 'Zephaniah', 23, 25, 24, 'O', '4NV2'),
(37, 2,   'HAG', 'Haggai', 24, 37, 32, 'O', '4NV2'),
(38, 14,  'ZEC', 'Zechariah', 25, 38, 33, 'O', '4NV2'),
(39, 4,   'MAL', 'Malachi', 26, 39, 38, 'O', '4NV2'),
(40, 28,  'MAT', 'Matthew', 99, 40, 43, 'N', '5NCV'),
(41, 16,  'MAR', 'Mark', 99, 61, 42, 'N', '5NCV'),
(42, 24,  'LUK', 'Luke', 99, 46, 49, 'N', '5NCV'),
(43, 21,  'JOH', 'John', 99, 62, 62, 'N', '5NCV'),
(44, 28,  'ACT', 'Acts', 99, 52, 54, 'N', '5NCV'),
(45, 16,  'ROM', 'Romans', 99, 45, 48, 'N', '5NCV'),
(46, 16,  '1CO', '1 Corinthians', 99, 43, 46, 'N', '5NCV'),
(47, 13,  '2CO', '2 Corinthians', 99, 44, 47, 'N', '5NCV'),
(48, 6,   'GAL', 'Galatians', 99, 47, 41, 'N', '5NCV'),
(49, 6,   'EPH', 'Ephesians', 99, 48, 50, 'N', '5NCV'),
(50, 4,   'PHP', 'Philippians', 99, 49, 51, 'N', '5NCV'),
(51, 4,   'COL', 'Colossians', 99, 50, 53, 'N', '5NCV'),
(52, 5,   '1TH', '1 Thessalonians', 99, 41, 44, 'N', '5NCV'),
(53, 3,   '2TH', '2 Thessalonians', 99, 42, 45, 'N', '5NCV'),
(54, 6,   '1TI', '1 Timothy', 99, 53, 55, 'N', '5NCV'),
(55, 4,   '2TI', '2 Timothy', 99, 54, 58, 'N', '5NCV'),
(56, 3,   'TIT', 'Titus', 99, 55, 56, 'N', '5NCV'),
(57, 1,   'PHM', 'Philemon', 99, 51, 52, 'N', '5NCV'),
(58, 13,  'HEB', 'Hebrews', 99, 56, 60, 'N', '5NCV'),
(59, 5,   'JAM', 'James', 99, 57, 40, 'N', '5NCV'),
(60, 5,   '1PE', '1 Peter', 99, 59, 57, 'N', '5NCV'),
(61, 3,   '2PE', '2 Peter', 99, 60, 59, 'N', '5NCV'),
(62, 5,   '1JO', '1 John', 99, 63, 63, 'N', '5NCV'),
(63, 1,   '2JO', '2 John', 99, 64, 64, 'N', '5NCV'),
(64, 1,   '3JO', '3 John', 99, 65, 65, 'N', '5NCV'),
(65, 1,   'JDE', 'Jude', 99, 58, 61, 'N', '5NCV'),
(66, 22,  'REV', 'Revelation', 99, 66, 66, 'N', '5NCV');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`orderChristian`);
COMMIT;

