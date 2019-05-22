<?php
// ============================================================================
function doQuery($link, $tQuery){
// ============================================================================
  echo '<!-- ' . $tQuery . ' -->';
  return mysqli_query($link, $tQuery);
}
// ============================================================================

// ============================================================================
function buildLink($tBookName, $iChapter, $tWords, $bShowMore){
// ============================================================================
  // $tReturn = '<a href="' . filter_input(INPUT_SERVER, 'PHP_SELF') . '?book=' . $tBookName; // we might be in plan.php!
  $tReturn = '<a href="bible.php?book=' . $tBookName; // we might be in plan.php!
  if($iChapter > 0){
    $tReturn .= '&chapter=' . $iChapter;
  }
  $tReturn .= '&search=' . str_replace(' ', '+', $tWords);
  if($bShowMore){
    $tReturn .= '&showMore=on';
  }
  $tReturn .= '">';
  return $tReturn;
}
// ============================================================================

// ============================================================================
function prepareBookList(){
// ============================================================================
  global $link, $tBook, $iBook;
  $tOutput = '';

  $tQuery = 'SELECT bookName, bookChapters, orderChristian FROM books ORDER BY orderChristian;';
  $result = doQuery($link, $tQuery);
  if (mysqli_num_rows($result) > 0) {
    $tOutput .= '<script type="text/javascript">';
    $tOutput .=  'var atBooks = [["Start from 1!",';
    $tOutput .=  '0]';

    while($row = mysqli_fetch_assoc($result)) {
      $tOutput .=  ', ["' . $row['bookName'] . '", ';
      $tOutput .=  $row['bookChapters'] . ']';
      if(strtoupper($row['bookName']) == strtoupper($tBook)){
        $iBook = $row['orderChristian'];
      }
    }
    $tOutput .=  '];';
    $tOutput .=  'var iBook=' . $iBook . ';';
    $tOutput .=  '</script>';
  } else {
    $tOutput .=  'Tell Carl something went wrong with the BibleStudyMan database :(';
  }
  mysqli_free_result($result);
  return $tOutput;
}
// ============================================================================

// ============================================================================
function prepareDropdownBookList(){
// ============================================================================
  global $link, $tBook;
  $tOutput = '';

  $tQuery = 'SELECT bookName FROM books;';
  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
      // $tOutput .= '<option value="' . $row['bookName'] . '">';
      $tOutput .= '<option value="' . $row['bookName'] . '"';
      // if ($row['bookName'] == $tBook) {
        // $tOutput .= ' selected';
      // }
      $tOutput .= '>' . $row['bookName'] . '</option>';
    }
  } else {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database :(';
  }
  mysqli_free_result($result);
  return $tOutput;
}
// ============================================================================

// ============================================================================
function prepareStrongs(){
// ============================================================================
  global $link;
  $atStrongs = array();

  $tQuery = 'SELECT strongsNumber, strongsOriginal, strongsEnglish FROM strongs;';
  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
      $atStrongs += [$row['strongsNumber'] => $row['strongsOriginal']];
    }
  } else {
    echo 'Tell Carl something went wrong with the BibleStudyMan database :(';
  }
  mysqli_free_result($result);

  return $atStrongs;
}
// ============================================================================

// ============================================================================
function strongs($tStrongsNo){
// ============================================================================
  global $atStrongs;
  return $atStrongs[$tStrongsNo];
}
// ============================================================================

// ============================================================================
function daysInMonth($month, $year){// calculate number of days in a month
// ============================================================================
/*
* days_in_month($month, $year)
* David Bindel from PHP.net Manual
* Returns the number of days in a given month and year, taking into account leap years.
*
* $month: numeric month (integers 1-12)
* $year: numeric year (any integer)
*
* Prec: $month is an integer between 1 and 12, inclusive, and $year is an integer.
* Post: none
*/
// corrected by ben at sparkyb dot net
  return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}
// ============================================================================

