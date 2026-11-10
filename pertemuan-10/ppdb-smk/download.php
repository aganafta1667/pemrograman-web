<?php
require_once 'includes/auth.php';
if (empty($_SESSION['user'])) { http_response_code(403); exit('Akses ditolak.'); }

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT b.nama_file, s.id_user
                     FROM berkas b
                     JOIN pendaftaran p ON p.id_pendaftaran = b.id_pendaftaran
                     JOIN siswa s ON s.id_siswa = p.id_siswa
                     WHERE b.id_berkas = ?');
$st->execute([$id]);
$row = $st->fetch();

$boleh = $row && ($_SESSION['user']['role'] === 'admin' || (int)$row['id_user'] === (int)$_SESSION['user']['id']);
if (!$boleh) { http_response_code(403); exit('Akses ditolak.'); }

$path = __DIR__ . '/uploads/' . basename($row['nama_file']);
if (!is_file($path)) { http_response_code(404); exit('File tidak ditemukan.'); }

header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($path));
header('Content-Disposition: inline; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);