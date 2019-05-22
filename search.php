<?php
  $tBook = filter_input(INPUT_GET, 'book', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tChapter = filter_input(INPUT_GET, 'chapter', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tVerses = filter_input(INPUT_GET, 'verses', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tWords = filter_input(INPUT_GET, 'words', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $bShowMore = isset($_GET['showMore']);
  $tMonth = filter_input(INPUT_GET, 'month', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tDay = filter_input(INPUT_GET, 'day', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tSortOrder = filter_input(INPUT_GET, 'sortOrder', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $bHighlightSW = isset($_GET['highlightSW']);
  $bShowOW = isset($_GET['showOW']);

  $iBook = 0;
  echo prepareBookList();
  $atStrongs = prepareStrongs();
?>

<script type="text/javascript">
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
  function dayDirection(tDirection) {
// ============================================================================
    var oDate = new Date();
    oDate.setMonth(document.searchForm.month.value-1)
    oDate.setDate(document.searchForm.day.value-1)
    if(tDirection=='pd'){ //prev day
      oDate.setDate(oDate.getDate()-1);
    }
    if(tDirection=='nd'){ //next day
      oDate.setDate(oDate.getDate()+1);
    }
    document.searchForm.month.value = oDate.getMonth()+1;
    document.searchForm.day.value =   oDate.getDate()+1;
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
  function setFields(tCallingFile){
// ============================================================================
    // alert('book <?php echo $tBook; ?>' +
    //       'chapter <?php echo $tChapter; ?>' +
    //       'words <?php echo $tWords; ?>' +
    //       'showMore <?php echo $bShowMore; ?>' +
    //       'highlightSW <?php echo $bHighlightSW; ?>' +
    //       'showOW <?php echo $bShowOW; ?>' +
    //       'month <?php echo $tMonth; ?>' +
    //       'day <?php echo $tDay; ?>');
    if(tCallingFile=='bible'){
      document.searchForm.book.value = '<?php echo $tBook; ?>';
      document.searchForm.chapter.value = '<?php echo $tChapter; ?>';
      document.searchForm.words.value = '<?php echo $tWords; ?>';
      document.searchForm.showMore.checked = '<?php echo $bShowMore; ?>';
    }
    if(tCallingFile=='plan'){
      document.searchForm.month.value = '<?php echo $month; ?>';
      document.searchForm.day.value = '<?php echo $day; ?>';
      document.searchForm.sortOrder.value = '<?php echo $tSortOrder; ?>';
    }
    document.searchForm.highlightSW.checked = '<?php echo $bHighlightSW; ?>';
    document.searchForm.showOW.checked = '<?php echo $bShowOW; ?>';
  }
// ============================================================================

// ============================================================================
  function clearField(tName){
// ============================================================================
    document.getElementById(tName).value = '';
  }
// ============================================================================
</script>
