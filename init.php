<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Start secure session
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// CSRF utilities
function get_csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function verify_csrf_or_fail(): void {
  $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
  if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo 'CSRF token invalid.';
    exit;
  }
}

// Simple helper to safely read input
function input(string $key, $default = null) {
  return $_POST[$key] ?? $_GET[$key] ?? $default;
}

?>

