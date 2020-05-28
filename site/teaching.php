<?php
  require_once 'header.php';
  // $tZoomMeeting = "285 995 1639";
  $tZoomMeeting = "405 426 880";
  $tZoomMeetingTrimmed = str_replace(' ', '', $tZoomMeeting);
  $tTime = 'today 9:00 am';
  $tZone = 'Europe/London';
  $iZoneMillis = strtotime($tTime . ' ' . $tZone);
  $iUTCMillis = strtotime($tTime . ' ' . 'UTC');
?>

<script src="scripts/showHide.js"></script>

<script type="text/javascript">
  var tZoomMeetingTrimmed = '<?php echo $tZoomMeetingTrimmed; ?>';
  var iZoneMillis = '<?php echo $iZoneMillis; ?>';
  var iUTCMillis = '<?php echo $iUTCMillis; ?>';
  var iBST = 0;
  if (iZoneMillis != iUTCMillis){iBST = 1;}
</script>
<script src="scripts/countdown.js"></script>

  <div class="main teaching">
    <h1>Online Teaching Sessions</h1>
    <!-- <div class="subMain plain">
      <h2>Further Along The Way...</h2>
      <p>Zoom Meeting ID: <strong><?php echo $tZoomMeeting; ?></strong></p>
      <h3 class="centerText" id="counter">_</h3>
      <p>This is a regular online Bible study group available on
        <a href="https://zoom.us" target="_blank">Zoom</a>. This is an open discussion
        where all opinions are free to be expressed and the only &lsquo;stupid&rsquo;
        question is the one you didn't ask!</p>
      <input type="button" value="More" name="button1" id="button1" class="center" onclick="showHide2('1');">
      <div name="block1" id="block1" class="collapse">
        <p>All are welcome and you can
          access it online via their <a href="https://zoom.us/j/<?php echo $tZoomMeetingTrimmed; ?>" target="_blank">website</a>
          incliding video or by phone for just the sound. I would encourage
          you to have a Bible ready (you can use the one on this site) and
          feel free to join in or just listen - whatever you feel most comfortable
          with.</p>
        <p>I do record these and will post some of them here for others to
          learn from. Subjects covered include:</p>
        <ul>
          <li>Isn't the Bible just some book?</li>
          <li>Born Again: Born of the Spirit</li>
          <li>Life and death</li>
          <li>Forgiveness</li>
          <li>What's the point in anything?</li>
          <li>The Power of the Holy Spirit</li>
          <li>What's God really like?</li>
          <li>The Cross: He Died, He Rose Again</li>
          <li>Who's responsible for evil?</li>
          <li>Jesus is Coming Back (how do we know?)</li>
          <li>Can I really trust Jesus?</li>
          <li>Listening & Talking to God</li>
          <li>Isn't Jesus just a myth?</li>
          <li>The Father: Knowing and Being Known</li>
          <li>How can I be free?</li>
          <li>Water Baptism</li>
          <li>How can there be a God?</li>
          <li>Breaking Bread: The Lord's Supper</li>
          <li>In an infinite universe anything can happen!</li>
          <li>Idols: Burn and Break</li>
          <li>I don't need people to have a relationship with God!</li>
          <li>The Generations: Sin, Curses & Blessings</li>
          <li>Inner Healing</li>
          <li>Go!</li>
          <li>We're saved by Grace, so why bother? </li>
          <li>Restitution: Paying Back & Making Amends</li>
          <li>I'm not a sinner! (oh really?)</li>
          <li>Leaving & Joining (sexual relationships)</li>
          <li>Is there a hell?</li>
          <li>What's the The Fruit of The Spirit all about?</li>
          <li>Once saved always saved?</li>
          <li>What does circumcision of the heart mean?</li>

        </ul>
        <p>
          Time: Tuesdays at 3pm UK time which is usually 7am Pacific Time,
          10am Eastern Time (but may differ by an hour either way a week or
          so either side of changes to Daylight Saving). You can check it accurately at
          <a href="https://www.thetimezoneconverter.com/?t=14%3A00&tz=London&" target="_blank">TheTimeZoneConverter</a>
        </p>
        <p>When it's time you can do <strong>ONE</strong> of the following:
          <ul>
            <li><a href="https://zoom.us/j/<?php echo $tZoomMeetingTrimmed; ?>" target="_blank">Join the Zoom Meeting online</a> or</li>
            <li>Ring <strong>0203 695 0088</strong> or <strong>0203 051 2874</strong> in the UK and enter the Meeting ID <strong><?php echo $tZoomMeeting; ?></strong> or</li>
            <li>Ring <strong>+1 646 558 8656</strong> (New York) in the US and enter the Meeting ID <strong><?php echo $tZoomMeeting; ?></strong> or</li>
            <li>Ring <strong>+1 408 638 0968</strong> (San Jose) in the US and enter the Meeting ID <strong><?php echo $tZoomMeeting; ?></strong> or</li>
            <li>Ring from elsewhere in the world, <a href="https://zoom.us/u/aeejTnNTZe" target="_blank">find your local number</a> and enter the Meeting ID <strong><?php echo $tZoomMeeting; ?></strong></li>
          </ul>
        </p>
        <input type="button" value="Go there!" class="center" onclick="window.location.href = 'https://zoom.us/j/<?php echo $tZoomMeetingTrimmed; ?>'">
      </div>
    </div> -->
    <div class="subMain plain">
            <h2>Teaching Videos</h2>
      <!-- <input type="button" value="More" name="button2" id="button2" class="center" onclick="showHide2('2');">
      <div name="block2" id="block2" class="collapse"> -->
      <div>
        <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FBibleStudyCarl%2Fvideos%2F567115487031461%2F&show_text=1&width=280" width="280" height="158" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>
        <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FBibleStudyCarl%2Fvideos%2F2199583323705807%2F&show_text=0&width=280" width="280" height="158" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>
        <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FBibleStudyCarl%2Fvideos%2F504843780047032%2F&show_text=0&width=560" width="280" height="158" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>
        <!-- <iframe src="https://www.youtube.com/embed/LhN9xm5FAWc" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe> -->
      </div>
    </div>
  </div>
<?php
    require_once 'footer.php';
?>
