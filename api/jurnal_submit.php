<?php
require_once __DIR__ . '/../init.php';
require_role(['Guru']);
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_SERVER['HTTP_X_CSRF_TOKEN'])) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'error' => 'CSRF']);
  exit;
}

$jurnalId = isset($data['jurnal_id']) ? (int)$data['jurnal_id'] : 0;
if (!$jurnalId) { echo json_encode(['ok' => false, 'error' => 'no_id']); exit; }

$pdo = get_pdo();
$user = current_user();

try {
  $stmt = $pdo->prepare('UPDATE jurnal SET status="submitted" WHERE id=? AND user_id=? AND status="draft"');
  $stmt->execute([$jurnalId, $user['id']]);
  if ($stmt->rowCount() === 0) {
    echo json_encode(['ok' => false, 'error' => 'not_found_or_already_submitted']);
  } else {
    echo json_encode(['ok' => true]);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false]);
}

