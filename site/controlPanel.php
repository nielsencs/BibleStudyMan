              <div id="controlPanel">
                <form name="searchForm" id="searchForm" action="<?php if ($bBible) { echo'bible'; } else { echo'plan'; } ?>" method="get" onsubmit="showWait();">

                <div id="searchSection">
<?php if ($bBible) { ?>
                  <table class="searchTable">
                    <tbody>
                      <tr>
                        <td colspan="2">
                          Search by word or book or both<br>
                          <input type="search" name="words" id="words" placeholder="Enter phrase or word(s)" value="<?php echo htmlspecialchars($tWords ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                          <br>
                          <input type="checkbox" name="exact" id="exact" <?php if($bExact){echo 'checked';}; ?>
                                 onclick="doSubmit('words')"><label for="exact"><abbr title="If this is checked you'll tend to
get fewer results as it treats the
words to the left as a phrase if
there are more than one or as the
exact word.">Exact</abbr></label>
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2">
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
                          <!--<input type="text" name="book" id="book" value="" list="books">-->
                          <!--<datalist name="books" id="books">-->
                          <select name="book" id="book"  onchange="doSubmit('book')">
                           <option value=""></option>
<?php
  echo prepareDropdownBookList();
?>
                          <!--</datalist>-->
                          </select>
                          <!--<input type="button" value="Clear" onclick="clearField('book')">-->
                        </td>
                        <td>
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
                          <!--<input type="button" value="Clear" onclick="clearField('chapter')">-->
                        </td>
                      </tr>
                    </tbody>
                  </table>
<?php } ?>
<?php if ($bPlan) { ?>
                <table class="searchTable">
                  <tr>
                    <td>
                      <select name="month" id="month" onchange="doSubmit('month')">
<?php
  echo prepareDropdownMonthList($iMonth);
?>
                      </select>
                    </td>
                    <td>
                      <input type="button" value="&lt;" onclick="dayDirection('pd')">
                      <input type="hidden" name="dayNext" id="dayNext" value="">
                      <select name="day" id="day" onchange="doSubmit('day')">
<?php
  echo prepareDropdownDayList($iDay, $tMonth, $iDaysInMonth);
?>
                      </select>
                      <input type="button" value="&gt;" onclick="dayDirection('nd')">
                      <input type="button" value="Today" onclick="dayDirection('today')">
                    </td>
                  </tr>
                </table>
<?php } ?>
                </div>
                <input type="hidden" name="verses" id="verses" value="<?php if ($tBook > ''){echo htmlspecialchars($tVerses ?? '', ENT_QUOTES, 'UTF-8');} ?>">
<?php require_once 'intWords.php'; ?>
              </form>
            </div><!-- controlPanel -->
