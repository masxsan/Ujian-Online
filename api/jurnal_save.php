<?php
require_once __DIR__ . '/../init.php';
require_role(['Guru']);

header('Content-Type: application/json');

// CSRF checked from header in init's verify function inside this handler
// We accept JSON payload
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_SERVER['HTTP_X_CSRF_TOKEN'])) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'CSRF']);
  exit;
}

$user = current_user();
$pdo = get_pdo();

$jurnalId = isset($data['jurnal_id']) && $data['jurnal_id'] !== '' ? (int)$data['jurnal_id'] : null;
$tanggal = $data['tanggal'] ?? null;
$kelas_id = isset($data['kelas_id']) ? (int)$data['kelas_id'] : ($user['kelas_id'] ?? null);
$mapel = trim($data['mapel'] ?? '');
$dpl_dimensi = trim($data['dpl_dimensi'] ?? '');
$tema = trim($data['tema'] ?? '');
$jenis = trim($data['jenis_kokurikuler'] ?? '');
$bentuk = trim($data['bentuk_kegiatan'] ?? '');
$mapel_terkait = trim($data['mapel_terkait'] ?? '');
$refleksi = trim($data['refleksi'] ?? '');

// Create or update jurnal (still draft)
try {
  $pdo->beginTransaction();
  if ($jurnalId) {
    $stmt = $pdo->prepare('UPDATE jurnal SET tanggal=?, kelas_id=?, mapel=?, dpl_dimensi=?, tema=?, jenis_kokurikuler=?, bentuk_kegiatan=?, mapel_terkait=?, refleksi=? WHERE id=? AND user_id=? AND status="draft"');
    $stmt->execute([$tanggal, $kelas_id, $mapel, $dpl_dimensi, $tema, $jenis, $bentuk, $mapel_terkait, $refleksi, $jurnalId, $user['id']]);
  } else {
    // insert new draft
    $stmt = $pdo->prepare('INSERT INTO jurnal (user_id, kelas_id, tanggal, mapel, dpl_dimensi, tema, jenis_kokurikuler, bentuk_kegiatan, mapel_terkait, refleksi, status) VALUES (?,?,?,?,?,?,?,?,?,?,"draft")');
    $stmt->execute([$user['id'], $kelas_id, $tanggal, $mapel, $dpl_dimensi, $tema, $jenis, $bentuk, $mapel_terkait, $refleksi]);
    $jurnalId = (int)$pdo->lastInsertId();
  }

  // per-siswa progres
  $progres = $data['progres'] ?? [];
  $catatan = $data['catatan'] ?? [];
  if (is_array($progres)) {
    foreach ($progres as $sid => $val) {
      $sid = (int)$sid;
      $pr = is_string($val) ? $val : '';
      $ct = isset($catatan[$sid]) ? (string)$catatan[$sid] : '';
      // upsert
      $stmt = $pdo->prepare('INSERT INTO progres_siswa (jurnal_id, siswa_id, progres, catatan) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE progres=VALUES(progres), catatan=VALUES(catatan)');
      $stmt->execute([$jurnalId, $sid, $pr, $ct]);
    }
  }

  $pdo->commit();
  echo json_encode(['ok' => true, 'jurnal_id' => $jurnalId]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'DB']);
}

