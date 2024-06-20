<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test ProcessStrongs</title>
  <link rel="stylesheet" type="text/css" href="styles/resetRichardClark.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato|Londrina+Solid:300&display=swap">
  <link rel="stylesheet" type="text/css" href="styles/general.css">
  <link rel="stylesheet" type="text/css" href="styles/pages.css">
</head>
<body>
    
</body>
</html>

<?php
require_once 'dbFunctions.php';
$atStrongs = array();

$atStrongs += ['H0430' => ['0', 'Elohim']];
$atStrongs += ['H3068' => ['1', 'Yahweh']];
// strongsNumber strongsOriginal strongsEnglish	
// H0430 Elohim God
// H3068 Yahweh ForeverOne


testIt('Oh ForeverOne{H3068} is God{H0430} and planted a garden eastward, in Eden, and there he put the man whom he had formed.');
testIt('He said, \"Blessed be ForeverOne{H3068}, the God{H0430} of Shem. Let Canaan be his servant.');

function testIt($tTestVerse){
  echo '<br><br><pre>' . $tTestVerse . '</pre>';
  echo '<pre>' . '012345----10---+----20---+----30---+----40---+----50---+----60---+----70---+----80' . '</pre><br><br>';
  echo processStrongs($tTestVerse, true, true, true). '<br><br>';
  echo processStrongs($tTestVerse, true, true, false). '<br><br>';
  echo processStrongs($tTestVerse, true, false, true). '<br><br>';
  echo processStrongs($tTestVerse, true, false, false). '<br><br>';
  echo processStrongs($tTestVerse, false, true, true). '<br><br>';
  echo processStrongs($tTestVerse, false, true, false). '<br><br>';
  echo processStrongs($tTestVerse, false, false, true). '<br><br>';
  echo processStrongs($tTestVerse, false, false, false). '<br><br>';
}