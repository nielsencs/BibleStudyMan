<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';
  require_once 'planFunctions.php';
  require_once 'timeStamp.php';
?>
  <script src="scripts/showHide.js"></script>

  <div class='intro'>
    <a href="about"><img class="mugshot" src="images/BSMMugshot_Trimmed_280x280.jpg" alt="mugshot of Carl"/></a>
    <p>Welcome to the <strong>Bible Study Man</strong> website where I
      hope we can learn together what The Bible has to teach us. My aim
      is to bring this <strong>amazing book alive</strong> in a
      <strong>fresh, interesting and</strong> sometimes
      <strong>challenging</strong> way!</p>
    <p>I have divided the site into <strong>four main sections</strong> detailed below.
      Feel free to <strong>explore</strong> and if there&apos;s anything you can&apos;t
      find or understand - <a href="mailto:carl@BibleStudyMan.co.uk">get in touch</a>!</p>
    <p class="signature">Carl Nielsen, January 2018</p>
    <p>P.S. If you'd be happy to sign up for the <a href="https://biblestudyman.eo.page/" target="_blank" rel="noopener noreferrer">monthly newsletter</a>
       <!-- (see the form at the bottom of this page) -->
         I'll keep in touch with you once a month (no more, I promise!) via email to let you know my progress.</p>
  </div>

  <div class="main home">
    <h1>The Four Main Sections</h1>
    <div class="subMain Bible">
      <a href="bible"><h2>The Bible</h2></a>
      <p>Do you know the difference between <em><span class="small-caps">The Lord</span></em> and <em>The Lord</em>?</p>
      <p>Well, in <strong>English</strong> there isn't much! Especially, if you&apos;re dyslexic like me, you might have missed that the first one is in
        <span class="small-caps">small caps</span> and the second one isn&apos;t. But in the original <strong>Hebrew</strong>,
        the difference is huge. &lsquo;<span class="small-caps">The Lord</span>&rsquo; (small caps) is <em>Yahweh</em> -
        The special name of God which which he revealed to Moses in the famous <strong>I AM</strong> passage in
        <a href="bible?book=Exodus&chapterNext=&chapter=3&verses=14-16&highlightSW=on&showOW=on&showTN=on">Exodus 3:14-16</a>.
        The other &lsquo;The Lord&rsquo; is &lsquo;Adonai&rsquo; which is a title of respect rather than a name.
        I believe God wants his name to be <strong>revealed</strong> and not hidden behind one of his titles.
      </p>
      <!-- <p>No? neither do I! Well that's a bit strong; what I mean is, it's not
        obvious which relates to the original words for God; and the differences are interesting. I&apos;m more clued up about &lsquo;elohim&rsquo; -
        the closest to our English &lsquo;god&rsquo;; &lsquo;Adonai&rsquo; - &lsquo;The Lord&rsquo;; &lsquo;YAHWEH&rsquo; - The name of God (the one
        that&apos;s often rendered &lsquo;<span class="small-caps">The Lord</span>&rsquo; with the small caps) which I am rendering ForeverOne.</p> -->
      <!-- <p>Then there&apos;s &lsquo;Jesus&rsquo; - the English form of the Greek &lsquo;Iesous&rsquo; which is itself a rendering of the Hebrew &rsquo;Yehoshua&rsquo;
        meaning &lsquo;ForeverOne is salvation&rsquo; - so I&apos;m using &lsquo;ForeverOneSaves&rsquo;.</p> -->
      <p>And then there are different Greek words for love, the two main ones being
        selfless-love (agape) and deep-friendship (fileo) that are usually treated the same as simply &lsquo;love&rsquo; transforming the meaning.</p>
      <!-- <p>I wanted a translation that brought these interesting differences to light
        but couldn&apos;t find anything that worked for me. The interlinear
        texts which seemed so promising to begin with turned out to be way too
        clumsy to actually use on a daily basis it seemed to me; I am not an expert in Hebrew
        or Greek but have some knowledge and lots of resources to draw upon. Anyway I discovered there was
        a public domain modern English translation and realised I could build
        it myself from that... Easier said than done!</p>  -->
      <p>I wanted a translation that brought out these nuances, but existing interlinear texts were too clumsy
        for daily use. So I created a minor adaptation of the public-domain World English Bible. My version highlights these distinctions - 
        divine names, place names, and other key terms.
      </p>
      <p>That’s why I now render YHWH as ForeverOne and Adonai as LordOfMine. So the words can breathe again, as they were first spoken.</p>
        <!-- <p>I wanted a translation that highlighted these nuances, but existing interlinear texts were too clumsy
          for daily use. I’m no expert in Hebrew or Greek, but with some knowledge and plenty of resources, 
          I realized I could adapt a translation myself.</p>
        <p>So I’ve created a minor adaptation of the public-domain World English Bible, enriching it with these 
          nuanced renderings of key words — names of God, place names, and others of special interest.
          The list is growing, and I welcome suggestions for further exploration and inclusion.</p> -->
        <!-- <p>So what I have is a minor adaptation of the World English Bible which
          includes nuanced meanings of particular ancient words for placenames,
          for God and others of special interest. This is a growing list and I
          welcome any suggestions for investigation and possible inclusion.</p> -->
        <!-- p>So what I have started (will I ever finish?) is a minor adaptation of
          the World English Bible which already differentiates Agape and Yahweh
          and others. It
          includes nuanced meanings of particular ancient words for placenames,
          for God and others of special interest. This is a growing list and I
          Welcome any suggestions for investigation and possible inclusion.
        </p -->
        <input type="button" value="Go there!" class="center" onclick="window.location.href = 'bible'">
    </div>

    <div class="subMain plan">
      <a href="plan"><h2>Bible Reading Plan</h2></a>
      <p>Have you ever tried to read The Bible from cover to cover?</p>
        <p>If not and you&apos;d like to, then this day-by-day plan will help you
          to read the entire Bible in one year. It takes less than half an hour
          a day even if you&apos;re a slow reader like me!</p>
        <p>The plan is based on four traditional sections - The Law (Torah or Pentateuch);
          The Prophets (Neviim); The Writings or Poetry (Ktuviim) and The
          New Testament.</p>
