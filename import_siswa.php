<?php
require_once __DIR__ . '/init.php';
require_role(['Guru']);
$user = current_user();
$pdo = get_pdo();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf_or_fail();
  if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
    $message = 'Upload CSV gagal.';
  } else {
    $kelas_id = (int)($_POST['kelas_id'] ?? ($user['kelas_id'] ?? 0));
    $tmp = $_FILES['csv']['tmp_name'];
    $fh = fopen($tmp, 'r');
    if ($fh) {
      // Expect header: nisn,nama,gender
      $line = 0;
      $inserted = 0;
      while (($row = fgetcsv($fh)) !== false) {
        $line++;
        if ($line === 1 && preg_match('/nisn/i', $row[0] ?? '')) {
          continue; // header
        }
        $nisn = trim($row[0] ?? '');
        $nama = trim($row[1] ?? '');
        $gender = strtoupper(trim($row[2] ?? ''));
        if ($nama === '') continue;
        $stmt = $pdo->prepare('INSERT INTO siswa (kelas_id, nisn, nama, gender) VALUES (?,?,?,?)');
        $stmt->execute([$kelas_id, $nisn ?: null, $nama, in_array($gender, ['L','P'], true) ? $gender : null]);
        $inserted++;
      }
      fclose($fh);
      $message = "Import selesai: $inserted siswa.";
    } else {
      $message = 'Tidak bisa membaca file.';
    }
  }
}

include __DIR__ . '/templates/header.php';
?>
<div class="card" style="max-width:720px;margin:24px auto;">
  <h2>Import Siswa (CSV)</h2>
  <?php if ($message): ?><div style="margin:8px 0;"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
    <label>Kelas</label>
    <select name="kelas_id" required>
      <?php
        $kelas = $pdo->query('SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas')->fetchAll();
        foreach ($kelas as $k):
          $sel = ((int)$k['id'] === (int)($user['kelas_id'] ?? 0)) ? 'selected' : '';
      ?>
        <option value="<?= (int)$k['id'] ?>" <?= $sel ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
      <?php endforeach; ?>
    </select>
    <label>File CSV</label>
    <input type="file" name="csv" accept=".csv" required>
    <div class="hint">Format kolom: nisn,nama,gender. Baris header opsional.</div>
    <div style="margin-top:12px;">
      <button class="btn btn-primary" type="submit">Upload</button>
      <a class="btn btn-outline" href="dashboard.php">Kembali</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>

