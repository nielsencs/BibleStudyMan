<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'planFunctions.php';
  require_once 'search.php';
  require_once 'timeStamp.php';

  $todaysVerses = '';

  if (strlen($tMonth) > 0){
    if ($tMonth > 12){
      $iMonth = 12;
    }elseif ($tMonth < 1){
      $iMonth = 1;
    }else {
      $iMonth = intval($tMonth);
    }
  }

  $iDaysInMonth = daysInMonth($iMonth, $iYear);

  if (strlen($tDay) > 0){
    if ($tDay > $iDaysInMonth){
      $iDay = $iDaysInMonth;
    }elseif ($tDay < 1){
      $iDay = 1;
    }else {
      $iDay = intval($tDay);
    }
  }
  echo '<!-- ';
  echo '$tMonth:' . $tMonth;
  echo '$tDay:' . $tDay;
  echo '$iMonth:' . $iMonth;
  echo '$iDay:' . $iDay;
  echo '-->';
?>
  <script type="text/javascript">
// ============================================================================
    function audioPlayPause(tAudioID){
// ============================================================================
      // alert(document.getElementById('b' + tAudioID).value);
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
                <h2>Readings for <?php echo monthName($iMonth) . ' ' . $iDay; ?>:</h2>
  <?php
    $tOutput = planTable($tSortOrder);
    echo 'First, ' . daysReadingsAsSentence($iMonth, $iDay);
  ?>
                <p>You can read the passages below. If you're looking to read for
                  a different day or want to use your own Bible, then
                <a href="planTable.php">here&apos;s the entire year&apos;s plan
                  as a list</a>. Enjoy!</p>

                <form name="searchForm" id="searchForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get" onsubmit="showWait();">

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
                    </td>
                  </tr>
<!--                  <tr>
                    <td colspan="2">
                      Sort Order
                      <select name="sortOrder" onchange="doSubmit('sortOrder')">
                        <option value="orderChristian"<?php if($tSortOrder === 'orderChristian'){echo ' selected';} ?>>Traditional Christian</option>
                        <option value="orderJewish"<?php if($tSortOrder === 'orderJewish'){echo ' selected';} ?>>Traditional Jewish</option>
                        <option value="orderChron1"<?php if($tSortOrder === 'orderChron1'){echo ' selected';} ?>>Chronological A</option>
                        <option value="orderChron2"<?php if($tSortOrder === 'orderChron2'){echo ' selected';} ?>>Chronological B</option>
                      </select>
                    </td>
                  </tr> -->
                </table>
<?php require_once 'intWords.php'; ?>
              </form>
                
                
  <?php
    echo daysReadingsAsVerses($iMonth, $iDay);
    include_once 'bibleDisclaimer.html';
  ?>
              </div>
          </div>
        </p>
    </div>
  </div>
<?php
  require_once 'footer.php';
?>
