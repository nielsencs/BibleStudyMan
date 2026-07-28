<?php
// update.php?slug=monthly-update-2025-08 (or route param)
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("
  SELECT title, label, issue_date, summary, emailoctopus_url
  FROM bsm_newsletters
  WHERE slug = :slug AND is_published = 1
  LIMIT 1
");
$stmt->execute([':slug' => $slug]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) { http_response_code(404); echo "Not found"; exit; }
?>
<article>
  <h1><?= htmlspecialchars($item['title']) ?></h1>
  <p><em><?= htmlspecialchars($item['label']) ?> &middot; <?= htmlspecialchars($item['issue_date']) ?></em></p>

  <p><?= nl2br(htmlspecialchars($item['summary'])) ?></p>

  <?php if (!empty($item['emailoctopus_url'])): ?>
    <p><a target="_blank" rel="noopener" href="<?= htmlspecialchars($item['emailoctopus_url']) ?>">
      View the original newsletter on EmailOctopus
    </a></p>
  <?php endif; ?>
</article>
