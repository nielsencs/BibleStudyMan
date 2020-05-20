<?php
// ============================================================================
function doQuery($link, $tQuery){
// ============================================================================
  echo '<!-- ' . $tQuery . ' -->';
  return mysqli_query($link, $tQuery);
}
// ============================================================================

// ============================================================================
function buildLink($tBookName, $iChapter, $tWords, $bPhrase){
// ============================================================================
  // $tReturn = '<a href="' . filter_input(INPUT_SERVER, 'PHP_SELF') . '?book=' . $tBookName;
  $tReturn = '<a href="bible.php?book=' . $tBookName; // we might be in plan.php!
  if($iChapter > 0){
    $tReturn .= '&chapter=' . $iChapter;
  }
  $tReturn .= '&words=' . str_replace(' ', '+', $tWords);
  if($bPhrase){
    $tReturn .= '&phrase=on';
  }
  $tReturn .= '">';
  return $tReturn;
}
// ============================================================================

// ============================================================================
function prepareBookAbbs(){
// ============================================================================
  global $link;
  $atBookAbbs = array();

  $tQuery = 'SELECT baBookAbbreviation, bookName FROM bookabbreviations INNER JOIN books ON baBookCode=books.bookCode;';
  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
      $atBookAbbs += [$row['baBookAbbreviation'] => $row['bookName']];
    }
  } else {
    echo 'Tell Carl something went wrong with the BibleStudyMan database :(';
  }
  mysqli_free_result($result);

  return $atBookAbbs;
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
      if(strtoupper($row['bookName']) === strtoupper($tBook)){
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
       if ($row['bookName'] === $tBook) {
         $tOutput .= ' selected';
       }
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
function prepareDropdownChapterList(){
// ============================================================================
  global $link, $tBook, $tChapter;
  $tOutput = '';

  $tQuery = 'SELECT bookName,bookChapters FROM books WHERE bookName = "' . $tBook . '";';
  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) === 1) {
    while($row = mysqli_fetch_assoc($result)) {
      for ($i=1;$i<=$row['bookChapters'];$i++){
        $tOutput .= '<option value="' . $i . '"';
        if ($i === intval($tChapter)) {
          $tOutput .= ' selected';
        }
        $tOutput .= '>' . $i . '</option>';
      }
    }
  } else {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database :(';
  }
  mysqli_free_result($result);
  return $tOutput;
}
// ============================================================================

// ============================================================================
function prepareDropdownMonthList($iMonth){
// ============================================================================
  $tOutput = '';

  for ($i=1;$i<=12;$i++){
    $tOutput .= '<option value="' . $i . '"';
    if ($i == $iMonth) {
      $tOutput .= ' selected';
    }
    $tOutput .= '>' . monthName($i) . '</option>';
  }
  return $tOutput;
}
// ============================================================================

