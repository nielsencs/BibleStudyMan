<?php
// ============================================================================
function getDayReadingQuery($iMonth, $iDay, $iSection = 0){
// ============================================================================
    list($query, $params) = getDayReadingQueryStart($iMonth, $iDay);
    list($whereClause, $whereParams) = getDayReadingQueryWhere($iMonth, $iDay, $iSection);
    $query .= $whereClause;
    $params = array_merge($params, $whereParams);
    return [$query, $params];
}
// ============================================================================

// ============================================================================
function getDayReadingQueryStart($iMonth, $iDay){
// ============================================================================
    $query = '';
    $params = [];
    if($iMonth === 2 && $iDay === 29){
        $query .= 'SELECT *, DATE_FORMAT(planDate,"%b %e") as planDateFormatted';
        $query .= ' FROM `plan-feb-29` INNER JOIN books ON `plan-feb-29`.bookCode=books.bookCode';
    } else {
        $query .= 'SELECT *, DATE_FORMAT(planDate,"%b %e") as planDateFormatted';
        $query .= ' FROM `plan-new` INNER JOIN `plan-days` ON `plan-new`.planDay=`plan-days`.ID';
        $query .= ' INNER JOIN books ON `plan-new`.bookCode=books.bookCode';
    }
    return [$query, $params];
}
// ============================================================================

// ============================================================================
function getDayReadingQueryWhere($iMonth, $iDay, $iSection = 0){
// ============================================================================
    $tQuery = '';
    $params = [];
    if($iMonth === 2 && $iDay === 29){
        $tTable = '`plan-feb-29`';
    } else {
        $tTable = '`plan-new`';
    }

    $tQuery .= ' WHERE MONTH(planDate)=?';
    $params[] = $iMonth;
    $tQuery .= ' AND DAY(planDate)=?';
    $params[] = $iDay;

    switch ($iSection){
        case 1:
            $tQuery .= ' AND ' . $tTable . '.sectionCode = ?';
            $params[] = '1TOR';
            break;
        case 2:
            $tQuery .= ' AND (' . $tTable . '.sectionCode = ? OR ' . $tTable . '.sectionCode = ?)';
            $params[] = '2NV1';
            $params[] = '4NV2';
            break;
        case 3:
            $tQuery .= ' AND ' . $tTable . '.sectionCode = ?';
            $params[] = '3KTV';
            break;
        case 4:
            $tQuery .= ' AND ' . $tTable . '.sectionCode = ?';
            $params[] = '5NCV';
            break;
    }
    $tQuery .= ' ORDER BY planDate, ' . $tTable . '.sectionCode ASC';

    return [$tQuery, $params];
}
// ============================================================================

