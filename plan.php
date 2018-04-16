<?php
    require_once 'header.php';
    require_once '../sqlCon.php';
    require_once 'dbFunctions.php';

    $tMonth = filter_input(INPUT_GET, 'month');
    $tDay = filter_input(INPUT_GET, 'day');

    $timestamp = time();
    //$bLeap = (date("L", $timestamp)==1);
    $year = date("Y", $timestamp);
    $month = date("n", $timestamp);
    $day = date("j", $timestamp);

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
?>
        <div class='intro'>
            <p>The day-by-day plan I devised for reading the entire Bible in one
            year based on four traditional sections - The Law (Torah or Pentateuch);
            The Prophets (Neviim); The Writings or Poetry (Ktuviim) and The
            New Testament.</p>
<?php
  echo daysReadingsAsSentence($month, $day);
?>
        </div>
        <div class="main plan">
            <h1>The Bible Reading Plan</h1>
            <div class="subMain sectGeneral">
                <p>I need to develop the code to:</p>
                <ul>
                    <li><a href="planTable.php">provide a list of readings</a></li>
                    <li>Enable a checklist for the user to 'mark as read'</li>
                    <li>provide alternative arrangements of the readings for variety</li>
                    <li>Any other requests?</li>
                </ul>
<?php
  echo daysReadingsAsVerses($month, $day);
?>
            </div>
        </div>
<?php
function daysReadingsAsSentence($month, $day){
  global $link;
  $tOutput = '';
  $tQuery = '';

  //$tQuery .= 'SET @rowNumber = 0; SELECT *, (@rowNumber := @rowNumber +1) AS rowNum';
  $tQuery .= 'SELECT *';
  $tQuery .= ', DATE_FORMAT(planDate,"%D %M") as planDateFormatted';
  $tQuery .= ' FROM plan, sections, books';
  $tQuery .= ' WHERE sections.sectionCode = plan.sectionCode';
  $tQuery .= ' AND books.bookCode = plan.bookCode';
  //$tQuery .= ' AND MONTH(planDate) = MONTH(NOW()) AND DAY(planDate) = DAY(NOW())';
  $tQuery .= ' AND MONTH(planDate) = "' . $month . '"';
  $tQuery .= ' AND DAY(planDate) = "' . $day . '"';
  $tQuery .= ' ORDER BY planDate, sections.sectionCode ASC;';

  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) == 0) {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
  } else {
    while($row = mysqli_fetch_assoc($result)) {
      if($row['sectionCode'] == '1TOR'){
        $tOutput .= '<p>The plan&rsquo;s readings for ' . $row['planDateFormatted'] . ' are 1, ';
      }
      //$tOutput .= "" . $row['sectionEnglish']  . ' (' . $row['sectionName'] . ') - ';
      if($row['sectionCode'] == '2NV1'){
          $tOutput .= '; then 2, ';
      }
      if($row['sectionCode'] == '3KTV'){
          $tOutput .= '; and 3, ';
      }
      if($row['sectionCode'] == '5NCV'){
          $tOutput .= '; and lastly ';
      }
      $tOutput .= 'from <strong>' . $row['bookName'] . ' ';
      $tOutput .= '' . $row["passageStart"] . ' to ';
      $tOutput .= '' . $row["passageEnd"] . '</strong>';
    }
    $tOutput .= '.</p>';
  }
  mysqli_free_result($result);
  return $tOutput;
}

function daysReadingsAsVerses($month, $day){
  global $link;
  $tOutput = '';
  $tQuery = '';

  $tQuery .= "SELECT *, DATE_FORMAT(planDate,'%b %e') as planDateFormatted ";
  $tQuery .= "FROM plan WHERE MONTH(planDate)='" . $month;
  $tQuery .= "' AND DAY(planDate)='" . $day . "' ORDER BY planDate ASC";

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
          if($tLastBookName != $row["bookName"] || $iLastChapter != $row["chapter"]){
              $iBookChapters = 2; //$row["bookChapters"];
              if ($tLastBookName > ''){
                $tOutput .= "</p>";
              }
              $tOutput .= "<h3>" . $row["bookName"];
              if($iBookChapters > 1){ // only say 'chapter' if more than 1
                  if($row["bookName"] == "Psalms"){ // only call it a chapter if not a psalm
                      $tOutput .= " Psalm ";
                  }else{
                      $tOutput .= " Chapter ";
                  }
                  $tOutput .= $row["chapter"];
              }
              $tOutput .= "</h3><p>";
          }
          $tOutput .= "<sup>" . $row["verseNumber"] . "</sup>" . $row["vt"] . " " ;
          $tLastBookName = $row["bookName"];
          $iLastChapter = $row["chapter"];
      }
  }
  mysqli_free_result($result);

  $tOutput .= "</p>";
  return $tOutput;
}

    mysqli_close($link);
    require_once 'footer.php';
?>
