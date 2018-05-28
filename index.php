<?php
  require_once 'header.php';
?>
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
    <a href="teaching.php" class="subMain teaching floatLeft">
      <h2>1. Online Teaching Sessions</h2>
      <p>Have I learnt anything by reading The Bible from-cover-to-cover?</p>
      <!-- I hope so! I also learnt from my time wrestling with clinical depression.
      What I've come to call The Nielsen Creed has it's roots in that time:
      God is God. God is Good. I am His. And He is mine. I unpack this and other subjects -->
      <!-- <p>These are both live and recorded. On the live sessions interaction is encouraged.</p> -->
            <!-- <p>These are both live and recorded. On the live sessions interaction -->
                <!-- is encouraged. These are available as mp3s and videos.</p> -->
      <p class="readmore">Read more...</p>
    </a>
    <a href="readings.php" class="subMain readings floatRight">
      <h2>2. Dramatic Bible Readings</h2>
      <p>Have you noticed that people often read The Bible without emotion?</p>
        <!-- Well there'll be none of that here!
            <p>These are available as mp3s and videos and are intended to bring
            a more dynamic realisation of the scriptures by a full-on
            'snot-and-tears' reading. I do not pretend that my delivery
            accurately reflects the mood or emotional tone of the original, but
            merely offer it as an alternative to the more traditional formal
            reading style as an aid to discussion.</p -->
      <p class="readmore">Read more...</p>
    </a>
    <a href="plan.php" class="subMain plan floatLeft">
      <h2>3. Bible Reading Plan</h2>
      <p>Have you read The Bible from cover to cover?</p>
      <!-- <p>If not then this day-by-day plan I devised will help you to read the entire bible in one year. It takes less tham half an hour a day (even if you're a slow reader like me!)</p> -->
          <!-- p>The day-by-day plan I devised for reading the entire Bible in one
          year based on four traditional sections - The Law (Torah or Pentateuch);
          The Prophets (Neviim); The Writings or Poetry (Ktuviim) and The
          New Testament.</p>
<?php
  echo daysReadingsAsSentence($month, $day);
?>
-->
      <p class="readmore">Read more...</p>
    </a>
    <a href="bible.php" class="subMain Bible floatRight">
      <h2>4. The Nielsen Edition of the World English Bible</h2>
      <p>Do you know the difference between 'God', 'The Lord' & 'God Almighty'?</p>
      <!-- p>This is a minor adaptation of the WEB to include the ancient words for placenames, for God and other words of special interest.</p -->
                  <!-- <p>This is a minor adaptation of the
                    <a href="https://worldenglishbible.org">WEB</a> to include the ancient words
                    for placenames, for God and other words of special interest.</p> -->

      <!-- <p>My minor adaptation of the WEB to include nuanced meanings of particular ancient words.</p> -->
      <p class="readmore">Read more...</p>
    </a>
  </div>
<?php
  require_once 'footer.php';
?>