// ============================================================================
function monthName($iMonth){// return text month from numeric
// ============================================================================
  $atMonths = array("Oops", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
  return $atMonths[$iMonth];
}
// ============================================================================

// ============================================================================
function basicPassageQuery(){
// ============================================================================
  $tBaseQuery = '';
  $tBaseQuery .= 'SELECT DISTINCT books.bookName, verses.chapter, verses.verseNumber, ';
  // $tBaseQuery .= 'REPLACE(REPLACE(verses.verseText, "[H430]", ""), "[H3068]", "") AS vt';
  // $tBaseQuery .= 'REPLACE(REPLACE(verses.verseText, "<H430>", ""), "<H3068>", "") AS vt';
  $tBaseQuery .= 'verses.verseText AS vt';
  $tBaseQuery .= ', books.bookName ';
  $tBaseQuery .= 'FROM verses INNER JOIN books ON verses.bookCode=books.bookCode ';

  return $tBaseQuery;
}
// ============================================================================

// ============================================================================
function buildPassageQuery($tBookCode, $tPassageStart, $tPassageEnd){
// ============================================================================
// sort out beginning to end for psalms
  $iCommaA = stripos($tPassageStart, ',');
  $iCommaB = strripos($tPassageEnd, ',');

  // if ($iCommaA > 0){
  //     $tPassageStart = substr($tPassageStart, 0, $iCommaA);
  // }
  //
  // if ($iCommaB > 0){
  //     $tPassageEnd = substr($tPassageEnd, $iCommaB+1);
  // }

  return buildPassageQuery2($tBookCode, $tPassageStart, $tPassageEnd);
}
// ============================================================================

// ============================================================================
function buildPassageQuery2($tBookCode, $tPassageStart, $tPassageEnd){
// ============================================================================
  $iColonA = stripos($tPassageStart, ':');
  $iColonB = stripos($tPassageEnd, ':');
  $tQuery = basicPassageQuery();

  if($tBookCode == '23J'){
    $tQuery .=' WHERE (books.bookCode = "2JO" OR books.bookCode = "3JO")';
  }else{
    $tQuery .=' WHERE books.bookCode = "' . $tBookCode . '"';
  }

  if ($iColonA > 0){
      $tChapterA = substr($tPassageStart, 0, $iColonA);
      $tVerseA = substr($tPassageStart, $iColonA+1);
  }else{
      $tChapterA = $tPassageStart;
      $tVerseA = '0';
  }

  if ($iColonB > 0){
      $tChapterB = substr($tPassageEnd, 0, $iColonB);
      $tVerseB = substr($tPassageEnd, $iColonB+1);
  }else{
      $tChapterB = $tPassageEnd;
      $tVerseB = '0';
  }

  if ($tChapterA == $tChapterB || $tChapterB == ''){ // one chapter
    if($tVerseA > ''){
      $tQuery .= ' AND verses.chapter = ' . $tChapterA;
    }else {
      $tQuery .= ' AND verses.chapter = ' . $tChapterA;
      $tQuery .= ' AND verses.verseNumber >=' . $tVerseA;
      $tQuery .= ' AND verses.verseNumber <=' . $tVerseB;
    }
  } else {
      $tQuery .= ' AND (';
      $tQuery .= '(verses.chapter = ' . $tChapterA;
      $tQuery .= ' AND verses.verseNumber >=' . $tVerseA . ')';
      $tQuery .= ' OR ';
      $tQuery .= ' (verses.chapter = ' . $tChapterB;
      $tQuery .= ' AND verses.verseNumber <=' . $tVerseB . ')';
      if ($tChapterB == $tChapterA + 1){
          $tQuery .= ')';
      } else {
      $tQuery .= ' OR ';
          $tQuery .= ' (verses.chapter >' . $tChapterA;
          $tQuery .= ' AND verses.chapter <' . $tChapterB . '))';
      }
  }
  $tQuery .= ' ORDER BY bookName, chapter, verseNumber ASC';

  return $tQuery;
}
// ============================================================================

// ============================================================================
function getDayReadingQuery($month, $day){
// ============================================================================
  $tQuery = '';

  $tQuery .= 'SELECT *, DATE_FORMAT(planDate,"%b %e") as planDateFormatted';
  $tQuery .= ' FROM newPlan INNER JOIN planDays ON newPlan.planDay=planDays.ID';
  $tQuery .= ' INNER JOIN books ON newPlan.bookCode=books.bookCode';
  $tQuery .= ' WHERE MONTH(planDate)="' . $month;
  $tQuery .= '" AND DAY(planDate)="' . $day . '" ORDER BY planDate, newPlan.sectionCode ASC';

  return $tQuery;
}
// ============================================================================

// ============================================================================
function daysReadingsAsSentence($month, $day){
// ============================================================================
  global $link;
  $tOutput = '';
  $tQuery = getDayReadingQuery($month, $day);

  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) == 0) {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
  } else {
    while($row = mysqli_fetch_assoc($result)) {
      if($row['sectionCode'] == '1TOR'){
        // $tOutput .= ' first, ';
        $tOutput .= '';
      }
      //$tOutput .= "" . $row['sectionEnglish']  . ' (' . $row['sectionName'] . ') - ';
      if($row['sectionCode'] == '2NV1'){
          // $tOutput .= '; next it&rsquo;s ';
          $tOutput .= '. Follow that by reading';
      }
      if($row['sectionCode'] == '3KTV'){
          $tOutput .= '. The third part is';
      }
      if($row['sectionCode'] == '5NCV'){
          $tOutput .= '; and lastly';
      }
      $tOutput .= ' <strong>';
// -------------- if whole chapter no from... to ------------
      if($row['endVerse'] > 0){
        if($row['startChapter'] != $row['endChapter']){
          $tOutput .= 'from';
        }
      }
      $tOutput .= ' ' . bookNameOrPsalm($row['bookName'], 0, false);

      $tOutput .= ' ' . $row['startChapter'];
      if($row['startVerse'] > 0){
        $tOutput .= ' verse';
        if($row['startChapter'] == $row['endChapter']){
          $tOutput .= 's';
        }
        $tOutput .= ' ' . $row['startVerse'];
      }
      if($row['endVerse'] > 0||$row['endChapter'] > 0){
        $tOutput .= ' to ';
        if($row['startChapter'] == $row['endChapter']){
          $tOutput .= $row['endVerse'];
        } else{
          $tOutput .= ' Chapter ';
          $tOutput .= $row['endChapter'];
          $tOutput .= ' verse ' . $row['endVerse'];
        }
      }
// -------------- if whole chapter no from... to ------------
      $tOutput .= '</strong>';
    }
  }
  mysqli_free_result($result);

  return $tOutput;
}
// ============================================================================

