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
function daysInMonth($month, $year)
{
// calculate number of days in a month
return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

function basicPassageQuery(){
  $tBaseQuery = '';
  $tBaseQuery .= 'SELECT books.bookName, verses.chapter, verses.verseNumber, ';
  $tBaseQuery .= 'REPLACE(REPLACE(verses.verseText, "[H430]", ""), "[H3068]", "") AS vt';
  $tBaseQuery .= ', books.bookName ';
  $tBaseQuery .= 'FROM verses INNER JOIN books ON verses.bookCode=books.bookCode ';

  return $tBaseQuery;
}

function buildPassageQuery($tBookCode, $tPassageStart, $tPassageEnd){
    $iColonA = stripos($tPassageStart, ':');
    $iColonB = stripos($tPassageEnd, ':');
    //$tQuery ='';
    //$tQuery .='SELECT bookName, chapter, verseNumber, verseText FROM books, verses';
    //$tQuery .=' WHERE books.bookCode = verses.bookCode ';
    //$tQuery .=' AND books.bookCode = "' . $tBookCode . '"';
    $tQuery = basicPassageQuery();
    $tQuery .=' WHERE books.bookCode = "' . $tBookCode . '"';

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


/*-------------------------
$tBook = filter_input(INPUT_GET, 'book');
$tChapter = filter_input(INPUT_GET, 'chapter');
$tVerse = filter_input(INPUT_GET, 'verse');
$tSearch = filter_input(INPUT_GET, 'search');

$bGotRequest = strlen($tBook . $tChapter. $tVerse . $tSearch)>0;
//echo '<script lang="Javascript">alert(count($_GET);</script>';
echo getPassageSearch($tBook, $tChapter, $tVerse, $tSearch, $bGotRequest);
-------------------------*/
/*
function getPassageSearch($tBook, $tChapter, $tVerse, $tSearch, $bGotRequest){
  global $link;
    $tBaseQuery = basicPassageQuery();

    if ($bGotRequest) {
        $tLastBookName = "";
        $iLastChapter = 0;

        if (empty($tBook)) {
            if (empty($tSearch)) {
                echo "<p>It seems you didn't enter a passage - this is one of my favourites:</p>";
                $tQuery = $tBaseQuery . ' WHERE bookCode ="JOH" AND chapter=3 AND verseNumber=16;';
                //echo "<p>I can't understand what you want - this is the beginning of The Bible:</p>";
                //$tQuery = $tBaseQuery . ' WHERE books.bookName ="Genesis" AND verses.chapter=1 AND verses.verseNumber<10;';
                //$tQuery = $tBaseQuery . ' WHERE 1;';
            }else{
                $tQuery = $tBaseQuery . ' WHERE verses.verseText LIKE "%' . $tSearch . '%";';
            }
        } else {
            $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '";';
            if (empty($tChapter))
            {
                $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '";';
            }else{
                if (empty($tVerse))
                {
                    $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '" AND verses.chapter=' . $tChapter . ';';
                }else{
                    $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '" AND verses.chapter=' . $tChapter . ' AND verses.verseNumber=' . $tVerse . ';';
                }
            }
        }
        $result = doQuery($link, $tQuery);

        if (mysqli_num_rows($result) == 0) {
          echo 'Tell Carl something went wrong with the BibleStudyMan database - trying to do "' . $tQuery . '"';
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
                echo "<sup>" . $row["verseNumber"] . "</sup>" . $row["vt"] . " ";
                $tLastBookName = $row["bookName"];
                $iLastChapter = $row["chapter"];
            }
        }
        mysqli_free_result($result);
    }

function checkChapVerse($tChapVerse) {
    if(instr($tChapVerse,"-")>0){
        echo "";
    }
}
//-------------------------
*/
?>
