<?php
  require_once 'header.php';
  require_once '../sqlCon.php';
  require_once 'dbFunctions.php';

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
          <div class="main plan">
              <h1>The Bible Reading Plan</h1>
              <div class="subMain sectGeneral">
                <h2>Readings for <?php echo monthName($month) . " " . $day; ?>:</h2>
  <?php
    // $tOutput = planTable('orderChron2');
    $tOutput = planTable('orderChristian');
    // echo planTable('orderJewish');
    // echo planTable('orderChron1');
    // echo planTable('orderChron2');
    echo daysReadingsAsSentence($month, $day);
  ?>
                <p>You can read the passages below. If you're looking to read for
                  a different day or want to use your own Bible, then the entire
                  year&rsquo;s plan is below them in a table. Enjoy!</p>
                <br />
                <div class="infoBox">
                  <p>This is still very much a work in progress (if you want to help
                    out then please check out the &lsquo;<a href="support.php">Support
                      this ministry</a>&rsquo; page). I do plan to develop the code to:</p>
                  <ul>
                    <li>Enable a checklist so you can &lsquo;mark as read&rsquo;</li>
                    <li>provide alternative arrangements of the readings for variety</li>
                    <li>Any other requests?</li>
                  </ul>
                </div>
  <?php
    echo daysReadingsAsVerses($month, $day);
  ?>
                <hr>
                <h2>Readings for the Year:</h2>
                <p>Ooh isn&rsquo;t it pretty? Well I like it anyway! More seriously the
                  colours can help you (just a little bit) if you have coloured ribbons as
                  dividers in your Bible to quickly get to the relevant section.</p>
                <br />
  <?php
    echo $tOutput;
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

