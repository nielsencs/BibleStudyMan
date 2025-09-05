<?php
// ============================================================================
function doQuery($pdo, $tQuery, $params = []){
// ============================================================================
  echo '<!-- ' . $tQuery . ' -->';
  $stmt = $pdo->prepare($tQuery);
  $stmt->execute($params);
  return $stmt;
}

// ============================================================================
function buildLink($tBookName, $iChapter, $tWords, $bExact, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
  $tReturn = '<a href="bible?book=' . htmlspecialchars($tBookName, ENT_QUOTES, 'UTF-8');
  if($iChapter > 0){
    $tReturn .= '&chapter=' . $iChapter;
  }
  $tReturn .= '&words=' . str_replace(' ', '+', htmlspecialchars($tWords, ENT_QUOTES, 'UTF-8'));
  if($bExact){
    $tReturn .= '&exact=on';
  }
  if ($bHighlightSW){
    $tReturn .= '&highlightSW=on';
  }
  if ($bShowOW){
    $tReturn .= '&showOW=on';
  }
  if ($bShowTN){
    $tReturn .= '&showTN=on';
  }
  $tReturn .= '">';
  return $tReturn;
}

// ============================================================================
function prepareBookAbbs(){
// ============================================================================
  global $pdo;
  $atBookAbbs = array();

  $tQuery = 'SELECT baBookAbbreviation, bookName FROM `book-abbreviations` INNER JOIN books ON baBookCode=books.bookCode;';
  $stmt = doQuery($pdo, $tQuery);

  while($row = $stmt->fetch()) {
    $atBookAbbs += [$row['baBookAbbreviation'] => $row['bookName']];
  }

  return $atBookAbbs;
}

// ============================================================================
function prepareBookList(){
// ============================================================================
  global $pdo, $tBook, $iBook;
  $tOutput = '';

  $tQuery = 'SELECT bookName, bookChapters, orderChristian FROM books ORDER BY orderChristian;';
  $stmt = doQuery($pdo, $tQuery);

  $tOutput .= '<script type="text/javascript">';
  $tOutput .=  'var atBooks = [["Start from 1!",';
  $tOutput .=  '0]';

  while($row = $stmt->fetch()) {
    $tOutput .=  ', ["' . htmlspecialchars($row['bookName'], ENT_QUOTES, 'UTF-8') . '", ';
    $tOutput .=  $row['bookChapters'] . ']';
    if(strtoupper($row['bookName']) === strtoupper($tBook)){
      $iBook = $row['orderChristian'];
    }
  }
  $tOutput .=  '];';
  $tOutput .=  'var iBook=' . $iBook . ';';
  $tOutput .=  '</script>';

  return $tOutput;
}

// ============================================================================
function prepareDropdownBookList(){
// ============================================================================
  global $pdo, $tBook;
  $tOutput = '';

  $tQuery = <<<SQL
    SELECT
      `bookName`
    FROM
      `books`
    ORDER BY
      TRIM(LEADING ' ' FROM REGEXP_REPLACE(bookName, '^[0-9 &]+ ', '')),
      CAST(SUBSTRING_INDEX(bookName, ' ', 1) AS UNSIGNED)
    SQL;
  $stmt = doQuery($pdo, $tQuery);

  while($row = $stmt->fetch()) {
    $tOutput .= '<option value="' . htmlspecialchars($row['bookName'], ENT_QUOTES, 'UTF-8') . '"';
     if ($row['bookName'] === $tBook) {
       $tOutput .= ' selected';
     }
    $tOutput .= '>' . htmlspecialchars($row['bookName'], ENT_QUOTES, 'UTF-8') . '</option>';
  }
  return $tOutput;
}

