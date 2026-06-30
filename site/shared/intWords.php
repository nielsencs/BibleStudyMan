<?php
  $intWordsWrap = $intWordsWrap ?? $bFloaty;
  $intWordsId = $intWordsId ?? 'wordsSection';
  $intWordsClass = $intWordsClass ?? 'searchOptions';
  $intWordsParagraphClass = $intWordsParagraphClass ?? '';
  $intWordsWrapperAttributes = '';
  if ($intWordsWrap) {
    $intWordsWrapperAttributes = ' id="' . htmlspecialchars($intWordsId, ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($intWordsClass, ENT_QUOTES, 'UTF-8') . '"';
  }
?>
<?php if ($intWordsWrap) { ?><div<?php echo $intWordsWrapperAttributes; ?>><?php } ?>
                  <p<?php if ($intWordsParagraphClass > '') { echo ' class="' . htmlspecialchars($intWordsParagraphClass, ENT_QUOTES, 'UTF-8') . '"'; } ?>>For certain <abbr
                   title="Things like the various words for God
and words that can have a variety of
translations. For example in both
Hebrew and Greek a certain word 
can mean spirit or breath or breeze.">significant</abbr> words:</p>
                  <input type="checkbox" name="highlightSW" id="highlightSW"
                  <?php if($bHighlightSW){echo 'checked';} ?>
                       onclick="doSubmit()"><label for="highlightSW"><span class="highlightOW">Highlight</span> them</label><br>
                  <input type="checkbox" name="showOW"      id="showOW"
                  <?php if($bShowOW){echo 'checked';} ?>
                       onclick="doSubmit()"><label for="showOW">Show in both languages</label><br>
                  <input type="checkbox" name="showTN"      id="showTN"
                  <?php if($bShowTN){echo 'checked';} ?>
                       onclick="doSubmit()"><label for="showTN">Prefer English</label><?php if (!$intWordsWrap) { ?><br><?php } ?>
<?php if ($intWordsWrap) { ?></div><?php } ?>
