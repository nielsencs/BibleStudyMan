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
  $tReturn = '<a href="bible?book=' . htmlspecialchars($tBookName, ENT_QUOTES, 'UTF-8'); // we might be in plan.php and we want to look up a bible passage!
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
    if(strtoupper((string)$row['bookName']) === strtoupper((string)$tBook)){
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
    // $tOutput .= '<option value="' . $row['bookName'] . '">';
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
  // $tBaseQuery .= 'REPLACE(REPLACE(verses.verseText, "[H430]", ""), "[H3068]", "") AS vt';
  // $tBaseQuery .= 'REPLACE(REPLACE(verses.verseText, "<H430>", ""), "<H3068>", "") AS vt';
  $tBaseQuery .= 'verses.verseText AS vt';
  $tBaseQuery .= ', books.bookName ';
  $tBaseQuery .= 'FROM verses INNER JOIN books ON verses.bookCode=books.bookCode ';

  return [$tBaseQuery, []];
}

// ============================================================================
function bookNameOrPsalm($tBookName, $iChapter, $bShowLinks, $bHighlightSW, $bShowOW, $bShowTN, $bPluralChapter = false){
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
function passage($tBook, $tChapter, $tVerses, $tWords, $bExact, 
                  $bHighlightSW, $bShowOW, $bShowTN, $bFloaty){
// ============================================================================
  global $pdo;
  $bProcessRequest = (strlen($tBook . $tChapter . $tVerses . $tWords) > 0);

  $tOutput = '';
  list($tBaseQuery, $params) = basicPassageQuery();

  $highlightWords = [];
  $highlightIsExact = false;

  if ($bProcessRequest) {
    if (empty($tBook)) {
      if (empty($tWords)) {
        $tOutput .=  '<h2>I&apos;m sorry I don&apos;t understand what you want - this is the beginning of The Bible:</h2>';
        $tQuery = $tBaseQuery . ' WHERE books.bookName = ? AND verses.chapter=1 AND verses.verseNumber<10;';
        $params[] = "Genesis";
      }else{
        $searchStrategy = get_search_strategy($tWords, $bExact);
        $tQuery = $tBaseQuery . ' WHERE ' . $searchStrategy['sql_where'] . ';';
        $params = $searchStrategy['sql_params'];
        $highlightWords = $searchStrategy['highlight_words'];
        $highlightIsExact = $searchStrategy['highlight_is_exact'];
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
            $searchStrategy = get_search_strategy($tWords, $bExact);
            $tQuery .= ' AND ' . $searchStrategy['sql_where'];
            $params = array_merge($params, $searchStrategy['sql_params']);
            $highlightWords = $searchStrategy['highlight_words'];
            $highlightIsExact = $searchStrategy['highlight_is_exact'];
        }
      }else{
        // ---- NOT searching down to verse level - keep commented in case I change my mind!
        // if (empty($tVerses))
        // {
          $tQuery .= ' AND verses.chapter = ?';
          $params[] = $tChapter;
        // }else{
        //     $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '" AND verses.chapter=' . $tChapter . ' AND verses.verseNumber=' . $tVerse . ';';
        // }
        // ---- NOT searching down to verse level - keep commented in case I change my mind!

        // ---- NOT searching words if chapter - highlight instead - keep commented in case I change my mind!
        // if (empty($tWords)) {
          // $tQuery = $tQuery . ';';
        // }else{
          // $tQuery = $tQuery . ' AND ' . addSQLWildcards($tWords, $bExact) . ';';
        // }
        // ---- NOT searching words if chapter - highlight instead - keep commented in case I change my mind!
      }
    }
    $tOutput .= showVerses($tQuery, $params, $tVerses, $bHighlightSW, $bShowOW, $bShowTN, $highlightWords, $highlightIsExact);
  }else{
    $tQuery = $tBaseQuery . ' INNER JOIN (
    SELECT bookCode, chapter, verseStart, verseEnd 
    FROM (
        SELECT "Genesis" as book, 1 as chapter, 1 as verseStart, 5 as verseEnd
        UNION SELECT "Psalms", 23, 1, 6
        UNION SELECT "Isaiah", 40, 28, 31
        UNION SELECT "Jeremiah", 29, 11, 12
        UNION SELECT "Matthew", 11, 28, 30
        UNION SELECT "John", 3, 16, 17
        UNION SELECT "Philippians", 4, 6, 7
        UNION SELECT "Romans", 8, 28, 31
        ORDER BY RAND()
        LIMIT 1
    ) encouragingVerses 
    INNER JOIN books ON books.bookName = encouragingVerses.book
) AS selected_passage ON books.bookCode = selected_passage.bookCode 
WHERE verses.chapter = selected_passage.chapter 
AND verses.verseNumber BETWEEN selected_passage.verseStart AND selected_passage.verseEnd;';

      $tOutput = '<h2>You can search for words, or a phrase, or pick a book in the box';
      if($bFloaty){
        $tOutput .= ' to the left';
      }else{
        $tOutput .= ' above';
      }
      $tOutput .= '. While your deciding what to lookup, here&apos;s a sample:</h2>';
      $tOutput .=  showVerses($tQuery, [], $tVerses, $bHighlightSW, $bShowOW, $bShowTN, [], false);
  }
  return $tOutput;
}

