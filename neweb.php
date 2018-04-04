<?php
  require_once 'header.php';
  require_once '../sqlCon.php';
  require_once 'dbFunctions.php';

  $tBook = filter_input(INPUT_GET, 'book');
  $tChapter = filter_input(INPUT_GET, 'chapter');
  $tVerse = filter_input(INPUT_GET, 'verse');
  $tSearch = filter_input(INPUT_GET, 'search');

  $bProcessRequest = strlen($tBook . $tChapter. $tVerse . $tSearch)>0;
  //echo '<script lang="Javascript">alert(count($_GET);</script>';
?>

        <div class='intro'>
            <p>This is a minor adaptation of the
              <a href="https://worldenglishbible.org">WEB</a> to include the ancient words
              for placenames, for God and other words of special interest.</p>
        </div>
        <div class="main NEWEB">
            <h1>The Nielsen Edition of the World English Bible</h1>
            <div class="subMain sectGeneral">
                <!-- p>This doesn't look like much yet...<br>
                I need to develop the code to:</p>
                <ul>
                    <li>complete the compilation of this edition</li>
                    <li>provide a lookup for certain words</li>
                    <li>Any other requests?</li>
                </ul -->

                <table class="searchTable">
                    <tr><td>&nbsp;</td><td>Book</td><td>Chapter</td></td>&nbsp;<td><td>Verse</td><td>&nbsp;</td></tr>

                    <form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get">
                        <tr><td>Passage:</td><td><input list="books" size="7" name="book" value="<?php echo $tBook; ?>">
                  <datalist id="books">
<?php
  $tQuery = 'SELECT bookName FROM books;';
  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
      echo '<option value="' . $row["bookName"] . '">';
    }
  } else {
    echo "Tell Carl something went wrong with the BibleStudyMan database :(";
  }
  mysqli_free_result($result);
?>
                  </datalist></td>
                  <td><input type="text" size="2" name="chapter" value="<?php echo $tChapter; ?>"></td><td>:</td><td><input type="text" size="2" name="verse" value="<?php echo $tVerse; ?>"></td>
                  <td><input type="submit"></td></tr>
                </form>

                <form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get">
                    <tr>
                        <td>Search:</td><td colspan="4"><input type="text" size="21" name="search" value="<?php echo $tSearch; ?>"></td><td><input type="submit"></td>
                    </tr>
                </form>
                </table>
<?php
  $tBaseQuery = basicPassageQuery();
    if ($bProcessRequest) {
        $tLastBookName = "";
        $iLastChapter = 0;

        if (empty($tBook)) {
            if (empty($tSearch)) {
                //echo "<p>It seems you didn't enter a passage - this is one of my favourites:</p>";
                //$tQuery = 'SELECT verseText FROM verses WHERE bookCode ="JOH" AND chapter=3 AND verseNumber=16;';
                echo "<p>I can't understand what you want - this is the beginning of The Bible:</p>";
                $tQuery = $tBaseQuery . ' WHERE books.bookName ="Genesis" AND verses.chapter=1 AND verses.verseNumber<10;';
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

        if (mysqli_num_rows($result) > 0) {
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
        } else {
            echo "Tell Carl something went wrong with the BibleStudyMan database :(";
        }
        mysqli_free_result($result);
    }
?>

            </div>
        </div>
<?php
  mysqli_close($link);
  require_once 'footer.php';

function checkChapVerse($tChapVerse) {
  if(instr($tChapVerse,"-")>0){
    echo "";
  }
}
?>
