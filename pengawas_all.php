<?php
require_once __DIR__ . '/init.php';
require_role(['Pengawas']);
$pdo = get_pdo();
include __DIR__ . '/templates/header.php';
?>
<div class="card">
  <h2>Semua Jurnal Sekolah</h2>
  <table class="table">
    <thead><tr><th>Tanggal</th><th>Guru</th><th>Sekolah</th><th>Kelas</th><th>Mapel</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php
      $stmt = $pdo->query('SELECT j.*, u.name as guru, u.school_name, k.nama_kelas FROM jurnal j JOIN users u ON j.user_id=u.id LEFT JOIN kelas k ON j.kelas_id=k.id ORDER BY j.tanggal DESC, j.id DESC');
      foreach ($stmt as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['tanggal']) ?></td>
          <td><?= htmlspecialchars($row['guru']) ?></td>
          <td><?= htmlspecialchars($row['school_name'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
          <td><?= htmlspecialchars($row['mapel'] ?? '-') ?></td>
          <td><span class="status-badge <?= $row['status'] === 'submitted' ? 'status-submitted' : 'status-draft' ?>"><?= htmlspecialchars($row['status']) ?></span></td>
          <td>
            <a class="btn btn-outline" href="jurnal_view.php?id=<?= (int)$row['id'] ?>">Detail</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>

