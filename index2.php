<?php
  require_once 'header.php';
?>
<script type="text/javascript">
  var bFullHover = ! window.matchMedia("(hover:none), (hover:on-demand)").matches; //returns true or false

  window.onload = function(){
    if (bFullHoover){
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
    // elem2 = document.getElementByClass('readmore');
    if (bShow) {
      elem.style.display = "block";
      elem.style.height = "auto";
      // elem2.style.display = "none";
    } else {
      elem.style.display = "none";
      // elem.style.height = 0;
      // elem2.style.display = "block";
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
       onmouseout="alert('teachBlock');">
      <h2>1. Online Teaching Sessions</h2>
      <p>Have I learnt anything by reading The Bible from-cover-to-cover?</p>
      <p class="centerText">Read more...</p>
      <div id="teachBlock" class="collapse">
        <p>I hope so! I also learnt from my time wrestling with clinical depression.
        What I&rsquo;ve come to call The Nielsen Creed has it&rsquo;s roots in that time:
        God is God. God is Good. I am His. And He is mine. I unpack this and other
        subjects in this growing library of video and audio sessions.</p>
        <!-- <p>These are both live and recorded. On the live sessions interaction is encouraged.</p> -->
        <!-- <p>These are both live and recorded. On the live sessions interaction -->
        <!-- is encouraged. These are available as mp3s and videos.</p> -->
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
        <p>There&rsquo;ll be none of that here!
        <p>These are available as mp3s and videos and are intended to bring
        a more dynamic realisation of the scriptures by a full-on
        &rsquo;snot-and-tears&rsquo; reading. I do not pretend that my delivery
        accurately reflects the mood or emotional tone of the original, but
        merely offer it as an alternative to the more traditional formal
        reading style as an aid to discussion.</p>
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
        <p>If not then this day-by-day plan I devised will help you to read the
        entire bible in one year. It takes less tham half an hour a day (even if you&rsquo;re a slow reader like me!)</p>
          <!-- p>The day-by-day plan I devised for reading the entire Bible in one
          year based on four traditional sections - The Law (Torah or Pentateuch);
          The Prophets (Neviim); The Writings or Poetry (Ktuviim) and The
          New Testament.</p> -->
<?php
  // echo daysReadingsAsSentence($month, $day);
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
          Adonai - The Lord (as opposed to The Lord);
          YAHWEH - The name of God.</p>
        <p>Or (perhaps more importantly?) four different words for love in the
          Greek texts that transform the traditional readings.</p>
        <p>I wanted a translation that gave these interesting difference the possibility
          of being discovered, but couldn&rsquo;t find anything that worked for me. The
          interlinear texts which seemed so promising to begin with seemed too
          clumsy to me. Anyway I discovered there was a public domain modern English
          translation and realised I could build it myself (easier said than done!)
        </p>
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
