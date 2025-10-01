<?php
require_once __DIR__ . '/init.php';
require_role(['Kepala Sekolah','Pengawas','Guru']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { http_response_code(404); echo 'Tidak ditemukan.'; exit; }

$pdo = get_pdo();
$stmt = $pdo->prepare('SELECT j.*, u.name as guru, u.school_name, k.nama_kelas FROM jurnal j JOIN users u ON j.user_id=u.id LEFT JOIN kelas k ON j.kelas_id=k.id WHERE j.id=?');
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { http_response_code(404); echo 'Tidak ditemukan.'; exit; }

$progres = $pdo->prepare('SELECT s.nama, p.progres, p.catatan FROM progres_siswa p JOIN siswa s ON p.siswa_id=s.id WHERE p.jurnal_id=? ORDER BY s.nama');
$progres->execute([$id]);
$rows = $progres->fetchAll();

include __DIR__ . '/templates/header.php';
?>
<div class="card">
  <div class="no-print" style="display:flex; gap:8px; justify-content:flex-end;">
    <button class="btn btn-outline" onclick="window.print()">Print / Export PDF</button>
    <a class="btn btn-outline" href="dashboard.php">Kembali</a>
  </div>
  <div style="text-align:center; margin-bottom:12px;">
    <div><strong>DINAS PENDIDIKAN DAN KEBUDAYAAN</strong></div>
    <div><strong>PEMERINTAH KABUPATEN WONOGIRI</strong></div>
    <div><strong><?= htmlspecialchars($j['school_name']) ?></strong></div>
  </div>
  <h2 style="text-align:center;">Jurnal Kegiatan Kokurikuler</h2>
  <div class="row two">
    <div>
      <div><strong>Tanggal:</strong> <?= htmlspecialchars($j['tanggal']) ?></div>
      <div><strong>Guru:</strong> <?= htmlspecialchars($j['guru']) ?></div>
      <div><strong>Kelas:</strong> <?= htmlspecialchars($j['nama_kelas'] ?? '-') ?></div>
    </div>
    <div>
      <div><strong>Mapel:</strong> <?= htmlspecialchars($j['mapel']) ?></div>
      <div><strong>Fokus DPL:</strong> <?= htmlspecialchars($j['dpl_dimensi'] ?? '-') ?></div>
      <div><strong>Tema:</strong> <?= htmlspecialchars($j['tema'] ?? '-') ?></div>
    </div>
  </div>
  <div class="row two" style="margin-top:8px;">
    <div><strong>Jenis Kokurikuler:</strong> <?= htmlspecialchars($j['jenis_kokurikuler'] ?? '-') ?></div>
    <div><strong>Bentuk Kegiatan:</strong> <?= htmlspecialchars($j['bentuk_kegiatan'] ?? '-') ?></div>
  </div>
  <div style="margin-top:8px;"><strong>Mapel Terkait:</strong> <?= htmlspecialchars($j['mapel_terkait'] ?? '-') ?></div>
  <div style="margin-top:8px;"><strong>Refleksi:</strong><br><?= nl2br(htmlspecialchars($j['refleksi'] ?? '-')) ?></div>

  <h3 style="margin-top:16px;">Progres Siswa</h3>
  <table class="table">
    <thead><tr><th>Nama</th><th>Progres</th><th>Catatan</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['nama']) ?></td>
          <td><?= htmlspecialchars($r['progres'] ?? '-') ?></td>
          <td><?= htmlspecialchars($r['catatan'] ?? '-') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>

