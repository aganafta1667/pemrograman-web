<?php
require_once 'includes/auth.php';

$jurusan = $pdo->query('SELECT kode_jurusan, nama_jurusan, kuota, deskripsi
                        FROM jurusan WHERE is_active = 1 ORDER BY nama_jurusan')->fetchAll();

$user = $_SESSION['user'] ?? null;
$dashboard = $user ? BASE_URL . ($user['role'] === 'admin' ? '/admin/dashboard.php' : '/siswa/dashboard.php') : null;

$judul = 'Beranda';
$css_prefix = '';
require_once 'includes/header.php';
?>

<header class="hero-section">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7 text-center text-lg-start">
        <span class="hero-badge">
          Penerimaan Peserta Didik Baru <?= e(TAHUN_AJARAN) ?>
        </span>
        <h1 class="hero-title">
          Penerimaan Peserta Didik Baru <br> SMK Nusantara
        </h1>
        <p class="hero-lead mb-4">
          Pendaftaran peserta didik baru SMK Nusantara dibuka secara daring. Silakan lakukan pendaftaran dan pilih program keahlian yang sesuai dengan bakat dan minat Anda.
        </p>
        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
          <?php if ($user): ?>
            <a href="<?= $dashboard ?>" class="btn btn-accent btn-lg">
              Buka Dashboard
            </a>
          <?php else: ?>
            <a href="auth/register.php" class="btn btn-accent btn-lg">
              Daftar Sekarang
            </a>
            <a href="auth/login.php" class="btn btn-outline-primary btn-lg">
              Masuk Akun
            </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block text-center">
        <img src="assets/images/image.png" alt="SMK Nusantara" class="img-fluid rounded-4" style="max-height: 350px;">
      </div>
    </div>
  </div>
</header>

<section id="jurusan" class="py-5">
  <div class="container">
    <div class="mb-4 text-start">
      <div class="d-flex align-items-center gap-2 mb-1">
        <h2 class="h3 mb-0">Program Keahlian</h2>
      </div>
      <p class="text-muted mb-0">SMK Nusantara membuka penerimaan untuk beberapa kompetensi keahlian unggulan:</p>
    </div>

    <div class="row g-4">
      <?php foreach ($jurusan as $j): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="badge-code"><?= e($j['kode_jurusan']) ?></span>
                <span class="small text-muted">
                  <i class="bi bi-people me-1"></i> Kuota: <strong><?= (int)$j['kuota'] ?></strong>
                </span>
              </div>
              <h5 class="card-title h5 mb-2"><?= e($j['nama_jurusan']) ?></h5>
              <p class="card-text text-muted small flex-grow-1">
                <?= e($j['deskripsi'] ?: 'Program keahlian terakreditasi dengan kurikulum berbasis industri dan kerja sama mitra kerja.') ?>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="persyaratan" class="py-5" style="background-color: #FFFFFF; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <h2 class="h4 mb-0">Persyaratan Pendaftaran</h2>
        </div>
        <p class="text-muted small mb-3">Siapkan berkas dan data sebelum melakukan pendaftaran daring:</p>
        
        <div class="card">
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex align-items-start gap-2 py-3">
              <i class="bi bi-check2 text-success mt-1"></i>
              <div>
                <strong>Kelulusan SMP/MTs</strong>
                <div class="text-muted small">Telah lulus atau duduk di kelas 9 SMP/MTs sederajat.</div>
              </div>
            </li>
            <li class="list-group-item d-flex align-items-start gap-2 py-3">
              <i class="bi bi-file-earmark-text text-primary mt-1"></i>
              <div>
                <strong>Kartu Keluarga (KK) &amp; Akta Kelahiran</strong>
                <div class="text-muted small">Scan atau foto jelas asli format JPG/PNG/PDF (maks. 2 MB).</div>
              </div>
            </li>
            <li class="list-group-item d-flex align-items-start gap-2 py-3">
              <i class="bi bi-journal-check text-primary mt-1"></i>
              <div>
                <strong>Rapor SMP/MTs</strong>
                <div class="text-muted small">Scan rapor semester terakhir format berkas PDF/gambar.</div>
              </div>
            </li>
            <li class="list-group-item d-flex align-items-start gap-2 py-3">
              <i class="bi bi-image text-primary mt-1"></i>
              <div>
                <strong>Pas Foto Terbaru</strong>
                <div class="text-muted small">Foto formal dengan latar belakang warna biru/merah.</div>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div id="jadwal" class="col-lg-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <h2 class="h4 mb-0">Jadwal Pelaksanaan</h2>
        </div>
        <p class="text-muted small mb-3">Rangkaian tahapan seleksi PPDB tahun ajaran <?= e(TAHUN_AJARAN) ?>:</p>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Tahap Kegiatan</th>
                <th>Waktu Pelaksanaan</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>Pendaftaran Online</strong></td>
                <td>1 Januari &ndash; 31 Maret 2027</td>
              </tr>
              <tr>
                <td><strong>Verifikasi Berkas</strong></td>
                <td>1 &ndash; 15 April 2027</td>
              </tr>
              <tr>
                <td><strong>Pengumuman Seleksi</strong></td>
                <td>20 April 2027</td>
              </tr>
              <tr>
                <td><strong>Daftar Ulang</strong></td>
                <td>21 &ndash; 30 April 2027</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="text-muted small mt-2">
          <i class="bi bi-info-circle me-1"></i> Jadwal dapat disesuaikan dengan kalender pendidikan dinas setempat.
        </p>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
