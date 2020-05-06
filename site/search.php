<?php
  $tBook = filter_input(INPUT_GET, 'book', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tChapter = filter_input(INPUT_GET, 'chapter', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tVerses = filter_input(INPUT_GET, 'verses', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tWords = filter_input(INPUT_GET, 'words', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $bShowMore = filter_input(INPUT_GET, 'showMore', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
  $tMonth = filter_input(INPUT_GET, 'month', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tDay = filter_input(INPUT_GET, 'day', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tSortOrder = filter_input(INPUT_GET, 'sortOrder', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  if($tBook . $tWords . $tMonth > ''){
    $bHighlightSW = filter_input(INPUT_GET, 'highlightSW', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
  }else{
    $bHighlightSW = true;
  }
  $bShowOW = filter_input(INPUT_GET, 'showOW', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';

  if(!($tSortOrder > '')){
    $tSortOrder = 'orderChristian';
  }

  $iBook = 0;
  echo prepareBookList();
  $atStrongs = prepareStrongs();
?>

<script type="text/javascript">
// ============================================================================
  function doSubmit(tField) {
// ============================================================================
    bDoit = false;
    if(tField > ''){
      bDoit = (tField === 'chapter')||(document.getElementById(tField).value);
      if(tField === 'book'){
        clearField('chapter');
      }
    }else{
      bDoit = <?php if($tBook . $tWords . $tMonth . $tDay . $tSortOrder > ''){echo 'true';}else{echo 'false';} ?>;
    }
    if(bDoit){
        //    if(document.searchForm.words.value > '' || bJustDoit){
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
  }
// ============================================================================

// ============================================================================
  function doDirection(tDirection) {
// ============================================================================
    // alert(iBook);
    var iLastBook = 67; // to accomodate extra '2&3 John' book!
    var iChapter = parseInt('0' + document.searchForm.chapter.value);

    if(tDirection==='pb'){ //prev book
      if(iBook===0){
        iBook = 1;
      }
      iBook = wrapNum(iBook, iLastBook, -1);
      document.searchForm.chapter.value='';
    }
    if(tDirection==='nb'){ //next book
      if(iBook===0){
        iBook = iLastBook;
      }
      iBook = wrapNum(iBook, iLastBook, +1);
      document.searchForm.chapter.value='';
    }
    if(tDirection==='pc'){ //prev chapter
      if(iChapter === 0 || iChapter === 1){
        iBook = wrapNum(iBook, iLastBook, -1);
        // document.searchForm.chapter.value='';
        document.searchForm.chapter.value=atBooks[iBook][1];
      }else {
        iChapter--;
        document.searchForm.chapter.value=iChapter;
      }
    }
    if(tDirection==='nc'){ //next chapter
      // if(iChapter == 0 || iChapter == iChapterMax){
      if(iChapter === 0 || iChapter === atBooks[iBook][1]){
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
    oDate.setMonth(document.searchForm.month.value-1);
    oDate.setDate(document.searchForm.day.value-1);
    if(tDirection==='pd'){ //prev day
      oDate.setDate(oDate.getDate()-1);
    }
    if(tDirection==='nd'){ //next day
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
  function clearField(tName){
// ============================================================================
    document.getElementById(tName).value = '';
  }
// ============================================================================

// ============================================================================
  function clearAllFields(tCallingFile){
// ============================================================================
    if(tCallingFile==='bible'){
      document.searchForm.book.value = '';
      document.searchForm.chapter.value = '';
      document.searchForm.words.value = '';
      document.searchForm.showMore.checked = '';
    }
    if(tCallingFile==='plan'){
      document.searchForm.month.value = '';
      document.searchForm.day.value = '';
      document.searchForm.sortOrder.value = '';
    }
  }
// ============================================================================
</script>
