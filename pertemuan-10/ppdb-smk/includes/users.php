<?php
function teks_panjang(string $s): int {
    return function_exists('mb_strlen') ? mb_strlen($s) : (preg_match_all('/./us', $s) ?: strlen($s));
}

function user_validate(array $in, ?int $editId = null): array {
    global $pdo;
    $err = [];
    $nama  = trim($in['nama'] ?? '');
    $email = strtolower(trim($in['email'] ?? ''));
    $role  = $in['role'] ?? '';
    $pass  = $in['password'] ?? '';
    $conf  = $in['konfirmasi'] ?? '';
    $aktif = isset($in['is_active']) ? 1 : 0;

    if ($nama === '') $err[] = 'Nama lengkap wajib diisi.';
    elseif (teks_panjang($nama) > 100) $err[] = 'Nama maksimal 100 karakter.';

    $emailOk = filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 100;
    if (!$emailOk) $err[] = 'Format email tidak valid.';

    if (!isset(ROLE_LABEL[$role])) $err[] = 'Role tidak valid.';

    if ($editId === null || $pass !== '') {
        if (strlen($pass) < 8) $err[] = 'Password minimal 8 karakter.';
        elseif ($pass !== $conf) $err[] = 'Konfirmasi password tidak sama.';
    }

    if ($emailOk) {
        $q = $pdo->prepare('SELECT 1 FROM users WHERE email = ? AND id_user <> ?');
        $q->execute([$email, $editId ?? 0]);
        if ($q->fetch()) $err[] = 'Email sudah digunakan pengguna lain.';
    }
    return [$err, ['nama' => $nama, 'email' => $email, 'role' => $role, 'password' => $pass, 'aktif' => $aktif]];
}

function jumlah_admin_aktif(int $kecualiId = 0): int {
    global $pdo;
    $q = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1 AND id_user <> ?");
    $q->execute([$kecualiId]);
    return (int)$q->fetchColumn();
}

function punya_pendaftaran(int $idUser): bool {
    global $pdo;
    $q = $pdo->prepare('SELECT COUNT(*) FROM pendaftaran p JOIN siswa s ON s.id_siswa = p.id_siswa WHERE s.id_user = ?');
    $q->execute([$idUser]);
    return (int)$q->fetchColumn() > 0;
}

function punya_riwayat_verifikasi(int $idUser): bool {
    global $pdo;
    $q = $pdo->prepare('SELECT COUNT(*) FROM riwayat_status WHERE id_admin = ?');
    $q->execute([$idUser]);
    return (int)$q->fetchColumn() > 0;
}

function pastikan_baris_siswa(int $idUser, string $nama): void {
    global $pdo;
    $q = $pdo->prepare('SELECT 1 FROM siswa WHERE id_user = ?');
    $q->execute([$idUser]);
    if (!$q->fetch()) {
        $pdo->prepare('INSERT INTO siswa (id_user, nama_lengkap) VALUES (?,?)')->execute([$idUser, $nama]);
    }
}
