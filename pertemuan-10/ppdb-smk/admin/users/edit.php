<?php
require_once '../../includes/auth.php';
require_once '../../includes/users.php';
require_role(AKSES['users']);

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM users WHERE id_user = ?');
$st->execute([$id]);
$t = $st->fetch();
if (!$t) {
    flash('Pengguna tidak ditemukan.', 'danger');
    header('Location: index.php'); exit;
}
$isSelf = ($id === (int)$_SESSION['user']['id']);

$errors = [];
$v = ['nama' => $t['nama'], 'email' => $t['email'], 'role' => $t['role'], 'aktif' => (int)$t['is_active']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $in = $_POST;
    if ($isSelf) { $in['role'] = $t['role']; $in['is_active'] = '1'; } 

    [$errors, $d] = user_validate($in, $id);
    $v = ['nama' => $d['nama'], 'email' => $d['email'], 'role' => $d['role'], 'aktif' => $d['aktif']];

    if (!$errors) {
        $kehilanganAdmin = $t['role'] === 'admin' && $t['is_active'] && ($d['role'] !== 'admin' || !$d['aktif']);
        if ($kehilanganAdmin && jumlah_admin_aktif($id) === 0) {
            $errors[] = 'Admin aktif terakhir tidak boleh diubah rolenya atau dinonaktifkan.';
        }
        if ($t['role'] === 'siswa' && $d['role'] !== 'siswa' && punya_pendaftaran($id)) {
            $errors[] = 'Role akun siswa yang sudah memiliki pendaftaran tidak dapat diubah.';
        }
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $sql = 'UPDATE users SET nama = ?, email = ?, role = ?, is_active = ?';
            $params = [$d['nama'], $d['email'], $d['role'], $d['aktif']];
            if ($d['password'] !== '') { $sql .= ', password = ?'; $params[] = password_hash($d['password'], PASSWORD_DEFAULT); }
            $pdo->prepare($sql . ' WHERE id_user = ?')->execute(array_merge($params, [$id]));
            if ($d['role'] === 'siswa') pastikan_baris_siswa($id, $d['nama']);
            $pdo->commit();

            $ket = "Mengubah pengguna {$d['email']} (#$id)";
            if ($t['role'] !== $d['role']) $ket .= ", role {$t['role']} menjadi {$d['role']}";
            if ($d['password'] !== '') $ket .= ', password direset';
            log_activity($ket);
            flash('Data pengguna berhasil diperbarui.');
            header('Location: index.php'); exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Gagal memperbarui pengguna. Pastikan email belum digunakan.';
        }
    }
}

$isEdit = true; $labelSimpan = 'Update Pengguna';
$judul = 'Edit Pengguna';
$css_prefix = '../../';
require_once '../../includes/header.php';
?>

<div class="container py-4" style="max-width: 720px;">
  <a href="index.php" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke daftar pengguna</a>
  <h3 class="h4 my-3">Edit Pengguna <span class="text-muted fs-6">#<?= $id ?></span></h3>
  <div class="card"><div class="card-body">
    <?php require __DIR__ . '/_form.php'; ?>
  </div></div>
</div>

<?php require_once '../../includes/footer.php'; ?>
