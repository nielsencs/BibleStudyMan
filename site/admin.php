<?php
// admin_newsletters.php
// Mini admin for BSM newsletters: login, list, edit (title, summary, EO URL).

declare(strict_types=1);
session_start();
require_once 'header.php';

// ❗ Generate a password hash via:  php -r "echo password_hash('CHOOSE_A_STRONG_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
const ADMIN_PASSWORD_HASH = 'REPLACE_WITH_password_hash_OUTPUT';

// --- helpers ---
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function admin_logged_in(): bool { return !empty($_SESSION['bsm_admin']); }

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}
function csrf_check(string $token): void {
  if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(400);
    exit('Bad CSRF token');
  }
}
function flash(?string $msg = null): ?string {
  if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
  $m = $_SESSION['flash'] ?? null;
  unset($_SESSION['flash']);
  return $m;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// --- auth gate ---
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $pass = $_POST['password'] ?? '';
  if (password_verify($pass, ADMIN_PASSWORD_HASH)) {
    $_SESSION['bsm_admin'] = true;
    flash('Welcome!');
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
  } else {
    flash('Incorrect password.');
  }
}
if ($action === 'logout') {
  session_destroy();
  header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
  exit;
}

if (!admin_logged_in()) {
  // --- login page ---
  $msg = flash();
  ?>
  <!doctype html>
  <html lang="en"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>BSM Admin — Login</title>
    <style>
      body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;line-height:1.5;margin:2rem;background:#f7f7fb}
      .card{max-width:420px;margin:5vh auto;background:#fff;border:1px solid #eee;border-radius:14px;padding:1.25rem 1.5rem;box-shadow:0 2px 16px rgba(0,0,0,.05)}
      .btn{padding:.6rem 1rem;border-radius:10px;border:1px solid #ddd;background:#fafafa;cursor:pointer}
      .btn-primary{background:#0b5fff;color:#fff;border-color:#0b5fff}
      .muted{color:#666}
      .msg{margin:.5rem 0;color:#b00020}
      label{display:block;margin:.5rem 0 .25rem}
      input[type=password]{width:100%;padding:.55rem;border:1px solid #ddd;border-radius:10px}
    </style>
  </head><body>
    <div class="card">
      <h1>BSM Admin</h1>
      <?php if ($msg): ?><div class="msg"><?= e($msg) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="login">
        <label>Password</label>
        <input type="password" name="password" required>
        <div style="margin-top:1rem;">
          <button class="btn btn-primary" type="submit">Log in</button>
        </div>
      </form>
      <p class="muted" style="margin-top:.75rem">Tip: put this file in a protected /admin folder.</p>
    </div>
  </body></html>
  <?php
  exit;
}

// --- actions (must be logged in) ---

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check($_POST['csrf'] ?? '');
  $id = (int)($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $summary = trim($_POST['summary'] ?? '');
  $eo = trim($_POST['emailoctopus_url'] ?? '');

  // validations
  if ($id <= 0) { http_response_code(400); exit('Invalid id'); }
  if ($title === '' || mb_strlen($title) > 255) {
    flash('Title is required and must be ≤ 255 chars.');
    header('Location: ?action=edit&id=' . $id);
    exit;
  }
  if ($eo !== '' && !filter_var($eo, FILTER_VALIDATE_URL)) {
    flash('EmailOctopus URL looks invalid.');
    header('Location: ?action=edit&id=' . $id);
    exit;
  }

  // update
  $stmt = $pdo->prepare("
    UPDATE bsm_newsletters
       SET title = :title,
           summary = :summary,
           emailoctopus_url = :eo
     WHERE id = :id
     LIMIT 1
  ");
  $stmt->execute([
    ':title' => $title,
    ':summary' => $summary,
    ':eo' => ($eo === '' ? null : $eo),
    ':id' => $id,
  ]);

  flash('Saved ✔');
  if (!empty($_POST['save_back'])) {
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
  } else {
    header('Location: ?action=edit&id=' . $id);
  }
  exit;
}

if ($action === 'edit') {
  $id = (int)($_GET['id'] ?? 0);
  if ($id <= 0) { http_response_code(404); exit('Not found'); }
  $stmt = $pdo->prepare("SELECT * FROM bsm_newsletters WHERE id = :id LIMIT 1");
  $stmt->execute([':id' => $id]);
  $item = $stmt->fetch();
  if (!$item) { http_response_code(404); exit('Not found'); }
  $msg = flash();
  ?>
  <!doctype html>
  <html lang="en"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit — <?= e($item['label']) ?> | BSM Admin</title>
    <style>
      body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;line-height:1.5;margin:2rem;background:#f7f7fb}
      .shell{max-width:900px;margin:0 auto}
      .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}
      a{color:#0b5fff;text-decoration:none}
      .card{background:#fff;border:1px solid #eee;border-radius:14px;padding:1rem 1.25rem;box-shadow:0 2px 16px rgba(0,0,0,.05)}
      label{display:block;margin:.75rem 0 .35rem;font-weight:600}
      input[type=text], input[type=url], textarea{
        width:100%;padding:.6rem;border:1px solid #ddd;border-radius:10px;background:#fff
      }
      textarea{min-height:160px}
      .grid{display:grid;gap:1rem}
      .row2{grid-template-columns:1fr 1fr}
      .btn{padding:.6rem 1rem;border-radius:10px;border:1px solid #ddd;background:#fafafa;cursor:pointer}
      .btn-primary{background:#0b5fff;color:#fff;border-color:#0b5fff}
      .btn-ghost{background:transparent}
      .muted{color:#666}
      .msg{margin:.5rem 0;color:#0b5fff}
      .danger{color:#b00020}
      .meta{display:flex;gap:1rem;color:#555;font-size:.95rem}
      .bar{display:flex;gap:.5rem;margin-top:1rem}
    </style>
  </head><body>
    <div class="shell">
      <div class="topbar">
        <div><a href="<?= e(strtok($_SERVER['REQUEST_URI'], '?')) ?>">← Back to list</a></div>
        <div><a href="?action=logout" class="danger">Log out</a></div>
      </div>

      <div class="card">
        <h1>Edit: <?= e($item['label']) ?></h1>
        <p class="meta">
          <span><strong>Date:</strong> <?= e($item['issue_date']) ?></span>
          <span><strong>Slug:</strong> <?= e($item['slug']) ?></span>
          <?php if (!empty($item['emailoctopus_url'])): ?>
            <span><a target="_blank" rel="noopener" href="<?= e($item['emailoctopus_url']) ?>">Open original email</a></span>
          <?php endif; ?>
        </p>
        <?php if ($msg): ?><div class="msg"><?= e($msg) ?></div><?php endif; ?>

        <form method="post">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <label for="title">Title</label>
          <input type="text" id="title" name="title" maxlength="255" required
                 value="<?= e($item['title']) ?>">

          <label for="summary">Summary</label>
          <textarea id="summary" name="summary" spellcheck="true"><?= e($item['summary'] ?? '') ?></textarea>

          <label for="eo">EmailOctopus URL (optional)</label>
          <input type="url" id="eo" name="emailoctopus_url"
                 placeholder="https://emailoctopus.com/..."
                 value="<?= e($item['emailoctopus_url'] ?? '') ?>">

          <div class="bar">
            <button class="btn btn-primary" type="submit">Save</button>
            <button class="btn" type="submit" name="save_back" value="1">Save & Back</button>
            <a class="btn btn-ghost" href="<?= e(strtok($_SERVER['REQUEST_URI'], '?')) ?>">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </body></html>
  <?php
  exit;
}

// default: list
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;

if ($year) {
  $stmt = $pdo->prepare("
    SELECT id, year, month_num, label, title, emailoctopus_url, updated_at
      FROM bsm_newsletters
     WHERE is_published = 1 AND year = :y
     ORDER BY year DESC, month_num DESC
  ");
  $stmt->execute([':y' => $year]);
} else {
  $stmt = $pdo->query("
    SELECT id, year, month_num, label, title, emailoctopus_url, updated_at
      FROM bsm_newsletters
     WHERE is_published = 1
     ORDER BY year DESC, month_num DESC
  ");
}
$rows = $stmt->fetchAll();

// build year filter
$years = $pdo->query("SELECT DISTINCT year FROM bsm_newsletters ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
$msg = flash();
?>
<!doctype html>
<html lang="en"><head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BSM Admin — Newsletters</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;line-height:1.5;margin:2rem;background:#f7f7fb}
    .shell{max-width:1100px;margin:0 auto}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}
    a{color:#0b5fff;text-decoration:none}
    .btn{padding:.5rem .8rem;border-radius:10px;border:1px solid #ddd;background:#fff;cursor:pointer}
    .tag{display:inline-block;padding:.15rem .5rem;border-radius:999px;border:1px solid #e1e1e1;background:#fff;color:#333;font-size:.85rem}
    table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #eee;border-radius:14px;overflow:hidden}
    th,td{padding:.65rem .75rem;border-bottom:1px solid #f0f0f0;text-align:left;vertical-align:top}
    th{background:#fafafa}
    tr:hover td{background:#fcfcff}
    .muted{color:#666}
    .msg{margin:.5rem 0;color:#0b5fff}
  </style>
</head><body>
  <div class="shell">
    <div class="topbar">
      <h1>BSM Admin — Newsletters</h1>
      <div>
        <form method="get" style="display:inline-block;margin-right:.5rem">
          <select name="year" onchange="this.form.submit()">
            <option value="">All years</option>
            <?php foreach ($years as $y): ?>
              <option value="<?= (int)$y ?>" <?= $year===$y?'selected':'' ?>><?= (int)$y ?></option>
            <?php endforeach; ?>
          </select>
          <noscript><button class="btn" type="submit">Go</button></noscript>
        </form>
        <a href="?action=logout">Log out</a>
      </div>
    </div>

    <?php if ($msg): ?><div class="msg"><?= e($msg) ?></div><?php endif; ?>

    <table>
      <thead>
        <tr>
          <th style="width:110px">Month</th>
          <th>Title</th>
          <th style="width:260px">EmailOctopus</th>
          <th style="width:140px">Updated</th>
          <th style="width:80px">Edit</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><span class="tag"><?= (int)$r['year'] ?>-<?= str_pad((string)$r['month_num'], 2, '0', STR_PAD_LEFT) ?></span><br><span class="muted"><?= e($r['label']) ?></span></td>
          <td><?= e($r['title']) ?></td>
          <td>
            <?php if (!empty($r['emailoctopus_url'])): ?>
              <a target="_blank" rel="noopener" href="<?= e($r['emailoctopus_url']) ?>">Open original</a>
            <?php else: ?>
              <span class="muted">—</span>
            <?php endif; ?>
          </td>
          <td><span class="muted"><?= e($r['updated_at']) ?></span></td>
          <td><a class="btn" href="?action=edit&id=<?= (int)$r['id'] ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body></html>
