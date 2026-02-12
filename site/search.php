<?php
  $tBook = filter_input(INPUT_GET, 'book', FILTER_UNSAFE_RAW);
  $tWords = filter_input(INPUT_GET, 'words', FILTER_UNSAFE_RAW);
  if($tWords > ''){
    $tWords = trim($tWords);
  }else{
      $tWords = '';
  }
  if(empty($tBook)){ // can't have a chapter without a book
    $tChapter = '';
    $tVerses = '';
  } else {
    $tChapter = filter_input(INPUT_GET, 'chapter', FILTER_UNSAFE_RAW);
    if(empty($tChapter)){ // if chapter dropdown too small
      $tChapter = filter_input(INPUT_GET, 'chapterNext', FILTER_UNSAFE_RAW);
    }
    if(empty($tChapter)){ // can't have verses without a chapter
      $tVerses = '';
    } else {
        $tVerses = filter_input(INPUT_GET, 'verses', FILTER_UNSAFE_RAW);
    }
  }
  $bExact = filter_input(INPUT_GET, 'exact', FILTER_DEFAULT, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
  $tMonth = filter_input(INPUT_GET, 'month', FILTER_UNSAFE_RAW);
  $tDay = filter_input(INPUT_GET, 'day', FILTER_UNSAFE_RAW);
  if(empty($tDay)){ // in case dropdown has too few days
    $tDay = filter_input(INPUT_GET, 'dayNext', FILTER_UNSAFE_RAW);
  }
  $tSortOrder = filter_input(INPUT_GET, 'sortOrder', FILTER_UNSAFE_RAW);
  if(empty($tBook . $tWords . $tMonth)){ // no search yet - 'clean' page
    $bHighlightSW = true;
    $bShowOW = true;
    $bShowTN = true;
  }else{
    $bHighlightSW = filter_input(INPUT_GET, 'highlightSW', FILTER_DEFAULT, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
    $bShowOW =      filter_input(INPUT_GET, 'showOW',      FILTER_DEFAULT, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
    $bShowTN =      filter_input(INPUT_GET, 'showTN',      FILTER_DEFAULT, FILTER_FLAG_NO_ENCODE_QUOTES) === 'on';
  }
  if(empty($tSortOrder) || !strpos('orderChristian|orderJewish|orderChron1|orderChron2', $tSortOrder)){
    $tSortOrder = 'orderChristian';
  }

  $iBook = 0;
  echo prepareBookList();
  $atStrongs = prepareStrongs();
  $atBookAbbs = prepareBookAbbs();
?>

<script type="text/javascript" src="scripts/controlPanel.js"></script>
<script type="text/javascript">
// ============================================================================
  function doSubmit(tField) {
// ============================================================================
    bDoit = false;
    if(tField > ''){
//      bDoit = (tField === 'chapter')||(document.getElementById(tField).value);
      bDoit = (tField === 'chapter')||(tField === 'sortOrder')||(document.getElementById(tField).value);
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
</script>
