<?php
// ============================================================================
function daysReadingsAsVerses($month, $day){
// ============================================================================
  global $link;
  $tOutput = '';
  $tQuery = getDayReadingQuery($month, $day);

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
    $tOutput .=  '<form class="" action="" method="post" onsubmit="return false">';
      for ($i=0; $i < $readCount; $i++) {
        $tOutput .=  '<h2 class="search-result__title">Section ' . ($i + 1) . '</h2>';

        $tOutput .=  '<p class="centerText">' . $readingList[$i]['bookName'];
        $tOutput .=  ' ' . $readingList[$i]['startChapter'] . ':' .  $readingList[$i]['startVerse'];
        $tOutput .=  ' - ' . $readingList[$i]['endChapter'] . ':' .  $readingList[$i]['endVerse'] . '</p>';

        $tAudio = 'media/' . $readingList[$i]['bookCode'] . '_' . $readingList[$i]['startChapter'];
        $tAudio .= '_' .  $readingList[$i]['startVerse'] . '-' . $readingList[$i]['endChapter'];
        $tAudio .= '_' . $readingList[$i]['endVerse'] . '.ogg';

        $tOutput .=  '<!-- ' . $tAudio . ' -->';
        if (file_exists($tAudio)){
          $tOutput .=  '<input type="button" id="bReading' . $i . '" name="bReading' . $i . '" value="listen" onclick="audioPlayPause(\'Reading' . $i . '\');"><br />';
          $tOutput .=  '<audio id="aReading' . $i . '" name="aReading' . $i . '" src="' . $tAudio . '"></audio>';
        }
        $tOutput .=  showReading($readingList[$i]['bookCode'], $readingList[$i]['startChapter'],  $readingList[$i]['startVerse'], $readingList[$i]['endChapter'], $readingList[$i]['endVerse']);
    }
    $tOutput .=  '</form>';
    $tOutput .=  '</section>';
  }
  return $tOutput;
}
// ============================================================================

// ============================================================================
function daysReadingsAsVersesNoAudio($month, $day){
// ============================================================================
  global $link;
  $tOutput = '';
  $tQuery = getDayReadingQuery($month, $day);

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
      $tOutput .=  showReading($readingList[$i]['bookCode'], $readingList[$i]['startChapter'],  $readingList[$i]['startVerse'], $readingList[$i]['endChapter'], $readingList[$i]['endVerse']);
    }
    $tOutput .=  '</section>';
  }
  return $tOutput;
}
// ============================================================================

// ============================================================================
function showReading($tBookCode, $iStartChapter, $iStartVerse, $iEndChapter, $iEndVerse){
// ============================================================================
  $tQuery =  buildPassageQueryNew($tBookCode, $iStartChapter, $iStartVerse, $iEndChapter, $iEndVerse);
  return showVerses($tQuery, '');
}
// ============================================================================

// ============================================================================
function buildPassageQueryNew($tBookCode, $iStartChapter, $iStartVerse, $iEndChapter, $iEndVerse){
// ============================================================================
  $tQuery = basicPassageQuery();

  if($tBookCode == '23J'){
    $tQuery .=' WHERE (books.bookCode = "2JO" OR books.bookCode = "3JO")';
  }else{
    $tQuery .=' WHERE books.bookCode = "' . $tBookCode . '"';
  }

  if ($iStartChapter == $iEndChapter || $iEndChapter < 1){ // one chapter
    if($iStartVerse < 1){ // whole chapter
      $tQuery .= ' AND verses.chapter = ' . $iStartChapter;
    }else {
      $tQuery .= ' AND verses.chapter = ' . $iStartChapter;
      $tQuery .= ' AND verses.verseNumber >=' . $iStartVerse;
      $tQuery .= ' AND verses.verseNumber <=' . $iEndVerse;
    }
  } else { // multiple chapters
      $tQuery .= ' AND (';
      $tQuery .= '(verses.chapter = ' . $iStartChapter;
      if ($iStartVerse > 0){ // not whole chapter
        $tQuery .= ' AND verses.verseNumber >=' . $iStartVerse;        
      }
      $tQuery .= ')';
      $tQuery .= ' OR ';
      $tQuery .= ' (verses.chapter = ' . $iEndChapter;
      if ($iEndVerse > 0){ // not whole chapter
        $tQuery .= ' AND verses.verseNumber <=' . $iEndVerse;
      }
      $tQuery .= ')';
      if ($iEndChapter == $iStartChapter + 1){
          $tQuery .= ')';
      } else {
      $tQuery .= ' OR ';
          $tQuery .= ' (verses.chapter >' . $iStartChapter;
          $tQuery .= ' AND verses.chapter <' . $iEndChapter . '))';
      }
  }
  $tQuery .= ' ORDER BY bookName, chapter, verseNumber ASC';

  return $tQuery;
}
// ============================================================================