<?php
  if ($pdo) {
    echo '<p>As a sample, today&apos;s sections to read are ' . daysReadingsAsSentence($iMonth, $iDay, true, true, true) . '. Enjoy!</p>';
  } else {
    echo '<p>A sample of one day&apos;s sections to read: <strong> Exodus Chapter 20 verses 1 to 17</strong>. Follow that by reading <strong> 1 Kings Chapter 10</strong>. The third part is <strong> Psalm  8 verses 4 to 5</strong>; and lastly <strong>from Luke Chapter 21 verse 25 to Chapter 22 verse 6</strong> Enjoy!</p>';
  }
?>
        <!-- p>If you'd rather read from your own Bible (there&apos;s nothing quite like a real book is there?) click here for <a href="planTable">the entire year&apos;s readings as a list.</a></p -->
        <input type="button" value="Go there!" class="center" onclick="window.location.href = 'plan'">
    </div>

    <div class="subMain readings">
      <a href="readings"><h2>Dramatic Bible Readings</h2></a>
      <p>Have you noticed that people often read The Bible without <strong>emotion</strong>?</p>
      <p>There&apos;ll be none of that here! If you read the texts it is sometimes clear that the speaker is <strong>angry or sad</strong>, other times not so much. For example
        was Peter saying &ldquo;If <em>YOU</em> say so...&rdquo; or &ldquo;<em>IF</em> you <em>SAY</em> so...&rdquo;
        in <a href="bible?book=Luke&chapterNext=&chapter=5&verses=5&highlightSW=on&showOW=on&showTN=on">Luke 5:5</a>? We can't know for sure.
        But I feel that a little conjecture can help throw some light, providing we know we&apos;re guessing!</p>
      <p>So what I'm attempting is to bring The Bible to life with very expressivereadings. I doubt my interpretation always accurately reflects the mood
        or emotional tone of the original, but I think it&apos;s an interesting and sometimes enlightening alternative to the usual formal reading style.</p>
      <input type="button" value="Go there!" class="center" onclick="window.location.href = 'readings'">
    </div>

    <div class="subMain teaching">
      <a href="teaching"><h2>Online Teaching Sessions</h2></a>
      <p>Would you think I&apos;ve <strong>learnt anything</strong> by reading The Bible <strong>from-cover-to-cover</strong> many times over several years?
      I certainly hope I have! I also learnt from my time wrestling with clinical depression. What I&apos;ve come to call <strong>The Nielsen Creed</strong> has
        it&apos;s roots in that time:</p>
      <p class="centerText"><strong>God is God</strong></p>
      <p class="centerText"><strong>God is Good</strong></p>
      <p class="centerText"><strong>I am His</strong></p>
      <p class="centerText">and <strong>He is mine</strong></p>
      <p>I unpack this and other subjects in this growing library of video and audio sessions.</p>
      <p>On the live sessions interaction is encouraged.</p>
      <input type="button" value="Go there!" class="center" onclick="window.location.href = 'teaching'">
    </div>
    
  </div>
<?php
    require_once 'newsletterForm.php';
?>
<?php
  require_once 'footer.php';
?>
