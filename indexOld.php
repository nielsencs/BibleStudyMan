<?php
  require_once 'header.php';
  require_once '../sqlCon.php';
  require_once 'dbFunctions.php';

  $timestamp = time();
  //$bLeap = (date("L", $timestamp)==1);
  $year = date("Y", $timestamp);
  $month = date("n", $timestamp);
  $day = date("j", $timestamp);
?>
<script type="text/javascript">
  var bFullHover = ! window.matchMedia("(hover:none), (hover:on-demand)").matches; // touchscreen without mouse gives false

  window.onload = function(){
    if (bFullHover){
      showHide('teachBlock', false);
      showHide('readBlock', false);
      showHide('planBlock', false);
      showHide('bibleBlock', false);
    } else {
      showHide('teachBlock', true);
      showHide('readBlock', true);
      showHide('planBlock', true);
      showHide('bibleBlock', true);
    }
  }

  function showHide(tElemName, bShow){
    elem = document.getElementById(tElemName);
    if (bShow) {
      elem.style.display = "block";
      // elem.style.height = "auto";
    } else {
      elem.style.display = "none";
      // elem.style.height = 0;
    }
  }
</script>

  <div class='intro'>
    <img class="mugshot" src="images/Carl-SmileSmall.png" alt="mugshot of Carl"/>
    <p>Welcome to the <strong>Bible Study Man</strong> website where I
      hope we can learn together what The Bible has to teach us. My aim
      is to bring this <strong>amazing book alive</strong> in a
      <strong>fresh</strong> and <strong>interesting</strong> (sometimes
      <strong>challenging</strong>) way!</p>
    <p>I have divided the site into four key sections detailed below.
      Feel free to explore and if there&rsquo;s anything you can&rsquo;t
      find or understand - get in touch!</p>
    <p class="signature">Carl Nielsen, January 2018</p>
  </div>

  <div class="main home">
    <h1>The Four Main Sections</h1>
    <a href="teaching.php" class="subMain teaching floatLeft"
       onmouseover="showHide('teachBlock', true);"
       onmouseout="showHide('teachBlock', false);">
      <h2>1. Online Teaching Sessions</h2>
      <p>Have I learnt anything by reading The Bible from-cover-to-cover?</p>
      <p class="centerText">Read more...</p>
      <div id="teachBlock" class="collapse">
        <p>I hope so! I also learnt from my time wrestling with clinical depression.
        What I&rsquo;ve come to call The Nielsen Creed has it&rsquo;s roots in
        that time:</p>
        <p>God is God.</p>
        <p>God is Good.</p>
        <p>I am His.</p>
        <p>And He is mine.</p>
        <p>I unpack this and other subjects in this growing library of video and
          audio sessions.</p>
        <!-- <p>On the live sessions interaction is encouraged.</p> -->
        <p class="centerText">Click to go there!</p>
      </div>
    </a>
    <a href="readings.php" class="subMain readings floatRight"
       onmouseover="showHide('readBlock', true);"
       onmouseout="showHide('readBlock', false);">
      <h2>2. Dramatic Bible Readings</h2>
      <p>Have you noticed that people often read The Bible without emotion?</p>
      <p class="centerText">Read more...</p>
      <div id="readBlock" class="collapse">
        <p>There&rsquo;ll be none of that here! If you read the texts it is sometimes
          clear that the author is angry or sad, other times not so much. For example
          was Peter saying &ldquo;If YOU say so...&rdquo; or &ldquo;IF you SAY so...&rdquo;
          in Luke 5:5? We don't know but I feel conjecture is helpful - providing
          we know that's what it is!</p>
        <p>These are intended to ring the changes and bring The Bible to life with
          full-on &rsquo;snot-and-tears&rsquo; readings. I doubt my intrpretation
          accurately reflects the mood or emotional tone of the original, but offer
          it as an alternative to the more traditional formal reading style.</p>
        <p class="centerText">Click to go there!</p>
      </div>
    </a>
    <a href="plan.php" class="subMain plan floatLeft"
       onmouseover="showHide('planBlock', true);"
       onmouseout="showHide('planBlock', false);">
      <h2>3. Bible Reading Plan</h2>
      <p>Have you read The Bible from cover to cover?</p>
      <p class="centerText">Read more...</p>
      <div id="planBlock" class="collapse">
        <p>If not and you&rsquo;d like to, then this day-by-day plan I devised
          will help you to read the entire bible in one year. It takes less than
          half an hour a day (even if you&rsquo;re a slow reader like me!)</p>
        <p>The plan is based on four traditional sections - The Law (Torah or Pentateuch);
          The Prophets (Neviim); The Writings or Poetry (Ktuviim) and The
          New Testament.</p>
<?php
  if ($link) {
    echo '<p>As a sample, today&rsquo;s bits to read are ' . daysReadingsAsSentence($month, $day) . '. Enjoy!</p>';
  }
?>
        <p class="centerText">Click to go there!</p>
      </div>
    </a>
    <a href="bible.php" class="subMain Bible floatRight"
       onmouseover="showHide('bibleBlock', true);"
       onmouseout="showHide('bibleBlock', false);">
      <h2>4. The Nielsen Edition of the World English Bible</h2>
      <p>Do you know the difference between &rsquo;God&rsquo;, &rsquo;
        <span class="small-caps">The Lord</span>&rsquo; (as opposed to &rsquo;The
        Lord&rsquo;) & &rsquo;God Almighty&rsquo;?</p>
      <p class="centerText">Read more...</p>
      <div id="bibleBlock" class="collapse">
        <p>No? neither do I! However I&rsquo;m more clued up about elohim - god(s);
          Adonai - The Lord; YAHWEH - The name of God.</p>
        <p>Or (perhaps more importantly?) four different words for love in the
          Greek texts that transform the traditional readings.</p>
        <p>I wanted a translation that brought these interesting difference to light
          but couldn&rsquo;t find anything that worked for me.</p>
        <p>The interlinear texts which seemed so promising to begin with turned
          out to be way too clumsy it seemed to me. Anyway I discovered there was
          a public domain modern English translation and realised I could build
          it myself from that... Easier said than done!</p>
        <p>So what I have is a (mostly planned) minor adaptation of the World
          English Bible which will include nuanced meanings of particular ancient
          words for placenames, for God and others of special interest.</p>
        <!-- <p>
          I&rsquo;ve tried to make it fast and
        </p> -->
        <p class="centerText">Click to go there!</p>
      </div>
    </a>
  </div>
<?php
  require_once 'footer.php';
?>
