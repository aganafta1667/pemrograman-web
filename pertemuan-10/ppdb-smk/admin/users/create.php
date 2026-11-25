<?php
require_once '../../includes/auth.php';
require_once '../../includes/users.php';
require_role(AKSES['users']);

$errors = [];
$v = ['nama' => '', 'email' => '', 'role' => '', 'aktif' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$errors, $d] = user_validate($_POST, null);
    $v = ['nama' => $d['nama'], 'email' => $d['email'], 'role' => $d['role'], 'aktif' => $d['aktif']];

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO users (nama, email, password, role, is_active) VALUES (?,?,?,?,?)')
                ->execute([$d['nama'], $d['email'], password_hash($d['password'], PASSWORD_DEFAULT), $d['role'], $d['aktif']]);
            $idBaru = (int)$pdo->lastInsertId();
            if ($d['role'] === 'siswa') pastikan_baris_siswa($idBaru, $d['nama']);
            $pdo->commit();

            log_activity("Menambah pengguna {$d['email']} (#$idBaru) dengan role {$d['role']}");
            flash('Pengguna "' . $d['nama'] . '" berhasil ditambahkan.');
            header('Location: index.php'); exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Gagal menyimpan pengguna. Pastikan email belum digunakan.';
        }
    }
}

$isEdit = false; $isSelf = false; $labelSimpan = 'Simpan Pengguna';
$judul = 'Tambah Pengguna';
$css_prefix = '../../';
require_once '../../includes/header.php';
?>

<div class="container py-4" style="max-width: 720px;">
  <a href="index.php" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke daftar pengguna</a>
  <h3 class="h4 my-3">Tambah Pengguna</h3>
  <div class="card"><div class="card-body">
    <?php require __DIR__ . '/_form.php'; ?>
  </div></div>
</div>

<?php require_once '../../includes/footer.php'; ?>
