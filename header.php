<!DOCTYPE html>
<html lang="en">

<?php
  $thisFile = $_SERVER['PHP_SELF'];
  $thisFile = substr($thisFile, strlen($thisFile)-9);
  $bHome = ($thisFile == 'index.php');
?>

<head>
  <title>BibleStudyMan.co.uk</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" type="text/css" href="styles/resetRichardClark.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato|Londrina+Solid:300">
  <link rel="stylesheet" type="text/css" href="styles/general.css">
  <link rel="stylesheet" type="text/css" href="styles/pages.css">
  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
  <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
  <script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body>
  <div id="waitHint" class="modal">
    <!-- <img class="center" src="images/wait.gif" alt="Wait"> -->
  </div>
  <div class="content">
    <noscript>
      <h1 class="centerText">It seems that you currently don&rsquo;t have Javascript enabled. To get the best from this site, you will want to enable it.</h1>
    </noscript>

    <img class="banner-image" src="images/BibleBannerRainbow<?php if(! $bHome){echo 'Bot';} ?>.jpg" alt="pic of open bible on desk">

    <div class="menu">
      <!-- <div class="logo"> -->
        <!-- <a href="https://www.BibleStudyMan.co.uk">BibleStudyMan</a> -->
        <a href="https://www.BibleStudyMan.co.uk"><img class="logo" src="images/BSMLogo.png" alt="BibleStudyMan logo"></a>
      <!-- </div> -->

      <ul class="nav">
        <li><a href="index.php">Home</a></li>
        <li><a href="teaching.php">Teaching</a></li>
        <li><a href="readings.php">Readings</a></li>
        <li><a href="plan.php">Plan</a></li>
        <li><a href="bible.php">Bible</a></li>
        <li><a href="pricing.php">Pricing</a></li>
        <li><a href="support.php">Support This Ministry</a></li>
      </ul>
    </div>
