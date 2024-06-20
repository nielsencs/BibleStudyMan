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

$atStrongs += ['H0430' => 'Elohim'];
$atStrongs += ['H3068' => 'Yahweh'];
// strongsNumber strongsOriginal strongsEnglish	
// H0430 Elohim God
// H3068 Yahweh ForeverOne


testIt('ForeverOne{H3068} God{H0430} planted a garden eastward, in Eden, and there he put the man whom he had formed.');
// testIt('He said, \"Blessed be ForeverOne{H3068}, the God{H0430} of Shem. Let Canaan be his servant.');

function testIt($tTestVerse){
  echo '<br><br><pre>' . $tTestVerse . '</pre>';
  echo '<pre>' . '012345----10---+----20---+----30---+----40---+----50---+----60---+----70---+----80' . '</pre><br><br><br>';
  echo processStrongs($tTestVerse, true, true, true). '<br>';
  echo processStrongs($tTestVerse, true, true, false). '<br>';
  echo processStrongs($tTestVerse, true, false, true). '<br>';
  echo processStrongs($tTestVerse, true, false, false). '<br>';
  echo processStrongs($tTestVerse, false, false, true). '<br>';
  echo processStrongs($tTestVerse, false, false, false). '<br>';
    
}