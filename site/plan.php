<?php
  $bFloaty = true; //false; // is the control panel 'floaty'?

  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'planFunctions.php';
  require_once 'search.php';
  require_once 'timeStamp.php';

  $todaysVerses = '';

  if (strlen((string)$tMonth) > 0){
    if ($tMonth > 12){
      $iMonth = 12;
    }elseif ($tMonth < 1){
      $iMonth = 1;
    }else {
      $iMonth = intval($tMonth);
    }
  }

  $iDaysInMonth = daysInMonth($iMonth, $iYear);

  if (strlen((string)$tDay) > 0){
    if ($tDay > $iDaysInMonth){
      $iDay = $iDaysInMonth;
    }elseif ($tDay < 1){
      $iDay = 1;
    }else {
      $iDay = intval($tDay);
    }
  }
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

                <form name="searchForm" id="searchForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');?>" method="get" onsubmit="showWait();">

                <table class="searchTable">
                  <tr>
                    <td>
                      <select name="month" id="month" onchange="doSubmit('month')">
<?php
  echo prepareDropdownMonthList($iMonth);
?>
                      </select>
                    </td>
                    <td>
                      <input type="button" value="&lt;" onclick="dayDirection('pd')">
                      <input type="hidden" name="dayNext" id="dayNext" value="">
                      <select name="day" id="day" onchange="doSubmit('day')">
<?php
  echo prepareDropdownDayList($iDay, $tMonth, $iDaysInMonth);
?>
                      </select>
                      <input type="button" value="&gt;" onclick="dayDirection('nd')">
                      <input type="button" value="Today" onclick="dayDirection('today')">
                    </td>
                  </tr>
                </table>
<?php require_once 'intWords.php'; ?>
              </form>


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
