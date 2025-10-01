<?php
require_once __DIR__ . '/db.php';

function find_user_by_email(string $email): ?array {
  $pdo = get_pdo();
  $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  return $user ?: null;
}

function find_user_by_id(int $id): ?array {
  $pdo = get_pdo();
  $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
  $stmt->execute([$id]);
  $user = $stmt->fetch();
  return $user ?: null;
}

function login_user(array $user): void {
  $_SESSION['user_id'] = $user['id'];
}

function logout_user(): void {
  unset($_SESSION['user_id']);
}

function current_user(): ?array {
  if (!empty($_SESSION['user_id'])) {
    return find_user_by_id((int)$_SESSION['user_id']);
  }
  return null;
}

function require_login(): void {
  if (!current_user()) {
    header('Location: login.php');
    exit;
  }
}

function user_is_role(string $role): bool {
  $user = current_user();
  return $user && strtolower($user['role']) === strtolower($role);
}

function require_role(array $roles): void {
  $user = current_user();
  if (!$user || !in_array(strtolower($user['role']), array_map('strtolower', $roles), true)) {
    http_response_code(403);
    echo 'Akses ditolak.';
    exit;
  }
}

?>