// ============================================================================
function prepareDropdownDayList($iDay, $tMonth, $iDaysInMonth){
// ============================================================================
  $tOutput = '';

  for ($i=1;$i<= $iDaysInMonth;$i++){
    $tOutput .= '<option value="' . $i . '"';
    if ($i == $iDay) {
      $tOutput .= ' selected';
    }
    $tOutput .= '>' . $i . '</option>';
  }
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
function daysInMonth($iMonth, $iYear){// calculate number of days in a month
// ============================================================================
/*
* days_in_month($iMonth, $iYear)
* David Bindel from PHP.net Manual
* Returns the number of days in a given month and year, taking into account leap years.
*
* $iMonth: numeric month (integers 1-12)
* $iYear: numeric year (any integer)
*
* Prec: $iMonth is an integer between 1 and 12, inclusive, and $iYear is an integer.
* Post: none
*/
// corrected by ben at sparkyb dot net
  return $iMonth === 2 ? ($iYear % 4 ? 28 : ($iYear % 100 ? 29 : ($iYear % 400 ? 28 : 29))) : (($iMonth - 1) % 7 % 2 ? 30 : 31);
}
// ============================================================================

// ============================================================================
function monthName($iMonth){// return text month from numeric
// ============================================================================
  $atMonths = array("Oops", "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December");
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

  if($tBookCode === '23J'){
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

  if ($tChapterA === $tChapterB || $tChapterB === ''){ // one chapter
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
      if ($tChapterB === $tChapterA + 1){
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
function getDayReadingQuery($iMonth, $day){
// ============================================================================
  $tQuery = '';

  $tQuery .= 'SELECT *, DATE_FORMAT(planDate,"%b %e") as planDateFormatted';
  $tQuery .= ' FROM newPlan INNER JOIN planDays ON newPlan.planDay=planDays.ID';
  $tQuery .= ' INNER JOIN books ON newPlan.bookCode=books.bookCode';
  $tQuery .= ' WHERE MONTH(planDate)="' . $iMonth;
  $tQuery .= '" AND DAY(planDate)="' . $day . '" ORDER BY planDate, newPlan.sectionCode ASC';

  return $tQuery;
}
// ============================================================================

// ============================================================================
function daysReadingsAsSentence($iMonth, $day){
// ============================================================================
  global $link;
  $tOutput = '';
  $tQuery = getDayReadingQuery($iMonth, $day);

  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) === 0) {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
  } else {
    while($row = mysqli_fetch_assoc($result)) {
      if($row['sectionCode'] === '1TOR'){
        // $tOutput .= ' first, ';
        $tOutput .= '';
      }
      //$tOutput .= "" . $row['sectionEnglish']  . ' (' . $row['sectionName'] . ') - ';
      if($row['sectionCode'] === '2NV1' || $row['sectionCode']=== '4NV2'){
          // $tOutput .= '; next it&rsquo;s ';
          $tOutput .= '. Follow that by reading';
      }
      if($row['sectionCode'] === '3KTV'){
          $tOutput .= '. The third part is';
      }
      if($row['sectionCode'] === '5NCV'){
          $tOutput .= '; and lastly';
      }
      $tOutput .= ' <strong>';
// -------------- if whole chapter no from... to ------------
      if($row['endVerse'] > 0){
        if($row['startChapter'] != $row['endChapter']){
          $tOutput .= 'from';
        }
      }
      $bChaptersOnly = isChaptersOnly($row);
      $tOutput .= ' ' . bookNameOrPsalm($row['bookName'], 0, false, $bChaptersOnly);

      $tOutput .= ' ' . $row['startChapter'];
      if($row['startVerse'] > 0){
        $tOutput .= ' verse';
        if($row['startChapter'] === $row['endChapter']){
          $tOutput .= 's';
        }
        $tOutput .= ' ' . $row['startVerse'];
      }
      if($row['endVerse'] > 0||$row['endChapter'] > 0){
        if($bChaptersOnly & $row['endChapter'] === $row['startChapter']+1){
          $tOutput .= ' and ';
        } else {
          $tOutput .= ' to ';          
        }
        if($row['startChapter'] === $row['endChapter']){
          $tOutput .= $row['endVerse'];
        }else{
          if (! $bChaptersOnly){
            $tOutput .= ' Chapter ';
          }
          $tOutput .= $row['endChapter'];
          if (! $bChaptersOnly){
            $tOutput .= ' verse ' . $row['endVerse'];
          }
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
function isChaptersOnly($row){
// ============================================================================
  return ($row['startChapter'] != $row['endChapter']) & (intval($row['startVerse']) === 0) & (intval($row['endVerse']) === 0);
}
// ============================================================================

// ============================================================================
function bookNameOrPsalm($tBookName, $iChapter, $bShowLinks, $bPluralChapter = false){
// ============================================================================
  global $tWords, $bPhrase;

  $tOutput = '';
  $iBookChapters = 2; // not important to get actual chapters in book unless only 1
  // if(strpos('Obadiah|Philemon|2 John|3 John|Jude', $tBookName)>0){
    // if($tBookName != 'John'){
  if(in_array($tBookName, array('Obadiah', 'Philemon', '2 John', '3 John', 'Jude')) ) {
    $iBookChapters = 1; // if only one chapter in book don't say 'chapter'!
  }

  if($bShowLinks){ // link to book only
    $tOutput .= buildLink($tBookName, 0, $tWords, $bPhrase);
  }
  if($tBookName === 'Psalms'){
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
      $tOutput .= buildLink($tBookName, $iChapter, $tWords, $bPhrase);
    }
    if($tBookName != 'Psalms'){
      if($bPluralChapter){
        $tOutput .= 'Chapters ';
      }else{
        $tOutput .= 'Chapter ';
      }
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
function passage($tBook, $tChapter, $tVerses, $tWords, $bPhrase){
// ============================================================================
  global $link, $bHighlightSW, $bShowOW;

  $bProcessRequest = (strlen($tBook . $tChapter . $tVerses . $tWords) > 0);

  $tOutput = '';
  $tBaseQuery = basicPassageQuery();
  if ($bProcessRequest) {
  // if (true) {
    // $tLastBookName = '';
    // $iLastChapter = 0;

    if (empty($tBook)) {
      if (empty($tWords)) {
        // $tOutput .= ''<p>It seems you didn&rsquo;t enter a passage - this is one of my favourites:</p>';
        // $tQuery = 'SELECT verseText FROM verses WHERE bookCode ="JOH" AND chapter=3 AND verseNumber=16;';
        $tOutput .=  '<h2>I&rsquo;m sorry I don&rsquo;t understand what you want - this is the beginning of The Bible:</h2>';
        $tQuery = $tBaseQuery . ' WHERE books.bookName ="Genesis" AND verses.chapter=1 AND verses.verseNumber<10;';
      }else{
        $tQuery = $tBaseQuery . ' WHERE ' . addSQLWildcards($tWords, $bPhrase) . ';';
      }
    } else {
      if ($tBook === '2 & 3 John') {
        $tQuery = $tBaseQuery . ' WHERE books.bookName ="2 John" OR  books.bookName ="3 John"';
      } else {
        $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '"';
      }
      if (empty($tChapter))
      {
        if (empty($tWords)) {
          $tQuery = $tQuery . ';';
        }else{
          $tQuery = $tQuery . ' AND  ' . addSQLWildcards($tWords, $bPhrase) . ';';
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
          // $tQuery = $tQuery . ' AND ' . addSQLWildcards($tWords, $bPhrase) . ';';
        // }
        // ---- NOT searching words if chapter - highlight instead - keep commented in case I change my mind!
      }
    }
    $tOutput .= showVerses($tQuery, $tVerses);
  }else{
// These two lines could be exchanged for the one below to give sample text when 
// blank search criteria eg on opening bible.php for the first time.
    $tQuery = $tBaseQuery . ' WHERE books.bookName ="Genesis" AND verses.chapter=1;';
    $tOutput = '<h2>You can search for words, or a phrase, or pick a book in the box above. While your deciding what to lookup, here&rsquo;s a sample:</h2>' . showVerses($tQuery, $tVerses);
//    $tOutput = '';
  }
  return $tOutput;
}
// ============================================================================

// ============================================================================
function showVerses($tQuery, $tVerses){
// ============================================================================
  global $link, $bHighlightSW, $bShowOW;

  $tOutput = '';
  $tLastBookName = '';
  $iLastChapter = 0;

  $result = doQuery($link, $tQuery);

  $tOutput .=  '<div class="bibleText">';

  if (mysqli_num_rows($result) === 0) {
    $tOutput .=  'It could be me... but I can&rsquo;t seem to find that!';
  } else {
    while($row = mysqli_fetch_assoc($result)) {
      if($tLastBookName != $row['bookName'] || $iLastChapter != $row['chapter']){
//        $iBookChapters = 2; //$row['bookChapters'];
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
function isInRange($verse, $tVerses){
// ============================================================================
  $bReturn = false;
  $iDashPosition = strpos($tVerses, '-');
  if ($iDashPosition > 0){ // from - to
    $iStartVerse = substr($tVerses, 0, $iDashPosition);
    $iEndVerse = substr($tVerses, $iDashPosition + 1);
    $bReturn = ($verse >= $iStartVerse) && ($verse <= $iEndVerse);
  }else{
     $bReturn = ($tVerses === $verse);
  }
  return $bReturn;
}
// ============================================================================

// ============================================================================
function processStrongs($tValue, $bHighlightSW, $bShowOW){
// ============================================================================
  $iWordStart = 0;
  $iTagStart = 0;
  $iTagEnd = 0;
//  $iSearchEnd = 0;
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
      if ($bHighlightSW){
        $tNewValue = $tNewValue . '<span class="highlightOW">';
      }
      $tNewValue = $tNewValue . substr($tValue, $iWordStart, $iTagEnd + 1 - $iWordStart);
      if ($bShowOW){
        $tNewValue = $tNewValue . ' <sub>(' . strongs($tStrongsNo) . ')</sub>';
      }
      if ($bHighlightSW){
        $tNewValue = $tNewValue . '</span>';
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
  global $tWords, $bPhrase;

  if ($tWords > ''){
//    if (! $bPhrase){
    if ($bPhrase){
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
  if($ind){
      return substr($haystack, 0, $ind) . '<span class="highlight">' .
         substr($haystack, $ind, $len) .'</span>' .
          highlight($needle, substr($haystack, $ind + $len));
  } else return $haystack;
}
// ============================================================================

// ============================================================================
function addSQLWildcards($tWords, $bPhrase){
// ============================================================================
//  return procesSearchWords($tWords, $bPhrase);

  if($bPhrase){ // 'phrase' was 'checked' regardless of number of words
    if (strpos($tWords, ' ') > 0){ // spaces present - probably more than one word!
      $tWords = 'verses.verseText LIKE "%' . $tWords . '%"';
    }else {
      $tWords = '(verses.verseText LIKE "' . $tWords . '%"' . ' OR verses.verseText LIKE "% ' . $tWords . '%")';
    }
  }else {
    // if (strpos($tWords, ' ') > 0){ // spaces present - more than one word!
      $tWords = 'verses.verseText LIKE "%' . str_replace(' ', '% %', $tWords) . '%"';
    // }else {
    // }
  }
  return $tWords;
}
// ============================================================================

// ============================================================================
function joinWords($atWords, $i, $iLen){
// ============================================================================
  $tWords = '';
//    echo '$i[' . $i . ']$iLen[' . $iLen . ']';
  for ($j=$i;$j < $iLen; $j++){
    $tWords .= $atWords[$j] . ' ';
  }
  return  trim($tWords);
}
// ============================================================================

// ============================================================================
function procesSearchWords($tWords, $bPhrase){
// ============================================================================
  $atWords = explode(' ', $tWords);
  $tWord = '';
  $iLen = count($atWords);

  $i = 0;
  $atBeginBook = beginsWithBook($atWords, $i, $iLen, $bPhrase);
  $tBook = $atBeginBook[0];
  $i = $atBeginBook[1];

  echo '####### BOOK ####### $tBook:[' . $tBook . '], $i:' . $i . '####### BOOK #######';

  if ($i === $iLen-1){ // done!
    echo 'Tada!';
    $tWords = '';
  }else{
//    for($j = $i;$j < $iLen; $j++) {
//      $tWord = $atWords[$j];
//      if ($j < $iLen){
//      $tValue = procesSearchWord($atWords, $j, $iLen, $bPhrase);
//      }else{
//        $tValue = procesSearchWord($atWords, 0);
//      }
//    }
//    if($bPhrase){
//      $tWords = 'verses.verseText LIKE "%' . str_replace(' ', '%  %', $tWords) . '%"';
//    }else{
//      $tWords = '(verses.verseText LIKE "' . $tWords . '%"' . ' OR verses.verseText LIKE "% ' . $tWords . '%")';
//    }else{
      // if (strpos($tWords, ' ') > 0){ // spaces present - probably more than one word!
//        $tWords = 'verses.verseText LIKE "%' . str_replace(' ', '% %', $tWords) . '%"';
      // }else {
      // }
    }
  
/*
  
  
  
  if($bPhrase){
    if (strpos($tWords, ' ') > 0){ // spaces present - probably more than one word!
//      $tWords = 'verses.verseText LIKE "%' . $tWords . '%"';
      $tWords = 'verses.verseText LIKE "%' . str_replace(' ', '%  %', $tWords) . '%"';
    }else {
      $tWords = '(verses.verseText LIKE "' . $tWords . '%"' . ' OR verses.verseText LIKE "% ' . $tWords . '%")';
    }
  }else{
    // if (strpos($tWords, ' ') > 0){ // spaces present - probably more than one word!
      $tWords = 'verses.verseText LIKE "%' . str_replace(' ', '% %', $tWords) . '%"';
    // }else {
    // }
  }

*/
  return $tBook . $tWords;
}
// ============================================================================

// ============================================================================
function procesSearchWord($atWords, $i, $iLen, $bPhrase){
// ============================================================================
  $tBook = '';
  $tWords = '';
  $atBookFound = findBibleBook($atWords, $i, $iLen);
  $i = $atBookFound[1];
  if (strlen($atBookFound[0]) > 0){ // first few words is a book
    $tBook .= 'books.bookName = "' . $atBookFound[0] . '"';
    if(is_numeric ($atWords[$i])){// is next word a chapter?
      $tBook .= ' AND verses.chapter = "' . $atBookFound[$i] . '"';
    }
  }else{
//    addStrongsWild($atWords, $i, $iLen)
  }
  $tWords = joinWords($atWords, $i, $iLen);
  echo '$tBook  NOW:' . $tBook;
  echo '$tWords NOW:' . $tWords;
  $tWords = $tWords . addStrongsWild($atWords, $i, $iLen, $bPhrase);

  return $tBook . $tWords;
}
// ============================================================================

// ============================================================================
function addStrongsWild($atWords, $i, $iLen, $bPhrase){
// ============================================================================
  global $atStrongs;
  $tWords = '';
  if($bPhrase){
    $tWild = '';
  } else {
    $tWild = '%';
  }
  for($i = $i;$i < $iLen; $i++) { // carry on from wherever we've got
//    $bFound = (array_search($atWords[$i], $atStrongs));
    $bFound = (array_search(strtolower($atWords[$i]), array_map('strtolower', $atStrongs)));
    if ($bFound){
      $tWords = $tWords . $atWords[$i] . '<____>' . $tWild . ' ';
    } else {
      $tWords = $tWords . $atWords[$i] . '' . $tWild . ' ';
    }
  }
  echo '$tWords:' . $tWords;
  return $tWords;
}
// ============================================================================

?>
