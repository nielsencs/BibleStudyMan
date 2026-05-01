<div id="controlPanel">
  <!-- abbr-tooltip.js for mobile-friendly abbr tooltips -->
  <script src="../scripts/abbr-tooltip.js"></script>
  <style>
    .abbr-tooltip-box {
      position: absolute;
      z-index: 9999;
      background: #fffbe8;
      color: #222;
      border: 1px solid #888;
      border-radius: 6px;
      box-shadow: 0 2px 8px #0002;
      padding: 0.7em 2.2em 0.7em 1em;
      font-size: 1em;
      max-width: 90vw;
      min-width: 180px;
      line-height: 1.4;
      word-break: break-word;
    }
    .abbr-tooltip-close {
      position: absolute;
      top: 0.2em;
      right: 0.5em;
      background: none;
      border: none;
      font-size: 1.2em;
      color: #888;
      cursor: pointer;
    }
    .abbr-tooltip-close:active {
      color: #c00;
    }
  </style>
  <form name="searchForm" id="searchForm" action="home" method="get" onsubmit="doSubmit('');">
    <input type="hidden" name="priority" id="priority" value="<?php echo htmlspecialchars($tPriority ?? '', ENT_QUOTES, 'UTF-8'); ?>">

    <div id="planSection" class="panelStageSection">
      <button class="plan" onclick="dayDirection('pd')" style="display:none">&nbsp;&lt;&nbsp;</button>
      <table class="searchTable">
        <tbody class="plan">
          <tr>
            <td colspan="2">
              <!-- <input type="button" value="&lt;" class="dayButton" onclick="dayDirection('pd')"> -->
              <select name="month" id="month" onchange="doSubmit('month')">
<?php
  echo prepareDropdownMonthList($iMonth);
?>
              </select>
              <input type="hidden" name="dayNext" id="dayNext" value="">
              <select name="day" id="day" onchange="doSubmit('day')">
<?php
  echo prepareDropdownDayList($iDay, $tMonth, $iDaysInMonth);
?>
              </select>
            </td>
            <td>
              <input type="button" value="Today" onclick="dayDirection('today')">
              <!-- <input type="button" value="&gt;" class="dayButton" onclick="dayDirection('nd')"> -->
            </td>
          </tr>
        </tbody>
      </table>
      <button class="plan" onclick="dayDirection('nd')" style="display:none">&nbsp;&gt;&nbsp;</button>
    </div>

    <div id="findSection" class="panelStageSection">
      <table class="searchTable">
        <tbody class="Bible">
          <tr>
            <td colspan="3">
              <input type="search" name="words" id="words" placeholder="Search..." value="<?php echo htmlspecialchars($tWords ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <input type="checkbox" name="exact" id="exact" <?php if($bExact){echo 'checked';}; ?>
                     onclick="doSubmit('words')">
              <label for="exact"><abbr title="If this is checked you'll tend to
get fewer results as it treats the
words to the left as a phrase if
there are more than one or as the
exact word.">Exact</abbr></label>
            </td>
          </tr>
          <tr>
            <td colspan="3">
              <input type="button" name="clearAll" id="clearAll" value="Clear" onclick="clearAllFields('bible')">
              &nbsp;&nbsp;&nbsp;
              <input type="submit" value="Search" onclick="document.searchForm.priority.value = 'Bible';">
            </td>
          </tr>
          <tr>
            <td>
              <input type="button" value="&lt;" onclick="doDirection('pb')" style="display:none">
              &nbsp;<abbr title="The Bible is a library of books.
You can select one of them here.">Book</abbr>&nbsp;
              <input type="button" value="&gt;" onclick="doDirection('nb')" style="display:none">
              <br>
              <select name="book" id="book" onchange="doSubmit('book')">
                <option value=""></option>
<?php
  echo prepareDropdownBookList();
?>
              </select>
            </td>
            <td colspan="2">
              <input type="button" value="&lt;" onclick="doDirection('pc')" style="display:none">
              &nbsp;<abbr title="The books in The Bible are divided
into chapters; once you&apos;ve picked a
book, you can pick a chapter here.">Chapter</abbr>&nbsp;
              <input type="button" value="&gt;" onclick="doDirection('nc')" style="display:none">
              <br>
              <input type="hidden" name="chapterNext" id="chapterNext" value="">
              <select name="chapter" id="chapter" onchange="doSubmit('chapter')">
                <option value=""><?php if ($tBook > ''){echo 'All';} ?></option>
<?php
  echo prepareDropdownChapterList();
?>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
      <input type="hidden" name="verses" id="verses" value="<?php if ($tBook > ''){echo htmlspecialchars($tVerses ?? '', ENT_QUOTES, 'UTF-8');} ?>">
    </div>

    <div id="prefsSection" class="panelStageSection collapsed searchOptions">
      <p class="centerText">For certain <abbr title="Things like the various words for God
and words that can have a variety of
translations. For example in both
Hebrew and Greek a certain word 
can mean spirit or breath or breeze.">significant</abbr> words:</p>
      <input type="checkbox" name="highlightSW" id="highlightSW"
      <?php if($bHighlightSW){echo 'checked';} ?>
          onclick="doSubmit()"><label for="highlightSW"><span class="highlightOW">Highlight</span> them</label><br>
      <input type="checkbox" name="showOW" id="showOW"
      <?php if($bShowOW){echo 'checked';} ?>
          onclick="doSubmit()"><label for="showOW">Show in both languages</label><br>
      <input type="checkbox" name="showTN" id="showTN"
      <?php if($bShowTN){echo 'checked';} ?>
          onclick="doSubmit()"><label for="showTN">Prefer English</label>
    </div>
  </form>
</div>
