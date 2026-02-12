<?php
  require_once 'header.php';

  $todaysVerses = '';
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

          <div class="main plan">
              <h1>The Bible Reading Plan</h1>
              <div class="subMain sectGeneral">
                <h2>Readings for <?php echo htmlspecialchars(monthName($iMonth) . ' ' . $iDay, ENT_QUOTES, 'UTF-8'); ?>:</h2>
<?php
  $tOutput = planTable($tSortOrder);
  echo 'First, ' . daysReadingsAsSentence($iMonth, $iDay, $bHighlightSW, $bShowOW, $bShowTN);
?>
                <p>You can read the passages below. If you're looking to read for
                  a different day or want to use your own Bible, then
                <a href="planTable">here&apos;s the entire year&apos;s plan
                  as a list</a>. Enjoy!</p>



  <?php
    echo daysReadingsAsVerses($iMonth, $iDay, $bHighlightSW, $bShowOW, $bShowTN);
    include_once 'bibleDisclaimer.html';
  ?>
        </div>
      </div>
    </div>
  </div>
<?php
  require_once 'footer.php';
?>
