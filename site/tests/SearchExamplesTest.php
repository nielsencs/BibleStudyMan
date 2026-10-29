<?php
// BSMT example searches (shared with tcsb-mobile), run against database/bibleVerses.sql
// loaded into an in-memory SQLite table. SQLite stands in for MariaDB: LIKE is
// case-insensitive in both, and REGEXP word boundaries are translated below.
require_once __DIR__ . '/../dbFunctions.php';

$root = dirname(__DIR__, 2);
$examplesPath = getenv('BSMT_SEARCH_EXAMPLES') ?: dirname($root) . '/BSMT/tests/search-examples.json';
if (!is_file($examplesPath)) {
    echo "SKIP: BSMT search examples not found (set BSMT_SEARCH_EXAMPLES)\n";
    exit(0);
}
$examples = json_decode(file_get_contents($examplesPath), true)['examples'];

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->sqliteCreateFunction('REGEXP', function ($pattern, $text) {
    $pattern = str_replace(['[[:<:]]', '[[:>:]]'], '\\b', $pattern);
    return preg_match('/' . $pattern . '/i', (string)$text) ? 1 : 0;
}, 2);
$pdo->exec('CREATE TABLE verses (bookCode TEXT, chapter INT, verseNumber INT, verseText TEXT, versePlain TEXT)');
$pdo->exec('CREATE TABLE books (bookCode TEXT, bookName TEXT)');

$unescape = fn($s) => str_replace(["\\'", '\\"'], ["'", '"'], $s);
$insert = $pdo->prepare('INSERT INTO verses VALUES (?,?,?,?,?)');
$bookNames = [];
foreach (file($root . '/database/bibleVerses.sql') as $line) {
    if (!preg_match("/VALUES \\('([A-Z0-9]{3})',\\s*(\\d+),\\s*(\\d+),\\s*'(.*)', '(.*)'\\);\\s*\$/", $line, $m)) {
        continue;
    }
    $insert->execute([$m[1], (int)$m[2], (int)$m[3], $unescape($m[4]), $unescape($m[5])]);
}
foreach (file($root . '/database/bibleSchema.sql') as $line) {
    if (preg_match("/INTO books .*VALUES\\('([A-Z0-9]{3})', '([^']*)'/", $line, $m)) {
        $bookNames[$m[1]] = $m[2];
    }
}

$failures = 0;
function runSearch(PDO $pdo, string $query, bool $exact): array {
    $strategy = get_search_strategy($query, $exact);
    $stmt = $pdo->prepare('SELECT bookCode, chapter, verseNumber, versePlain FROM verses WHERE ' . $strategy['sql_where']);
    $stmt->execute($strategy['sql_params']);
    return $stmt->fetchAll();
}
function refOf(array $row, array $bookNames): string {
    return ($bookNames[$row['bookCode']] ?? $row['bookCode']) . ' ' . $row['chapter'] . ':' . $row['verseNumber'];
}
function check(bool $ok, string $label): void {
    global $failures;
    echo ($ok ? 'PASS: ' : 'FAIL: ') . $label . "\n";
    if (!$ok) { $failures++; }
}

foreach ($examples as $ex) {
    $label = '"' . $ex['query'] . '"' . ($ex['exact'] ? ' (exact)' : '');
    $rows = runSearch($pdo, $ex['query'], $ex['exact']);
    $refs = array_map(fn($r) => refOf($r, $bookNames), $rows);
    foreach ($ex['contains'] ?? [] as $ref) {
        check(in_array($ref, $refs, true), "$label finds $ref");
    }
    foreach ($ex['notContains'] ?? [] as $ref) {
        check(!in_array($ref, $refs, true), "$label does not find $ref");
    }
    if (isset($ex['everyHitContains'])) {
        $all = $rows !== [] && count(array_filter($rows, fn($r) => stripos($r['versePlain'], $ex['everyHitContains']) === false)) === 0;
        check($all, "$label: every result contains " . $ex['everyHitContains']);
    }
    if (isset($ex['moreThan'])) {
        check(count($rows) > count(runSearch($pdo, $ex['moreThan'], $ex['exact'])), "$label finds more than \"" . $ex['moreThan'] . '"');
    }
}

if ($failures > 0) {
    echo "\n$failures failure(s)\n";
    exit(1);
}
echo "\nAll BSMT example search tests passed\n";
