<?php
require_once 'header.php';

$years = $pdo->query("
  SELECT year, slug, summary
  FROM newsletter_years
  ORDER BY year DESC
");

$year_rows = $years->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
  SELECT year, month_num, label, title, slug, summary, emailoctopus_url
  FROM newsletters
  ORDER BY year ASC, month_num ASC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" type="text/css" href="styles/newsletter.css">

        <div class="main home">
            <h1>BSM Monthly Updates — Complete Archive (Jan 2022 - Aug 2025)</h1>
            <div class="subMain sectGeneral">
<p class='muted'>Compiled archive of monthly newsletters. Each entry includes a brief highlight summary. Use the
    table of contents to jump to any month.</p>
<div class='toc'>
    <h2>Table of Contents/Monthly Updates Archive</h2>

    <h3 id='2022'>2022</h3>
    <p><strong>Where it all began.</strong> Well, not quite - this project&apos;s roots go right back to 14th September 1980, but this was the year that I started the newsletter: a fresh focus on using God&apos;s name (YHWH) rather than hiding it behind “LORD,” regular tweaks to make the site easier to read, and a Bible-in-a-year plan to help you stay in the story. Alongside the work were honest, everyday moments—from prayers and chance encounters to Father-Christmas season—that kept everything rooted in real life and real faith.</p>
    <p>If you&apos;re new, these early updates set the tone: open-handed, thankful, and always inviting you to read, question, pray, and journey with me.</p>

<?php
// Group by year
$byYear = [];
foreach ($rows as $r) {
    $byYear[$r['year']][] = $r;
}
?>

<?php foreach ($byYear as $year => $items): ?>
    <h3><?= htmlspecialchars($year ?? '') ?></h3>
    <ul>
        <?php foreach ($items as $it): ?>
            <li>
                <a href="/updates/<?= urlencode($it['slug']) ?>">
                    <?= htmlspecialchars($it['label'] ?? '') ?>
                </a>
                <?php if (!empty($it['emailoctopus_url'])): ?>
                    &nbsp;— <a href="<?= htmlspecialchars($it['emailoctopus_url'] ?? '') ?>" rel="noopener">Original Email</a>
                <?php endif; ?>
                <br><small><?= htmlspecialchars($it['summary'] ?? '') ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>
            </div>
        </div>

<?php
    require_once 'footer.php';
?>