// ============================================================================
function daysReadingsAsSentence($iMonth, $iDay, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
    global $pdo;
    $tOutput = '';
    list($tQuery, $params) = getDayReadingQuery($iMonth, $iDay);
    $stmt = doQuery($pdo, $tQuery, $params);
    $rows = $stmt->fetchAll();

    if (count($rows) === 0) {
        $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . htmlspecialchars($tQuery ?? '', ENT_QUOTES, 'UTF-8') . '"';
    } else {
        if($iMonth === 2 && $iDay === 29){
            $tOutput .= ' let me say Happy Leap-Year-Extra-Day.</p><p><strong>Today is a special day!</strong> ';
            $tOutput .= 'Because February 29th doesn&apos;t come round very often it ';
            $tOutput .= 'can&apos;t sensibly be included in the regular plan. However, ';
            $tOutput .= 'since you&apos;ve taken the trouble to look here today, I&apos;ve ';
            $tOutput .= 'prepared a little collection of shorter passages to reflect on.</p><p>';
        }
        foreach($rows as $row) {
            if($row['sectionCode'] === '1TOR'){
                // $tOutput .= ' first, ';
                $tOutput .= '';
            }
            //$tOutput .= "" . $row['sectionEnglish']  . ' (' . $row['sectionName'] . ') - ';
            if($row['sectionCode'] === '2NV1' || $row['sectionCode']=== '4NV2'){
                // $tOutput .= '; next it&apos;s ';
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
            $bSameChapter = isSameChapter($row);

            $tOutput .= ' ' . bookNameOrPsalm($row['bookName'], 0, false, $bChaptersOnly && ! $bSameChapter, $bHighlightSW, $bShowOW, $bShowTN);

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

    return $tOutput;
}
// ============================================================================

// ============================================================================
function daysSectionReading($iMonth, $iDay, $iSection, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
    global $pdo;
    $tOutput = '';
    list($tQuery, $params) = getDayReadingQuery($iMonth, $iDay, $iSection);
    $stmt = doQuery($pdo, $tQuery, $params);
    $rows = $stmt->fetchAll();

    if (count($rows) === 0) {
        $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . htmlspecialchars($tQuery ?? '', ENT_QUOTES, 'UTF-8') . '"';
    } else {
        foreach($rows as $row) {
            $bChaptersOnly = isChaptersOnly($row);
            $bSameChapter = isSameChapter($row);

            $tOutput .= bookNameOrPsalm($row['bookName'], -1, false, $bChaptersOnly && ! $bSameChapter, $bHighlightSW, $bShowOW, $bShowTN);

            $tOutput .= $row['startChapter'];
            if($row['startVerse'] > 0){
                $tOutput .= ':' . $row['startVerse'];
                if($row['startChapter'] === $row['endChapter']){
                    $tOutput .= '-';
                }
            }else{
                if($row['endChapter'] > 0 && $row['startChapter'] != $row['endChapter']){
                    $tOutput .= '-';
                }
            }
            if($row['endVerse'] > 0 || $row['endChapter'] > 0){
                if($row['startChapter'] === $row['endChapter']){
                    $tOutput .= $row['endVerse'];
                }else{
                    if (! $bChaptersOnly){
                        $tOutput .= '-';
                    }
                    $tOutput .= $row['endChapter'];
                    if (! $bChaptersOnly){
                        $tOutput .= ':' . $row['endVerse'];;
                    }
                }
            }
        }
    }

    return $tOutput;
}
// ============================================================================

// ============================================================================
function isChaptersOnly($row){
// ============================================================================
//  return ($row['startChapter'] != $row['endChapter']) & (intval($row['startVerse']) === 0) & (intval($row['endVerse']) === 0);
    return (intval($row['startVerse']) === 0) && (intval($row['endVerse']) === 0);
}
// ============================================================================

// ============================================================================
function isSameChapter($row){
// ============================================================================
    return (intval($row['endChapter']) === 0) || (intval($row['startChapter']) === intval($row['endChapter']));
}
// ============================================================================

// ============================================================================
function daysReadingsAsVerses($iMonth, $iDay, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
    global $pdo;
    $tOutput = '';
    list($tQuery, $params) = getDayReadingQuery($iMonth, $iDay);
    $stmt = doQuery($pdo, $tQuery, $params);
    $readingList = $stmt->fetchAll();

    if (count($readingList) == 0) {
        $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . htmlspecialchars($tQuery ?? '', ENT_QUOTES, 'UTF-8') . '"';
    } else {
        $readCount = count($readingList);
        if ($readCount > 0){
            $tOutput .=  '<section class="search-result">';
            $tOutput .=  '<form class="" action="" method="post" onsubmit="return false">';
            for ($i=0; $i < $readCount; $i++) {
                $tOutput .=  '<h2 class="search-result__title">Section ' . ($i + 1) . '</h2>';

//        $tOutput .=  '<p class="centerText">' . $readingList[$i]['bookName'];
//        $tOutput .=  ' ' . $readingList[$i]['startChapter'] . ':' .  $readingList[$i]['startVerse'];
//        $tOutput .=  ' - ' . $readingList[$i]['endChapter'] . ':' .  $readingList[$i]['endVerse'] . '</p>';
                $tOutput .=  '<p class="centerText">' . daysSectionReading($iMonth, $iDay, $i + 1, $bHighlightSW, $bShowOW, $bShowTN) . '</p>';

                $tAudio = 'media/' . $readingList[$i]['bookCode'] . '_' . $readingList[$i]['startChapter'];
                $tAudio .= '_' .  $readingList[$i]['startVerse'] . '-' . $readingList[$i]['endChapter'];
                $tAudio .= '_' . $readingList[$i]['endVerse'] . '.ogg';

                $tOutput .=  '<!-- ' . htmlspecialchars($tAudio ?? '', ENT_QUOTES, 'UTF-8') . ' -->';
                if (file_exists($tAudio)){
                    $tOutput .=  '<input type="button" id="bReading' . $i . '" name="bReading' . $i . '" value="listen" onclick="audioPlayPause(\'Reading' . $i . '\');"><br />';
                    $tOutput .=  '<audio id="aReading' . $i . '" name="aReading' . $i . '" src="' . htmlspecialchars($tAudio ?? '', ENT_QUOTES, 'UTF-8') . '"></audio>';
                }
                list($passageQuery, $passageParams) = buildPassageQueryNew($readingList[$i]['bookCode'], $readingList[$i]['startChapter'],  $readingList[$i]['startVerse'], $readingList[$i]['endChapter'], $readingList[$i]['endVerse']);
                $tOutput .=  showVerses($passageQuery, $passageParams, '', $bHighlightSW, $bShowOW, $bShowTN);
            }
            $tOutput .=  '</form>';
            $tOutput .=  '</section>';
        }
    }
    return $tOutput;
}
// ============================================================================

// ============================================================================
function showReading($tBookCode, $iStartChapter, $iStartVerse, $iEndChapter, $iEndVerse, $bHighlightSW, $bShowOW, $bShowTN){
// ============================================================================
    list($tQuery, $params) =  buildPassageQueryNew($tBookCode, $iStartChapter, $iStartVerse, $iEndChapter, $iEndVerse);
    return showVerses($tQuery, $params, '', $bHighlightSW, $bShowOW, $bShowTN);
}
// ============================================================================

/*
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
*/

// ============================================================================
function buildPassageQueryNew($tBookCode, $iStartChapter, $iStartVerse, $iEndChapter, $iEndVerse){
// ============================================================================
    list($tQuery, $params) = basicPassageQuery();

    if($tBookCode == '23J'){
        $tQuery .=' WHERE (books.bookCode = ? OR books.bookCode = ?)';
        $params[] = "2JO";
        $params[] = "3JO";
    }else{
        $tQuery .=' WHERE books.bookCode = ?';
        $params[] = $tBookCode;
    }

    if ($iStartChapter == $iEndChapter || $iEndChapter < 1){ // one chapter
        if($iStartVerse < 1){ // whole chapter
            $tQuery .= ' AND verses.chapter = ?';
            $params[] = $iStartChapter;
        }else {
            if($iEndVerse < 1){ // one verse!
                $tQuery .= ' AND verses.chapter = ?';
                $params[] = $iStartChapter;
                $tQuery .= ' AND verses.verseNumber = ?';
                $params[] = $iStartVerse;
            }else {
                $tQuery .= ' AND verses.chapter = ?';
                $params[] = $iStartChapter;
                $tQuery .= ' AND verses.verseNumber >= ?';
                $params[] = $iStartVerse;
                $tQuery .= ' AND verses.verseNumber <= ?';
                $params[] = $iEndVerse;
            }
        }
    } else { // multiple chapters
        $tQuery .= ' AND (';
        $tQuery .= '(verses.chapter = ?';
        $params[] = $iStartChapter;
        if ($iStartVerse > 0){ // not whole chapter
            $tQuery .= ' AND verses.verseNumber >= ?';
            $params[] = $iStartVerse;
        }
        $tQuery .= ')';
        $tQuery .= ' OR ';
        $tQuery .= ' (verses.chapter = ?';
        $params[] = $iEndChapter;
        if ($iEndVerse > 0){ // not whole chapter
            $tQuery .= ' AND verses.verseNumber <= ?';
            $params[] = $iEndVerse;
        }
        $tQuery .= ')';
        if ($iEndChapter == $iStartChapter + 1){
            $tQuery .= ')';
        } else {
            $tQuery .= ' OR ';
            $tQuery .= ' (verses.chapter > ?';
            $params[] = $iStartChapter;
            $tQuery .= ' AND verses.chapter < ?))';
            $params[] = $iEndChapter;
        }
    }
    $tQuery .= ' ORDER BY bookName, chapter, verseNumber ASC';

    return [$tQuery, $params];
}
// ============================================================================

// ============================================================================
function sectionQuery($sectionCode, $tSortOrder){ // build query to get 1 sorted section
  /*
  > $sectionCode should be one of '1TOR', '2NV1', '3KTV', '5NCV' (4 is a throwback
    to when I split Neviim in two) from TABLE 'sections'
  > $tSortOrder should be one of 'orderChristian', 'orderJewish', 'orderChron1',
    'orderChron2' from TABLE books
  */
// ============================================================================
    $tQuery = 'SELECT ';
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
    $tQuery .= '`plan-new`.sectionCode = ? ';
    $tQuery .= 'ORDER BY ';
    $tQuery .= 'books.' . $tSortOrder . ', '; // Note: $tSortOrder is validated against a list of allowed values
    $tQuery .= '`plan-new`.planDay;';

    $params = [$sectionCode];

    return [$tQuery, $params];
}
// ============================================================================

// ============================================================================
function planTable($tSortOrder = 'orderChristian'){// build HTML table of the years reading plan
// ============================================================================
    global $pdo;
    $tOutput = '';

    $allowedSortOrders = ['orderChristian', 'orderJewish', 'orderChron1', 'orderChron2'];
    if (!in_array($tSortOrder, $allowedSortOrders)) {
        $tSortOrder = 'orderChristian';
    }

    list($tQuery1, $params1) = sectionQuery('1TOR', $tSortOrder);
    $result1 = doQuery($pdo, $tQuery1, $params1)->fetchAll();

    list($tQuery2, $params2) = sectionQuery('2NV1', $tSortOrder);
    $result2 = doQuery($pdo, $tQuery2, $params2)->fetchAll();

    list($tQuery3, $params3) = sectionQuery('3KTV', $tSortOrder);
    $result3 = doQuery($pdo, $tQuery3, $params3)->fetchAll();

    list($tQuery4, $params4) = sectionQuery('5NCV', $tSortOrder);
    $result4 = doQuery($pdo, $tQuery4, $params4)->fetchAll();

    $tOutput = PTBuild($result1, $result2, $result3, $result4);

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
    $tOutput .= '</tr>';

    $date = new DateTime('2001-01-01');

    for ($i=0; $i < 365; $i++) {
        $row1 = $result1[$i];
        $row2 = $result2[$i];
        $row3 = $result3[$i];
        $row4 = $result4[$i];

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
        $tOutput .= '</tr>';
    }
    $tOutput .= '</table>';

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
    $tOutput .= htmlspecialchars($row['bookName'] ?? '', ENT_QUOTES, 'UTF-8');
    $tOutput .= '">';
    $tOutput .= htmlspecialchars($row['bookName'] ?? '', ENT_QUOTES, 'UTF-8');
    $tOutput .= '</a> ';

    if ($row['bookChapters'] > 1){
        $tOutput .= '<a href="bible.php?book=';
        $tOutput .= htmlspecialchars($row['bookName'] ?? '', ENT_QUOTES, 'UTF-8');
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
                $tOutput .= htmlspecialchars($row['bookName'] ?? '', ENT_QUOTES, 'UTF-8');
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
