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
                        Search by word or book or both<br />
                        <input type="search" name="words" id="words" placeholder="Enter phrase or word(s)" value="<?php echo $tWords; ?>">
                        <input type="checkbox" name="phrase" id="phrase" <?php if($bPhrase){echo 'checked';}; ?>
                               onclick="doSubmit('words')"><label><abbr title="If this is checked you'll get fewer results as it treats the words to the left as a phrase.">Phrase</abbr></label>
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
                        &nbsp;<abbr title="The Bible is a library of books. You can select one of them below">Book</abbr>&nbsp;
                        <input type="button" value="&gt;" onclick="doDirection('nb')">
                        <br />
                        <!--<input type="text" name="book" id="book" value="" list="books">-->
                        <!--<datalist name="books" id="books">-->
                        <select name="book" id="book"  onchange="doSubmit('book')">
                         <option value=""></option>;
<?php
  echo prepareDropdownBookList();
?>
                        <!--</datalist>-->
                        </select>
                        <!--<input type="button" value="Clear" onclick="clearField('book')">-->
                      </td>
                      <td>
                        <input type="button" value="&lt;" onclick="doDirection('pc')">
                        &nbsp;<abbr title="The books in The Bible are divided into chapters; once you&rsquo;ve picked a book, you can pick a chapter below.">Chapter</abbr>&nbsp;
                        <input type="button" value="&gt;" onclick="doDirection('nc')">
                        <br />
                        <select name="chapter" id="chapter" onchange="doSubmit('chapter')">
                          <option value=""><?php if ($tBook > ''){echo 'All';} ?></option>;
<?php
  echo prepareDropdownChapterList();
?>
                        </select>
                        <!--<input type="button" value="Clear" onclick="clearField('chapter')">-->
                      </td>
                    </tr>
                  </tbody>
                </table>
<?php require_once 'intWords.php'; ?>
              </form>
<?php
  echo passage($tBook, $tChapter, $tVerses, $tWords, $bPhrase);
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
