<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';

  $tBook = filter_input(INPUT_GET, 'book', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tChapter = filter_input(INPUT_GET, 'chapter', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tVerses = filter_input(INPUT_GET, 'verses', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tWords = filter_input(INPUT_GET, 'words', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $bShowMore = filter_input(INPUT_GET, 'showMore', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
  $bHighlightSW = filter_input(INPUT_GET, 'highlightSW', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
  $bShowOW = filter_input(INPUT_GET, 'showOW', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
?>

<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->

<?php
  $iBook = 0;
  echo prepareBookList();
  $atStrongs = prepareStrongs();
?>

<script type="text/javascript">
// ============================================================================
  window.onload = function(){
// ============================================================================
    // document.body.style.cursor = 'default';
    setFields();
  };
// ============================================================================

// ============================================================================
  function doSubmit(bJustDoit = false) {
// ============================================================================
    // if(wordCount(document.searchForm.words.value) > 0){
    if(document.searchForm.words.value > '' || bJustDoit){
      showWait();
      document.searchForm.submit();
    }
  }
// ============================================================================

// ============================================================================
  function wordCount(tString){
// ============================================================================
    var iCount = 0;
    if(tString > ''){
      iCount = tString.trim().indexOf(' ') + 1;
    }
    return iCount;
  }
// ============================================================================

// ============================================================================
  function showWait() {
// ============================================================================
    document.getElementById('waitHint').style.display = 'block';
    // document.waitHint.style.display = 'block';
    // document.body.style.cursor = 'wait';
    // document.body.style.cursor = 'progress';
    // alert();
  }
// ============================================================================

// ============================================================================
  function doDirection(tDirection) {
// ============================================================================
    // alert(iBook);
    var iLastBook = 67; // to accomodate extra '2&3 John' book!
    var iChapter = parseInt('0' + document.searchForm.chapter.value);

    if(tDirection=='pb'){ //prev book
      if(iBook==0){
        iBook = 1;
      }
      iBook = wrapNum(iBook, iLastBook, -1);
      document.searchForm.chapter.value='';
    }
    if(tDirection=='nb'){ //next book
      if(iBook==0){
        iBook = iLastBook;
      }
      iBook = wrapNum(iBook, iLastBook, +1);
      document.searchForm.chapter.value='';
    }
    if(tDirection=='pc'){ //prev chapter
      if(iChapter == 0 || iChapter == 1){
        iBook = wrapNum(iBook, iLastBook, -1);
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
        iBook = wrapNum(iBook, iLastBook, +1);
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
// ============================================================================

// ============================================================================
  function wrapNum(iNum, iMax, iSkip) {
// ============================================================================
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
// ============================================================================

// ============================================================================
  function setFields(){
// ============================================================================
    document.searchForm.book.value = "<?php echo $tBook; ?>";
    document.searchForm.chapter.value = "<?php echo $tChapter; ?>";
    document.searchForm.words.value = "<?php echo $tWords; ?>";
    document.searchForm.showMore.checked = "<?php echo $bShowMore; ?>";
    document.searchForm.highlightSW.checked = "<?php echo $bHighlightSW; ?>";
    document.searchForm.showOW.checked = "<?php echo $bShowOW; ?>";
  }
// ============================================================================

// ============================================================================
  function clearField(tName){
// ============================================================================
    document.getElementById(tName).value = '';
  }
// ============================================================================
</script>
        <div class="main Bible">
            <h1>The Bible</h1>
            <div class="subMain sectGeneral">
                <form name="searchForm" id="searchForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get" onsubmit="showWait();">

                <table class="searchTable">
                  <tbody>
                    <tr>
                      <td colspan="2">
                          Search by keyword or book or a combination<br />
                        <!-- <td colspan="2"><input type="text" name="words" id="words" value="<?php echo $tWords; ?>"></td> -->
                        <input type="text" name="words" id="words" placeholder="Enter phrase or word" value="">
                        <!-- <input type="checkbox" name="showMore" id="showMore"<?php if($bShowMore){ echo ' checked';} ?>> Show More? -->
                        <input type="button" value="x" onclick="clearField('words')">
                        <input type="checkbox" name="showMore" id="showMore" onclick="doSubmit()"> Show More?
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <input type="button" value="&lt;" onclick="doDirection('pb')">
                        Book
                        <input type="button" value="&gt;" onclick="doDirection('nb')">
                        <br />
                        <!--<input type="text" name="book" id="book" value="" list="books">-->
                        <!--<datalist name="books" id="books">-->
                        <select name="book" id="book" onchange="doSubmit(true)"> 
                         <option value="">Pick a book</option>'; 
<?php
  echo prepareDropdownBookList();
?>
                        <!--</datalist>-->
                         </select> 
                        <input type="button" value="x" onclick="clearField('book')">
                      </td>
                      <td>
                        <input type="button" value="&lt;" onclick="doDirection('pc')">
                        Chapter
                        <input type="button" value="&gt;" onclick="doDirection('nc')">
                        <br />
                        <input type="number" name="chapter" id="chapter" value="" onchange="doSubmit(true)">
                        <input type="button" value="x" onclick="clearField('chapter')">
                      </td>
                    </tr>
                    <tr>
                      <td><input type="reset" name="clearAll" id="clearAll" value="Clear All"></td>
                      <td><input type="submit" value="Search"></td>
                    </tr>
                  </tbody>
                </table>
                <p>For certain words where the meaning is difficult to translate,
                  <input type="checkbox" name="highlightSW" id="highlightSW" checked onclick="doSubmit(true)">highlight them
                  <input type="checkbox" name="showOW" id="showOW" onclick="doSubmit(true)">Show Hebrew/Greek
                </p>
              </form>
<?php
  echo passage($tBook, $tChapter, $tVerses, $tWords, $bShowMore);
?>
        <p>This is a minor adaptation of the <a href="https://worldenglishbible.org" target="_blank">WEB</a>
          to include nuanced meanings of particular ancient words for placenames,
          God and others of special interest. In general square brackets:[] are
          used to indicated words not found in the original text. It also indicates
          the 5 books of the Psalms; and a few passages considered by some to be
          of questionable authenticity.</p>
            </div>
        </div>
<?php

  mysqli_close($link);
  require_once 'footer.php';
?>
