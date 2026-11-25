<?php
require_once '../../includes/auth.php';
require_once '../../includes/users.php';
require_role(AKSES['users']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$st = $pdo->prepare('SELECT u.id_user, u.nama, u.email, u.role, u.is_active, s.foto
                     FROM users u LEFT JOIN siswa s ON s.id_user = u.id_user
                     WHERE u.id_user = ?');
$st->execute([$id]);
$u = $st->fetch();

$alasan = null;
if (!$u) {
    $alasan = 'Pengguna tidak ditemukan.';
} elseif ($id === (int)$_SESSION['user']['id']) {
    $alasan = 'Anda tidak dapat menghapus akun Anda sendiri.';
} elseif ($u['role'] === 'admin' && jumlah_admin_aktif($id) === 0) {
    $alasan = 'Akun admin terakhir tidak boleh dihapus.';
} elseif (punya_pendaftaran($id)) {
    $alasan = 'Akun ini memiliki riwayat pendaftaran dan tidak dapat dihapus permanen. Nonaktifkan akun sebagai gantinya.';
} elseif (punya_riwayat_verifikasi($id)) {
    $alasan = 'Akun ini tercatat pada riwayat verifikasi dan tidak dapat dihapus permanen. Nonaktifkan akun sebagai gantinya.';
}

if ($alasan) {
    flash($alasan, 'danger');
    header('Location: index.php'); exit;
}

try {
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM siswa WHERE id_user = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM users WHERE id_user = ?')->execute([$id]);
    $pdo->commit();

    if (!empty($u['foto'])) @unlink(__DIR__ . '/../../uploads/foto/' . basename($u['foto']));
    log_activity("Menghapus pengguna {$u['email']} (#$id), role {$u['role']}");
    flash('Pengguna "' . $u['nama'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash('Pengguna tidak dapat dihapus karena masih terhubung dengan data lain. Nonaktifkan akun sebagai gantinya.', 'danger');
}
header('Location: index.php'); exit;
