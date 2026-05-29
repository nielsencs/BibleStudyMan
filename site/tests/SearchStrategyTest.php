<?php
require_once __DIR__ . '/../dbFunctions.php';

$failures = 0;

function assertSame($expected, $actual, $message) {
    global $failures;
    if ($expected !== $actual) {
        $failures++;
        echo "FAIL: $message\n";
        echo "Expected: " . var_export($expected, true) . "\n";
        echo "Actual:   " . var_export($actual, true) . "\n\n";
    } else {
        echo "PASS: $message\n";
    }
}

function assertContainsText($needle, $haystack, $message) {
    global $failures;
    if (strpos($haystack, $needle) === false) {
        $failures++;
        echo "FAIL: $message\n";
        echo "Needle:   " . var_export($needle, true) . "\n";
        echo "Haystack: " . var_export($haystack, true) . "\n\n";
    } else {
        echo "PASS: $message\n";
    }
}

assertSame(
    'god said light is and light became',
    normalise_search_text_for_matching('God{H0430} said, "Light is!" and light became!'),
    'normalisation removes Strong\'s tags and punctuation for issue #178'
);

assertSame(
    'you are the light of the world',
    normalise_search_text_for_matching('<p>You-all are the light of the world.'),
    'normalisation lets ordinary you match TCSB you-all'
);

$exactLight = get_search_strategy('god said light', true);
assertSame(['% god said light %'], $exactLight['sql_params'], 'exact search preserves word order as a normalised phrase');
assertContainsText("CONCAT(' ',", $exactLight['sql_where'], 'exact search uses padded normalised SQL text');
assertContainsText('REGEXP_REPLACE', $exactLight['sql_where'], 'exact search ignores punctuation via SQL normalisation');

$exactYouAll = get_search_strategy('you are the light of the world', true);
assertSame(['% you are the light of the world %'], $exactYouAll['sql_params'], 'exact search can look for ordinary phrase without TCSB plural marker');
assertContainsText("REPLACE(", $exactYouAll['sql_where'], 'exact search SQL includes replacements');
assertContainsText("'-all'", $exactYouAll['sql_where'], 'exact search SQL removes TCSB -all marker');

$exactGodsAnger = get_search_strategy("god's anger", true);
assertSame(['% god s anger %'], $exactGodsAnger['sql_params'], 'exact search still matches apostrophe possessive as normalised words');
assertSame(['god', 'anger'], $exactGodsAnger['highlight_words'], 'highlighting ignores standalone possessive s');

assertSame(
    '<span class="highlightOW"><span class="highlightWord">God</span></span> <sub>(Elohim)</sub>&apos;s <span class="highlightWord">anger</span>',
    highlightWords(['god', 'anger'], '<span class="highlightOW">God</span> <sub>(Elohim)</sub>&apos;s anger', true),
    'highlighting preserves HTML entities around possessives'
);

assertSame(
    '&apos;s',
    highlightWords(['apo'], '&apos;s', true),
    'highlighting never splits HTML entities'
);

$looseSelfless = get_search_strategy('selflessly love', false);
assertSame(['% selflessly %', '% love %'], $looseSelfless['sql_params'], 'loose search normalises punctuation into separate words');
assertContainsText(' AND ', $looseSelfless['sql_where'], 'loose search requires all words');

if ($failures > 0) {
    echo "\n$failures failure(s)\n";
    exit(1);
}

echo "\nAll search strategy tests passed\n";
