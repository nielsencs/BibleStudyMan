<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'search.php';

  $atBookChapSearch = bookChapSearch($tWords, $tBook, $tChapter);
  if($atBookChapSearch[0] > ''){ // book found in search
    $tBook = $atBookChapSearch[0];
    if($atBookChapSearch[1] > ''){ // chapter found in search
      $tChapter = $atBookChapSearch[1];
      if($atBookChapSearch[2] > ''){ // verses found in search
        $tVerses = $atBookChapSearch[2];
      }
    }
  }
  $tWords = $atBookChapSearch[3];
?>

        <div class="main Bible">
            <h1>The Bible</h1>
            <div class="subMain sectGeneral">
                <form name="searchForm" id="searchForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');?>" method="get" onsubmit="showWait();">

                <table class="searchTable">
                  <tbody>
                    <tr>
                      <td colspan="2">
                        Search by word or book or both<br />
                        <input type="search" name="words" id="words" placeholder="Enter phrase or word(s)" value="<?php echo htmlspecialchars($tWords, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="checkbox" name="exact" id="exact" <?php if($bExact){echo 'checked';}; ?>
                               onclick="doSubmit('words')"><label><abbr title="If this is checked you'll tend to
                                                                                get fewer results as it treats the
                                                                                words to the left as a phrase if
                                                                                there are more than one or as the
                                                                                exact word.">Exact</abbr></label>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <input type="button" name="clearAll" id="clearAll" value="Clear" onclick="clearAllFields('bible')">
                        &nbsp;&nbsp;&nbsp;
                        <input type="submit" value="Search">
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <input type="button" value="&lt;" onclick="doDirection('pb')">
                        &nbsp;<abbr title="The Bible is a library of books.
You can select one of them here.">Book</abbr>&nbsp;
                        <input type="button" value="&gt;" onclick="doDirection('nb')">
                        <br />
                        <!--<input type="text" name="book" id="book" value="" list="books">-->
                        <!--<datalist name="books" id="books">-->
                        <select name="book" id="book"  onchange="doSubmit('book')">
                         <option value=""></option>
<?php
  echo prepareDropdownBookList();
?>
                        <!--</datalist>-->
                        </select>
                        <!--<input type="button" value="Clear" onclick="clearField('book')">-->
                      </td>
                      <td>
                        <input type="button" value="&lt;" onclick="doDirection('pc')">
                        &nbsp;<abbr title="The books in The Bible are divided
into chapters; once you&apos;ve picked a
book, you can pick a chapter here.">Chapter</abbr>&nbsp;
                        <input type="button" value="&gt;" onclick="doDirection('nc')">
                        <br />
                        <input type="hidden" name="chapterNext" id="chapterNext" value="">
                        <select name="chapter" id="chapter" onchange="doSubmit('chapter')">
                          <option value=""><?php if ($tBook > ''){echo 'All';} ?></option>
<?php
  echo prepareDropdownChapterList();
?>
                        </select>
                        <!--<input type="button" value="Clear" onclick="clearField('chapter')">-->
                      </td>
                    </tr>
                  </tbody>
                </table>
                <input type="hidden" name="verses" id="verses" value="<?php if ($tBook > ''){echo htmlspecialchars($tVerses, ENT_QUOTES, 'UTF-8');} ?>">
<?php require_once 'intWords.php'; ?>
              </form>
<?php
  echo passage($tBook, $tChapter, $tVerses, $tWords, $bExact, $bHighlightSW, $bShowOW, $bShowTN);
  include_once 'bibleDisclaimer.html';
?>
            </div>
        </div>
<?php

  require_once 'footer.php';

// ============================================================================
function bookChapSearch($tWords, $tBook, $tChapter){
// ============================================================================
    $tVerses = '';

    $atWords = explode(' ', $tWords);
    $iLen = count($atWords);

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
        // If no book was found, we do nothing, so the original $tWords is used for a text search.
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


// ============================================================================
function intToWords($x) {
// ============================================================================
  $tNWord = '';
  $atNWords = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven',
              'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen',
              'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen',
              'nineteen', 'twenty', 30 => 'thirty', 40 => 'forty',
              50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty',
              90 => 'ninety' , 'hundred' => 'hundred', 'thousand'=> 'thousand', 'million'=>'million',
              'separator'=>'and', 'minus'=>'minus'];

  if (!is_numeric($x)) {
    $tNWord = '#';
  } else if (fmod($x, 1) != 0) {
    $tNWord = '#';
  } else {
    if ($x < 0) {
      $tNWord = $atNWords['minus'] . ' ';
      $x = -$x;
    } else {
      $tNWord = '';
    }
    // ... now $x is a non-negative integer.
    if ($x < 21) {  // 0 to 20
      $tNWord .= $atNWords[$x];
    } else if ($x < 100) {  // 21 to 99
      $tNWord .= $atNWords[10 * floor($x / 10)];
      $r = fmod($x, 10);
      if ($r > 0) {
        $tNWord .= '-' . $atNWords[$r];
      }
    } else if ($x < 1000) {  // 100 to 999
      $tNWord .= $atNWords[floor($x / 100)] . ' ' . $atNWords['hundred'];
      $r = fmod($x, 100);
      if ($r > 0) {
        $tNWord .= ' ' . $atNWords['separator'] . ' ' . intToWords($r);
      }
    } else if ($x < 1000000) {  // 1000 to 999999
      $tNWord .= intToWords(floor($x / 1000)) . ' ' . $atNWords['thousand'];
      $r = fmod($x, 1000);
      if ($r > 0) {
        $tNWord .= ' ';
        if ($r < 100) {
          $tNWord .= $atNWords['separator'] . ' ';
        }
        $tNWord .= intToWords($r);
      }
    } else {    //  millions
      $tNWord .= intToWords(floor($x / 1000000)) . ' ' . $atNWords['million'];
      $r = fmod($x, 1000000);
      if ($r > 0) {
        $tNWord .= ' ';
        if ($r < 100) {
          $tNWord .= $atNWords['separator'] . ' ';
        }
        $tNWord .= intToWords($r);
      }
    }
  }
  return $tNWord;
}
// ============================================================================

?>
