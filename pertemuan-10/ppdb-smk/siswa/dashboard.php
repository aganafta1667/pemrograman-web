<?php
require_once '../includes/auth.php';
require_role('siswa');

$st = $pdo->prepare('SELECT p.id_pendaftaran, p.nomor_pendaftaran, p.status, j.nama_jurusan
    FROM siswa s LEFT JOIN pendaftaran p ON p.id_siswa=s.id_siswa AND p.tahun_ajaran=?
    LEFT JOIN jurusan j ON j.id_jurusan=p.id_jurusan WHERE s.id_user=?');
$st->execute([TAHUN_AJARAN, $_SESSION['user']['id']]);
$d = $st->fetch();

$stSiswa = $pdo->prepare('SELECT nisn, tempat_lahir, tanggal_lahir, alamat, asal_sekolah, no_hp FROM siswa WHERE id_user=?');
$stSiswa->execute([$_SESSION['user']['id']]);
$bio = $stSiswa->fetch();
$biodataLengkap = true;
if ($bio) {
    foreach (['nisn','tempat_lahir','tanggal_lahir','alamat','asal_sekolah','no_hp'] as $col) {
        if (empty($bio[$col])) {
            $biodataLengkap = false;
            break;
        }
    }
} else {
    $biodataLengkap = false;
}

$berkasAda = false;
if (!empty($d['id_pendaftaran'])) {
    $stB = $pdo->prepare('SELECT COUNT(*) FROM berkas WHERE id_pendaftaran=?');
    $stB->execute([$d['id_pendaftaran']]);
    $berkasAda = ($stB->fetchColumn() > 0);
}

$status = $d['status'] ?? null;
$sudahDaftar = !empty($d['nomor_pendaftaran']);
$sudahVerif = in_array($status, ['Diverifikasi', 'Diterima', 'Ditolak']);
$sudahHasil = in_array($status, ['Diterima', 'Ditolak']);

$judul = 'Dashboard Calon Siswa';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
    <div>
      <h3 class="h4 mb-1">Selamat Datang, <?= e($_SESSION['user']['nama']) ?></h3>
      <p class="text-muted small mb-0">Portal Penerimaan Peserta Didik Baru Tahun Ajaran <?= e(TAHUN_AJARAN) ?></p>
    </div>
  </div>

  <?= flash() ?>

  <div class="card mb-4">
    <div class="card-body p-3 p-md-4">
      <h6 class="text-muted text-uppercase fw-bold small mb-2">Tahapan Pendaftaran</h6>
      
      <div class="step-progress">
        <div class="step-item <?= $biodataLengkap ? 'completed' : 'active' ?>">
          <div class="step-num"><?= $biodataLengkap ? '<i class="bi bi-check-lg"></i>' : '1' ?></div>
          <span class="step-title">Biodata</span>
        </div>

        <div class="step-item <?= $sudahDaftar ? 'completed' : ($biodataLengkap ? 'active' : '') ?>">
          <div class="step-num"><?= $sudahDaftar ? '<i class="bi bi-check-lg"></i>' : '2' ?></div>
          <span class="step-title">Pilih Jurusan</span>
        </div>

        <div class="step-item <?= $berkasAda ? 'completed' : ($sudahDaftar ? 'active' : '') ?>">
          <div class="step-num"><?= $berkasAda ? '<i class="bi bi-check-lg"></i>' : '3' ?></div>
          <span class="step-title">Upload Berkas</span>
        </div>

        <div class="step-item <?= $sudahVerif ? 'completed' : ($berkasAda ? 'active' : '') ?>">
          <div class="step-num"><?= $sudahVerif ? '<i class="bi bi-check-lg"></i>' : '4' ?></div>
          <span class="step-title">Verifikasi</span>
        </div>

        <div class="step-item <?= $sudahHasil ? 'completed' : '' ?>">
          <div class="step-num"><?= $sudahHasil ? '<i class="bi bi-check-lg"></i>' : '5' ?></div>
          <span class="step-title">Hasil Seleksi</span>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="stat-card">
        <span class="stat-label">Nomor Pendaftaran</span>
        <div class="fs-4 fw-bold text-primary mt-1 mb-1">
          <?= e($d['nomor_pendaftaran'] ?? 'Belum ada') ?>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="stat-card">
        <span class="stat-label">Pilihan Jurusan</span>
        <div class="fs-4 fw-bold text-primary mt-1 mb-1">
          <?= e($d['nama_jurusan'] ?? 'Belum memilih') ?>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="stat-card">
        <span class="stat-label">Status Pendaftaran</span>
        <div class="fs-4 fw-bold text-primary mt-1 mb-1">
          <?= e($d['status'] ?? 'Menunggu pendaftaran') ?>
        </div>
      </div>
    </div>
  </div>

  <h6 class="text-muted text-uppercase fw-bold small mb-3">Menu</h6>
  <div class="row g-3">
    <div class="col-sm-6 col-lg-3">
      <a href="biodata.php" class="card card-action h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-2 mb-2 text-primary">
            <i class="bi bi-person-lines-fill fs-4"></i>
            <h5 class="h6 mb-0">Isi Biodata</h5>
          </div>
          <p class="text-muted small mb-0">Lengkapi data pribadi, asal sekolah, dan nomor kontak aktif.</p>
        </div>
      </a>
    </div>

    <div class="col-sm-6 col-lg-3">
      <a href="pendaftaran.php" class="card card-action h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-2 mb-2 text-primary">
            <i class="bi bi-pencil-square fs-4"></i>
            <h5 class="h6 mb-0">Pilih Jurusan</h5>
          </div>
          <p class="text-muted small mb-0">Kirim pilihan program keahlian yang diminati.</p>
        </div>
      </a>
    </div>

    <div class="col-sm-6 col-lg-3">
      <a href="upload_berkas.php" class="card card-action h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-2 mb-2 text-primary">
            <i class="bi bi-cloud-arrow-up fs-4"></i>
            <h5 class="h6 mb-0">Upload Berkas</h5>
          </div>
          <p class="text-muted small mb-0">Unggah berkas KK, Akta, Rapor, dan Pas Foto.</p>
        </div>
      </a>
    </div>

    <div class="col-sm-6 col-lg-3">
      <a href="status.php" class="card card-action h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-2 mb-2 text-primary">
            <i class="bi bi-file-earmark-check fs-4"></i>
            <h5 class="h6 mb-0">Cek Status</h5>
          </div>
          <p class="text-muted small mb-0">Pantau catatan verifikasi panitia dan hasil pengumuman.</p>
        </div>
      </a>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
