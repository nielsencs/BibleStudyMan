<?php
	$tHeader = "";
	$tHeader .= "Content-Security-Policy: default-src 'self';";
	$tHeader .= " style-src 'self' 'unsafe-inline' fonts.googleapis.com;";
	$tHeader .= " font-src 'self' fonts.googleapis.com fonts.gstatic.com;";
	$tHeader .= " img-src 'self' www.paypalobjects.com  gallery.eo.page/tentacles/icons/v1/powered-by/otto.svg;";
	$tHeader .= " script-src 'self' 'unsafe-inline' www.google.com www.gstatic.com;";
	$tHeader .= " script-src-elem 'self' 'unsafe-inline' eocampaign1.com/form/7062f448-850b-11ec-9835-06b4694bee2a.js www.google.com/recaptcha/api.js www.gstatic.com/recaptcha/releases/ c6.patreon.com/becomePatronButton.bundle.js;";
  $tHeader .= " frame-src www.youtube.com w.soundcloud.com www.patreon.com;";

  header($tHeader);
?>
<!DOCTYPE html>
<html lang="en">

<?php
  $bHome = strpos(filter_input(INPUT_SERVER, 'SCRIPT_NAME'),'index.php') || strpos(filter_input(INPUT_SERVER, 'SCRIPT_NAME'),'home.php');
  require_once '../sqlCon.php';
?>

<head>
  <title>BibleStudyMan.co.uk</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" type="text/css" href="styles/resetRichardClark.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato|Londrina+Solid:300&display=swap">
  <link rel="stylesheet" type="text/css" href="styles/general.css">
  <link rel="stylesheet" type="text/css" href="styles/pages.css">

  <!-- script src="https://cmp.osano.com/AzZcqjS44O5M21NrU/dda2dc42-5678-4e89-b7d6-c6ea02b5d890/osano.js"></script -->

  <script src="scripts/jquery-3.5.1.min.js"></script>
  <script src='https://www.google.com/recaptcha/api.js'></script>

  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">

  <link rel="apple-touch-icon" sizes="57x57" href="icons/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="icons/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="icons/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="icons/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="icons/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="icons/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="icons/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="icons/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="icons/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="icons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="icons/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="icons/favicon-16x16.png">
  <link rel="manifest" href="manifest.webmanifest">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="icons/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  <script src="scripts/app.js"></script>
</head>

<body>
  <div id="waitHint" class="modal"></div>
  <div class="content">
    <noscript>
      <h1 class="centerText">It seems that you currently don&rsquo;t have Javascript enabled. To get the best from this site, you will want to enable it.</h1>
    </noscript>

    <img class="banner-image" src="images/BibleBannerRainbow<?php if(! $bHome){echo 'Bot';} ?>Low.jpg" alt="pic of open Bible on desk">

    <div class="menu">
      <a href="https://www.BibleStudyMan.co.uk"><img class="logo" src="images/BSMLogo.png" alt="BibleStudyMan logo"></a>

      <ul class="nav">
        <li><a href="home">Home</a></li>
        <li><a href="teaching">Teaching</a></li>
        <li><a href="readings">Readings</a></li>
        <li><a href="plan">Plan</a></li>
        <li><a href="bible">Bible</a></li>
        <li><a href="pricing">Pricing</a></li>
        <li><a href="supportMe">Support Me</a></li>
      </ul>
    </div>