// ============================================================================
function showVerses($tQuery, $params, $tVerses, $bHighlightSW, $bShowOW, $bShowTN, $highlightWords = [], $highlightIsExact = false){
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
      // either chapter/book heading or just verse(s)
      if($tLastBookName != $row['bookName'] || $iLastChapter != $row['chapter']){
        $tOutput .=  PHP_EOL . '<h3>';
        $tOutput .=  bookNameOrPsalm($row['bookName'], $row['chapter'], true, $bHighlightSW, $bShowOW, $bShowTN, $bPluralChapter = false);
        $tOutput .=  '</h3>' . PHP_EOL;
      }
      $tOutput .= showVerse($tVerses, $row, $highlightWords, $highlightIsExact);
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
function showVerse($tVerses, $row, $highlightWords = [], $highlightIsExact = false){
// ============================================================================
  global $bHighlightSW, $bShowOW, $bShowTN;
  $tVersesExpanded = '@' . expandVerseList($tVerses);
  $tThisVerse = ',' . $row['verseNumber'] . ',';
  $tThisVerseText = $row['vt'];

  $bVerseSearched = (strpos($tVersesExpanded, $tThisVerse));
  $tOutput = '';

  if(strtolower(substr($tThisVerseText, 0, 3)) == '<p>'){ // if this verse starts a paragraph
    $tOutput .=  '<p>';
    $tThisVerseText = substr($tThisVerseText, 3);
    // place it before the verse number
  }
  
  if ($bVerseSearched){ //if verse searched for highlight the whole verse
    $tOutput .=  '<span class="highlightVerse">';
  }

  $tOutput .=  doVerseNumber($row['verseNumber']);
  $tOutput .=  highlightSearch(processStrongs($tThisVerseText, $bHighlightSW, $bShowOW, $bShowTN), $highlightWords, $highlightIsExact) . ' ';

  if ($bVerseSearched){ //if verse searched for highlight the whole verse
    $bEndPara = strtolower(substr($tThisVerseText, -4)) == '</p>'; // if this verse ends a paragraph
    if($bEndPara){
      $tThisVerseText = substr($tThisVerseText, 0, -4);
      // place it after any highlighting
    }
    $tOutput .=  '</span>';
    if($bEndPara){
      $tOutput .=  '</p>';
      // place it after any highlighting
    }
  }

  return $tOutput;
}

// ============================================================================
function expandVerseList($tVerses){
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
    // This function is complex because it modifies a string based on finding
    // markers, but must be careful not to break HTML that might already be
    // in the string. This new version processes the string linearly to avoid
    // the bugs in the original implementation, and uses a regex to correctly
    // identify the word before the tag, as per user guidance.

    $finalOutput = '';
    $lastPos = 0;

    // The regex finds a word (alphanumeric + apostrophe) followed by a Strong's tag like {H1234}
    while (preg_match('/([a-zA-Z\-]+)\{([HG]\d+)\}/', $tValue, $matches, PREG_OFFSET_CAPTURE, $lastPos)) {
        
        $fullMatchInfo = $matches[0];
        $wordInfo = $matches[1];
        $strongsNoInfo = $matches[2];

        $matchStartPosition = $fullMatchInfo[1];
        $word = $wordInfo[0];
        $strongsNo = $strongsNoInfo[0];

        // 1. Append the text between the last match and this current one.
        $finalOutput .= substr($tValue, $lastPos, $matchStartPosition - $lastPos);

        // 2. Process the matched word and Strong's number.
        $strongsData = strongs($strongsNo);
        if (!isset($strongsData[0]) || !isset($strongsData[1])) {
            // If Strong's data is missing, just append the original matched word and tag to be safe.
            $finalOutput .= $fullMatchInfo[0];
        } else {
            $tWord1 = $word;
            $tWord2 = $strongsData[1];
    
            if ($strongsData[0] > 0 && !$bShowTN) { // is a name and don't show translated
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

        // 3. Update the last position to search from after this match.
        $lastPos = $matchStartPosition + strlen($fullMatchInfo[0]);
    }

    // 4. Append any remaining part of the string after the last match.
    $finalOutput .= substr($tValue, $lastPos);

    return $finalOutput;
}

// ============================================================================
function highlightSearch($tValue, $highlightWords = [], $highlightIsExact = false){
// ============================================================================
    if (!empty($highlightWords)) {
        $tValue = highlightWords($highlightWords, $tValue, $highlightIsExact);
    }
    return $tValue;
}

// ============================================================================
function highlightWords(array $words, string $haystack, bool $exactMatch = false): string {
// ============================================================================
    $words = array_unique(array_map(fn($input) => strtolower(trim($input)), $words));
    $words = array_filter($words);

    if (empty($words)) {
        return $haystack;
    }

    // Conditionally add word boundaries for exact matching
    $boundary = $exactMatch ? '\\b' : '';

    // The pattern for the words to highlight
    $highlightPattern = '/' . $boundary . '(' . implode('|', array_map('preg_quote', $words)) . ')' . $boundary . '/i';
    $replacementCallback = fn($match) => "<span class=\"highlightWord\">{$match[0]}</span>";

    // Split the haystack into text nodes and HTML tags
    $parts = preg_split('/(<[^>]*>)/', $haystack, -1, PREG_SPLIT_DELIM_CAPTURE);

    $newHaystack = '';
    foreach ($parts as $part) {
        // If the part is not an HTML tag, run the highlighting on it
        if (isset($part[0]) && $part[0] !== '<') {
            $newHaystack .= preg_replace_callback($highlightPattern, $replacementCallback, $part);
        } else {
            // Otherwise, it's a tag, so append it without changes
            $newHaystack .= $part;
        }
    }

    return $newHaystack;
}

// ============================================================================
function get_search_strategy(string $searchTerms, bool $isExact): array {
// ============================================================================
    $words = array_values(array_filter(explode(' ', trim($searchTerms))));
    $sqlWhereClause = '';
    $sqlParams = [];
    $highlightWords = [];
    $highlightIsExact = false;

    if ($isExact) {
        if (count($words) === 1) {
            // Use MySQL's word boundary regex for a single exact word.
            $sqlWhereClause = 'verses.verseText REGEXP ?';
            $sqlParams[] = '[[:<:]]' . $words[0] . '[[:>:]]';
        } else {
            // For an exact phrase, search for the whole string.
            $sqlWhereClause = 'verses.verseText LIKE ?';
            $sqlParams[] = '%' . $searchTerms . '%';
        }
        $highlightWords = $words;
        $highlightIsExact = true;

    } else { // Not an exact search
        if (count($words) > 0) {
            $conditions = [];
            foreach ($words as $word) {
                $conditions[] = 'verses.verseText LIKE ?';
                $sqlParams[] = '%' . $word . '%';
            }
            $sqlWhereClause = implode(' AND ', $conditions);
        }
        $highlightWords = $words;
        $highlightIsExact = false;
    }

    return [
        'sql_where' => $sqlWhereClause,
        'sql_params' => $sqlParams,
        'highlight_words' => $highlightWords,
        'highlight_is_exact' => $highlightIsExact,
    ];
}

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

//  $tWidth = '320';
//  $tHeight = '180';
  $tWidth = '325';  // almost the same as above but better thumbnails!
  $tHeight = '183'; // almost the same as above but better thumbnails!

  $stmt = doQuery($pdo, $tQuery, $params);

  if ($stmt->rowCount() == 0) {
    $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . htmlspecialchars($tQuery, ENT_QUOTES, 'UTF-8') . '"';
  } else {
    while ($row = $stmt->fetch()) {
      $tOutput .= '<div class="media">';
      if(! empty($row["audioURL"])){
        $tOutput .= '<p class="centerText">' . htmlspecialchars($row["mediaName"], ENT_QUOTES, 'UTF-8') . '</p>';
        $tOutput .= '<br />';
//        $tOutput .= ' <a href="https://soundcloud.com/user-442938965/';
//        $tOutput .= $row["audioURL"];
//        $tOutput .= '" target="_blank">Play on SoundCloud.com';
//        $tOutput .= '</a> ';
//        $tOutput .= '<br />';

//        $tOutput .= '<iframe width="100%" height="166" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/';
        $tOutput .= '<iframe width="100%" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/';
//        $tOutput .= '<iframe scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/';
        $tOutput .= htmlspecialchars($row["audioTrack"], ENT_QUOTES, 'UTF-8');
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
        $tOutput .= htmlspecialchars($row["videoURL"], ENT_QUOTES, 'UTF-8');
//        $tOutput .= '?controls=1&modestbranding=0"';
        $tOutput .= '?rel=0" frameborder="1" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
      }
      $tOutput .= '</div>';
    }
  }
  return $tOutput;
}
?>
