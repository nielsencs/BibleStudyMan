<?php
  function doQuery($link, $tQuery){
  echo '<!-- ' . $tQuery . ' -->';
  return mysqli_query($link, $tQuery);
}

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
  function daysInMonth($month, $year){// calculate number of days in a month
    return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
  }

  function basicPassageQuery(){
    $tBaseQuery = '';
    $tBaseQuery .= 'SELECT DISTINCT books.bookName, verses.chapter, verses.verseNumber, ';
    $tBaseQuery .= 'REPLACE(REPLACE(verses.verseText, "[H430]", ""), "[H3068]", "") AS vt';
    $tBaseQuery .= ', books.bookName ';
    $tBaseQuery .= 'FROM verses INNER JOIN books ON verses.bookCode=books.bookCode ';

    return $tBaseQuery;
  }

  function buildPassageQuery($tBookCode, $tPassageStart, $tPassageEnd){
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
          // $tOutput .= 'The plan&rsquo;s readings for ' . $row['planDateFormatted'] . ' are';
        }
        //$tOutput .= "" . $row['sectionEnglish']  . ' (' . $row['sectionName'] . ') - ';
        if($row['sectionCode'] == '2NV1'){
            // $tOutput .= '; next it&rsquo;s ';
            $tOutput .= '. Follow that by reading';
        }
        if($row['sectionCode'] == '3KTV'){
            $tOutput .= '. The third chunk is';
        }
        if($row['sectionCode'] == '5NCV'){
            $tOutput .= '; and lastly';
        }
        $tOutput .= ' <strong>';
  // -------------- if whole chapter no from... to ------------
        if($row['passageEnd'] > ''){
          $tOutput .= 'from';
        }
        $tOutput .= ' ' . bookNameOrPsalm($row['bookName'], 0, false);

        $tOutput .= ' ' . $row['passageStart'];
        if($row['passageEnd'] > ''){
          $tOutput .= ' to ' . $row['passageEnd'];
        }
  // -------------- if whole chapter no from... to ------------
        $tOutput .= '</strong>';
      }
    }
    mysqli_free_result($result);

    return $tOutput;
  }

  function bookNameOrPsalm($tBookName, $iChapter, $bShowLinks){
    global $tSearch, $bPhrase;

    $tOutput = '';
    $iBookChapters = 2; // not important to get actual chapters in book unless only 1
    // if(strpos('Obadiah|Philemon|2 John|3 John|Jude', $tBookName)>0){
      // if($tBookName != 'John'){
    if(in_array($tBookName, array('Obadiah', 'Philemon', '2 John', '3 John', 'Jude')) ) {
      $iBookChapters = 1; // if only one chapter in book don't say 'chapter'!
    }

    if($bShowLinks){ // link to book only
      $tOutput .= buildLink($tBookName, 0, $tSearch, $bPhrase);
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
        $tOutput .= buildLink($tBookName, $iChapter, $tSearch, $bPhrase);
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

  function buildLink($tBookName, $iChapter, $tSearch, $bPhrase){
    $tReturn = '<a href="' . filter_input(INPUT_SERVER, 'PHP_SELF') . '?book=' . $tBookName;
    if($iChapter > 0){
      $tReturn .= '&chapter=' . $iChapter;
    }
    $tReturn .= '&search=' . str_replace(' ', '+', $tSearch);
    if($bPhrase){
      $tReturn .= '&phrase=on';
    }
    $tReturn .= '">';
    return $tReturn;
  }

  function passage($tBook, $tChapter, $tVerses, $tSearch, $bPhrase){
    global $link;

    $bProcessRequest = (strlen($tBook . $tChapter . $tVerses . $tSearch) > 0);

    $tOutput = '';
    $tBaseQuery = basicPassageQuery();
    if ($bProcessRequest) {
    // if (true) {
      $tLastBookName = '';
      $iLastChapter = 0;

      if (empty($tBook)) {
        if (empty($tSearch)) {
          // $tOutput .= ''<p>It seems you didn&rsquo;t enter a passage - this is one of my favourites:</p>';
          // $tQuery = 'SELECT verseText FROM verses WHERE bookCode ="JOH" AND chapter=3 AND verseNumber=16;';
          $tOutput .=  '<p>I can&rsquo;t understand what you want - this is the beginning of The Bible:</p>';
          $tQuery = $tBaseQuery . ' WHERE books.bookName ="Genesis" AND verses.chapter=1 AND verses.verseNumber<10;';
        }else{
          $tQuery = $tBaseQuery . ' WHERE verses.verseText LIKE "%' . percentify($tSearch, $bPhrase) . '%";';
        }
      } else {
        $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '"';
        if (empty($tChapter))
        {
          if (empty($tSearch)) {
            $tQuery = $tQuery . ';';
          }else{
            $tQuery = $tQuery . ' AND verses.verseText LIKE "' . percentify($tSearch, $bPhrase) . '";';
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
          // if (empty($tSearch)) {
            $tQuery = $tQuery . ';';
          // }else{
            // $tQuery = $tQuery . ' AND verses.verseText LIKE "' . percentify($tSearch, $bPhrase) . '";';
          // }
          // ---- NOT searching words if chapter - highlight instead - keep commented in case I change my mind!
        }
      }
// ------------------- display Bible passage -------------------------------
      $result = doQuery($link, $tQuery);

      if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
          if($tLastBookName != $row['bookName'] || $iLastChapter != $row['chapter']){
            $iBookChapters = 2; //$row['bookChapters'];
            $tOutput .=  '<h3>';
            $tOutput .=  bookNameOrPsalm($row['bookName'], $row['chapter'], true);
            $tOutput .=  '</h3>';
          }
          if (isInRange($row['verseNumber'], $tVerses)){
            $tOutput .=  '<span class="highlight">';
            $tOutput .=  '<sup>' . $row['verseNumber'] . '</sup>' . $row['vt'] . ' ';
            $tOutput .=  '</span>';
          }else{
            $tOutput .=  '<sup>' . $row['verseNumber'] . '</sup>' . highlightSearch($row['vt']) . ' ';
          }
          $tLastBookName = $row['bookName'];
          $iLastChapter = $row['chapter'];
        }
      } else {
        $tOutput .=  'It could be me... but there doesn&rsquo;t seem to be a match for that!';
      }
      mysqli_free_result($result);
// ------------------- display Bible passage -------------------------------
    }
    return $tOutput;
  }

  function highlightSearch($tValue){
    global $tSearch, $bPhrase;

    if ($tSearch > ''){
      if ($bPhrase){
        // $tValue = str_ireplace($tSearch, '<span class="highlight">' . $tSearch . '</span>', $tValue);
        $tValue = highlight($tSearch, $tValue);
      }else {
        $atSearch = explode (' ', $tSearch);
        foreach ($atSearch as $tSearchWord) {
          // $tValue = str_ireplace($tSearchWord, '<span class="highlight">' . $tSearchWord . '</span>', $tValue);
          $tValue = highlight($tSearchWord, $tValue);
        }
      }
    }
    return $tValue;
  }

function highlightStr($needle, $haystack) {
    preg_match_all("/$needle+/i", $haystack, $matches);
    if (is_array($matches[0]) && count($matches[0]) >= 1) {
        foreach ($matches[0] as $match) {
            $haystack = str_replace($match, '<span class="highlight">' . $match . '</span>', $haystack);
        }
    }
    return $haystack;
}

function highlight($needle, $haystack){
    $ind = stripos($haystack, $needle);
    $len = strlen($needle);
    if($ind !== false){
        return substr($haystack, 0, $ind) . '<span class="highlight">' .
           substr($haystack, $ind, $len) .'</span>' .
            highlight($needle, substr($haystack, $ind + $len));
    } else return $haystack;
}

  function percentify($tValue, $bPhrase){
    if($bPhrase){
      $tValue = '%' . $tValue . '%';
    }else {
      $tValue = '%' . str_replace(' ', '% %', $tValue) . '%';
    }
    return $tValue;
  }

  function isInRange($verse, $tVerses){
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

?>
