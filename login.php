<?php
require_once __DIR__ . '/init.php';
if (current_user()) {
  header('Location: dashboard.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf_or_fail();
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $user = find_user_by_email($email);
  if ($user && password_verify($password, $user['password_hash'])) {
    login_user($user);
    header('Location: dashboard.php');
    exit;
  } else {
    $error = 'Email atau kata sandi salah.';
  }
}
include __DIR__ . '/templates/header.php';
?>
<div class="card" style="max-width:520px;margin:24px auto;">
  <h2>Masuk</h2>
  <?php if ($error): ?><div style="color:#b91c1c;margin:8px 0;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Kata Sandi</label>
    <input type="password" name="password" required>
    <div style="margin-top:12px; display:flex; gap:8px;">
      <button class="btn btn-primary" type="submit">Masuk</button>
      <a class="btn btn-outline" href="register.php">Daftar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>

