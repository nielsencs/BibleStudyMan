<?php
  require_once 'header.php';
  require_once '../sqlCon.php';
  require_once 'dbFunctions.php';

  $tBook = filter_input(INPUT_GET, 'book', FILTER_SANITIZE_STRING);
  $tChapter = filter_input(INPUT_GET, 'chapter', FILTER_SANITIZE_STRING);
  $tVerses = filter_input(INPUT_GET, 'verses', FILTER_SANITIZE_STRING);
  $tSearch = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
  $bPhrase = isset($_GET['phrase']);
?>

<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->

<?php
// ----------- prepare list of books for selection --------------
  $iBook = 0;
  $tQuery = 'SELECT bookName, bookChapters, orderChristian FROM books ORDER BY orderChristian;';
  $result = doQuery($link, $tQuery);
  if (mysqli_num_rows($result) > 0) {
    echo '<script type="text/javascript">';
    echo 'var atBooks = [["Start from 1!",';
    echo '0]';

    while($row = mysqli_fetch_assoc($result)) {
      echo ', ["' . $row['bookName'] . '", ';
      echo $row['bookChapters'] . ']';
      if(strtoupper($row['bookName']) == strtoupper($tBook)){
        $iBook = $row['orderChristian'];
      }
    }
    echo '];';
    echo 'var iBook=' . $iBook . ';';
    echo '</script>';
  } else {
    echo 'Tell Carl something went wrong with the BibleStudyMan database :(';
  }
  mysqli_free_result($result);
// ----------- prepare list of books for selection --------------
?>

<script type="text/javascript">
  window.onload = function(){
    // document.body.style.cursor = 'default';
    setFields();
  }

  function doSubmit() {
    if(wordCount(document.searchForm.search.value) > 0){
      showWait();
      document.searchForm.submit();
    }
  }

  function wordCount(tString){
    var iCount = 0;
    if(tString > ''){
      iCount = tString.trim().indexOf(' ');
    }
    return iCount;
  }

  function showWait() {
    document.getElementById('waitHint').style.display = 'block';
    // document.waitHint.style.display = 'block';
    // document.body.style.cursor = 'wait';
    // document.body.style.cursor = 'progress';
    // alert();
  }

  function doDirection(tDirection) {
    // alert(iBook);
    var iChapter = parseInt('0' + document.searchForm.chapter.value);

    if(tDirection=='pb'){ //prev book
      if(iBook==0){
        iBook = 1;
      }
      iBook = wrapNum(iBook, 66, -1);
      document.searchForm.chapter.value='';
    }
    if(tDirection=='nb'){ //next book
      if(iBook==0){
        iBook = 66;
      }
      iBook = wrapNum(iBook, 66, +1);
      document.searchForm.chapter.value='';
    }
    if(tDirection=='pc'){ //prev chapter
      if(iChapter == 0 || iChapter == 1){
        iBook = wrapNum(iBook, 66, -1);
        // document.searchForm.chapter.value='';
        document.searchForm.chapter.value=atBooks[iBook][1];
      }else {
        iChapter--;
        document.searchForm.chapter.value=iChapter;
      }
    }
    if(tDirection=='nc'){ //next chapter
      // if(iChapter == 0 || iChapter == iChapterMax){
      if(iChapter == 0 || iChapter == atBooks[iBook][1]){
        iBook = wrapNum(iBook, 66, +1);
        iChapter = 1;
      }else {
        // iChapter = wrapNum(iChapter, iChapterMax, +1);
        iChapter = wrapNum(iChapter, atBooks[iBook][1], +1);
      }
      document.searchForm.chapter.value=iChapter;
    }
      // alert(atBooks[iBook][0] + ' : ' + iBook);
      document.searchForm.book.value=atBooks[iBook][0];
      showWait();
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

  function setFields(){
    document.searchForm.book.value = "<?php echo $tBook; ?>";
    document.searchForm.chapter.value = "<?php echo $tChapter; ?>";
    document.searchForm.search.value = "<?php echo $tSearch; ?>";
    document.searchForm.phrase.checked = "<?php echo $bPhrase; ?>";
  }

  function clearField(tName){
    // document.getElementById(tName).value = '';
    document.getElementById(tName).value = '';
    // document.searchForm.search.value = '';
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

                <form name="searchForm" id="searchForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get" onsubmit="showWait();">

                <table class="searchTable">
                    <tbody>
                    <tr>
                    <td>
                      Book
                      <br />
                      <input type="text" name="book" id="book" value="" list="books">
                      <datalist name="books" id="books">
                      <!-- <td><select name="book" id="book"> -->
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
                      </datalist>
                      <!-- </select> -->
                      <br />
                      <input type="button" value="&lt;" onclick="doDirection('pb')">
                      <input type="button" value="Clear" onclick="clearField('book')">
                      <input type="button" value="&gt;" onclick="doDirection('nb')">
                </td>
                    </td>
                    <td>
                      Chapter
                <br />
                      <!-- <input type="text" name="chapter" id="chapter" value="<?php echo $tChapter; ?>"> -->
                      <input type="text" name="chapter" id="chapter" value=""><br />
                      <input type="button" value="&lt;" onclick="doDirection('pc')">
                      <input type="button" value="Clear" onclick="clearField('chapter')">
                      <input type="button" value="&gt;" onclick="doDirection('nc')">
                  </td>
                  </tr>
                <tr>
                  <td colspan="2">Words<br />
                    <input type="button" value="Clear" onclick="clearField('search')">
                    <!-- <td colspan="2"><input type="text" name="search" id="search" value="<?php echo $tSearch; ?>"></td> -->
                    <input type="text" name="search" id="search" value="">
                    <!-- <input type="checkbox" name="phrase" id="phrase"<?php if($bPhrase){ echo ' checked';} ?>> Phrase? -->
                    <input type="checkbox" name="phrase" id="phrase" onclick="doSubmit()"> Phrase?
                  </td>
                </tr>
                <tr>
                    <td><input type="reset" name="clearAll" id="clearAll" value="Clear All"></td>
                    <td><input type="submit" value="Search"></td>
                </tr>

                </tbody>
                </table>
                </form>
<?php
  echo passage($tBook, $tChapter, $tVerses, $tSearch, $bPhrase);
?>
            </div>
        </div>
<?php

  mysqli_close($link);
  require_once 'footer.php';
?>
