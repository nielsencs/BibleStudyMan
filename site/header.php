<!DOCTYPE html>
<html lang="en">

<?php
  $thisFile = $_SERVER['PHP_SELF'];
  $thisFile = substr($thisFile, strlen($thisFile)-9);
  $bHome = ($thisFile == 'index.php');
  require_once '../sqlCon.php';
?>

<head>
  <title>BibleStudyMan.co.uk</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet" type="text/css" href="styles/resetRichardClark.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato|Londrina+Solid:300">
  <link rel="stylesheet" type="text/css" href="styles/general.css">
  <link rel="stylesheet" type="text/css" href="styles/pages.css">
  
  <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
  <script src='https://www.google.com/recaptcha/api.js'></script>

  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">

  <link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="/icons/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="/icons/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
</head>

<body>
  <div id="waitHint" class="modal">
    <!-- <img class="center" src="images/wait.gif" alt="Wait"> -->
  </div>
  <div class="content">
    <noscript>
      <h1 class="centerText">It seems that you currently don&rsquo;t have Javascript enabled. To get the best from this site, you will want to enable it.</h1>
    </noscript>

    <img class="banner-image" src="images/BibleBannerRainbow<?php if(! $bHome){echo 'Bot';} ?>.jpg" alt="pic of open Bible on desk">

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
        <li><a href="support.php">Support Me</a></li>
      </ul>
    </div>
