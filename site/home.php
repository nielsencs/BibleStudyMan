<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'planFunctions.php';
  require_once 'timeStamp.php';
?>

  <script src="scripts/showHide.js"></script>

  <div class='intro'>
    <a href="about.php"><img class="mugshot" src="images/BSMMugshot_Trimmed_280x280.jpg" alt="mugshot of Carl"/></a>
    <p>Welcome to the <strong>Bible Study Man</strong> website where I
      hope we can learn together what The Bible has to teach us. My aim
      is to bring this <strong>amazing book alive</strong> in a
      <strong>fresh, interesting and</strong> sometimes
      <strong>challenging</strong> way!</p>
    <p>I have divided the site into <strong>four main sections</strong> detailed below.
      Feel free to <strong>explore</strong> and if there&apos;s anything you can&apos;t
      find or understand - <a href="mailto:carl@BibleStudyMan.co.uk">get in touch</a>!</p>
    <p class="signature">Carl Nielsen, January 2018</p>
    <p>P.S. If you'd be happy to sign up for the monthly newsletter (see 
                the form at the bottom of this page) I'll keep in touch with you once
                a month (no more, I promise!) via email to let you know my progress.</p>
  </div>

  <div class="main home">
    <h1>The Four Main Sections</h1>
    <div class="subMain teaching">
      <a href="teaching.php"><h2>1. Online Teaching Sessions</h2></a>
      <p>Would you think I&apos;ve learnt anything by reading The Bible from-cover-to-cover many times over several years?</p>
      <input type="button" value="More" name="button1" id="button1" class="center" onclick="showHide2('1');">
      <div name="block1" id="block1" class="collapse">
        <p>I certainly hope I have! I also learnt from my time wrestling with
          clinical depression. What I&apos;ve come to call The Nielsen Creed has
          it&apos;s roots in that time:</p>
        <p class="centerText">God is God.</p>
        <p class="centerText">God is Good.</p>
        <p class="centerText">I am His.</p>
        <p class="centerText">And He is mine.</p>
        <p>I unpack this and other subjects in this growing library of video and
          audio sessions.</p>
        <p>On the live sessions interaction is encouraged.</p>
        <input type="button" value="Go there!" class="center" onclick="window.location.href = 'teaching.php'">
      </div>
    </div>
    <div class="subMain readings">
      <a href="readings.php"><h2>2. Dramatic Bible Readings</h2></a>
      <p>Have you noticed that people often read The Bible without emotion?</p>
      <input type="button" value="More" name="button2" id="button2" class="center" onclick="showHide2('2');">
      <div name="block2" id="block2" class="collapse">
        <p>There&apos;ll be none of that here! If you read the texts it is sometimes
          clear that the speaker is angry or sad, other times not so much. For example
          was Peter saying &ldquo;If YOU say so...&rdquo; or &ldquo;IF you SAY so...&rdquo;
          in Luke 5:5? We don't know but I feel that a little conjecture can help
          throw some light, providing we know we&apos;re guessing!</p>
        <!-- p>So these are my best guess (sometimes one of several &lsquo;best&rsquo;
          guesses) intended to ring the changes and bring The Bible to life with
          full-on &lsquo;snot-and-tears&rsquo; readings. I doubt my interpretation
          accurately reflects the mood or emotional tone of the original, but I
          think it&apos;s an interesting alternative to the usual formal reading style.</p -->
        <p>So what I'm attempting is to bring The Bible to life with very expressive
          readings. I doubt my interpretation accurately reflects the mood or emotional
          tone of the original, but I think it&apos;s an interesting and sometimes
          enlightening alternative to the usual formal reading style.</p>
        <input type="button" value="Go there!" class="center" onclick="window.location.href = 'readings.php'">
      </div>
    </div>
    <div class="subMain plan">
      <a href="plan.php"><h2>3. Bible Reading Plan</h2></a>
      <p>Have you ever tried to read The Bible from cover to cover?</p>
      <input type="button" value="More" name="button3" id="button3" class="center" onclick="showHide2('3');">
      <div name="block3" id="block3" class="collapse">
        <p>If not and you&apos;d like to, then this day-by-day plan will help you
          to read the entire Bible in one year. It takes less than half an hour
          a day even if you&apos;re a slow reader like me!</p>
        <p>The plan is based on four traditional sections - The Law (Torah or Pentateuch);
          The Prophets (Neviim); The Writings or Poetry (Ktuviim) and The
          New Testament.</p>
