<?php
require_once '../includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([trim($_POST['email'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id_user'], 'nama' => $u['nama'], 'role' => $u['role']];
        header('Location: ' . BASE_URL . ($u['role'] === 'admin' ? '/admin/dashboard.php' : '/siswa/dashboard.php'));
        exit;
    }
    $error = 'Email atau password salah.';
}

$judul = 'Login Akun';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="text-center mb-4">
      <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle p-3 mb-2" style="width:56px; height:56px;">
        <i class="bi bi-person-fill fs-3 text-primary"></i>
      </div>
      <h3 class="h4 mb-1">Masuk ke Akun</h3>
      <p class="text-muted small mb-0">Silakan masukkan email dan kata sandi Anda</p>
    </div>

    <?= flash() ?>
    <?php if ($error): ?>
      <div class="alert alert-danger py-2 mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div class="mb-3">
        <label class="form-label" for="email">Alamat Email</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Kata Sandi</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary w-100 mt-2">
        Masuk
      </button>
    </form>

    <div class="mt-4 pt-3 border-top text-center">
      <p class="small text-muted mb-0">
        Belum memiliki akun? <a href="register.php" class="fw-semibold">Registrasi Sekarang</a>
      </p>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
