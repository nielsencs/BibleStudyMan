<?php
  $tBook = filter_input(INPUT_GET, 'book', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tChapter = filter_input(INPUT_GET, 'chapter', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tVerses = filter_input(INPUT_GET, 'verses', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  $tWords = trim(filter_input(INPUT_GET, 'words', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
  $bPhrase = filter_input(INPUT_GET, 'phrase', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
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
  $atBookAbbs = prepareBookAbbs();
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
    var iMonth = parseInt(document.searchForm.month.value);
    var iDay = parseInt(document.searchForm.day.value);
    if(tDirection==='pd'){ //prev day
      if(iDay > 1){
        iDay --;
      }else{
        iMonth = wrapNum(iMonth, 12, -1);
        iDay = daysInMonth(iMonth, oDate.getFullYear());
      }
    }
    if(tDirection==='nd'){ //next day
      if(iDay < daysInMonth(iMonth, oDate.getFullYear())){
        iDay ++;
      }else{
        iMonth = wrapNum(iMonth, 12, +1);
        iDay = 1;
      }
    }
    document.searchForm.month.value = iMonth;
    document.searchForm.day.value =   iDay;
    showWait();
    document.searchForm.submit();
  }
// ============================================================================

// ============================================================================
function daysInMonth(iMonth, iYear){// calculate number of days in a month
// ============================================================================
/*
* days_in_month($iMonth, $iYear)
* David Bindel from PHP.net Manual
* Returns the number of days in a given month and year, taking into account leap years.
*
* $iMonth: numeric month (integers 1-12)
* $iYear: numeric year (any integer)
*
* Prec: $iMonth is an integer between 1 and 12, inclusive, and $iYear is an integer.
* Post: none
*/
// corrected by ben at sparkyb dot net
  return iMonth === 2 ? (iYear % 4 ? 28 : (iYear % 100 ? 29 : (iYear % 400 ? 28 : 29))) : ((iMonth - 1) % 7 % 2 ? 30 : 31);
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
      document.searchForm.phrase.checked = '';
    }
    if(tCallingFile==='plan'){
      document.searchForm.month.value = '';
      document.searchForm.day.value = '';
      document.searchForm.sortOrder.value = '';
    }
  }
// ============================================================================
</script>
