<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'bibleFunctions.php';

  /*
  $month = filter_input(INPUT_GET, 'book');
  $day = filter_input(INPUT_GET, 'chapter');
  $tVerse = filter_input(INPUT_GET, 'verse');
  $tWords = filter_input(INPUT_GET, 'search');

  $bGotRequest = strlen($tBook . $tChapter. $tVerse . $tWords)>0;
  */
  $todaysVerses = '';
  $tMonth = filter_input(INPUT_GET, 'month');
  $tDay = filter_input(INPUT_GET, 'day');
  $bHighlightSW = true;
  $bShowOW = true;
  $iBook = 0;
  echo prepareBookList();
  $atStrongs = prepareStrongs();

  $timestamp = time();
  //$bLeap = (date('L', $timestamp)==1);
  $year = date('Y', $timestamp);
  $month = date('n', $timestamp);
  $day = date('j', $timestamp);

  if (strlen($tMonth) > 0){
    if ($tMonth > 12){
      $month = 12;
    }elseif ($tMonth < 1){
      $month = 1;
    }else {
      $month = intval($tMonth);
    }
  }

  $daysInMonth = daysInMonth($month, $year);
  if (strlen($tDay) > 0){
    if ($tDay > $daysInMonth){
      $day = $daysInMonth;
    }elseif ($tDay < 1){
      $day = 1;
    }else {
      $day = $tDay;
    }
  }
  echo '<!-- ';
  echo '$tMonth:' . $tMonth;
  echo '$tDay:' . $tDay;
  echo '$month:' . $month;
  echo '$day:' . $day;
  echo '-->';
?>
  <script type="text/javascript">
// ============================================================================
    function audioPlayPause(tAudioID){
// ============================================================================
      // alert(document.getElementById('b' + tAudioID).value);
      if(document.getElementById('b' + tAudioID).value == 'listen' || document.getElementById('b' + tAudioID).value == 'continue'){
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
                <h2>Readings for <?php echo monthName($month) . ' ' . $day; ?>:</h2>
  <?php
    // $tOutput = planTable('orderChron2');
    $tOutput = planTable('orderChristian');
    // echo planTable('orderJewish');
    // echo planTable('orderChron1');
    // echo planTable('orderChron2');
    echo 'First, ' . daysReadingsAsSentence($month, $day);
  ?>
                <p>You can read the passages below. If you're looking to read for
                  a different day or want to use your own Bible, then
                <a href="planTable.php">here&rsquo;s the entire year&rsquo;s plan
                  as a list</a>. Enjoy!</p>

  <?php
    echo daysReadingsAsVerses($month, $day);
  ?>
              </div>
          </div>
        </p>
    </div>
  </div>
<?php
  mysqli_close($link);
  require_once 'footer.php';
?>
