<?php
  $bFloaty = false; // is the control panel 'floaty'?

  require_once 'dbFunctions.php';
  require_once 'bibleSearchFunctions.php';
  require_once 'header.php';
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
  if ($bFloaty) {
    echo '<link rel="stylesheet" href="styles/controlPanel.css">' . PHP_EOL;
    echo '<script type="text/javascript" src="scripts/controlPanel.js"></script>' . PHP_EOL;
  }
?>
        <div class="main Bible">
            <h1>The CleanSlate Bible</h1>
            <div class="subMain sectGeneral">
              <div id="controlPanel">
<?php if ($bFloaty) {echo '<button id="panelToggle" title="Toggle search panel">&lt;</button>' . PHP_EOL;} ?>
                <!-- <button id="panelToggle" title="Toggle search panel">&lt;</button> -->
                <form name="searchForm" id="searchForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] ?? '', ENT_QUOTES, 'UTF-8');?>" method="get" onsubmit="showWait();">

                <table class="searchTable">
                  <tbody>
                    <tr>
                      <td colspan="2">
                        Search by word or book or both<br />
                        <input type="search" name="words" id="words" placeholder="Enter phrase or word(s)" value="<?php echo htmlspecialchars($tWords ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
                <input type="hidden" name="verses" id="verses" value="<?php if ($tBook > ''){echo htmlspecialchars($tVerses ?? '', ENT_QUOTES, 'UTF-8');} ?>">
<?php require_once 'intWords.php'; ?>
              </form>
            </div><!-- controlPanel -->
<?php
  echo passage($tBook, $tChapter, $tVerses, $tWords, $bExact, $bHighlightSW, $bShowOW, $bShowTN, $bFloaty);
  include_once 'bibleDisclaimer.html';
?>
            </div>
        </div>
<?php

  require_once 'footer.php';

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