<?php
  function daysReadingsAsVerses($month, $day){
    global $link;
    $tOutput = '';
    $tQuery = '';

    $tQuery .= 'SELECT *, DATE_FORMAT(planDate,"%b %e") as planDateFormatted ';
    $tQuery .= 'FROM plan WHERE MONTH(planDate)="' . $month;
    $tQuery .= '" AND DAY(planDate)="' . $day . '" ORDER BY planDate ASC';

    $result = doQuery($link, $tQuery);

    if (mysqli_num_rows($result) == 0) {
      $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
    } else {
      $readingList = array();
      while($row = mysqli_fetch_assoc($result)) {
        array_push($readingList, $row);
      }
    }
    mysqli_free_result($result);

    $readCount = count($readingList);
    if ($readCount > 0){
      $tOutput .=  '<section class="search-result">';
      for ($i=0; $i < $readCount; $i++) {
        $tOutput .=  '<h2 class="search-result__title">Section ' . ($i + 1) . '</h2>';
        $tOutput .=  showReading($readingList[$i]['bookCode'], $readingList[$i]['passageStart'], $readingList[$i]['passageEnd']);
      }
      $tOutput .=  '</section>';
    }
    return $tOutput;
  }

  function showReading($tBookCode, $tPassageStart, $tPassageEnd){
    $tQuery =  buildPassageQuery($tBookCode, $tPassageStart, $tPassageEnd);
    return showVerses($tQuery);
  }

  function sectionQuery($sectionCode, $tOrder){ // build query to get 1 sorted section
    $tQuery = '';

    $tQuery .= 'SELECT ';
    $tQuery .= 'newPlan.planID, ';
    $tQuery .= 'books.bookName, ';
    $tQuery .= 'books.bookCode, ';
    $tQuery .= 'newPlan.startChapter, ';
    $tQuery .= 'books.bookChapters, ';
    $tQuery .= 'newPlan.startVerse, ';
    $tQuery .= 'newPlan.endChapter, ';
    $tQuery .= 'newPlan.endVerse ';
    $tQuery .= 'FROM ';
    $tQuery .= 'newPlan INNER JOIN books ON newPlan.bookCode=books.bookCode ';
    $tQuery .= 'WHERE ';
    $tQuery .= 'newPlan.sectionCode = "' . $sectionCode . '" ';
    $tQuery .= 'ORDER BY ';
    $tQuery .= 'books.' . $tOrder . ', ';
    $tQuery .= 'newPlan.planDay;';

    return $tQuery;
  }

  function planTable($tOrder = 'orderChristian'){ // build HTML table of the years reading plan
    global $link;
    global $todaysVerses;
    $tOutput = '';

    $tQuery1 = sectionQuery('1TOR', $tOrder);
    $result1 = doQuery($link, $tQuery1);
    if (mysqli_num_rows($result1) == 0) {
      echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery1 . '"';
    } else {
      $tQuery2 = sectionQuery('2NV1', $tOrder);
      $result2 = doQuery($link, $tQuery2);
      if (mysqli_num_rows($result2) == 0) {
        echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery2 . '"';
      } else {
        $tQuery3 = sectionQuery('3KTV', $tOrder);
        $result3 = doQuery($link, $tQuery3);
        if (mysqli_num_rows($result3) == 0) {
          echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery3 . '"';
        } else {
          $tQuery4 = sectionQuery('5NCV', $tOrder);
          $result4 = doQuery($link, $tQuery4);
          if (mysqli_num_rows($result4) == 0) {
            echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery4 . '"';
          } else {
            $tOutput = PTBuild($result1, $result2, $result3, $result4);
          }
        }
      }
    }
    mysqli_free_result($result1);
    mysqli_free_result($result2);
    mysqli_free_result($result3);
    mysqli_free_result($result4);
    return $tOutput;
  }

  function PTBuild($result1, $result2, $result3, $result4){ // build the HTML table
    global $todaysVerses;
    $tOutput = '';

    $tOutput .= '<table class="planTable">';
    $tOutput .= '<tr>';
    $tOutput .= '<th>Date</th>';

    // $tOutput .= '<th colspan="3">Torah</th>';
    // $tOutput .= '<th colspan="3">Nevi&rsquo;im</th>';
    // $tOutput .= '<th colspan="3">K&rsquo;tuvim</th>';
    // $tOutput .= '<th colspan="3">New Covenant</th>';
    $tOutput .= '<th>Section 1</th>';
    $tOutput .= '<th>Section 2</th>';
    $tOutput .= '<th>Section 3</th>';
    $tOutput .= '<th>Section 4</th>';
    $tOutput .= '<th>Read it!</th>';

    $date = new DateTime('2001-01-01');

    for ($i=0; $i < 365; $i++) {
      $row1 = mysqli_fetch_assoc($result1);
      $row2 = mysqli_fetch_assoc($result2);
      $row3 = mysqli_fetch_assoc($result3);
      $row4 = mysqli_fetch_assoc($result4);

      $tOutput .= '</tr>';
      $tOutput .= '<tr>';

      $tOutput .= '<td><a href="plan.php?month=' . $date->format('m');
      //$tOutput .= '<td>';
      $tOutput .= '&day=' . $date->format('j') . '">';
      $tOutput .= $date->format('M j') . '</a>';
      $tOutput .= '</td>';

      $tOutput .= addSection($row1);
      $tOutput .= addSection($row2);
      $tOutput .= addSection($row3);
      $tOutput .= addSection($row4);

      $tOutput .= '<td><input type="checkbox"></td>';

      $date->add(new DateInterval('P1D'));
    }
    $tOutput .= '</tr></table>';

    return $tOutput;
  }

  function addSection($row) { // add a section column to the plan table
    $tOutput = '';

    $tOutput .= '<td>';
    $tOutput .= '<a href="bible.php?book=';
    $tOutput .= $row['bookName'];
    $tOutput .= '">';
    $tOutput .= $row['bookName'];
    $tOutput .= '</a> ';

    if ($row['bookChapters'] > 1){
      $tOutput .= '<a href="bible.php?book=';
      $tOutput .= $row['bookName'];
      $tOutput .= '&chapter=';
      $tOutput .= $row['startChapter'];
      $tOutput .= '">';
      $tOutput .= $row['startChapter'];
      $tOutput .= '</a>';
      if ($row['endChapter'] > 0){
        $tOutput .= ":";
      }
    }

    if ($row['startVerse'] > 0){
      $tOutput .= $row['startVerse'];
    }

    if ($row['bookChapters'] > 1){
      if ($row['endChapter'] > 0){
        $tOutput .= ' - ';
        if ($row['endChapter'] != $row['startChapter']){
          $tOutput .= '<a href="bible.php?book=';
          $tOutput .= $row['bookName'];
          $tOutput .= '&chapter=';
          $tOutput .= $row['endChapter'];
          $tOutput .= '">';
          $tOutput .= $row['endChapter'];
          $tOutput .= '</a> ';
        }
        if ($row['endVerse'] > 0){
          if ($row['endChapter'] != $row['startChapter']){
            $tOutput .= ":";
          }
          $tOutput .= $row['endVerse'];
        }
      }
    } else {
      if ($row['endVerse'] > 0){
        $tOutput .= '-' . $row['endVerse'];
      }
    }

    $tOutput .= '</td>';
    return $tOutput;
  }
?>