// ============================================================================
function bookNameOrPsalm($tBookName, $iChapter, $bShowLinks){
// ============================================================================
  global $tWords, $bShowMore;

  $tOutput = '';
  $iBookChapters = 2; // not important to get actual chapters in book unless only 1
  // if(strpos('Obadiah|Philemon|2 John|3 John|Jude', $tBookName)>0){
    // if($tBookName != 'John'){
  if(in_array($tBookName, array('Obadiah', 'Philemon', '2 John', '3 John', 'Jude')) ) {
    $iBookChapters = 1; // if only one chapter in book don't say 'chapter'!
  }

  if($bShowLinks){ // link to book only
    $tOutput .= buildLink($tBookName, 0, $tWords, $bShowMore);
  }
  if($tBookName == 'Psalms'){
    $tOutput .= 'Psalm';
  }else {
    $tOutput .= $tBookName;
  }
  if($bShowLinks){
    $tOutput .= '</a>';
  }
  if ($iBookChapters > 1){
    $tOutput .= ' ';
    if($bShowLinks){ // link to book and chapter
      $tOutput .= buildLink($tBookName, $iChapter, $tWords, $bShowMore);
    }
    if($tBookName != 'Psalms'){
      $tOutput .= 'Chapter ';
    }
    if ($iChapter > 0){
      $tOutput .= $iChapter;
      if($bShowLinks){
        $tOutput .= '</a>';
      }
    }
  }
  return $tOutput;
}
// ============================================================================

