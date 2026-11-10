<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function require_role($role) {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header('Location: ' . BASE_URL . '/auth/login.php'); exit;
    }
}
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403); die('Token tidak valid.');
    }
}
function flash($msg = null, $type = 'success') {
    if ($msg) { $_SESSION['flash'] = [$msg, $type]; return; }
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']);
        return "<div class='alert alert-$t'>" . e($m) . "</div>";
    }
}