<?php
  if ($link) {
    echo '<p>As a sample, today&apos;s sections to read are ' . daysReadingsAsSentence($iMonth, $iDay, $bHighlightSW, $bShowOW, $bShowTN) . '. Enjoy!</p>';
  } else {
    echo '<p>A sample of one day&apos;s sections to read: <strong> Exodus Chapter 20 verses 1 to 17</strong>. Follow that by reading <strong> 1 Kings Chapter 10</strong>. The third part is <strong> Psalm  8 verses 4 to 5</strong>; and lastly <strong>from Luke Chapter 21 verse 25 to Chapter 22 verse 6</strong> Enjoy!</p>';
  }
?>
        <!-- p>If you'd rather read from your own Bible (there&apos;s nothing quite like a real book is there?) click here for <a href="planTable.php">the entire year&apos;s readings as a list.</a></p -->
        <input type="button" value="Go there!" class="center" onclick="window.location.href = 'plan.php'">
      </div>
    </div>
    <div class="subMain Bible">
      <a href="bible.php"><h2>4. The Bible</h2></a>
      <p>Do you know the difference between &lsquo;God&rsquo;, &lsquo;
      <span class="small-caps">The Lord</span>&rsquo; (as opposed to &lsquo;The
      Lord&rsquo;) & &lsquo;God Almighty&rsquo;?</p>
      <input type="button" value="More" name="button4" id="button4" class="center" onclick="showHide2('4');">
      <div name="block4" id="block4" class="collapse">
        <p>No? neither do I! Well that's a bit strong; what I mean is, it's not
          obvious which relates to the original words for God; and the differences
          are interesting. I&apos;m more clued up about &lsquo;elohim&rsquo; -
          the closest to our English &lsquo;god&rsquo;; &lsquo;Adonai&rsquo; -
          &lsquo;The Lord&rsquo;; &lsquo;YAHWEH&rsquo; - The name of God (the one
          that&apos;s often rendered &lsquo;<span class="small-caps">The
          Lord</span>&rsquo; with the small caps) which I am rendering ForeverOne.
          Or the four different words for love in the Greek texts that transform the
          traditional readings.</p>
        <p>I wanted a translation that brought these interesting differences to light
          but couldn&apos;t find anything that worked for me. The interlinear
          texts which seemed so promising to begin with turned out to be way too
          clumsy to actually use on a daily basis it seemed to me; I am not an expert in Hebrew
          or Greek but have some knowledge and lots of resources to draw upon. Anyway I discovered there was
          a public domain modern English translation and realised I could build
          it myself from that... Easier said than done!</p>
        <p>So what I have is a minor adaptation of the World English Bible which
          includes nuanced meanings of particular ancient words for placenames,
          for God and others of special interest. This is a growing list and I
          welcome any suggestions for investigation and possible inclusion.</p>
        <!-- p>So what I have started (will I ever finish?) is a minor adaptation of
          the World English Bible which already differentiates Agape and Yahweh
          and others. It
          includes nuanced meanings of particular ancient words for placenames,
          for God and others of special interest. This is a growing list and I
          Welcome any suggestions for investigation and possible inclusion.
        </p -->
        <input type="button" value="Go there!" class="center" onclick="window.location.href = 'bible.php'">
      </div>
    </div>
  </div>
<?php
    require_once 'newsletterForm.php';
?>
<?php
  require_once 'footer.php';
?>
