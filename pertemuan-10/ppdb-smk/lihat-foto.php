<?php
require_once 'includes/auth.php';
if (!current_user_valid()) { http_response_code(403); exit('Akses ditolak.'); }

$id = (int)($_GET['id'] ?? 0);   // id_siswa
$st = $pdo->prepare('SELECT foto, id_user FROM siswa WHERE id_siswa = ?');
$st->execute([$id]);
$row = $st->fetch();

$boleh = $row && !empty($row['foto'])
    && (can('berkas') || (int)$row['id_user'] === (int)$_SESSION['user']['id']);
if (!$boleh) { http_response_code(404); exit('Foto tidak ditemukan.'); }

$path = __DIR__ . '/uploads/foto/' . basename($row['foto']);
if (!is_file($path)) { http_response_code(404); exit('File foto tidak tersedia.'); }

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
if (!in_array($mime, ['image/jpeg', 'image/png'], true)) { http_response_code(404); exit; }

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');
readfile($path);
