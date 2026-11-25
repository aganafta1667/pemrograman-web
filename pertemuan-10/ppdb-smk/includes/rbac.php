<?php
const ROLE_LABEL = [
    'admin'       => 'Admin',
    'panitia'     => 'Panitia',
    'verifikator' => 'Verifikator',
    'siswa'       => 'Siswa',
];

const ROLE_STAFF = ['admin', 'panitia', 'verifikator'];

const AKSES = [
    'admin_dashboard' => ['admin', 'panitia', 'verifikator'],
    'users'           => ['admin'],
    'jurusan'         => ['admin'],
    'verifikasi'      => ['admin', 'verifikator'],
    'laporan'         => ['admin', 'panitia'],
    'berkas'          => ['admin', 'verifikator'],
];

function can(string $modul): bool {
    $role = $_SESSION['user']['role'] ?? '';
    return in_array($role, AKSES[$modul] ?? [], true);
}

function home_url(?string $role = null): string {
    $role = $role ?? ($_SESSION['user']['role'] ?? '');
    if (in_array($role, ROLE_STAFF, true)) return BASE_URL . '/admin/dashboard.php';
    if ($role === 'siswa') return BASE_URL . '/siswa/dashboard.php';
    return BASE_URL . '/auth/login.php';
}

function current_user_valid(): bool {
    global $pdo;
    if (empty($_SESSION['user']['id'])) return false;
    $st = $pdo->prepare('SELECT nama, role, is_active FROM users WHERE id_user = ?');
    $st->execute([$_SESSION['user']['id']]);
    $u = $st->fetch();
    if (!$u) { unset($_SESSION['user']); return false; }
    if (!$u['is_active']) {
        unset($_SESSION['user']);
        flash('Akun Anda dinonaktifkan. Silakan hubungi administrator.', 'danger');
        return false;
    }
    $_SESSION['user']['role'] = $u['role'];
    $_SESSION['user']['nama'] = $u['nama'];
    return true;
}

function require_login(): void {
    if (!current_user_valid()) {
        header('Location: ' . BASE_URL . '/auth/login.php'); exit;
    }
}

function require_role($roles): void {
    require_login();
    if (!in_array($_SESSION['user']['role'], (array)$roles, true)) {
        tampilkan_403();
    }
}

function tampilkan_403(): void {
    http_response_code(403);
    $judul = 'Akses Ditolak';
    $css_prefix = BASE_URL . '/';
    require __DIR__ . '/header.php';
    echo '<div class="container py-5 text-center" style="max-width:520px">'
       . '<div class="display-4 text-muted mb-2"><i class="bi bi-shield-lock"></i></div>'
       . '<h3 class="h4">403 - Akses ditolak</h3>'
       . '<p class="text-muted">Role Anda tidak memiliki izin untuk membuka halaman ini.</p>'
       . '<a href="' . e(home_url()) . '" class="btn btn-primary">Kembali ke Dashboard</a>'
       . '</div>';
    require __DIR__ . '/footer.php';
    exit;
}

function role_badge(string $role): string {
    $label = ROLE_LABEL[$role] ?? $role;
    return '<span class="badge-role badge-role-' . e($role) . '">' . e($label) . '</span>';
}

function log_activity(string $aktivitas, ?int $idUser = null): void {
    global $pdo;
    try {
        $pdo->prepare('INSERT INTO activity_logs (id_user, aktivitas, ip_address) VALUES (?,?,?)')
            ->execute([
                $idUser ?? ($_SESSION['user']['id'] ?? null),
                function_exists('mb_substr') ? mb_substr($aktivitas, 0, 255) : substr($aktivitas, 0, 255),
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
    } catch (Throwable $e) {
    }
}
