<div id="controlPanel">
  <form name="searchForm" id="searchForm" action="home" method="get" onsubmit="showWait();">
    <input type="hidden" name="priority" id="priority" value="Plan">

    <div id="planSection" class="panelStageSection">
      <table class="searchTable">
        <tbody class="plan">
          <tr>
            <td colspan="2">
              <select name="month" id="month" onchange="doSubmit('month')">
<?php
  echo prepareDropdownMonthList($iMonth);
?>
              </select>
              <input type="button" value="&lt;" onclick="dayDirection('pd')">
              <input type="hidden" name="dayNext" id="dayNext" value="">
              <select name="day" id="day" onchange="doSubmit('day')">
<?php
  echo prepareDropdownDayList($iDay, $tMonth, $iDaysInMonth);
?>
              </select>
              <input type="button" value="&gt;" onclick="dayDirection('nd')">
            </td>
            <td>
              <input type="button" value="Today" onclick="dayDirection('today')">
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div id="findSection" class="panelStageSection collapsed">
      <table class="searchTable">
        <tbody class="Bible">
          <tr>
            <td colspan="3">
              <input type="search" name="words" id="words" placeholder="Enter phrase or word(s)" value="<?php echo htmlspecialchars($tWords ?? '', ENT_QUOTES, 'UTF-8'); ?>">
              <br>
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
              <input type="submit" value="Search">
            </td>
          </tr>
          <tr>
            <td>
              <input type="button" value="&lt;" onclick="doDirection('pb')">
              &nbsp;<abbr title="The Bible is a library of books.
              You can select one of them here.">Book</abbr>&nbsp;
              <input type="button" value="&gt;" onclick="doDirection('nb')">
              <br>
              <select name="book" id="book" onchange="doSubmit('book')">
                <option value=""></option>
<?php
  echo prepareDropdownBookList();
?>
              </select>
            </td>
            <td colspan="2">
              <input type="button" value="&lt;" onclick="doDirection('pc')">
              &nbsp;<abbr title="The books in The Bible are divided
              into chapters; once you&apos;ve picked a
              book, you can pick a chapter here.">Chapter</abbr>&nbsp;
              <input type="button" value="&gt;" onclick="doDirection('nc')">
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
      <p>For certain <abbr
        title="Things like the various words for God
and words that can have a variety of
translations. For example in both
Hebrew and Greek a certain word 
can mean spirit or breath or breeze.">interesting</abbr> words:<br>
        <input type="checkbox" name="highlightSW" id="highlightSW"
        <?php if($bHighlightSW){echo 'checked';} ?>
            onclick="doSubmit()"><label for="highlightSW"><span class="highlightOW">highlight</span> them</label><br>
        <input type="checkbox" name="showOW" id="showOW"
        <?php if($bShowOW){echo 'checked';} ?>
            onclick="doSubmit()"><label for="showOW">Show Hebrew/Greek</label><br>
        <input type="checkbox" name="showTN" id="showTN"
        <?php if($bShowTN){echo 'checked';} ?>
            onclick="doSubmit()"><label for="showTN">Show Translated Names</label><br>
      </p>
    </div>
  </form>
</div>