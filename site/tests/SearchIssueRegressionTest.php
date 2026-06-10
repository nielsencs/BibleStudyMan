<?php
require_once __DIR__ . '/../dbFunctions.php';
require_once __DIR__ . '/../bibleSearchFunctions.php';

$failures = 0;
$coveredIssues = [];
$strictKnownOpenIssues = getenv('BSM_STRICT_ISSUE_TESTS') === '1';

$atBookAbbs = [
    'gen' => 'Genesis',
    'genesis' => 'Genesis',
    'mat' => 'Matthew',
    'matthew' => 'Matthew',
    '1 jn' => '1 John',
    '1 john' => '1 John',
    '1jn' => '1 John',
    'phm' => 'Philemon',
    'philemon' => 'Philemon',
];

function noteIssue(int $issue): void {
    global $coveredIssues;
    $coveredIssues[$issue] = true;
}

function passLine(string $message): void {
    echo "PASS: $message\n";
}

function failLine(string $message, $expected, $actual): void {
    global $failures;
    $failures++;
    echo "FAIL: $message\n";
    echo "Expected: " . var_export($expected, true) . "\n";
    echo "Actual:   " . var_export($actual, true) . "\n\n";
}

function assertSameIssue(int $issue, $expected, $actual, string $message, bool $knownOpenIssue = false): void {
    global $strictKnownOpenIssues;
    noteIssue($issue);
    if ($expected === $actual) {
        passLine("#$issue $message");
        return;
    }
    if ($knownOpenIssue && !$strictKnownOpenIssues) {
        echo "XFAIL: #$issue $message — known open issue\n";
        echo "Expected: " . var_export($expected, true) . "\n";
        echo "Actual:   " . var_export($actual, true) . "\n\n";
        return;
    }
    failLine("#$issue $message", $expected, $actual);
}

function assertContainsIssue(int $issue, string $needle, string $haystack, string $message, bool $knownOpenIssue = false): void {
    global $strictKnownOpenIssues;
    noteIssue($issue);
    if (strpos($haystack, $needle) !== false) {
        passLine("#$issue $message");
        return;
    }
    if ($knownOpenIssue && !$strictKnownOpenIssues) {
        echo "XFAIL: #$issue $message — known open issue\n";
        echo "Needle:  " . var_export($needle, true) . "\n";
        echo "Haystack:" . var_export($haystack, true) . "\n\n";
        return;
    }
    failLine("#$issue $message", "contains $needle", $haystack);
}

// Closed search regressions.
assertSameIssue(3, ['% almighty %'], get_search_strategy('Almighty ', false)['sql_params'], 'trailing spaces do not kill word search');
assertSameIssue(4, ['Genesis', '', '', ''], bookChapSearch('gen', '', ''), 'abbreviated book names can be found');
assertSameIssue(4, ['Matthew', '15', '3', ''], bookChapSearch('mat 15:3', '', ''), 'abbreviated book plus chapter:verse can be found');

$bHighlightSW = false;
$bShowOW = false;
$bShowTN = false;
$verseOutput = showVerse('16', ['bookName' => 'John', 'chapter' => 3, 'verseNumber' => 16, 'vt' => 'For God so loved the world'], ['god'], false);
assertContainsIssue(8, 'highlightVerse', $verseOutput, 'clicked-through verse is highlighted');
assertContainsIssue(8, 'highlightWord', $verseOutput, 'search word remains highlighted in clicked-through verse');

assertSameIssue(27, '<span class="highlightOW"><span class="highlightWord">God</span></span> <span class="highlightWord">tested</span>', highlightWords(['god', 'tested'], '<span class="highlightOW">God</span> tested', true), 'exact phrase highlighting survives interesting-word span');
assertSameIssue(51, 'god', normalise_search_text_for_matching('God{H0430}'), 'Strong numbers are removed before text search');
assertSameIssue(56, ['Matthew', '5', '', ''], bookChapSearch('5', 'Matthew', ''), 'number with selected book is treated as a chapter');
assertSameIssue(67, '<span class="highlightOW">ForeverOne</span> <span class="highlightWord">to</span> us', highlightWords(['to'], '<span class="highlightOW">ForeverOne</span> to us'), 'search for to does not corrupt highlight HTML tags');
assertSameIssue(146, '<span class="highlightWord">Paul</span> stood up', highlightWords(['paul'], 'Paul stood up'), 'word at beginning of line is highlighted');
assertSameIssue(168, 'you are the light of the world', normalise_search_text_for_matching('<span class="highlightWord">You</span> are the light of the world'), 'search ignores generated HTML highlight spans');
assertContainsIssue(170, 'highlightSW=on', buildLink('Matthew', 5, 'light', false, true, false, false), 'book/chapter links preserve interesting-word checkbox state');
assertSameIssue(171, 'God said light', strip_tags(highlightWords([], 'God said light')), 'cleared search leaves page text unhighlighted');

// Formerly open search bugs that now have executable regression coverage.
assertSameIssue(46, 'because foreverone your god', normalise_search_text_for_matching('Because ForeverOne{H3068} your God{H0430}'), 'exact searches can match interesting words carrying Strong tags');
assertSameIssue(68, ['', '', '', 'mat'], bookChapSearch('mat', '', ''), 'mat alone is treated as a word search rather than only Matthew the book');
assertSameIssue(69, 'a <span class="highlightWord">mat</span> matter mating Matred', highlightWords(['mat'], 'a mat matter mating Matred', true), 'exact single-word search highlights only the whole word');
assertSameIssue(102, ['1 John', '3', '16', ''], bookChapSearch('1 JN 3:16', '', ''), 'spaced numbered abbreviation 1 JN 3:16 is recognised');
assertSameIssue(102, ['1 John', '3', '16', ''], bookChapSearch('1jn 3:16', '', ''), 'compact numbered abbreviation 1jn 3:16 is recognised');
assertSameIssue(121, ['Philemon', '1', '11', ''], bookChapSearch('phm 11', '', ''), 'single-chapter book reference treats bare number as verse');
assertSameIssue(178, ['% god said light %'], get_search_strategy('god said light', true)['sql_params'], 'exact phrase search keeps word order while ignoring punctuation');
assertSameIssue(178, 'god said light is and there was light', normalise_search_text_for_matching('God{H0430} said, "Light is!" and there was light.'), 'exact search normalisation ignores punctuation and quotes');

// Search-related UI/performance issues that need browser or DB-backed tests later.
foreach ([1, 2, 9, 12, 13, 16, 17, 36, 65, 66, 91, 134] as $issue) {
    noteIssue($issue);
    echo "TODO: #$issue needs a browser, form-state, or DB-backed regression test\n";
}

$expectedSearchIssues = [1, 2, 3, 4, 8, 9, 12, 13, 16, 17, 27, 36, 46, 51, 56, 65, 66, 67, 68, 69, 91, 102, 121, 134, 146, 168, 170, 171, 178];
$missing = array_values(array_diff($expectedSearchIssues, array_keys($coveredIssues)));
if ($missing !== []) {
    failLine('search issue coverage inventory is missing issue numbers', [], $missing);
}

if ($failures > 0) {
    echo "\n$failures failure(s)\n";
    exit(1);
}

echo "\nSearch issue regression inventory passed\n";
