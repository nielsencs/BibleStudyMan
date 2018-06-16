<?php
  require_once 'header.php';
  require_once '../sqlCon.php';
  require_once 'dbFunctions.php';

  $tBook = filter_input(INPUT_GET, 'book');
  $tChapter = filter_input(INPUT_GET, 'chapter');
  $tVerse = filter_input(INPUT_GET, 'verse');
  $tSearch = filter_input(INPUT_GET, 'search');
  $tJump = filter_input(INPUT_GET, 'jump');
  if($tJump == ''){$tJump = 'c';}
  $tDirection = filter_input(INPUT_GET, 'direction');

  $bProcessRequest = (strlen($tBook . $tChapter . $tVerse . $tSearch) > 0);
?>

<?php
  $iBook = 0;
  $iChapterMax = 0;
  $tQuery = 'SELECT bookName, bookChapters, orderChristian FROM books ORDER BY orderChristian;';
  $result = doQuery($link, $tQuery);
  if (mysqli_num_rows($result) > 0) {
    echo '<script type="text/javascript">';
    echo 'var atBooks = ["Start from 1!"';

    while($row = mysqli_fetch_assoc($result)) {
      echo ', "' . $row['bookName'] . '"';
      if(strtoupper($row['bookName']) == strtoupper($tBook)){
        $iBook = $row['orderChristian'];
        $iChapterMax = $row['bookChapters'];
      }
    }
    echo '];';
    echo 'var iBook=' . $iBook . ';';
    echo 'var iChapterMax=' . $iChapterMax . ';';
    echo '</script>';
  } else {
    echo 'Tell Carl something went wrong with the BibleStudyMan database :(';
  }
  mysqli_free_result($result);
?>

<script type="text/javascript">
  function doDirection(tDirection) {
    // alert(iBook);
    var iChapter = parseInt("0" + document.searchForm.chapter.value);
    var iChapter = parseInt("0" + document.searchForm.chapter.value);

    if(tDirection=="pb"){ //prev book
      if(iBook==0){
        iBook = 1;
      }
      iBook = wrapNum(iBook, 66, -1);
    }
    if(tDirection=="nb"){ //next book
      if(iBook==0){
        iBook = 66;
      }
      iBook = wrapNum(iBook, 66, +1);
    }
    if(tDirection=="pc"){ //prev chapter
      if(iChapter == 0 || iChapter == 1){
        iBook = wrapNum(iBook, 66, -1);
        document.searchForm.chapter.value='';
      }else {
        iChapter--;
        document.searchForm.chapter.value=iChapter;
      }
    }
    if(tDirection=="nc"){ //next chapter
      if(iChapter == 0 || iChapter == iChapterMax){
        iBook = wrapNum(iBook, 66, +1);
        iChapter = 1;
      }else {
        iChapter = wrapNum(iChapter, iChapterMax, +1);
      }
      document.searchForm.chapter.value=iChapter;
    }
      // alert(atBooks[iBook] + " : " + iBook);
      document.searchForm.book.value=atBooks[iBook];
      document.searchForm.submit();
  }

  function wrapNum(iNum, iMax, iSkip) {
    iNum = iNum + iSkip;
    // alert(iNum);
    if(iNum < 1){
      iNum = iMax;
    }
    if(iNum > iMax){
      iNum = 1;
    }
    return iNum;
  }
</script>
        <div class="main Bible">
            <h1>The Nielsen Edition of the World English Bible</h1>
            <div class="subMain sectGeneral">
        <p>This is a minor adaptation of the <a href="https://worldenglishbible.org">WEB</a>
          to include nuanced meanings of particular ancient words for placenames,
          God and others of special interest.</p>
                <!-- p>This doesn't look like much yet...<br>
                I need to develop the code to:</p>
                <ul>
                    <li>complete the compilation of this edition</li>
                    <li>provide a lookup for certain words</li>
                    <li>Any other requests?</li>
                </ul -->

                <form name="searchForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get">

                <table class="searchTable">
                    <tbody>
                    <tr>
                    <td>
                <input type="button" value="&lt;prev" onclick="doDirection('pb')">
                        Book
                <input type="button" value="next&gt;" onclick="doDirection('nb')">
                    </td>
                    <td>
                <input type="button" value="&lt;prev" onclick="doDirection('pc')">
                        Chapter
                <input type="button" value="next&gt;" onclick="doDirection('nc')">
                    </td>
                    </tr>

                    <tr>
                    <td><input type="text" name="book" value="<?php echo $tBook; ?>" list="books">
                    <datalist name="books">
                    <!-- <td><select name="book"> -->
                        <!-- <option value="">Pick a book</option>'; -->
<?php
  $tQuery = 'SELECT bookName FROM books;';
  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
      // echo '<option value="' . $row["bookName"] . '">';
      echo '<option value="' . $row["bookName"] . '"';
      // if ($row["bookName"] == $tBook) {
        // echo ' selected';
      // }
      echo '>' . $row["bookName"] . '</option>';
    }
  } else {
    echo "Tell Carl something went wrong with the BibleStudyMan database :(";
  }
  mysqli_free_result($result);
?>
                  </datalist></td>
                  <!-- </select></td> -->
                  <td>
                      <input type="text" name="chapter" value="<?php echo $tChapter; ?>">
                  </td>
                  </tr>
                <tr>
                    <td colspan="2">Words</td>
                </tr>
                <tr>
                    <!-- <td colspan="2"><input type="text" name="search" value="<?php echo $tSearch . ' ' . soundex($tSearch); ?>"></td> -->
                    <td colspan="2"><input type="text" name="search" value="<?php echo $tSearch; ?>"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Search"></td>
                </tr>

                </tbody>
                </table>
                </form>
<?php
echo $tDirection;
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
            $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '"';
            if (empty($tChapter))
            {
              if (empty($tSearch)) {
                $tQuery = $tQuery . ';';
              }else{
                $tQuery = $tQuery . ' AND verses.verseText LIKE "%' . $tSearch . '%";';
              }
            }else{
                // if (empty($tVerse))
                // {
                    $tQuery = $tQuery . ' AND verses.chapter=' . $tChapter;
                // }else{
                //     $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '" AND verses.chapter=' . $tChapter . ' AND verses.verseNumber=' . $tVerse . ';';
                // }
              if (empty($tSearch)) {
                $tQuery = $tQuery . ';';
              }else{
                $tQuery = $tQuery . ' AND verses.verseText LIKE "%' . $tSearch . '%";';
              }
            }
        }

// ------------------- display Bible passage -------------------------------
        $result = doQuery($link, $tQuery);

        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                if($tLastBookName != $row["bookName"] || $iLastChapter != $row["chapter"]){
                    $iBookChapters = 2; //$row["bookChapters"];
                    echo "<h3>";
                    if($row["bookName"] != "Psalms"){ // don't show book if psalm
                      echo $row["bookName"];
                    }
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
            echo "It could be me... but there doesn't seem to be a match for that!";
        }
        mysqli_free_result($result);
// ------------------- display Bible passage -------------------------------
    }
?>

            </div>
        </div>
<?php
  mysqli_close($link);
  require_once 'footer.php';

function checkChapVerse($tChapVerse) { // regex ? for search entries
  if(instr($tChapVerse,"-")>0){ // range - verse or chapter 1-3
    echo "";
  }
}
?>
