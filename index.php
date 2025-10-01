<?php
require_once __DIR__ . '/init.php';

$user = current_user();
if ($user) {
  header('Location: dashboard.php');
  exit;
}
header('Location: login.php');
exit;
?>

