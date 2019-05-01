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
      $month = $tMonth;
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
                <p>You can read the passages below. If you're looking to read for
                  a different day or want to use your own Bible, then the entire
                  year&rsquo;s plan as a list is below them. Enjoy!</p>
                <p>This is still very much a work in progress (if you want to help
                  out then please check out the &lsquo;<a href="support.php">Support
                    this ministry</a>&rsquo; page. I do plan to develop the code to:</p>
                <ul>
                  <li>Enable a checklist so you can &lsquo;mark as read&rsquo;</li>
                  <li>provide alternative arrangements of the readings for variety</li>
                  <li>Any other requests?</li>
                </ul>
                <p>The readings:</p>
  <?php
    echo daysReadingsAsSentence($month, $day);
    echo daysReadingsAsVerses($month, $day);
  ?>
      <p>Ooh isn&rsquo;t it pretty? Well I like it anyway! More seriously the
        colours can help you (just a little bit) if you have coloured ribbons as
        dividers in your Bible to quickly get to the relevant section.</p>
      <br />
  <?php
    // planTable('orderChristian', true);
    // planTable('orderJewish', true);
    // planTable('orderChron1', true);
    // planTable('orderChron2', true);
    echo planTable('orderChron2', true);
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

  function buildSection($sectionCode, $tOrder)
  {
    $tQuery = '';

    $tQuery .= 'SELECT ';
    $tQuery .= 'newPlan.planID, ';
    // $tQuery .= '  books.bookName, ';
    $tQuery .= 'books.bookCode, ';
    $tQuery .= 'newPlan.startChapter, ';
    $tQuery .= 'newPlan.startVerse, ';
    $tQuery .= 'newPlan.endChapter, ';
    $tQuery .= 'newPlan.endVerse ';
    $tQuery .= 'FROM ';
    $tQuery .= 'newPlan INNER JOIN books ON newPlan.bookCode=books.bookCode';
    $tQuery .= 'WHERE ';
    $tQuery .= 'newPlan.sectionCode = "' . $sectionCode . '" ';
    $tQuery .= 'ORDER BY ';
    $tQuery .= 'books.' . $tOrder;
    $tQuery .= 'newPlan.planDay;';

    return $tQuery;
  }

  function processPlan($tOrder='orderChristian')
  {
    global $link;

    $tQuery1 = buildSection('1TOR', $tOrder);
    $result1 = doQuery($link, $tQuery1);
    $tQuery2 = buildSection('2NV1', $tOrder);
    $result2 = doQuery($link, $tQuery2);
    $tQuery3 = buildSection('3KTV', $tOrder);
    $result3 = doQuery($link, $tQuery3);
    $tQuery4 = buildSection('5NCV', $tOrder);
    $result4 = doQuery($link, $tQuery4);

    if (mysqli_num_rows($result1) == 0) {
      echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery1 . '"';
    } else {
      $date = new DateTime('2000-01-01');

      $tQuery2 = '';
      $iRow = 0;
      while($row = mysqli_fetch_assoc($result)) {
        $tQuery2 .= 'INSERT INTO ThePlan (`ID`, `planDate`, `sectionCode`, `bookCode`, `passageStart`, `passageEnd`) VALUES (';
        $tQuery2 .= $iRow++ . ', ';
        $tQuery2 .= "'" . $date->format('Y-m-d') . "', ";
        $tQuery2 .= "'" . $row['sectionCode'] . "', ";
        $tQuery2 .= "'" . $row['bookCode'] . "', ";
        $tQuery2 .= "'" . $row['startChapter'] . ":" . $row['startVerse'] . "', ";
        $tQuery2 .= "'" . $row['endChapter'] . ":" . $row['endVerse'] . "'";
        $tQuery2 .= ');';

        if($iRow % 4 == 0){
          $date->add(new DateInterval('P1D'));
        }
      }
      $result2 = doQuery($link, $tQuery2);

      if (mysqli_num_rows($result2) == 0) {
        echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
      }
    }
  }

  function planTable($bAllColumns){
    global $link;
    $tOutput = '';
    $tQuery = '';

    $tQuery .= 'SELECT *, DATE_FORMAT(planDate, "%b %e") as planDateFormatted';
    //$tQuery .= ', DATE_FORMAT(planDate, '<a href="plan.php?month=%c&day=%e">') as search';
    $tQuery .= ', DATE_FORMAT(planDate, "%c") as searchMonth';
    $tQuery .= ', DATE_FORMAT(planDate, "%e") as searchDay';
    // $tQuery .= ' FROM plan';
    $tQuery .= ' FROM thePlan';
    $tQuery .= ' ORDER BY planDate ASC';

    $result = doQuery($link, $tQuery);

    if (mysqli_num_rows($result) == 0) {
      $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
    } else {
      $tOutput .= '<table class="planTable">';
      $tOutput .= '<tr>';
      $tOutput .= '<th>Date</th>';
      if($bAllColumns){
        // $tOutput .= '<th colspan="3">Torah</th>';
        // $tOutput .= '<th colspan="3">Nevi&rsquo;im</th>';
        // $tOutput .= '<th colspan="3">K&rsquo;tuvim</th>';
        // $tOutput .= '<th colspan="3">New Covenant</th>';
        $tOutput .= '<th colspan="3">Section 1</th>';
        $tOutput .= '<th colspan="3">Section 2</th>';
        $tOutput .= '<th colspan="3">Section 3</th>';
        $tOutput .= '<th colspan="3">Section 4</th>';
      }
      while($row = mysqli_fetch_assoc($result)) {
        if ($row['planDateFormatted'] != 'Feb 29'){
          if($row['sectionCode'] == '1TOR'){
            $tOutput .= '</tr>';
            $tOutput .= '<tr>';
            $tOutput .= '<td><a href="plan.php?month=' . $row['searchMonth'];
            //$tOutput .= '<td>';
            $tOutput .= '&day=' . $row['searchDay'] . '">';
            $tOutput .= $row['planDateFormatted'] . '</a>';
          }
          $tOutput .= '</td>';
          if($bAllColumns){
            $tOutput .= '<td>';
            $tOutput .= $row["bookCode"];
            $tOutput .= '</td><td>';
            $tOutput .= $row["passageStart"];
            $tOutput .= '</td><td>';
            $tOutput .= $row["passageEnd"];
          }
        }
      }
      $tOutput .= '</tr></table>';
    }
    mysqli_free_result($result);
    return $tOutput;
  }
?>
