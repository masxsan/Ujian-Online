<?php
// Basic configuration for Rapor Kokurikuler SMP
// Adjust these settings to match your hosting environment

define('APP_NAME', 'Rapor Kokurikuler SMP');
define('APP_PRIMARY_COLOR', '#2e7d32'); // Hijau Daun

// Database settings
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'rapor_kokurikuler');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Determine base URL automatically (best-effort) for assets and links
function base_url(): string {
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
  $scheme = $https ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
  $dir = rtrim(str_replace(basename($scriptName), '', $scriptName), '/');
  return rtrim("$scheme://$host$dir", '/');
}

?>

