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
//    $tWords = $atBookChapSearch[3];
  }
  $tWords = $atBookChapSearch[3];
?>

        <div class="main Bible">
            <h1>The Bible</h1>
            <div class="subMain sectGeneral">
                <form name="searchForm" id="searchForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get" onsubmit="showWait();">

                <table class="searchTable">
                  <tbody>
                    <tr>
                      <td colspan="2">
                        Search by word or book or both<br />
                        <input type="search" name="words" id="words" placeholder="Enter phrase or word(s)" value="<?php echo $tWords; ?>">
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
                <input type="hidden" name="verses" id="verses" value="<?php if ($tBook > ''){echo $tVerses;} ?>">
<?php require_once 'intWords.php'; ?>
              </form>
<?php
  echo passage($tBook, $tChapter, $tVerses, $tWords, $bExact);
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
  if(is_numeric($tWords)){
    if(empty($tBook)){ // treat as number
      $tWords = intToWords($tWords);
    } else {
      if(empty($tChapter)){
        $tChapter = $tWords;
      } else {
        $tVerses = $tWords;
      }
      $tWords = '';
    }
  }elseif(is_numeric(substr($tWords, 0, 1))) {
    if(strpos($tWords, '-')>0 || strpos($tWords, ',')>0){
      $tVerses = $tWords;
    }
  } else {
    $atWords = explode(' ', $tWords);
    $iLen = count($atWords);

    $atBeginsWithBook = beginsWithBook($atWords, $iLen);
    $tBookNew = $atBeginsWithBook[0];
    if(empty($tBook) || $tBookNew !== $tBook){
      $tBook = $tBookNew;
      $tChapter = $atBeginsWithBook[1];
      $tVerses = $atBeginsWithBook[2];
      $i = $atBeginsWithBook[3];

      if ($i === $iLen){ // done!
        $tWords = '';
      }else{
        $tWords = joinWords($atWords, $i, $iLen);
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

  $atFindBook = findBook($atWords, 0, $iLen);
  $i = $atFindBook[1];

  if (strlen($atFindBook[0]) > 0){ // first few words is a book
    $tBook = $atFindBook[0];
    $atFindChapterVerse = findChapterVerse($atWords, $i, $iLen);
    $tChapter = $atFindChapterVerse[0];
    $tVerses = $atFindChapterVerse[1];
    $i = $atFindChapterVerse[2];
  }
  return [$tBook, $tChapter, $tVerses, $i];
}
// ============================================================================

// ============================================================================
function findBook($atWords, $i, $iLen){
// ============================================================================
//  abbreviations with or without fullstop
  // Gen chapter 1 vs Gen 1 vs gn 1 vs Gn 1
  // 1 cor vs 1cor
  global $atBookAbbs;
  $tMayBeBook = '';
  if(is_numeric ($atWords[0]) || stripos($atWords[0], 'first second third i ii iii 1st 2nd 3rd') > 0){
    if ($iLen >= 1){
      $tMayBeBook = $atWords[0] . ' ' . $atWords[1];
      $i ++;
    }
  } else {
    $tMayBeBook = $atWords[0];
  }
  $atSongs = isItSongs($tMayBeBook, $atWords, $i, $iLen);
  $tMayBeBook = $atSongs[0];
  $i = $atSongs[1];
  $tBook = getBookName($tMayBeBook, $atBookAbbs);
  if($tBook > ''){
    $i = $i + 1;
  }
  return [$tBook, $i];
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
function isItSongs($tBook, $atWords, $i, $iLen){
// ============================================================================
  if (strtolower($atWords[0]) === 'song' || substr((strtolower($atWords[0])), 0, 4) === 'cant'){
    $tBook = 'Song of Songs';
    if ($iLen > 1){
      if (strtolower($atWords[1]) === 'of'){
        $i = $i + 1;
        if ($iLen >= 2){
          if (substr((strtolower($atWords[2])), 0, 4) === 'cant' || substr((strtolower($atWords[2])), 0, 2) === 'so'){
            $i = $i + 1;
          }
        }
      } else {
        if (strtolower($atWords[1]) === 'of'){
          $i = $i + 2;
        } else {
          $tBook = 'Song of Songs';
          $i++;
        }
      }
    }
  }
  return [$tBook, $i];
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
          $tNWordord .= $atNWords['separator'] . ' ';
        }
        $tNWord .= intToWords($r);
      }
    }
  }
  return $tNWord;
}
// ============================================================================

?>
