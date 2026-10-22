<?php
// Render-level checks of BSMT decision 0008 using a small in-memory database.
require_once __DIR__ . '/../dbFunctions.php';
require_once __DIR__ . '/../planFunctions.php';

$failures = 0;
function expectHas($label, $needle, $html) { global $failures; if (strpos($html, $needle) !== false) { echo "PASS: $label\n"; return; } $failures++; echo "FAIL: $label\nWanted: $needle\nIn: $html\n\n"; }
function expectNot($label, $needle, $html) { global $failures; if (strpos($html, $needle) === false) { echo "PASS: $label\n"; return; } $failures++; echo "FAIL: $label\nUnwanted: $needle\nIn: $html\n\n"; }
function countOf($needle, $html) { return substr_count($html, $needle); }

$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE books (bookCode TEXT, bookName TEXT)');
$pdo->exec('CREATE TABLE verses (bookCode TEXT, chapter INT, verseNumber INT, verseText TEXT)');
$pdo->exec("INSERT INTO books VALUES ('DEU','Deuteronomy'),('JUD','Jude'),('PSA','Psalms')");
$ins = $pdo->prepare('INSERT INTO verses VALUES (?,?,?,?)');
foreach ([['DEU',26,12,'Twelfth.'],['DEU',26,13,'Thirteenth.'],['DEU',27,1,'First of 27.'],['DEU',27,2,'Second of 27.'],
          ['JUD',1,1,'Jude one.'],['JUD',1,2,'Jude two.'],
          ['PSA',11,0,'For the Chief Musician.'],['PSA',11,1,'Psalm eleven one.'],['PSA',12,1,'Psalm twelve one.']] as $r) $ins->execute($r);

$bHighlightSW = false; $bShowOW = false; $bShowTN = false; $bExact = false; $bLimitSearchResults = true; $tWords = '';
list($base, $params) = basicPassageQuery();
function render($where, $params, $continuous = true) {
    global $base, $bHighlightSW, $bShowOW, $bShowTN;
    ob_start();
    $html = showVerses($base . $where . ' ORDER BY books.bookCode, verses.chapter, verses.verseNumber;', $params, '', $bHighlightSW, $bShowOW, $bShowTN, [], false, null, $continuous);
    ob_end_clean();
    return $html;
}

$deu = render(" WHERE books.bookName = 'Deuteronomy'", []);
expectHas('Deut: heading at start', 'Deuteronomy</a> <a', $deu);
expectHas('Deut: one heading only', '1', (string)countOf('<h3>', $deu));
expectHas('Deut: chapter-qualified marker', '<sup>27:1</sup>', $deu);
expectHas('Deut: plain marker for same chapter', '<sup>13</sup>', $deu);
expectNot('Deut: no qualified marker for first shown verse', '<sup>26:12</sup>', $deu);

$jude = render(" WHERE books.bookName = 'Jude'", []);
expectNot('Jude: no heading', '<h3>', $jude);
expectHas('Jude: plain markers', '<sup>2</sup>', $jude);
expectHas('Jude: 2 verses uses plural', '2 verses<br>', $jude);

$one = render(" WHERE books.bookName = 'Jude' AND verses.verseNumber = 1", []);
expectHas('one verse says 1 verse', '1 verse<br>', $one);
expectNot('one verse never says 1 verses', '1 verses', $one);

$psa = render(" WHERE books.bookName = 'Psalms'", []);
expectHas('Psalm: heading text', 'Psalm</a> <a', $psa);
expectHas('Psalm: heading for every Psalm', '2', (string)countOf('<h3>', $psa));
expectHas('Psalm: verse 0 text in heading', 'psalm-heading-intro', $psa);
expectNot('Psalm: no "Psalms" in a heading', 'Psalms</a>', $psa);

// Scattered search hits keep a heading wherever the book/chapter changes.
$hits = render(" WHERE books.bookName = 'Deuteronomy' AND verses.verseNumber = 1 OR (books.bookName='Deuteronomy' AND verses.chapter=26 AND verses.verseNumber=12)", [], false);
expectHas('search hits: heading per chapter', '2', (string)countOf('<h3>', $hits));

// Plan wording
$atRow = ['bookName' => 'Numbers', 'startChapter' => 33, 'startVerse' => 6, 'endChapter' => 33, 'endVerse' => 30];
expectHas('reference text shortens range', 'Numbers 33:6-30', daysReadingRefText($atRow));
$psRow = ['bookName' => 'Psalms', 'startChapter' => 46, 'startVerse' => 0, 'endChapter' => 0, 'endVerse' => 0];
expectHas('Psalms reference says Psalm', 'Psalm 46', daysReadingRefText($psRow));
expectNot('Psalms reference never says Psalms', 'Psalms', daysReadingRefText($psRow));

if ($failures) { echo "$failures failure(s)\n"; exit(1); }
echo "All Bible text render tests passed\n";
