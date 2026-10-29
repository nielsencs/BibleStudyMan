<?php
require_once __DIR__ . '/../dbFunctions.php';

$failures = 0;
function check($ok, $message) {
    global $failures;
    if ($ok) { echo "PASS: $message\n"; } else { $failures++; echo "FAIL: $message\n"; }
}

// The Plural you drop-down lives with the significant-words options (shared by BSM and TCSB).
$bFloaty = false; $bHighlightSW = true; $bShowOW = false; $bShowTN = false;
$tPluralYou = 'yall';
ob_start();
require __DIR__ . '/../shared/intWords.php';
$tHtml = ob_get_clean();
check(strpos($tHtml, 'id="pluralYou"') !== false, 'significant-words block holds the Plural you drop-down');
check(strpos($tHtml, '<option value="yall" selected>') !== false, 'chosen form is selected');
check(strpos($tHtml, 'savePluralYou') !== false, 'choice is remembered');

$tPanel = file_get_contents(__DIR__ . '/../controlPanel.php');
check(strpos($tPanel, 'id="pluralYou"') === false, 'search panel no longer holds the drop-down');

$tTcsbJs = file_get_contents(__DIR__ . '/../TCSB/scripts/controlPanel.js');
check(strpos($tTcsbJs, 'function savePluralYou') !== false, 'TCSB script remembers the choice');

if ($failures > 0) { echo "\n$failures failure(s)\n"; exit(1); }
echo "\nAll plural-you control tests passed\n";
