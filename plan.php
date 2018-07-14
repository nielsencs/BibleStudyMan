<?php
  require_once 'header.php';
  require_once '../sqlCon.php';
  require_once 'dbFunctions.php';

  $tMonth = filter_input(INPUT_GET, 'month');
  $tDay = filter_input(INPUT_GET, 'day');

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
              <p>Click here for <a href="planTable.php">the entire year&rsquo;s readings as a list.</a></p>
              <p>I need to develop the code to:</p>
              <ul>
                <li>Enable a checklist for the user to 'mark as read'</li>
                <li>provide alternative arrangements of the readings for variety</li>
                <li>Any other requests?</li>
              </ul>
              <p>Today's readings:</p>
<?php
  echo daysReadingsAsVerses($month, $day);
?>
            </div>
        </div>
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
      $tOutput .=  '<div class="bibleText">';
      $tOutput .=  showPassage($readingList[$i]['bookCode'], $readingList[$i]['passageStart'], $readingList[$i]['passageEnd']);
      $tOutput .=  '</div>';
    }
    $tOutput .=  '</section>';
  }
  return $tOutput;
}

function showPassage($tBookCode, $tPassageStart, $tPassageEnd){
  global $link;
  $tOutput = '';
  $tLastBookName = '';

  $tQuery =  buildPassageQuery($tBookCode, $tPassageStart, $tPassageEnd);
  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) == 0) {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '" in dbFunctions.showPassage';
  } else {
    while($row = mysqli_fetch_assoc($result)) {
      if($tLastBookName != $row['bookName'] || $iLastChapter != $row['chapter']){
        if ($tLastBookName > ''){
          $tOutput .= '</p>';
        }
        // $tOutput .= '<h3>' . bookNameOrPsalm($row['bookName'], $row['chapter']) . ' ';
        $tOutput .= '<h3>';
        $tOutput .= bookNameOrPsalm($row['bookName'], $row['chapter'], true);
        $tOutput .= '</h3><p>';
      }
      $tOutput .= '<sup>' . $row['verseNumber'] . '</sup>' . $row['vt'] . ' ' ;
      $tLastBookName = $row['bookName'];
      $iLastChapter = $row['chapter'];
    }
  }
  mysqli_free_result($result);

  $tOutput .= '</p>';
  return $tOutput;
}

    mysqli_close($link);
    require_once 'footer.php';
?>