// ============================================================================
function prepareDropdownChapterList(){
// ============================================================================
  global $pdo, $tBook, $tChapter;
  $tOutput = '';

  $tQuery = 'SELECT bookName,bookChapters FROM books WHERE bookName = ?;';
  $stmt = doQuery($pdo, $tQuery, [$tBook]);

  if ($stmt->rowCount() === 1) {
    while($row = $stmt->fetch()) {
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
  return $tOutput;
}

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
function prepareStrongs(){
// ============================================================================
  global $pdo;
  $atStrongs = array();

  $tQuery = 'SELECT strongsNumber, strongsIsName, strongsOriginal, strongsEnglish FROM strongs;';
  $stmt = doQuery($pdo, $tQuery);

  while($row = $stmt->fetch()) {
    $atStrongs += [$row['strongsNumber'] => [$row['strongsIsName'],$row['strongsOriginal']]];
  }

  return $atStrongs;
}

// ============================================================================
function strongs($tStrongsNo){
// ============================================================================
  global $atStrongs;
  return $atStrongs[$tStrongsNo];
}

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
function monthName($iMonth){// return text month from numeric
// ============================================================================
  $atMonths = array("Oops", "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December");
  return $atMonths[$iMonth];
}

// ============================================================================
function basicPassageQuery(){
// ============================================================================
  $tBaseQuery = '';
  $tBaseQuery .= 'SELECT DISTINCT books.bookName, verses.chapter, verses.verseNumber, ';
  $tBaseQuery .= 'verses.verseText AS vt';
  $tBaseQuery .= ', books.bookName ';
  $tBaseQuery .= 'FROM verses INNER JOIN books ON verses.bookCode=books.bookCode ';

  return $tBaseQuery;
}

// ============================================================================
function bookNameOrPsalm($tBookName, $iChapter, $bShowLinks, $bPluralChapter = false, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
  global $tWords, $bExact;

  $tOutput = '';
  $iBookChapters = 2; // not important to get actual chapters in book unless only 1
  if(in_array($tBookName, array('Obadiah', 'Philemon', '2 John', '3 John', 'Jude')) ) {
    $iBookChapters = 1; // if only one chapter in book don't say 'chapter'!
  }

  if($bShowLinks){ // link to book only
    $tOutput .= buildLink($tBookName, 0, $tWords, $bExact, $bHighlightSW, $bShowOW, $bShowTN);
  }
  if($tBookName === 'Psalms'){
    $tOutput .= 'Psalm';
  }else {
    $tOutput .= htmlspecialchars($tBookName, ENT_QUOTES, 'UTF-8');
  }
  if($bShowLinks){
    $tOutput .= '</a>';
  }
  if ($iBookChapters > 1){
    $tOutput .= ' ';
    if($bShowLinks){ // link to book and chapter
      $tOutput .= buildLink($tBookName, $iChapter, $tWords, $bExact, $bHighlightSW, $bShowOW, $bShowTN);
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
function passage($tBook, $tChapter, $tVerses, $tWords, $bExact, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
  global $pdo;
  $bProcessRequest = (strlen($tBook . $tChapter . $tVerses . $tWords) > 0);

  $tOutput = '';
  $tBaseQuery = basicPassageQuery();
  $params = [];

  if ($bProcessRequest) {
    if (empty($tBook)) {
      if (empty($tWords)) {
        $tOutput .=  '<h2>I&apos;m sorry I don&apos;t understand what you want - this is the beginning of The Bible:</h2>';
        $tQuery = $tBaseQuery . ' WHERE books.bookName = ? AND verses.chapter=1 AND verses.verseNumber<10;';
        $params[] = "Genesis";
      }else{
        list($whereClause, $params) = addSQLWildcards($tWords, $bExact);
        $tQuery = $tBaseQuery . ' WHERE ' . $whereClause . ';';
      }
    } else {
      if ($tBook === '2 & 3 John') {
        $tQuery = $tBaseQuery . ' WHERE books.bookName = ? OR books.bookName = ?';
        $params[] = "2 John";
        $params[] = "3 John";
      } else {
        $tQuery = $tBaseQuery . ' WHERE books.bookName = ?';
        $params[] = $tBook;
      }
      if (empty($tChapter))
      {
        if (!empty($tWords)) {
            list($whereClause, $wordParams) = addSQLWildcards($tWords, $bExact);
            $tQuery .= ' AND ' . $whereClause;
            $params = array_merge($params, $wordParams);
        }
      }else{
          $tQuery .= ' AND verses.chapter = ?';
          $params[] = $tChapter;
      }
    }
    $tOutput .= showVerses($tQuery, $params, $tVerses, $bHighlightSW, $bShowOW, $bShowTN);
  }else{
    $tQuery = $tBaseQuery . ' WHERE books.bookName = ? AND verses.chapter=1;';
    $params[] = "Genesis";
    $tOutput = '<h2>You can search for words, or a phrase, or pick a book in the box above. While you&apos;re deciding what to lookup, here&apos;s a sample:</h2>' . showVerses($tQuery, $params, $tVerses, $bHighlightSW, $bShowOW, $bShowTN);
  }
  return $tOutput;
}

// ============================================================================
function showVerses($tQuery, $params, $tVerses, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
  global $pdo;

  $tOutput = '';
  $tLastBookName = '';
  $iLastChapter = 0;
  
  $stmt = doQuery($pdo, $tQuery, $params);
  $rows = $stmt->fetchAll();
  $iRows = count($rows);
  $iBooks = countBooks($rows);

  $tOutput .=  '<div class="bibleText';
  if ($iBooks > 1 || $iRows < 7){
    $tOutput .=  ' spanAll';
  }
  $tOutput .=  '">';

  if ($iRows == 0) {
    $tOutput .=  'It could be me... but I can&apos;t seem to find that!';
  } else {
    $tOutput .= $iRows . ' verses<br />';
    foreach($rows as $row) {
      if($tLastBookName != $row['bookName'] || $iLastChapter != $row['chapter']){
        $tOutput .=  PHP_EOL . '<h3>';
        $tOutput .=  bookNameOrPsalm($row['bookName'], $row['chapter'], true, $bPluralChapter = false, $bHighlightSW, $bShowOW, $bShowTN);
        $tOutput .=  '</h3>' . PHP_EOL;
      }
      $tOutput .= showVerse($tVerses, $row);
      $tLastBookName = $row['bookName'];
      $iLastChapter = $row['chapter'];
    }
  }

  $tOutput .=  '</div>' . PHP_EOL;
  return $tOutput;
}

// ============================================================================
function countBooks($rows){
// ============================================================================
  $iBooks = 0;
  $tLastBookName = '';
  foreach ($rows as $row) {
    if ($row['bookName'] != $tLastBookName){
      $iBooks++;
      $tLastBookName = $row['bookName'];
    }
  }
  return $iBooks;
}

// ============================================================================
function showVerse($tVerses, $row){
// ============================================================================
  global $bHighlightSW, $bShowOW, $bShowTN;
  $tVersesExpanded = '@' . expandVerseList($tVerses);
  $tThisVerse = ',' . $row['verseNumber'] . ',';
  $tThisVerseText = $row['vt'];

  $bVerseSearched = (strpos($tVersesExpanded, $tThisVerse));
  $tOutput = '';

  if(strtolower(substr($tThisVerseText, 0, 3)) == '<p>'){
    $tOutput .=  '<p>';
    $tThisVerseText = substr($tThisVerseText, 3);
  }
  
  if ($bVerseSearched){
    $tOutput .=  '<span class="highlightVerse">';
  }

  $tOutput .=  doVerseNumber($row['verseNumber']);
  $tOutput .=  highlightSearch(processStrongs($tThisVerseText, $bHighlightSW, $bShowOW, $bShowTN)) . ' ';

  if ($bVerseSearched){
    $bEndPara = strtolower(substr($tThisVerseText, -4)) == '</p>';
    if($bEndPara){
      $tThisVerseText = substr($tThisVerseText, 0, -4);
    }
    $tOutput .=  '</span>';
    if($bEndPara){
      $tOutput .=  '</p>';
    }
  }

  return $tOutput;
}

// ============================================================================
function expandVerseList($tVerses){
// ============================================================================
  $tVersesExpanded = '';
  $tVerses .= '@';
  $iLen = strlen($tVerses);
  $iDash = 0;
  $tLastChar = '';
  $tLastNum = '';
  $tThisNum = '';
  for ($i = 0; $i <= $iLen; $i++) {
    $tThisChar = substr($tVerses, $i, 1);
    if(is_numeric($tThisChar)){
      $tThisNum .= $tThisChar;
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
function doVerseNumber($iVerseNumber){
// ============================================================================
  $tOutput =  '';
  if ($iVerseNumber > 0) {
    $tOutput .= '<sup>' . $iVerseNumber . '</sup>';
  }
  return $tOutput;
}

// ============================================================================
function isSentence($text){
// ============================================================================
  $tLastChar = substr($text, -1);
  return (strpos('@.!?', $tLastChar)>0);
}

// ============================================================================
function processStrongs($tValue, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
    $finalOutput = '';
    $lastPos = 0;

    while (preg_match('/([a-zA-Z0-9\']+)\{([HG]\d+)\}/', $tValue, $matches, PREG_OFFSET_CAPTURE, $lastPos)) {
        
        $fullMatchInfo = $matches[0];
        $wordInfo = $matches[1];
        $strongsNoInfo = $matches[2];

        $matchStartPosition = $fullMatchInfo[1];
        $word = $wordInfo[0];
        $strongsNo = $strongsNoInfo[0];

        $finalOutput .= substr($tValue, $lastPos, $matchStartPosition - $lastPos);

        $strongsData = strongs($strongsNo);
        if (!isset($strongsData[0]) || !isset($strongsData[1])) {
            $finalOutput .= $fullMatchInfo[0];
        } else {
            $tWord1 = $word;
            $tWord2 = $strongsData[1];
    
            if ($strongsData[0] > 0 && !$bShowTN) {
                $tWord1_orig = $tWord1;
                $tWord1 = $tWord2;
                $tWord2 = $tWord1_orig;
            }
    
            $processedWord = '';
            if ($bHighlightSW) {
                $processedWord .= '<span class="highlightOW">';
            }
            $processedWord .= htmlspecialchars($tWord1, ENT_QUOTES, 'UTF-8');
            if ($bShowOW) {
                $processedWord .= ' <sub>(' . htmlspecialchars($tWord2, ENT_QUOTES, 'UTF-8') . ')</sub>';
            }
            if ($bHighlightSW) {
                $processedWord .= '</span>';
            }
            $finalOutput .= $processedWord;
        }

        $lastPos = $matchStartPosition + strlen($fullMatchInfo[0]);
    }

    $finalOutput .= substr($tValue, $lastPos);

    return $finalOutput;
}

// ============================================================================
function highlightSearch($tValue){
// ============================================================================
    global $tWords, $bExact;
    
  if ($tWords > ''){
    if ($bExact){
      $tValue = highlight($tWords, $tValue);
    }else {
      $atSearch = explode (' ', $tWords);
      $tValue = highlightWords($atSearch, $tValue);
    }
  }
  return '<!-- highlightSearch ' . htmlspecialchars($tWords, ENT_QUOTES, 'UTF-8') . ' -->' . $tValue;
    }

// ============================================================================
function highlight($needle, $haystack){
// ============================================================================
  $words = explode(' ', $needle);
  return highlightWords($words, $haystack);
}

// ============================================================================
function highlightWords(array $words, string $haystack): string {
// ============================================================================
    $words = array_unique(array_map(fn($input) => strtolower(trim($input)), $words));
    $words = array_filter($words);

        if (empty($words)) {
        return $haystack;
    }

    $pattern = '/\b(' . implode('|', array_map('preg_quote', $words)) . ')\b/i';
    return preg_replace_callback(
        $pattern,
        fn($match) => "<span class=\"highlightWord\">{$match[0]}</span>",
        $haystack
    );
}

// ============================================================================
function addSQLWildcards($tWords, $bExact){
// ============================================================================
  return procesSearchWords($tWords, $bExact);
}

// ============================================================================
function procesSearchWords($tWords, $bExact){
// ============================================================================
  $atWords = explode(' ', $tWords);
  $iLen = count($atWords);
  $tNewWords = '';
  $params = [];

  if($bExact){
    if ($iLen === 1){
      $tNewWords = 'verses.verseText REGEXP ?';
      $params[] = $tWords . '{1}[ \.\,\:\;]';
    }else {
      $tNewWords .= 'verses.verseText LIKE ?';
      $params[] = '%' . $tWords . '%';
    }
  }else {
    if ($iLen === 1){
      $tNewWords .= 'verses.verseText LIKE ?';
      $params[] = '%' . $tWords . '%';
    } else {
      $conditions = [];
      foreach ($atWords as $tWord) {
        $conditions[] = 'verses.verseText LIKE ?';
        $params[] = '%' . $tWord . '%';
      }
      $tNewWords = implode(' AND ', $conditions);
    }
  }
  return [$tNewWords, $params];
}

// ============================================================================
function joinWords($atWords, $i, $iLen){
// ============================================================================
  $tWords = '';
  for ($j=$i;$j < $iLen; $j++){
    $tWords .= $atWords[$j] . ' ';
  }
  return  trim($tWords);
}

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
  for($i = $i;$i < $iLen; $i++) {
    $bFound = (array_search(strtolower($atWords[$i]), array_map('strtolower', $atStrongs)));
    if ($bFound){
      $tWords = $tWords . $atWords[$i] . '<____>' . $tWild . ' ';
    } else {
      $tWords = $tWords . $atWords[$i] . '' . $tWild . ' ';
    }
  }
  echo '$tWords:' . htmlspecialchars($tWords, ENT_QUOTES, 'UTF-8');
  return $tWords;
}

// ============================================================================
function videoList($tClass = 'R'){
// ============================================================================
  global $pdo;
  $tOutput = '';
  $tQuery = 'SELECT * FROM media WHERE media.mediaClass = ?;';
  $params = [$tClass];

  $tWidth = '325';
  $tHeight = '183';

  $stmt = doQuery($pdo, $tQuery, $params);

  if ($stmt->rowCount() == 0) {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . htmlspecialchars($tQuery, ENT_QUOTES, 'UTF-8') . '"';
  } else {
    while ($row = $stmt->fetch()) {
      $tOutput .= '<div class="media">';
      if(! empty($row["audioURL"])){
        $tOutput .= '<p class="centerText">' . htmlspecialchars($row["mediaName"], ENT_QUOTES, 'UTF-8') . '</p>';
        $tOutput .= '<br />';
        $tOutput .= '<iframe width="100%" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/';
        $tOutput .= htmlspecialchars($row["audioTrack"], ENT_QUOTES, 'UTF-8');
        $tOutput .= '&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true"></iframe>';
        $tOutput .= '<br />';
      }

      if(! empty($row["videoURL"])){
        $tOutput .= '<iframe width = "' . $tWidth . '" height = "' . $tHeight . '" src="https://www.youtube.com/embed/';
        $tOutput .= htmlspecialchars($row["videoURL"], ENT_QUOTES, 'UTF-8');
        $tOutput .= '?rel=0" frameborder="1" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
      }
      $tOutput .= '</div>';
    }
  }
  return $tOutput;
}
?>
