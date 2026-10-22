<?php
// Checks the shared Bible text formatting logic (BSMT decision 0008) against the shared fixtures.
require_once __DIR__ . '/../bibleFormat.php';

$failures = 0;
$fx = json_decode(file_get_contents(__DIR__ . '/bibleTextFixtures.json'), true);

function check($label, $expected, $actual) {
    global $failures;
    if ($expected === $actual) { echo "PASS: $label\n"; return; }
    $failures++;
    echo "FAIL: $label\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true) . "\n\n";
}

foreach ($fx['bookLabel'] as [$in, $out]) check("bookLabel $in", $out, bookLabel($in));
foreach ($fx['headingText'] as $c) check("headingText {$c['book']} {$c['chapter']}", $c['expected'], chapterHeadingText($c['book'], $c['chapter'], $c['bookChapters']));
foreach ($fx['formatRange'] as $c) check("formatRange {$c['expected']}", $c['expected'], formatRange($c['book'], $c['a'], $c['b']));
foreach ($fx['plural'] as $c) check("plural {$c['expected']}", $c['expected'], pluralise($c['n'], $c['singular'], $c['plural']));
foreach ($fx['readingsSentence'] as $c) check('readingsSentence ' . count($c['refs']), $c['expected'], readingsSentence($c['refs']));
foreach ($fx['planDayTitle'] as $c) check("planDayTitle {$c['expected']}", $c['expected'], planDayTitle($c['year'], $c['month'], $c['day']));
foreach ($fx['sequences'] as $seq) {
    $headings = []; $markers = []; $prev = null;
    foreach ($seq['verses'] as $i => [$book, $chapter, $verse]) {
        $h = headingForVerse($book, $chapter, $seq['bookChapters'], $prev, $i === 0);
        $headings[] = $h;
        $markers[] = verseMarkerText($book, $chapter, $verse, $prev, $h !== null);
        $prev = [$book, $chapter];
    }
    check($seq['name'] . ' headings', $seq['headings'], $headings);
    check($seq['name'] . ' markers', $seq['markers'], $markers);
}
if ($failures) { echo "$failures failure(s)\n"; exit(1); }
echo "All Bible text format tests passed\n";
