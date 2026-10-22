<?php
// Shared Bible text formatting logic. Governing rule: BSMT decision 0008.

const ONE_CHAPTER_BOOKS = ['Obadiah', 'Philemon', '2 John', '3 John', 'Jude'];

function bookLabel($tBook){
  $t = (string)$tBook;
  return ($t === 'Psalms' || $t === 'Psalm') ? 'Psalm' : $t;
}

function isPsalmsBook($tBook){
  return $tBook === 'Psalms' || $tBook === 'Psalm';
}

// Books with one chapter (or an unknown count of 0/1) get no chapter heading.
function bookChapterCountFor($tBook){
  return in_array($tBook, ONE_CHAPTER_BOOKS, true) ? 1 : 2; // only "is it 1?" matters
}

function chapterHeadingText($tBook, $iChapter, $iBookChapters){
  if (isPsalmsBook($tBook)) return 'Psalm ' . $iChapter;
  if ($iBookChapters <= 1) return null;
  return $tBook . ' Chapter ' . $iChapter;
}

// $aPrevious = [book, chapter] of the previous verse, or null. Returns heading text or null.
function headingForVerse($tBook, $iChapter, $iBookChapters, $aPrevious, $bStartOfPassage){
  $tText = chapterHeadingText($tBook, $iChapter, $iBookChapters);
  if ($bStartOfPassage) return $tText;
  if (isPsalmsBook($tBook) && ($aPrevious === null || $aPrevious[0] !== $tBook || $aPrevious[1] !== $iChapter)) return $tText;
  return null;
}

function verseMarkerText($tBook, $iChapter, $iVerse, $aPrevious, $bHeadingHere){
  if (!$bHeadingHere && $aPrevious !== null && $aPrevious[0] === $tBook && $aPrevious[1] !== $iChapter) {
    return $iChapter . ':' . $iVerse;
  }
  return (string)$iVerse;
}

function formatRangePart($aPart){
  return $aPart[1] > 0 ? $aPart[0] . ':' . $aPart[1] : (string)$aPart[0];
}

// $a, $b = [chapter, verse]; verse 0 = whole chapter; $b[0] < 1 and $b[1] < 1 = no end.
function formatRange($tBook, $a, $b){
  $tLabel = bookLabel($tBook);
  if ($b[0] < 1 && $b[1] < 1) return $tLabel . ' ' . formatRangePart($a);
  if ($b[0] < 1) $b[0] = $a[0]; // end verse in the same chapter
  if ($a === $b) return $tLabel . ' ' . formatRangePart($a);
  if ($a[0] === $b[0]) {
    if ($a[1] > 0 && $b[1] > 0) return $tLabel . ' ' . $a[0] . ':' . $a[1] . '-' . $b[1];
    return $tLabel . ' ' . $a[0];
  }
  if ($a[1] < 1 && $b[1] < 1) return $tLabel . ' ' . $a[0] . '-' . $b[0];
  return $tLabel . ' ' . formatRangePart($a) . '-' . formatRangePart($b);
}

function pluralise($n, $tSingular, $tPlural){
  return $n . ' ' . ($n === 1 ? $tSingular : $tPlural);
}

function readingsSentence($atRefs){
  $n = count($atRefs);
  if ($n === 0) return '';
  if ($n === 1) return 'Read ' . $atRefs[0] . '.';
  $t = 'First, ' . $atRefs[0] . '.';
  for ($i = 1; $i < $n - 1; $i++) $t .= ' Then ' . $atRefs[$i] . '.';
  return $t . ($n === 2 ? ' Then ' : ' Lastly, ') . $atRefs[$n - 1] . '.';
}

function planDayTitle($iYear, $iMonth, $iDay){
  $oDate = new DateTime(sprintf('%04d-%02d-%02d', $iYear, $iMonth, $iDay));
  return 'Day ' . ((int)$oDate->format('z') + 1) . ' · ' . $iDay . ' ' . $oDate->format('M');
}