// ============================================================================
function passage($tBook, $tChapter, $tVerses, $tWords, $bShowMore){
// ============================================================================
  global $link, $bHighlightSW, $bShowOW;

  $bProcessRequest = (strlen($tBook . $tChapter . $tVerses . $tWords) > 0);

  // $tOutput = '';
  $tBaseQuery = basicPassageQuery();
  if ($bProcessRequest) {
  // if (true) {
    // $tLastBookName = '';
    // $iLastChapter = 0;

    if (empty($tBook)) {
      if (empty($tWords)) {
        // $tOutput .= ''<p>It seems you didn&rsquo;t enter a passage - this is one of my favourites:</p>';
        // $tQuery = 'SELECT verseText FROM verses WHERE bookCode ="JOH" AND chapter=3 AND verseNumber=16;';
        $tOutput .=  '<p>I can&rsquo;t understand what you want - this is the beginning of The Bible:</p>';
        $tQuery = $tBaseQuery . ' WHERE books.bookName ="Genesis" AND verses.chapter=1 AND verses.verseNumber<10;';
      }else{
        $tQuery = $tBaseQuery . ' WHERE ' . addSQLWildcards($tWords, $bShowMore) . ';';
      }
    } else {
      if ($tBook == '2 & 3 John') {
        $tQuery = $tBaseQuery . ' WHERE books.bookName ="2 John" OR  books.bookName ="3 John"';
      } else {
        $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '"';
      }
      if (empty($tChapter))
      {
        if (empty($tWords)) {
          $tQuery = $tQuery . ';';
        }else{
          $tQuery = $tQuery . ' AND  ' . addSQLWildcards($tWords, $bShowMore) . ';';
        }
      }else{
        // ---- NOT searching down to verse level - keep commented in case I change my mind!
        // if (empty($tVerses))
        // {
          $tQuery = $tQuery . ' AND verses.chapter=' . $tChapter;
        // }else{
        //     $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '" AND verses.chapter=' . $tChapter . ' AND verses.verseNumber=' . $tVerse . ';';
        // }
        // ---- NOT searching down to verse level - keep commented in case I change my mind!

        // ---- NOT searching words if chapter - highlight instead - keep commented in case I change my mind!
        // if (empty($tWords)) {
          $tQuery = $tQuery . ';';
        // }else{
          // $tQuery = $tQuery . ' AND ' . addSQLWildcards($tWords, $bShowMore) . ';';
        // }
        // ---- NOT searching words if chapter - highlight instead - keep commented in case I change my mind!
      }
    }
// ------------------- display Bible passage -------------------------------
    // $result = doQuery($link, $tQuery);
    //
    // if (mysqli_num_rows($result) > 0) {
    //   while($row = mysqli_fetch_assoc($result)) {
    //     if($tLastBookName != $row['bookName'] || $iLastChapter != $row['chapter']){
    //       $iBookChapters = 2; //$row['bookChapters'];
    //       $tOutput .=  '<h3>';
    //       $tOutput .=  bookNameOrPsalm($row['bookName'], $row['chapter'], true);
    //       $tOutput .=  '</h3>';
    //     }
    //     if (isInRange($row['verseNumber'], $tVerses)){
    //       $tOutput .=  '<span class="highlight">';
    //       if ($row['verseNumber'] > 0) {
    //         $tOutput .=  '<sup>' . $row['verseNumber'] . '</sup>';
    //       }
    //       $tOutput .=  processStrongs($row['vt'], $bHighlightSW, $bShowOW) . ' ';
    //       $tOutput .=  '</span>';
    //     }else{
    //       // $tOutput .=  '<sup>' . $row['verseNumber'] . '</sup>' . highlightSearch(processStrongs($row['vt'], true, true)) . ' ';
    //       $tOutput .=  '<sup>' . $row['verseNumber'] . '</sup>' . highlightSearch(processStrongs($row['vt'], $bHighlightSW, $bShowOW)) . ' ';
    //     }
    //     $tLastBookName = $row['bookName'];
    //     $iLastChapter = $row['chapter'];
    //   }
    // } else {
    //   $tOutput .=  'It could be me... but there doesn&rsquo;t seem to be a match for that!';
    // }
    // mysqli_free_result($result);
    $tOutput = showVerses($tQuery);
// ------------------- display Bible passage -------------------------------
  }
  return $tOutput;
}
// ============================================================================

// ============================================================================
function showVerses($tQuery){
// ============================================================================
  global $link, $bHighlightSW, $bShowOW;

  $tOutput = '';
  $tLastBookName = '';
  $iLastChapter = 0;

  $result = doQuery($link, $tQuery);

  $tOutput .=  '<div class="bibleText">';

  if (mysqli_num_rows($result) == 0) {
    $tOutput .=  'It could be me... but there doesn&rsquo;t seem to be a match for that!';
  } else {
    while($row = mysqli_fetch_assoc($result)) {
      if($tLastBookName != $row['bookName'] || $iLastChapter != $row['chapter']){
        $iBookChapters = 2; //$row['bookChapters'];
        if ($tLastBookName > ''){
          $tOutput .= '</p>';
        }
        $tOutput .=  '<h3>';
        $tOutput .=  bookNameOrPsalm($row['bookName'], $row['chapter'], true);
        $tOutput .=  '</h3><p>';
      }
      if (isInRange($row['verseNumber'], $tVerses)){
        $tOutput .=  '<span class="highlight">';
        if ($row['verseNumber'] > 0) {
          $tOutput .=  '<sup>' . $row['verseNumber'] . '</sup>';
        }
        $tOutput .=  processStrongs($row['vt'], $bHighlightSW, $bShowOW) . ' ';
        $tOutput .=  '</span>';
      }else{
        if ($row['verseNumber'] > 0) {
          $tOutput .=  '<sup>' . $row['verseNumber'] . '</sup>';
        }
        $tOutput .=  highlightSearch(processStrongs($row['vt'], $bHighlightSW, $bShowOW)) . ' ';
      }
      $tLastBookName = $row['bookName'];
      $iLastChapter = $row['chapter'];
    }
  }
  mysqli_free_result($result);

  $tOutput .=  '</div>';
  return $tOutput;
}
// ============================================================================

