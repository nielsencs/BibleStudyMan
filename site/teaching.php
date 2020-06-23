<?php
  require_once 'header.php';
  require_once 'dbFunctions.php';

  $atMeetings = getMeetings();
  $iNumMeetings = sizeof($atMeetings);

  $tTime = 'today 9:00 am';                        // determine if BST part 1 - php
  $tZone = 'Europe/London';                        // determine if BST part 1 - php
  $iZoneMillis = strtotime($tTime . ' ' . $tZone); // determine if BST part 1 - php
  $iUTCMillis = strtotime($tTime . ' ' . 'UTC');   // determine if BST part 1 - php
?>

<script src="scripts/showHide.js"></script>
<script src="scripts/countdown.js"></script>

<script type="text/javascript">
  var oDate = new Date();
  var oTarget = [];
  var iEarliest = 7; // earliest time can join meeting in minutes
  var iLatest = 30;

  var iZoneMillis = '<?php echo $iZoneMillis; ?>'; // determine if BST part 2 - js
  var iUTCMillis = '<?php echo $iUTCMillis; ?>';   // determine if BST part 2 - js
  var iBST = 0;                                    // determine if BST part 2 - js
  if (iZoneMillis !== iUTCMillis){iBST = 1;}       // determine if BST part 2 - js
<?php
  for($i = 0; $i < $iNumMeetings; $i++){
?>

  oTarget.push(nextMeeting(oDate,     // a date object
                            <?php echo $atMeetings[$i]['meetingDay'];?>,         // 2=Tuesday
                            <?php echo substr($atMeetings[$i]['meetingTime'],0,2);?>,        // 19=7pm
                            iLatest,   // last time can join meeting in minutes
                            iBST));     // summer time
//  setInterval(function(){countDown(<?php echo $i;?>, '');}, 1000);
  setInterval(function(){countDown(<?php echo $i;?>, '<?php echo $atMeetings[$i]['meetingURL'];?>');}, 1000);

<?php
  }
?>
</script>

  <div class="main teaching">
    <h1>Online Teaching Sessions</h1>
<?php
  for($i = 0; $i < $iNumMeetings; $i++){
    echo showMeeting($atMeetings[$i], $i);
  }
?>
    <div class="subMain sectGeneral">
      <h2>Teaching Videos</h2>
      <div>
<?php
  echo videoList('T');
?>
      </div>
    </div>
  </div>
<?php
  require_once 'footer.php';

// ============================================================================
function getMeetings(){
// ============================================================================
  global $link;

  $atOutput = array();
  $tQuery = '';
  $tQuery .= 'SELECT * ';
  $tQuery .= 'FROM meetings ';
  $tQuery .= 'WHERE meetingActive;';

  $result = doQuery($link, $tQuery);

  if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
      array_push($atOutput,$row);
    }
  }
  mysqli_free_result($result);
  return $atOutput;
}
// ============================================================================

// ============================================================================
function showMeeting($row, $i){
// ============================================================================
  $tOutput = '';
  $tOutput .= '<div class="subMain plain">';
  $tOutput .= '<h2>' . $row['meetingName'] . '</h2>';
  $tOutput .= '<p class="centerText"><strong>';
  $tOutput .= jddayofweek($row['meetingDay']-1, 1) . 's at ' . $row['meetingTime'] . ' UK time.'; // which is usually 7am Pacific Time,';
  $tOutput .= '</strong></p>';

  $tOutput .= '<p>Zoom Meeting ID: <strong>' . $row['meetingRoom'] . '</strong>';
  $tOutput .= ' Password: <strong>' . $row['meetingPassword'] . '</strong></p>';
  $tOutput .= '<h3 class="centerText" id="counter' . $i . '">_</h3>';
  $tOutput .= '<p>' . $row['meetingDescription'] . '</p>';
  $tOutput .= '<p>This is a regular online Bible study group available on';
  $tOutput .= '  <a href="https://zoom.us" target="_blank">Zoom</a>. This is an open discussion';
  $tOutput .= '  where all opinions are free to be expressed and the only &lsquo;stupid&rsquo;';
  $tOutput .= '  question is the one you didn&rsquo;t ask!</p>';
  $tOutput .= '<input type="button" value="More" name="button' . $row['meetingID'] . '" id="button' . $row['meetingID'] . '" class="center" onclick="showHide2(\'' . $row['meetingID'] . '\');">';
  $tOutput .= '<div name="block' . $row['meetingID'] . '" id="block' . $row['meetingID'] . '" class="collapse">';
  $tOutput .= '  <p>All are welcome and you can access it online via the Zoom ';
  $tOutput .= '    <a href="' . $row['meetingURL'] . '" target="_blank">website</a>';
//  $tOutput .= '    incliding video or by phone for just the sound.';
//  $tOutput .= '     I would encourage you to have a Bible ready (you can use the one on this site) and';
  $tOutput .= '    Feel free to join in or just listen - whatever you feel most comfortable with.</p>';
  $tOutput .= '  <p>I may record these (with your permission) and post some parts';
  $tOutput .= '    of them here for others to learn from. Subjects covered include:</p>';

  $tOutput .= $row['meetingDetails'];

  $tOutput .= '  <p>';
  $tOutput .= 'Time: ' . jddayofweek($row['meetingDay']-1, 1) . 's at ' . $row['meetingTime'] . ' UK time.'; // which is usually 7am Pacific Time,';

//  $tOutput .= '    Time: Tuesdays at 3pm UK time which is usually 7am Pacific Time,';
//  $tOutput .= '    10am Eastern Time (but may differ by an hour either way a week or';
//  $tOutput .= '    so either side of changes to Daylight Saving). You can check it accurately at';
  $tOutput .= '    You can check when that is locally for you at';
  $tOutput .= '    <a href="https://www.thetimezoneconverter.com/?t=' . urlencode($row['meetingTime']) . '&tz=London&" target="_blank">TheTimeZoneConverter</a>';
  $tOutput .= '  </p>';
//  $tOutput .= '  <p>When it&rsquo;s time you can do <strong>ONE</strong> of the following:';
//  $tOutput .= '    <ul>';
//  $tOutput .= '      <li><a href="' . $row['meetingURL'] . '" target="_blank">Join the Zoom Meeting online</a> or</li>';
//  $tOutput .= '      <li>Ring <strong>0203 695 0088</strong> or <strong>0203 051 2874</strong> in the UK and enter the Meeting ID <strong>' . $row['meetingRoom'] . '</strong> or</li>';
//  $tOutput .= '      <li>Ring <strong>+1 646 558 8656</strong> (New York) in the US and enter the Meeting ID <strong>' . $row['meetingRoom'] . '</strong> or</li>';
//  $tOutput .= '      <li>Ring <strong>+1 408 638 0968</strong> (San Jose) in the US and enter the Meeting ID <strong>' . $row['meetingRoom'] . '</strong> or</li>';
//  $tOutput .= '      <li>Ring from elsewhere in the world, <a href="https://zoom.us/u/aeejTnNTZe" target="_blank">find your local number</a> and enter the Meeting ID <strong>' . $row['meetingRoom'] . '</strong></li>';
//  $tOutput .= '    </ul>';
//  $tOutput .= '  </p>';
  $tOutput .= '  <p>When it&rsquo;s time you can <a href="' . $row['meetingURL'] . '" target="_blank">join the Zoom Meeting online</a>.</p>';
  $tOutput .= '  <input type="button" value="Go there!" class="center" onclick="window.location.href = \'' . $row['meetingURL'] . '\'">';
  $tOutput .= '</div>';
  $tOutput .= '</div>';

  return $tOutput;
}
// ============================================================================
