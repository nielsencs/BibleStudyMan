<?php
// ============================================================================
function bookChapSearch($tWords, $tBook, $tChapter){
// ============================================================================
    $tVerses = '';

    $atWords = explode(' ', $tWords);
    $iLen = count($atWords);
    $hadSelectedBook = $tBook > '';

    if ($iLen > 0 && $atWords[0] !== '') {
        $atBeginsWithBook = beginsWithBook($atWords, $iLen);
        $tBookNew = $atBeginsWithBook[0];

        // If a book was successfully parsed from the beginning of the string
        if ($tBookNew > '') {
            $tBook = $tBookNew;
            $tChapter = $atBeginsWithBook[1];
            $tVerses = $atBeginsWithBook[2];
            $wordsConsumed = $atBeginsWithBook[3];

            // The rest of the string becomes the new search term
            $tWords = joinWords($atWords, $wordsConsumed, $iLen);
        }
        // If no book was found but a book has already been selected, a leading
        // number can still be a chapter or verse shortcut rather than word text.
        if ($tBookNew === '' && $hadSelectedBook) {
            $atFindChapterVerse = findChapterVerse($atWords, 0, $iLen);
            $potentialChapter = $atFindChapterVerse[0];
            $potentialVerses = $atFindChapterVerse[1];
            $wordsConsumed = $atFindChapterVerse[2];

            if ($potentialChapter > '') {
                if ($tChapter > '') {
                    $tVerses = $potentialChapter;
                } else {
                    $tChapter = $potentialChapter;
                    $tVerses = $potentialVerses;
                }
                $tWords = joinWords($atWords, $wordsConsumed, $iLen);
            }
        }
    }

    return [$tBook, $tChapter, $tVerses, $tWords];
}
// ============================================================================

// ============================================================================
function beginsWithBook($atWords, $iLen){
// ============================================================================
  $tBook = '';
  $tChapter = '';
  $tVerses = '';
  $i = 0; // Default to 0 words consumed

  $atFindBook = findBook($atWords, 0, $iLen);
  $potentialBook = $atFindBook[0];
  $wordsConsumedByBook = $atFindBook[1];

  if (strlen($potentialBook) > 0) { // We found a potential book
    $atFindChapterVerse = findChapterVerse($atWords, $wordsConsumedByBook, $iLen);
    $potentialChapter = $atFindChapterVerse[0];
    $potentialVerses = $atFindChapterVerse[1];
    $wordsConsumedTotal = $atFindChapterVerse[2];

    // Condition: Is it a real book reference?
    // It is if a chapter was found, OR if the whole search query was just the book name.
    if ($potentialChapter > '' || $wordsConsumedByBook === $iLen) {
        $tBook = $potentialBook;
        $tChapter = $potentialChapter;
        $tVerses = $potentialVerses;
        $i = $wordsConsumedTotal;
    }
    // ELSE: It was a false alarm (e.g., "mat" in a sentence), so we return empty values
    // and the original search string will be used for a text search.
  }

  return [$tBook, $tChapter, $tVerses, $i];
}
// ============================================================================

// ============================================================================
function findBook($atWords, $i, $iLen) {
// ============================================================================
    global $atBookAbbs;

    // Attempt 1: Multi-word book names (most specific)
    if ($iLen >= 3) {
        $three_words = $atWords[0] . ' ' . $atWords[1] . ' ' . $atWords[2];
        $tBook = getBookName($three_words, $atBookAbbs);
        if ($tBook > '') {
            return [$tBook, 3];
        }
    }
    if ($iLen >= 2) {
        $two_words = $atWords[0] . ' ' . $atWords[1];
        $tBook = getBookName($two_words, $atBookAbbs);
        if ($tBook > '') {
            return [$tBook, 2];
        }
    }

    // Attempt 2: Direct single-word match (e.g., "1Jn", "Genesis")
    $firstWord = $atWords[0];
    $tBook = getBookName($firstWord, $atBookAbbs);
    if ($tBook > '') {
        return [$tBook, 1];
    }

    // Attempt 3: Reconstructed single-word match (e.g., "1jn" -> "1 jn")
    if (preg_match('/^([0-9]+)([a-zA-Z].*)$/', $firstWord, $matches)) {
        $reconstructed = $matches[1] . ' ' . $matches[2];
        $tBook = getBookName($reconstructed, $atBookAbbs);
        if ($tBook > '') {
            return [$tBook, 1];
        }
    }

    return ['', 0];
}
// ============================================================================

// ============================================================================
function findChapterVerse($atWords, $i, $iLen){
// ============================================================================
  $iKeep = $i;
  $iColonCount = 0;
  $tChapter = '';
  $tVerses = '';

  for ($j=$i;$j < $iLen; $j++){
    if(is_numeric(substr($atWords[$j], 0, 1)) && $j===$i){ // is first remaining 'word' a chapter?
      $tChapter .= $atWords[$j];
      $iKeep = $iKeep+1;
      $iColon = strpos($tChapter, ':');
      if($iColon > 0){ // verses
        $tVerses = substr($tChapter, $iColon+1);
        $tChapter = substr($tChapter, 0, $iColon);
        $iKeep = $iKeep+1;
      }
    }
    if($j>$i){ // on to the rest
      if($atWords[$j] === ':' || strtolower($atWords[$j]) === 'vv'){ // verses
        if($iColonCount === 0){
          $tChapter .= $atWords[$i];
          $iKeep = $iKeep+1;
        }
        $iColonCount++;
        if(is_numeric(substr($atWords[$j+1], 0, 1))){
          $tVerses .= $atWords[$j+1];
          $iKeep = $iKeep+1;
        }
      }
    }
  }

  return [$tChapter, $tVerses, $iKeep];
}
// ============================================================================

// ============================================================================
function getBookName($tWord, $atBookAbbs){
// ============================================================================
  foreach ($atBookAbbs as $tAbbr => $tName) // as list($tAbbr, $tName) )
  {
    if(strtolower($tWord) === strtolower($tAbbr)){
      return $tName;
    }
  }
  return '';
}
// ============================================================================


?>
