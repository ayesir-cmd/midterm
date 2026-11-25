<?php
use App\Helpers\View;
$token = $_SESSION['csrf_token'] ?? '';
$user = $_SESSION['user'] ?? null;
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
$title = $title ?? 'Rootsy';
$layout = $layout ?? 'app';
$active = $active ?? '';
$isAdmin = ($user['role_name'] ?? '') === 'Administrator';
$recordsActive = in_array($active, ['farmers', 'farms', 'crops', 'plantings', 'harvests', 'lookups'], true);
$settingsActive = in_array($active, ['settings', 'users', 'audit'], true);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#151C16">
  <title><?= View::e($title) ?> | Rootsy</title>
  <link rel="manifest" href="/manifest.webmanifest">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/assets/css/theme.css">
</head>
<body class="<?= $layout === 'auth' ? 'auth-body' : '' ?>">
<?php if ($layout === 'auth'): ?>
  <main>
    <?php require dirname(__FILE__) . '/' . ltrim($view, '/'); ?>
  </main>
<?php else: ?>
  <?php unset($_SESSION['flash_success'], $_SESSION['flash_error']); ?>
  <div class="app-shell">
    <aside class="sidebar" id="sidebar" aria-label="Primary navigation">
      <a class="brand" href="/" <?= $active === 'dashboard' ? 'aria-current="page"' : '' ?>>
        <span class="brand-mark"><img src="/assets/icons/rootsy-mark.png" alt=""></span><span>Rootsy</span>
      </a>
      <nav aria-label="Main">
        <button class="nav-group-toggle <?= $recordsActive ? 'active' : '' ?>" type="button"
                aria-expanded="<?= $recordsActive ? 'true' : 'false' ?>" aria-controls="recordsMenu">
          Records
        </button>
        <div class="nav-submenu" id="recordsMenu" <?= $recordsActive ? '' : 'hidden' ?>>
          <a class="<?= $active === 'farmers' ? 'active' : '' ?>" href="/farmers" <?= $active === 'farmers' ? 'aria-current="page"' : '' ?>>Farmers</a>
          <a class="<?= $active === 'farms' ? 'active' : '' ?>" href="/farms" <?= $active === 'farms' ? 'aria-current="page"' : '' ?>>Farms</a>
          <a class="<?= $active === 'crops' ? 'active' : '' ?>" href="/crops" <?= $active === 'crops' ? 'aria-current="page"' : '' ?>>Crops</a>
          <a class="<?= $active === 'plantings' ? 'active' : '' ?>" href="/plantings" <?= $active === 'plantings' ? 'aria-current="page"' : '' ?>>Plantings</a>
          <a class="<?= $active === 'harvests' ? 'active' : '' ?>" href="/harvests" <?= $active === 'harvests' ? 'aria-current="page"' : '' ?>>Harvests</a>
          <?php if ($isAdmin): ?>
            <a class="<?= $active === 'lookups' ? 'active' : '' ?>" href="/lookups" <?= $active === 'lookups' ? 'aria-current="page"' : '' ?>>Lookup Values</a>
          <?php endif; ?>
        </div>
        <a class="nav-link <?= $active === 'reports' ? 'active' : '' ?>" href="/reports" <?= $active === 'reports' ? 'aria-current="page"' : '' ?>>Reports</a>
        <button class="nav-group-toggle <?= $settingsActive ? 'active' : '' ?>" type="button"
                aria-expanded="<?= $settingsActive ? 'true' : 'false' ?>" aria-controls="settingsMenu">
          Settings
        </button>
        <div class="nav-submenu" id="settingsMenu" <?= $settingsActive ? '' : 'hidden' ?>>
          <a class="<?= $active === 'settings' ? 'active' : '' ?>" href="/change-password" <?= $active === 'settings' ? 'aria-current="page"' : '' ?>>Change Password</a>
          <?php if ($isAdmin): ?>
            <a class="<?= $active === 'users' ? 'active' : '' ?>" href="/users" <?= $active === 'users' ? 'aria-current="page"' : '' ?>>User Management</a>
            <a class="<?= $active === 'audit' ? 'active' : '' ?>" href="/audit" <?= $active === 'audit' ? 'aria-current="page"' : '' ?>>Audit Logs</a>
          <?php endif; ?>
        </div>
      </nav>
      <form method="post" action="/logout" class="logout-form">
        <input type="hidden" name="csrf_token" value="<?= View::e($token) ?>">
        <button type="submit" class="nav-logout">Logout</button>
      </form>
    </aside>
    <button class="menu-button" id="menuButton" type="button" aria-expanded="false" aria-controls="sidebar">Menu</button>
    <section class="main-content">
      <header class="page-header">
        <div><p class="eyebrow">Rootsy</p><h1><?= View::e($title) ?></h1></div>
        <?php if ($user): ?><div class="user-chip"><strong><?= View::e($user['full_name']) ?></strong><span><?= View::e($user['role_name']) ?></span></div><?php endif; ?>
      </header>
      <?php if ($success): ?><div class="alert alert-success"><?= View::e($success) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error"><?= View::e($error) ?></div><?php endif; ?>
      <main>
        <?php require dirname(__FILE__) . '/' . ltrim($view, '/'); ?>
      </main>
    </section>
  </div>
<?php endif; ?>
<script src="/assets/js/app.js"></script>
</body>
</html>