// ============================================================================
function processStrongs($tValue, $bHighlight, $bBracketOriginal){
// ============================================================================
  $iWordStart = 0;
  $iTagStart = 0;
  $iTagEnd = 0;
  $iSearchEnd = 0;
  $tNewValue = '';
  $tStrongsNo = '';

  do {
    $iTagStart = strpos($tValue, '<');
    // echo '<!-- $iTagStart:' . $iTagStart . ' -->';
    if ($iTagStart > 0) {
      $iWordStart = strrpos(substr($tValue, 0, $iTagStart), ' '); // look for preceeding space
      if (($iWordStart > 0)){$iWordStart = $iWordStart + 1;}else{$iWordStart = 0;}; // if first word - no space
      // echo '<!-- $iWordStart:' . $iWordStart . ':over 0:' . ($iWordStart > 0) . ' -->';
      $iTagEnd = strpos(substr($tValue, 0), '>');
      // echo '<!-- $iTagEnd:' . $iTagEnd . ' -->';
      $tStrongsNo = substr($tValue, $iTagStart + 1, $iTagEnd - 1 - $iTagStart);

      $tNewValue = $tNewValue . substr($tValue, 0, $iWordStart);
      if ($bHighlight){
        $tNewValue = $tNewValue . '<span style="background: lavender"';
        // $tNewValue = $tNewValue . 'onmouseover=""';
        // $tNewValue = $tNewValue . 'onmouseout=""';
        $tNewValue = $tNewValue . '>';
      }
      $tNewValue = $tNewValue . substr($tValue, $iWordStart, $iTagEnd + 1 - $iWordStart);
      if ($bBracketOriginal){
        $tNewValue = $tNewValue . ' <sub>(' . strongs($tStrongsNo) . ')</sub>';
      }
      if ($bHighlight){
        $tNewValue = $tNewValue . '</span> ';
      }

      $tValue = substr($tValue, $iTagEnd + 1);
    }
  } while ($iTagStart > 0);
  return $tNewValue . $tValue;
}
// ============================================================================

// ============================================================================
function highlightSearch($tValue){
// ============================================================================
  global $tWords, $bShowMore;

  if ($tWords > ''){
    if (! $bShowMore){
      // $tValue = str_ireplace($tWords, '<span class="highlight">' . $tWords . '</span>', $tValue);
      $tValue = highlight($tWords, $tValue);
    }else {
      $atSearch = explode (' ', $tWords);
      foreach ($atSearch as $tWordsWord) {
        // $tValue = str_ireplace($tWordsWord, '<span class="highlight">' . $tWordsWord . '</span>', $tValue);
        $tValue = highlight($tWordsWord, $tValue);
      }
    }
  }
  return '<!-- highlightSearch ' . $tWords . ' -->' . $tValue;
}
// ============================================================================

// ============================================================================
function highlight($needle, $haystack){
// ============================================================================
  $ind = stripos($haystack, $needle);
  $len = strlen($needle);
  if($ind !== false){
      return substr($haystack, 0, $ind) . '<span class="highlight">' .
         substr($haystack, $ind, $len) .'</span>' .
          highlight($needle, substr($haystack, $ind + $len));
  } else return $haystack;
}
// ============================================================================

// ============================================================================
function addSQLWildcards($tValue, $bShowMore){
// ============================================================================
  if($bShowMore){
    // if (strpos($tValue, ' ') > 0){ // spaces present - more than one word!
      $tValue = 'verses.verseText LIKE "%' . str_replace(' ', '% %', $tValue) . '%"';
    // }else {
    // }
  }else {
    if (strpos($tValue, ' ') > 0){ // spaces present - probably more than one word!
      $tValue = 'verses.verseText LIKE "%' . $tValue . '%"';
    }else {
      $tValue = '(verses.verseText LIKE "' . $tValue . '%"' . ' OR verses.verseText LIKE "% ' . $tValue . '%")';
    }
  }
  return $tValue;
}
// ============================================================================

// ============================================================================
function isInRange($verse, $tVerses){
// ============================================================================
  $bReturn = false;
  $iDashPosition = strpos($tVerses, '-');
  if ($iDashPosition > 0){
    $iStartVerse = substr($tVerses, 0, $iDashPosition);
    $iEndVerse = substr($tVerses, $iDashPosition + 1);
    $bReturn = ($verse >= $iStartVerse) && ($verse <= $iEndVerse);
  }else{
     $bReturn = ($tVerses == $verse);
  }
  return $bReturn;
}
// ============================================================================

?>
