<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';

  $tSection = filter_input(INPUT_GET, 'section', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tBook = filter_input(INPUT_GET, 'book', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tChapter = filter_input(INPUT_GET, 'chapter', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tVerses = filter_input(INPUT_GET, 'verses', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tDays = filter_input(INPUT_GET, 'days', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $bShowMore = isset($_GET['showMore']);
  $bHighlightSW = isset($_GET['highlightSW']);
  $bShowOW = isset($_GET['showOW']);
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
  }
// ============================================================================

// ============================================================================
  function doSubmit(bJustDoit = false) {
// ============================================================================
    // if(wordCount(document.searchForm.days.value) > 0){
    if(document.searchForm.days.value > '' || bJustDoit){
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
    document.searchForm.section.value = "<?php echo $tSection; ?>";
    document.searchForm.book.value = "<?php echo $tBook; ?>";
    document.searchForm.chapter.value = "<?php echo $tChapter; ?>";
    document.searchForm.days.value = "<?php echo $tDays; ?>";
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
</script>
        <div class="main Bible">
            <h1>Plan Maker</h1>
            <div class="subMain sectGeneral">
                <form name="searchForm" id="searchForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get" onsubmit="showWait();">

                <table class="searchTable">
                  <tbody>
                    <tr>
                      <td>
                        Book
                        <br />
                        <input type="text" name="section" id="section" value="" list="sections">
                        <datalist name="sections" id="sections">
                        <option value="1TOR">Torah/Law</option>
                        <option value="2NV1">Nevi&lsquo;im/Prophets</option>
                        <option value="3KTV">K&lsquo;Tuvim/Writings</option>
                        <option value="5NCV">New Covenant/New Testament</option>
                        </datalist>

                        <input type="text" name="book" id="book" value="" list="books">
                        <datalist name="books" id="books">
<?php
  echo prepareDropdownBookList();
?>
                        </datalist>
                        <br />

                        <input type="button" value="&lt;" onclick="doDirection('pb')">
                        <input type="button" value="Clear" onclick="clearField('book')">
                        <input type="button" value="&gt;" onclick="doDirection('nb')">
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
                      <td colspan="2">Days<br />
                        <input type="button" value="Clear" onclick="clearField('days')">
                        <input type="text" name="days" id="days" value="365">
                        <input type="checkbox" name="showMore" id="showMore" onclick="doSubmit()"> Show More?
                        <br />
                        &lsquo;Special&rsquo; Words<br />
                        <input type="checkbox" name="highlightSW" id="highlightSW" onclick="doSubmit(true)">Highlight
                        <input type="checkbox" name="showOW" id="showOW" onclick="doSubmit(true)">Show Hebrew/Greek
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
  $text = planTest($tSection, $tBook, $tChapter, $tVerses, $tDays, $bShowMore);

  $chunk = strlen($text)/$tDays;

  for ($i = 0; $i < $tDays; $i++) {
    echo "<p><b>";
    echo 'Day ';
    echo $i + 1;
    echo "</b><br>" . substr($text, $i * $chunk, $chunk) . "</p>";
  }
?>
            </div>
        </div>
<?php

  require_once 'footer.php';

// ============================================================================
  function planTest($tSection, $tBook, $tChapter, $tVerses, $tDays, $bShowMore){
// ============================================================================
    global $link, $bHighlightSW, $bShowOW;

    $bProcessRequest = (strlen($tSection . $tBook . $tChapter . $tVerses . $tWords) > 0);

    // $tOutput = '';
    $tBaseQuery = basicMakerQuery();
    if ($bProcessRequest) {
      if (empty($tSection)) {
        if (empty($tBook)) {
          if (empty($tWords)) {
            $tOutput .=  '<p>I can&rsquo;t understand what you want - this is the beginning of The Bible:</p>';
            $tQuery = $tBaseQuery . ' WHERE books.bookName ="Genesis" AND verses.chapter=1 AND verses.verseNumber<10;';
          }else{
            $tQuery = $tBaseQuery . ' WHERE ' . addSQLWildcards($tWords, $bShowMore) . ';';
          }
        } else {
          if ($tBook == '2 & 3 John') {
            $tQuery = $tBaseQuery . ' WHERE books.bookName ="2 John" OR  books.bookName ="3 John"';
          } else {
            $tQuery = $tBaseQuery . ' WHERE books.bookName ="' . $tBook . '"';
          }
          if (empty($tChapter))
          {
            if (empty($tWords)) {
              $tQuery = $tQuery . ';';
            }else{
              $tQuery = $tQuery . ' AND  ' . addSQLWildcards($tWords, $bShowMore) . ';';
            }
          }else{
            $tQuery = $tQuery . ' AND verses.chapter=' . $tChapter;
            $tQuery = $tQuery . ';';
          }
        }
      } else {
        if($tSection == '2NV1'){
          $tSection = '%NV%';
        }
        $tQuery = $tBaseQuery . ' WHERE books.sectionCode LIKE "' . $tSection . '"';
      }
      $tOutput = showVerses($tQuery, '');
      return $tOutput;
    }
  }
// ============================================================================

// ============================================================================
  function basicMakerQuery(){
// ============================================================================
    $tBaseQuery = '';
    $tBaseQuery .= 'SELECT DISTINCT books.sectionCode, books.bookName, verses.chapter, verses.verseNumber, ';
    // $tBaseQuery .= 'REPLACE(REPLACE(verses.verseText, "[H430]", ""), "[H3068]", "") AS vt';
    // $tBaseQuery .= 'REPLACE(REPLACE(verses.verseText, "<H430>", ""), "<H3068>", "") AS vt';
    $tBaseQuery .= 'verses.verseText AS vt';
    $tBaseQuery .= ', books.bookName ';
    $tBaseQuery .= 'FROM verses INNER JOIN books ON verses.bookCode=books.bookCode ';

    return $tBaseQuery;
  }
// ============================================================================

?>
