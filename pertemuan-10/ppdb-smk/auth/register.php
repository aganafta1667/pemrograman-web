<?php
require_once '../includes/auth.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $conf = $_POST['konfirmasi'] ?? '';

    if ($nama === '') $errors[] = 'Nama wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($pass) < 8) $errors[] = 'Password minimal 8 karakter.';
    if ($pass !== $conf) $errors[] = 'Konfirmasi password tidak sama.';

    if (!$errors) {
        $cek = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $cek->execute([$email]);
        if ($cek->fetch()) $errors[] = 'Email sudah terdaftar.';
    }
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO users (nama,email,password,role) VALUES (?,?,?,'siswa')")
                ->execute([$nama, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            $pdo->prepare('INSERT INTO siswa (id_user, nama_lengkap) VALUES (?,?)')
                ->execute([$pdo->lastInsertId(), $nama]);
            $pdo->commit();
            flash('Registrasi berhasil, silakan masuk ke akun Anda.');
            header('Location: login.php'); exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Terjadi kesalahan, coba lagi.';
        }
    }
}

$judul = 'Registrasi Akun Siswa';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="auth-wrapper">
  <div class="auth-card" style="max-width: 480px;">
    <div class="text-center mb-4">
      <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle p-3 mb-2" style="width:56px; height:56px;">
        <i class="bi bi-person-plus-fill fs-3 text-primary"></i>
      </div>
      <h3 class="h4 mb-1">Registrasi Siswa</h3>
      <p class="text-muted small mb-0">Buat akun untuk mengajukan pendaftaran PPDB</p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger py-2 mb-3">
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $er): ?>
            <li><?= e($er) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div class="mb-3">
        <label class="form-label" for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan Nama Lengkap" value="<?= e($_POST['nama'] ?? '') ?>" required autocomplete="name">
      </div>
      <div class="mb-3">
        <label class="form-label" for="email">Alamat Email Aktif</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
      </div>
      <div class="row g-2 mb-3">
        <div class="col-sm-6">
          <label class="form-label" for="password">Kata Sandi</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Min. 8 karakter" required autocomplete="new-password">
        </div>
        <div class="col-sm-6">
          <label class="form-label" for="konfirmasi">Konfirmasi Sandi</label>
          <input type="password" id="konfirmasi" name="konfirmasi" class="form-control" placeholder="Konfirmasi" required autocomplete="new-password">
        </div>
      </div>
      <button type="submit" class="btn btn-accent w-100 mt-2">
        Buat Akun
      </button>
    </form>

    <div class="mt-4 pt-3 border-top text-center">
      <p class="small text-muted mb-0">
        Sudah memiliki akun? <a href="login.php" class="fw-semibold">Masuk di sini</a>
      </p>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
