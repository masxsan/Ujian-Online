<?php
require_once __DIR__ . '/init.php';
if (current_user()) {
  header('Location: dashboard.php');
  exit;
}

$pdo = get_pdo();
$kelas = $pdo->query('SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf_or_fail();
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? 'Guru';
  $kelas_id = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
  $school_name = trim($_POST['school_name'] ?? '');

  if (!$name || !$email || !$password || !$role) {
    $error = 'Semua field wajib diisi.';
  } else if (find_user_by_email($email)) {
    $error = 'Email sudah terdaftar.';
  } else if (strtolower($role) === 'guru' && !$kelas_id) {
    $error = 'Guru wajib memilih kelas.';
  } else {
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, kelas_id, school_name, created_at) VALUES (?,?,?,?,?,?,NOW())');
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $kelas_id, $school_name]);
    $user = find_user_by_email($email);
    login_user($user);
    header('Location: dashboard.php');
    exit;
  }
}
include __DIR__ . '/templates/header.php';
?>
<div class="card" style="max-width:720px;margin:24px auto;">
  <h2>Registrasi</h2>
  <?php if ($error): ?><div style="color:#b91c1c;margin:8px 0;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
    <div class="row two">
      <div>
        <label>Nama Lengkap</label>
        <input type="text" name="name" required>
      </div>
      <div>
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div>
        <label>Kata Sandi</label>
        <input type="password" name="password" required>
      </div>
      <div>
        <label>Peran</label>
        <select name="role" id="role-select" required>
          <option value="Guru">Guru</option>
          <option value="Kepala Sekolah">Kepala Sekolah</option>
          <option value="Pengawas">Pengawas</option>
        </select>
      </div>
      <div id="kelas-field">
        <label>Pilih Kelas (untuk Guru)</label>
        <select name="kelas_id">
          <option value="">-- Pilih Kelas --</option>
          <?php foreach ($kelas as $k): ?>
            <option value="<?= (int)$k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="hint">Jika kelas belum ada, minta admin menambahkannya.</div>
      </div>
      <div>
        <label>Nama Sekolah</label>
        <input type="text" name="school_name" placeholder="contoh: SMPN 1 Wonogiri" required>
      </div>
    </div>
    <div style="margin-top:12px;">
      <button class="btn btn-primary" type="submit">Daftar</button>
      <a class="btn btn-outline" href="login.php">Sudah punya akun?</a>
    </div>
  </form>
</div>
<script>
  const roleSelect = document.getElementById('role-select');
  const kelasField = document.getElementById('kelas-field');
  function syncKelas(){
    if (!roleSelect) return;
    const v = roleSelect.value.toLowerCase();
    kelasField.style.display = (v === 'guru') ? 'block' : 'none';
  }
  roleSelect && roleSelect.addEventListener('change', syncKelas);
  syncKelas();
</script>
<?php include __DIR__ . '/templates/footer.php'; ?>

