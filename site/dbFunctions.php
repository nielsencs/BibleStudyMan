<?php
// ============================================================================
function doQuery($link, $tQuery){
// ============================================================================
  echo '<!-- ' . $tQuery . ' -->';
  return mysqli_query($link, $tQuery);
}
// ============================================================================

// ============================================================================
function buildLink($tBookName, $iChapter, $tWords, $bExact){
// ============================================================================
  $tReturn = '<a href="bible.php?book=' . $tBookName; // we might be in plan.php and we want to look up a bible passage!
  if($iChapter > 0){
    $tReturn .= '&chapter=' . $iChapter;
  }
  $tReturn .= '&words=' . str_replace(' ', '+', $tWords);
  if($bExact){
    $tReturn .= '&exact=on';
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

  $tQuery = 'SELECT baBookAbbreviation, bookName FROM `book-abbreviations` INNER JOIN books ON baBookCode=books.bookCode;';
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
function bookNameOrPsalm($tBookName, $iChapter, $bShowLinks, $bPluralChapter = false){
// ============================================================================
  global $tWords, $bExact;

  $tOutput = '';
  $iBookChapters = 2; // not important to get actual chapters in book unless only 1
  // if(strpos('Obadiah|Philemon|2 John|3 John|Jude', $tBookName)>0){
    // if($tBookName != 'John'){
  if(in_array($tBookName, array('Obadiah', 'Philemon', '2 John', '3 John', 'Jude')) ) {
    $iBookChapters = 1; // if only one chapter in book don't say 'chapter'!
  }

  if($bShowLinks){ // link to book only
    $tOutput .= buildLink($tBookName, 0, $tWords, $bExact);
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
      $tOutput .= buildLink($tBookName, $iChapter, $tWords, $bExact);
    }
    if ($iChapter > -1){
    if($tBookName != 'Psalms'){
      if($bPluralChapter){
        $tOutput .= 'Chapters ';
      }else{
        $tOutput .= 'Chapter ';
      }
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
function passage($tBook, $tChapter, $tVerses, $tWords, $bExact){
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
        $tQuery = $tBaseQuery . ' WHERE ' . addSQLWildcards($tWords, $bExact) . ';';
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
          $tQuery = $tQuery . ' AND  ' . addSQLWildcards($tWords, $bExact) . ';';
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
          // $tQuery = $tQuery . ' AND ' . addSQLWildcards($tWords, $bExact) . ';';
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
    $tVersesExpanded = expandVerses($tVerses);
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

      if (strpos('@' . $tVersesExpanded, ',' . $row['verseNumber'] . ',')){
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
function expandVerses($tVerses){
// ============================================================================
// turn mixed dash and comma search into commas only with a leading comma
// to ensure 0 is never first position. For example:
    
// From: '3,5,7-11,19-21,25,28-30,33'
// To:  ',3,5,7,8,9,10,11,19,20,21,25,28,29,30,33'   
// ----------------------------------------------------------------------------
  $tVersesExpanded = '';
  $tVerses .= '@';
  $iLen = strlen($tVerses);
  $iDash = 0;
  $tLastChar = '';
  $tLastNum = '';
  $tThisNum = '';
//  $iCommaPosition = strpos($tVerses, ',');
  for ($i = 0; $i <= $iLen; $i++) {
    $tThisChar = substr($tVerses, $i, 1);
    if(is_numeric($tThisChar)){
      $tThisNum .= $tThisChar;
//      $tVersesExpanded .= $tThisChar;
      $tLastChar = $tThisChar;
    }else{
      if($iDash > 0){
        for ($j = $tLastNum+1; $j <= $tThisNum; $j++){
          $tVersesExpanded .= ',' . $j;
        }
        $iDash = 0;
      }else{
        $tVersesExpanded .= ',' . $tThisNum;
      }
      $tLastNum = $tThisNum;
      $tThisNum = '';
      if($tThisChar === '-'){
        $iDash = $i;
      }
      $tLastChar = $tThisChar;
    }
  }
  return $tVersesExpanded;
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
  global $tWords, $bExact;

  if ($tWords > ''){
//    if (! $bExact){
    if ($bExact){
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
function addSQLWildcards($tWords, $bExact){
// ============================================================================
return procesSearchWords($tWords, $bExact);

  if($bExact){ // 'Exact' was 'checked' regardless of number of words
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
function procesSearchWords($tWords, $bExact){
// ============================================================================
  if($bExact){ // 'Exact' was 'checked' regardless of number of words
    $tWords = 'verses.verseText REGEXP "' . $tWords . '{1}[ \.\,\:\;]"';
  }else {
    $tWords = 'verses.verseText LIKE "%' . str_replace(' ', '% %', $tWords) . '%"';
  }
  return $tWords;
}
// ============================================================================

// ============================================================================
function procesSearchWords2($tWords, $bExact){
// ============================================================================
  $atWords = explode(' ', $tWords);
  $tWord = '';
  $iLen = count($atWords);

  if ($iLen === 1){
    //treat 1 word differently
  }





  $i = 0;

    for($j = $i;$j < $iLen; $j++) {
      $tWord = $atWords[$j];
      if ($j < $iLen){
      $tValue = procesSearchWord($atWords, $j, $iLen, $bExact);
      }else{
        $tValue = procesSearchWord($atWords, 0);
      }
    }
    if($bExact){
      $tWords = 'verses.verseText LIKE "%' . str_replace(' ', '%  %', $tWords) . '%"';
    }else{
      $tWords = '(verses.verseText LIKE "' . $tWords . '%"' . ' OR verses.verseText LIKE "% ' . $tWords . '%")';
//    }else{
      // if (strpos($tWords, ' ') > 0){ // spaces present - probably more than one word!
        $tWords = 'verses.verseText LIKE "%' . str_replace(' ', '% %', $tWords) . '%"';
      // }else {
    }

/*
  if($bExact){
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
  return $tWords;
}
// ============================================================================

// ============================================================================
function procesSearchWord($atWords, $i, $iLen, $bExact){
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
  $tWords = $tWords . addStrongsWild($atWords, $i, $iLen, $bExact);

  return $tBook . $tWords;
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
function addStrongsWild($atWords, $i, $iLen, $bExact){
// ============================================================================
  global $atStrongs;
  $tWords = '';
  if($bExact){
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

// ============================================================================
function videoList($tClass = 'R'){
// ============================================================================
  global $link;
  $tOutput = '';
  $tQuery = '';
  $tQuery .= 'SELECT * ';
  $tQuery .= 'FROM media ';
  $tQuery .= 'WHERE media.mediaClass = "' . $tClass . '";';

//  $tWidth = '320';
//  $tHeight = '180';
  $tWidth = '325';  // almost the same as above but better thumbnails!
  $tHeight = '183'; // almost the same as above but better thumbnails!

  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) == 0) {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
  } else {
    while ($row = mysqli_fetch_assoc($result)) {
      $tOutput .= '<div class="media">';
      if(! empty($row["audioURL"])){
        $tOutput .= '<p class="centerText">' . $row["mediaName"] . '</p>';
        $tOutput .= '<br />';
//        $tOutput .= ' <a href="https://soundcloud.com/user-442938965/';
//        $tOutput .= $row["audioURL"];
//        $tOutput .= '" target="_blank">Play on SoundCloud.com';
//        $tOutput .= '</a> ';
//        $tOutput .= '<br />';

//        $tOutput .= '<iframe width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/';
        $tOutput .= '<iframe width="100%" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/';
//        $tOutput .= '<iframe scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/';
        $tOutput .= $row["audioTrack"];
        $tOutput .= '&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe>';
        $tOutput .= '<br />';
      }

      if(! empty($row["videoURL"])){
//        $tOutput .= '<a class="centerText" href="https://youtu.be/';
//        $tOutput .= $row["videoURL"];
//        $tOutput .= '" target="_blank">Play on YouTube.com';
//        $tOutput .= '</a>';
//        $tOutput .= '<br />';

        $tOutput .= '<iframe width = "' . $tWidth . '" height = "' . $tHeight . '" src="https://www.youtube.com/embed/';
        $tOutput .= $row["videoURL"];
//        $tOutput .= '?controls=1&modestbranding=0"';
        $tOutput .= '?rel=0" frameborder="1" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
      }
      $tOutput .= '</div>';
    }
  }
  mysqli_free_result($result);
  return $tOutput;
}
// ============================================================================
?>
