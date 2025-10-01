<?php
require_once __DIR__ . '/init.php';
require_login();
$user = current_user();
$pdo = get_pdo();

$role = strtolower($user['role']);
include __DIR__ . '/templates/header.php';
?>
<div class="card">
  <h2>Dashboard</h2>
  <?php if ($role === 'guru'): ?>
    <div class="row two">
      <a class="btn btn-primary" href="guru_jurnal.php">Tulis Jurnal Harian</a>
      <a class="btn btn-outline" href="import_siswa.php">Import Siswa (CSV)</a>
    </div>
    <h3 style="margin-top:16px;">Jurnal Saya</h3>
    <table class="table">
      <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php
        $stmt = $pdo->prepare('SELECT j.*, k.nama_kelas FROM jurnal j LEFT JOIN kelas k ON j.kelas_id=k.id WHERE j.user_id=? ORDER BY j.tanggal DESC, j.id DESC');
        $stmt->execute([$user['id']]);
        foreach ($stmt as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['tanggal']) ?></td>
            <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['mapel'] ?? '-') ?></td>
            <td><span class="status-badge <?= $row['status'] === 'submitted' ? 'status-submitted' : 'status-draft' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
            <td>
              <a class="btn btn-outline" href="guru_jurnal.php?id=<?= (int)$row['id'] ?>">Buka</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php elseif ($role === 'kepala sekolah'): ?>
    <h3>Jurnal Masuk</h3>
    <table class="table">
      <thead><tr><th>Tanggal</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php
        $stmt = $pdo->query('SELECT j.*, u.name as guru, k.nama_kelas FROM jurnal j JOIN users u ON j.user_id=u.id LEFT JOIN kelas k ON j.kelas_id=k.id WHERE j.status="submitted" ORDER BY j.tanggal DESC');
        foreach ($stmt as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['tanggal']) ?></td>
            <td><?= htmlspecialchars($row['guru']) ?></td>
            <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['mapel'] ?? '-') ?></td>
            <td>
              <a class="btn btn-primary" href="jurnal_view.php?id=<?= (int)$row['id'] ?>">Detail & Cetak</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="row two">
      <a class="btn btn-primary" href="pengawas_all.php">Lihat Semua Jurnal</a>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>

