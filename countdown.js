var oDate = new Date();
var iEarliest = 7 // earliest time can join meeting in minutes

var iLatest = 30;
var oTarget = nextMeeting(oDate,     // a date object
                          2,         // 2=Tuesday
                          19,        // 19=7pm
                          iLatest,   // last time can join meeting in minutes
                          iBST);     // summer time
// ============================================================================
var oCountdown = setInterval(function() {
// ============================================================================
  var oNow = new Date();

  var distance = oTarget - oNow;

  // Time calculations for days, hours, minutes and seconds
  var iD = Math.floor(distance / (1000 * 60 * 60 * 24));
  var iH = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  var iM = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var iS = Math.floor((distance % (1000 * 60)) / 1000);

  // Display the result in the element with id='counter'
  var tOutput = '';
  if (iS > 0){
    tOutput = iS + 's ';
  }
  if (iM > 0){
    tOutput = iM + 'm ' + tOutput;
  }
  if (iH > 0 || iD > 0){ // always display hours if more than 0 days away
    tOutput = iH + 'h ' + tOutput;
  }
  if (iD > 0){
    tOutput = iD + 'd ' + tOutput;
  }

  document.getElementById('counter').innerHTML = 'The next meeting is in ' + tOutput;

  if (distance < 1000 * 60 * 7) { // iEarliest mins to go - let 'em in early :)
    if (distance < -1000 * 60 * iLatest) { // let people join up to iLatest mins late
      document.getElementById('counter').innerHTML =
      // clearInterval(oCountdown);
      'The next meeting is in a week.';
      window.location.replace(window.location.pathname + window.location.search + window.location.hash);
    } else {
      if (distance < -1000 * 60 * 7) { // iEarliest mins in - not 'just starting'
        document.getElementById('counter').innerHTML =
        'The next meeting is <a href="https://zoom.us/j/' + tZoomMeetingTrimmed + '"" target="_blank">on right now - join us!</a>';
      } else {
        document.getElementById('counter').innerHTML =
        'The next meeting is <a href="https://zoom.us/j/' + tZoomMeetingTrimmed + '"" target="_blank">just starting - join us!</a>';
      }
    }
  }
}, 1000);
// ============================================================================

// ===========================================================================
function nextMeeting(oDate,           // today
                     iMeetDay,        // 0-6 sun-sat
                     iMeetTime,       // hour
                     iMeetMinutes,    // duration
                     iBST){           // summer time
// ===========================================================================
  var iYear = oDate.getUTCFullYear(); // 2019
  var iMonth = oDate.getUTCMonth();   // 0-11
  var iDate = oDate.getUTCDate();     // 1-31
  var iDay = oDate.getUTCDay();       // 0-6

  iMeetTime = iMeetTime - iBST;
  if(iDay == iMeetDay){ //the meeting's today!
    if(oDate.getUTCHours() >= iMeetTime){ // too late?
      if(oDate.getUTCHours() == iMeetTime){ // on time
        if(oDate.getUTCMinutes() > iMeetMinutes){ // too late
          iDate = iDate + 7;
        }
      } else { // also too late
        iDate = iDate + 7;
      }
    }
  } else {
    iDate = iDate + ((7 + iMeetDay - iDay) % 7);
  }
  return new Date(Date.UTC(iYear, iMonth, iDate, iMeetTime, 0)); // forces UTC
}
// ===========================================================================
