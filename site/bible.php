<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'search.php';
    $atBookChapSearch = bookChapSearch($tWords);
    if($atBookChapSearch[0] > ''){ // book found in search
      $tBook = $atBookChapSearch[0];
      if($atBookChapSearch[1] > ''){ // chapter found in search
        $tChapter = $atBookChapSearch[1];
      }
      $tWords = $atBookChapSearch[2];
    }
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
                        <input type="checkbox" name="phrase" id="phrase" <?php if($bPhrase){echo 'checked';}; ?>
                               onclick="doSubmit('words')"><label><abbr title="If this is checked you'll get fewer results as it treats the words to the left as a phrase.">Phrase</abbr></label>
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
                        &nbsp;<abbr title="The Bible is a library of books. You can select one of them below">Book</abbr>&nbsp;
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
                        &nbsp;<abbr title="The books in The Bible are divided into chapters; once you&rsquo;ve picked a book, you can pick a chapter below.">Chapter</abbr>&nbsp;
                        <input type="button" value="&gt;" onclick="doDirection('nc')">
                        <br />
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
<?php require_once 'intWords.php'; ?>
              </form>
<?php
  echo passage($tBook, $tChapter, $tVerses, $tWords, $bPhrase);
?>
        <p>This is a minor adaptation of the <a href="https://worldenglishbible.org" target="_blank">WEB</a>
          to include nuanced meanings of particular ancient words for placenames,
          God and others of special interest. In general square brackets:[] are
          used to indicated words not found in the original text. It also indicates
          the 5 books of the Psalms; and a few passages considered by some to be
          of questionable authenticity.</p>
            </div>
        </div>
<?php

  require_once 'footer.php';

// ============================================================================
function bookChapSearch($tWords){
// ============================================================================
  $atWords = explode(' ', $tWords);
  $iLen = count($atWords);

  $atBeginsWithBook = beginsWithBook($atWords, $iLen);
  $tBook = $atBeginsWithBook[0];
  $tChapter = $atBeginsWithBook[1];

  $i = $atBeginsWithBook[2];

  if ($i === $iLen){ // done!
    $tWords = '';
  }else{
    $tWords = joinWords($atWords, $i, $iLen);
  }
  return [$tBook, $tChapter, $tWords];
}
// ============================================================================

// ============================================================================
function beginsWithBook($atWords, $iLen){
// ============================================================================
  $tBook = '';
  $tChapter = '';
  $atFindBook = findBook($atWords, 0, $iLen);
  $i = $atFindBook[1];

  if (strlen($atFindBook[0]) > 0){ // first few words is a book
    $tBook = $atFindBook[0];
    $atFindChapter = findChapter($atWords, $i, $iLen);
    $tChapter = $atFindChapter[0];
    $i = $atFindChapter[1];
  }
  return [$tBook, $tChapter, $i];
}
// ============================================================================

// ============================================================================
function findBook($atWords, $i, $iLen){
// ============================================================================
//  abbreviations with or without fullstop
  // Gen chapter 1 vs Gen 1 vs gn 1 vs Gn 1
  // 1 cor vs 1cor
  global $atBookAbbs;
  if(is_numeric ($atWords[0]) || stripos($atWords[0], 'first second third i ii iii 1st 2nd 3rd') > 0){
    if ($iLen > 1){
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
function findChapter($atWords, $i, $iLen){
// ============================================================================
  $tChapter = '';
  $iKeep = $i;
  for ($j=$i;$j < $iLen; $j++){
    if(is_numeric(substr($atWords[$j], 0, 1)) && $j===$i){ // is first remaining 'word' a chapter?
      $tChapter .= $atWords[$i];
      $iKeep = $i+1;
      $iColon = strpos($tChapter, ':');
      if($iColon > 0){ // verses - we don't yet search for them yet!
        $tChapter = substr($tChapter, 0, $iColon-1);
      }
    }
    if($j>$i){ // on to the rest
      if($atWords[$j] === ':'){ // verses - we don't yet search for them yet!
        $tChapter .= $atWords[$i];
        $iKeep = $i+1;
      }
      if(is_numeric(substr($atWords[$j], 0, 1)) && $j===$i){ // should be search words by now
        $iKeep = $i+1;
      }
    }
  }

  return [$tChapter, $iKeep];
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

?>
