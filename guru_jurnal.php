<?php
require_once __DIR__ . '/init.php';
require_role(['Guru']);

$user = current_user();
$pdo = get_pdo();

$jurnal = null;
$jurnalId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if ($jurnalId) {
  $stmt = $pdo->prepare('SELECT * FROM jurnal WHERE id=? AND user_id=? LIMIT 1');
  $stmt->execute([$jurnalId, $user['id']]);
  $jurnal = $stmt->fetch();
  if (!$jurnal) { http_response_code(404); echo 'Jurnal tidak ditemukan.'; exit; }
}

// Siswa pada kelas guru
$kelasId = (int)($jurnal['kelas_id'] ?? ($user['kelas_id'] ?? 0));
$siswa = [];
if ($kelasId) {
  $st = $pdo->prepare('SELECT * FROM siswa WHERE kelas_id = ? ORDER BY nama');
  $st->execute([$kelasId]);
  $siswa = $st->fetchAll();
}

// Ambil progres jika jurnal sudah ada
$progresMap = [];
if ($jurnal) {
  $st = $pdo->prepare('SELECT * FROM progres_siswa WHERE jurnal_id=?');
  $st->execute([$jurnal['id']]);
  foreach ($st as $row) {
    $progresMap[(int)$row['siswa_id']] = $row;
  }
}

include __DIR__ . '/templates/header.php';
?>
<div class="card">
  <h2>Jurnal Harian Guru</h2>
  <div id="autosave-indicator" class="hint"></div>
  <form data-autosave="true" data-save-url="api/jurnal_save.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
    <input type="hidden" name="jurnal_id" value="<?= $jurnal ? (int)$jurnal['id'] : '' ?>">
    <div class="row two">
      <div>
        <label>Tanggal</label>
        <input type="date" name="tanggal" value="<?= htmlspecialchars($jurnal['tanggal'] ?? date('Y-m-d')) ?>" required>
      </div>
      <div>
        <label>Kelas</label>
        <select name="kelas_id" required>
          <?php
            $kelas = $pdo->query('SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas')->fetchAll();
            foreach ($kelas as $k):
              $sel = ((int)$k['id'] === $kelasId) ? 'selected' : '';
          ?>
            <option value="<?= (int)$k['id'] ?>" <?= $sel ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Mata Pelajaran</label>
        <input type="text" name="mapel" value="<?= htmlspecialchars($jurnal['mapel'] ?? '') ?>" placeholder="contoh: Bahasa Indonesia" required>
      </div>
      <div>
        <label>Fokus Dimensi DPL</label>
        <input type="text" name="dpl_dimensi" value="<?= htmlspecialchars($jurnal['dpl_dimensi'] ?? '') ?>" placeholder="contoh: Bernalar Kritis">
      </div>
      <div>
        <label>Tema</label>
        <input type="text" name="tema" value="<?= htmlspecialchars($jurnal['tema'] ?? '') ?>" placeholder="tema kegiatan">
      </div>
      <div>
        <label>Jenis Kokurikuler</label>
        <input type="text" name="jenis_kokurikuler" value="<?= htmlspecialchars($jurnal['jenis_kokurikuler'] ?? '') ?>" placeholder="contoh: Proyek, Ekstrakurikuler">
      </div>
      <div>
        <label>Bentuk Kegiatan</label>
        <input type="text" name="bentuk_kegiatan" value="<?= htmlspecialchars($jurnal['bentuk_kegiatan'] ?? '') ?>" placeholder="contoh: Diskusi Kelompok">
      </div>
      <div>
        <label>Mata Pelajaran Terkait</label>
        <input type="text" name="mapel_terkait" value="<?= htmlspecialchars($jurnal['mapel_terkait'] ?? '') ?>" placeholder="opsional">
      </div>
    </div>

    <div style="margin-top:12px;">
      <label>Refleksi</label>
      <textarea name="refleksi" placeholder="Refleksi pelaksanaan dan tindak lanjut."><?= htmlspecialchars($jurnal['refleksi'] ?? '') ?></textarea>
    </div>

    <h3 style="margin-top:16px;">Progres DPL per Siswa</h3>
    <?php if (!$kelasId): ?>
      <div class="hint">Silakan pilih kelas terlebih dahulu.</div>
    <?php elseif (!$siswa): ?>
      <div class="hint">Belum ada data siswa pada kelas ini. Gunakan menu Import Siswa.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr><th>Nama</th><th>Progres</th><th>Catatan</th></tr>
          </thead>
          <tbody>
            <?php foreach ($siswa as $s):
              $sid = (int)$s['id'];
              $p = $progresMap[$sid]['progres'] ?? '';
              $c = $progresMap[$sid]['catatan'] ?? '';
            ?>
              <tr>
                <td><?= htmlspecialchars($s['nama']) ?></td>
                <td>
                  <select name="progres[<?= $sid ?>]">
                    <?php
                      $opts = ['Belum Mulai','Mulai','Berkembang','Mahir'];
                      foreach ($opts as $opt):
                        $sel = ($opt === $p) ? 'selected' : '';
                    ?>
                      <option <?= $sel ?> value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td><input type="text" name="catatan[<?= $sid ?>]" value="<?= htmlspecialchars($c) ?>" placeholder="opsional"></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div class="row two" style="margin-top:16px;align-items:center;">
      <div>
        <span class="hint">Isian akan tersimpan otomatis. Pastikan Tanggal dan Mapel terisi.</span>
      </div>
      <div style="text-align:right;">
        <button id="btn-submit-to-head" class="btn btn-primary" type="button">Kirim ke Kepala Sekolah</button>
      </div>
    </div>
  </form>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>