// ============================================================================
function sectionQuery($sectionCode, $tSortOrder){ // build query to get 1 sorted section
// ============================================================================
  $tQuery = '';

  $tQuery .= 'SELECT ';
  $tQuery .= '`plan-new`.planID, ';
  $tQuery .= 'books.bookName, ';
  $tQuery .= 'books.bookCode, ';
  $tQuery .= '`plan-new`.startChapter, ';
  $tQuery .= 'books.bookChapters, ';
  $tQuery .= '`plan-new`.startVerse, ';
  $tQuery .= '`plan-new`.endChapter, ';
  $tQuery .= '`plan-new`.endVerse ';
  $tQuery .= 'FROM ';
  $tQuery .= '`plan-new` INNER JOIN books ON `plan-new`.bookCode=books.bookCode ';
  $tQuery .= 'WHERE ';
  $tQuery .= '`plan-new`.sectionCode = "' . $sectionCode . '" ';
  $tQuery .= 'ORDER BY ';
  $tQuery .= 'books.' . $tSortOrder . ', ';
  $tQuery .= '`plan-new`.planDay;';

  return $tQuery;
}
// ============================================================================

// ============================================================================
function planTable($tSortOrder = 'orderChristian'){// build HTML table of the years reading plan
// ============================================================================
  global $link;
  global $todaysVerses;
  $tOutput = '';

  $tQuery1 = sectionQuery('1TOR', $tSortOrder);
  $result1 = doQuery($link, $tQuery1);
  if (mysqli_num_rows($result1) == 0) {
    echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery1 . '"';
  } else {
    $tQuery2 = sectionQuery('2NV1', $tSortOrder);
    $result2 = doQuery($link, $tQuery2);
    if (mysqli_num_rows($result2) == 0) {
      echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery2 . '"';
    } else {
      $tQuery3 = sectionQuery('3KTV', $tSortOrder);
      $result3 = doQuery($link, $tQuery3);
      if (mysqli_num_rows($result3) == 0) {
        echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery3 . '"';
      } else {
        $tQuery4 = sectionQuery('5NCV', $tSortOrder);
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
// ============================================================================

// ============================================================================
function PTBuild($result1, $result2, $result3, $result4){ // build the HTML table
// ============================================================================
  global $todaysVerses;
  $tOutput = '';

  $tOutput .= '<table class="planTable">';
  $tOutput .= '<tr>';
  $tOutput .= '<th>Date</th>';

  $tOutput .= '<th>Section 1</th>';
  $tOutput .= '<th>Section 2</th>';
  $tOutput .= '<th>Section 3</th>';
  $tOutput .= '<th>Section 4</th>';
  // $tOutput .= '<th>Read it!</th>';

  $date = new DateTime('2001-01-01');

  for ($i=0; $i < 365; $i++) {
    $row1 = mysqli_fetch_assoc($result1);
    $row2 = mysqli_fetch_assoc($result2);
    $row3 = mysqli_fetch_assoc($result3);
    $row4 = mysqli_fetch_assoc($result4);

    $tOutput .= '</tr>';
    $tOutput .= '<tr>';

    $tOutput .= '<td><a href="plan.php?month=' . $date->format('m');
    $tOutput .= '&day=' . $date->format('j') . '">';
    $tOutput .= $date->format('M j') . '</a>';
    $tOutput .= '</td>';

    $tOutput .= PTAddSection($row1);
    $tOutput .= PTAddSection($row2);
    $tOutput .= PTAddSection($row3);
    $tOutput .= PTAddSection($row4);

    // $tOutput .= '<td><input type="checkbox"></td>';

    $date->add(new DateInterval('P1D'));
  }
  $tOutput .= '</tr></table>';

  return $tOutput;
}
// ============================================================================

// ============================================================================
function PTAddSection($row) { // add a section column to the plan table
// ============================================================================
  $tOutput = '';

  $bChaptersOnly = isChaptersOnly($row);
  
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
    if ($row['endChapter'] > 0 && !$bChaptersOnly){
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
// ============================================================================
?>
