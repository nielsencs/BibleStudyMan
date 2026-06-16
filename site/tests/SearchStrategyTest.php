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
assertSame(['%god said light%'], $exactLight['sql_params'], 'exact phrase search keeps simple literal LIKE matching');
assertContainsText('verses.verseText LIKE ?', $exactLight['sql_where'], 'exact phrase search uses the verse text directly');

$exactYouPhrase = get_search_strategy('you are the light of the world', true);
assertSame(
    ['%you are the light of the world%', '%you-all are the light of the world%'],
    $exactYouPhrase['sql_params'],
    'exact phrase search expands ordinary you to include TCSB you-all'
);

$literalYouAll = get_search_strategy('you-all', false);
assertSame(['%you-all%'], $literalYouAll['sql_params'], 'loose search keeps explicit TCSB you-all literal');
assertSame(['you-all'], $literalYouAll['highlight_words'], 'literal you-all highlights the explicit TCSB marker');

$ordinaryYou = get_search_strategy('you', false);
assertSame(['%you%', '%you-all%'], $ordinaryYou['sql_params'], 'loose search expands ordinary you to include TCSB you-all');

$exactGodsAnger = get_search_strategy("god's anger", true);
assertSame(['%god\'s anger%'], $exactGodsAnger['sql_params'], 'exact phrase search keeps apostrophe phrases literal');
assertSame(['god', 's', 'anger'], $exactGodsAnger['highlight_words'], 'exact highlighting receives normalised words for display matching');

assertSame(
    '<span class="highlightOW"><span class="highlightWord">God</span></span> <sub>(Elohim)</sub>&apos;s <span class="highlightWord">anger</span>',
    highlightWords(['god', 's', 'anger'], '<span class="highlightOW">God</span> <sub>(Elohim)</sub>&apos;s anger', true),
    'exact highlighting preserves HTML entities around possessives'
);

assertSame(
    '&apos;s',
    highlightWords(['apo'], '&apos;s', true),
    'highlighting never splits HTML entities'
);

assertSame(
    'God scattered <span class="highlightWord">God</span> <span class="highlightWord">said</span> <span class="highlightWord">Light</span>',
    highlightWords(['god', 'said', 'light'], 'God scattered God said Light', true),
    'exact highlighting marks only the matched word sequence, not every word occurrence'
);

assertSame(
    '<span class="highlightWord">You-all</span> <span class="highlightWord">are</span> <span class="highlightWord">the</span> <span class="highlightWord">light</span>',
    highlightWords(['you', 'are', 'the', 'light'], 'You-all are the light', true),
    'exact highlighting follows TCSB you-all normalisation'
);

assertSame(
    '<span class="highlightWord">Selflessly-love</span> your neighbor',
    highlightWords(['selflessly', 'love'], 'Selflessly-love your neighbor', true),
    'exact highlighting follows hyphen normalisation'
);

assertSame(
    '<span class="highlightWord">Selflessly-love</span> <span class="highlightWord">your</span> <span class="highlightWord">neighbor</span>',
    highlightWords(['love', 'your', 'neighbor'], 'Selflessly-love your neighbor', true),
    'exact highlighting can start inside a hyphenated normalised token and continue across words'
);

$looseSelfless = get_search_strategy('selflessly love', false);
assertSame(['%selflessly%', '%love%'], $looseSelfless['sql_params'], 'loose search keeps simple literal word matching');
assertContainsText(' AND ', $looseSelfless['sql_where'], 'loose search requires all words');

if ($failures > 0) {
    echo "\n$failures failure(s)\n";
    exit(1);
}

echo "\nAll search strategy tests passed\n";
