<?php
function doQuery($link, $tQuery){
    echo '<!-- ' . $tQuery . ' -->';
    return mysqli_query($link, $tQuery);
}

function showPassage($tBookCode, $tPassageStart, $tPassageEnd){
  global $link;
  //$result = doQuery($link, buildPassageQuery($tBookCode, $tPassageStart, $tPassageEnd));

  //$tQuery = getPassage($tBook, $tChapter, $tVerse, $tSearch, $bGotRequest);
  $tQuery =  buildPassageQuery($tBookCode, $tPassageStart, $tPassageEnd);
  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) == 0) {
    echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '" in dbFunctions.showPassage';
  } else {
    while($row = mysqli_fetch_assoc($result)) {
          if($tLastBookName != $row["bookName"] || $iLastChapter != $row["chapter"]){
              $iBookChapters = 2; //$row["bookChapters"];
              echo "<h3>" . $row["bookName"];
              if($iBookChapters > 1){ // only say 'chapter' if more than 1
                  if($row["bookName"] == "Psalms"){ // only call it a chapter if not a psalm
                      echo " Psalm ";
                  }else{
                      echo " Chapter ";
                  }
                  echo $row["chapter"];
              }
              echo "</h3>";
          }
          echo "<sup>" . $row["verseNumber"] . "</sup>" . $row["vt"] . " " . $row["verseText"] . " " ;
          $tLastBookName = $row["bookName"];
          $iLastChapter = $row["chapter"];
      }
  }
  mysqli_free_result($result);
}

function buildPassageQuery($tBookCode, $tPassageStart, $tPassageEnd){
    $iColonA = stripos($tPassageStart, ':');
    $iColonB = stripos($tPassageEnd, ':');
    $tQuery ='';

    $tQuery .='SELECT bookName, chapter, verseNumber, verseText FROM books, verses';
    $tQuery .=' WHERE books.bookCode = verses.bookCode ';
    $tQuery .=' AND books.bookCode = "' . $tBookCode . '"';

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

    if ($tChapterA == $tChapterB){
        $tQuery .= ' AND verses.chapter = ' . $tChapterA;
        $tQuery .= ' AND verses.verseNumber >=' . $tVerseA;
        $tQuery .= ' AND verses.verseNumber <=' . $tVerseB;
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

function planTable($link){
    $tOutput = '';
    $tQuery = '';

    $tQuery .= 'SELECT *, DATE_FORMAT(planDate,"%b %e") as planDateFormatted';
    $tQuery .= ' FROM plan';
    $tQuery .= ' ORDER BY planDate ASC';

    $result = doQuery($link, $tQuery);

    if (mysqli_num_rows($result) == 0) {
        $tOutput .= 'Tell Carl something went wrong with the BibleStudyMan database :(';
    } else {
        $tOutput .= '<table>';
        $tOutput .= '<tr>';
        $tOutput .= '<th>Date</th>';
        $tOutput .= '<th colspan="3">Torah</th>';
        $tOutput .= '<th colspan="3">Nevi&rsquo;im</th>';
        $tOutput .= '<th colspan="3">K&rsquo;tuvim</th>';
        $tOutput .= '<th colspan="3">New Covenant</th>';
        while($row = mysqli_fetch_assoc($result)) {
            if ($row['planDateFormatted'] != 'Feb 29'){
                if($row['sectionCode'] == '1TOR'){
                    $tOutput .= '</tr>';
                    $tOutput .= '<tr>';
                    $tOutput .= "<td>";
                    $tOutput .= $row['planDateFormatted'];
                }
                $tOutput .= '</td><td>';
                $tOutput .= $row["bookCode"];
                $tOutput .= '</td><td>';
                $tOutput .= $row["passageStart"];
                $tOutput .= '</td><td>';
                $tOutput .= $row["passageEnd"];
            }
        }
        $tOutput .= '</tr></table>';
    }
    mysqli_free_result($result);
    return $tOutput;
}

?>
