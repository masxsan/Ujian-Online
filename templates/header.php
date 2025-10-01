<?php
require_once __DIR__ . '/../init.php';
$user = current_user();
$csrf = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= APP_NAME ?></title>
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf) ?>">
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="nav container">
      <div class="brand"><?= APP_NAME ?></div>
      <nav>
        <?php if ($user): ?>
          <span><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
          <a class="button" href="dashboard.php">Dashboard</a>
          <a class="button" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="button" href="login.php">Login</a>
          <a class="button" href="register.php">Registrasi</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main class="container">

