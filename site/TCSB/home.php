<?php
  require_once 'header.php';
?>
  <script type="text/javascript">
// ============================================================================
    function audioPlayPause(tAudioID){
// ============================================================================
      if(document.getElementById('b' + tAudioID).value === 'listen' || document.getElementById('b' + tAudioID).value === 'continue'){
        document.getElementById('b' + tAudioID).value = 'stop';
        document.getElementById('a' + tAudioID).play();
        document.getElementById('a' + tAudioID).controls = true;
      } else {
        document.getElementById('b' + tAudioID).value = 'continue';
        document.getElementById('a' + tAudioID).pause();
        document.getElementById('a' + tAudioID).controls = false;
      }
    }
// ============================================================================
  </script>

        <div class="main TCSB">
            <h1>The CleanSlate Bible</h1>
            <div class="subMain sectGeneral">
<?php
  $emptyBibleSearch = empty($tBook) && empty($tChapter) && empty($tVerses) && empty($tWords);
  if($emptyBibleSearch || $tPriority === 'Plan'){ // no Bible search or month specified, show today's verses
    echo "<h2>Readings for " . htmlspecialchars(monthName($iMonth) . ' ' . $iDay, ENT_QUOTES, 'UTF-8') . ":</h2>";
    $tOutput = planTable($tSortOrder);
    echo 'First, ' . daysReadingsAsSentence($iMonth, $iDay, $bHighlightSW, $bShowOW, $bShowTN);
?>
                <!-- <p>You can read the passages below. If you're looking to read for
                  a different day or want to use your own Bible, then
                <a href="planTable">here&apos;s the entire year&apos;s plan
                  as a list</a>. Enjoy!</p> -->
<?php
    echo daysReadingsAsVerses($iMonth, $iDay, $bHighlightSW, $bShowOW, $bShowTN);
  }else{
    echo passage($tBook, $tChapter, $tVerses, $tWords, $bExact, $bHighlightSW, $bShowOW, $bShowTN, $bFloaty);
  }
  echo bibleDisclaimer();
?>
            </div>
        </div>

<?php
// Output JS to scroll to first searched verse if $tBook, $tChapter, $tVerses are set
if (!empty($tBook) && !empty($tChapter) && !empty($tVerses)) {
    // Get the first verse number from $tVerses (handles comma/dash lists)
    $firstVerse = preg_replace('/[^0-9,-]/', '', $tVerses);
    $firstVerseNum = '';
    if (preg_match('/(\d+)/', $firstVerse, $m)) {
        $firstVerseNum = $m[1];
    }
    $verseId = 'verse-' . preg_replace('/[^a-zA-Z0-9]/', '', $tBook) . '-' . $tChapter . '-' . $firstVerseNum;
    echo "<script>\n";
    echo "window.addEventListener('DOMContentLoaded', function() {\n";
    echo "  var el = document.getElementById('" . $verseId . "');\n";
    echo "  if (el) { el.scrollIntoView({behavior: 'smooth', block: 'center'}); }\n";
    echo "});\n";
    echo "</script>\n";
}
?>
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
