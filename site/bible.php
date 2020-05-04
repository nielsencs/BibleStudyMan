<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'search.php';
 ?>

        <div class="main Bible">
            <h1>The Bible</h1>
            <div class="subMain sectGeneral">
                <form name="searchForm" id="searchForm" action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF');?>" method="get" onsubmit="showWait();">

                <table class="searchTable">
                  <tbody>
                    <tr>
                      <td colspan="2">
                        <!--Search by keyword or book or a combination<br />-->
                        <input type="search" name="words" id="words" placeholder="Enter phrase or word" value="<?php echo $tWords; ?>">
                        <input type="checkbox" name="showMore" id="showMore" <?php if($bShowMore){echo 'checked';}; ?> onclick="doSubmit('words')"><label>Show More?</label>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <input type="button" value="&lt;" onclick="doDirection('pb')">
                        &nbsp;Book&nbsp;
                        <input type="button" value="&gt;" onclick="doDirection('nb')">
                        <br />
                        <!--<input type="text" name="book" id="book" value="" list="books">-->
                        <!--<datalist name="books" id="books">-->
                        <select name="book" id="book" onchange="doSubmit('book'')">
                         <option value="">Pick a book</option>';
<?php
  echo prepareDropdownBookList();
?>
                        <!--</datalist>-->
                         </select>
                        <input type="button" value="Clear" onclick="clearField('book')">
                      </td>
                      <td>
                        <input type="button" value="&lt;" onclick="doDirection('pc')">
                        &nbsp;Chapter&nbsp;
                        <input type="button" value="&gt;" onclick="doDirection('nc')">
                        <br />
                        <input type="number" name="chapter" id="chapter" value="<?php echo $tChapter; ?>" onchange="doSubmit('chapter')">
                        <input type="button" value="Clear" onclick="clearField('chapter')">
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <input type="button" name="clearAll" id="clearAll" value="Clear All" onclick="clearAllFields('bible')">
                        &nbsp;&nbsp;&nbsp;
                        <input type="submit" value="Search">
                      </td>
                    </tr>
                  </tbody>
                </table>
                <p>For certain interesting words:<br />
                <input type="checkbox" name="highlightSW" id="highlightSW"
                  <?php if($bHighlightSW){echo 'checked';}; ?>
                       onclick="doSubmit()"><label>highlight them</label> 
                <input type="checkbox" name="showOW"      id="showOW"
                  <?php if($bShowOW){echo 'checked';}; ?>
                       onclick="doSubmit()"><label>Show Hebrew/Greek</label> 
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

  require_once 'footer.php';
?>
