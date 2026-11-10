<?php
require_once '../includes/auth.php';
require_role('siswa');
$uid = $_SESSION['user']['id'];

$st = $pdo->prepare('SELECT p.id_pendaftaran, p.nomor_pendaftaran, p.status, p.tanggal_daftar,
                            s.nama_lengkap, j.kode_jurusan, j.nama_jurusan
                     FROM siswa s
                     JOIN pendaftaran p ON p.id_siswa = s.id_siswa AND p.tahun_ajaran = ?
                     JOIN jurusan j ON j.id_jurusan = p.id_jurusan
                     WHERE s.id_user = ?');
$st->execute([TAHUN_AJARAN, $uid]);
$p = $st->fetch();

$riwayat = [];
$berkas = [];
if ($p) {
    $r = $pdo->prepare('SELECT status_sebelumnya, status_baru, catatan, tanggal_perubahan
                        FROM riwayat_status WHERE id_pendaftaran = ?
                        ORDER BY tanggal_perubahan DESC, id_riwayat DESC');
    $r->execute([$p['id_pendaftaran']]);
    $riwayat = $r->fetchAll();

    $b = $pdo->prepare('SELECT jenis_berkas, tanggal_upload FROM berkas WHERE id_pendaftaran = ?');
    $b->execute([$p['id_pendaftaran']]);
    $berkas = $b->fetchAll();
}

$judul = 'Status Pendaftaran Siswa';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4" style="max-width: 800px;">
  <div class="mb-3">
    <h3 class="h4 mb-1">Status Hasil Seleksi &amp; Riwayat</h3>
    <p class="text-muted small">Informasi perkembangan seleksi administrasi PPDB</p>
  </div>

  <?= flash() ?>

  <?php if (!$p): ?>
    <div class="card border-warning">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3">
          <div>
            <h5 class="h6 mb-1">Anda Belum Mengirimkan Pendaftaran</h5>
            <p class="text-muted small mb-2">Silakan lengkapi biodata dan pilih jurusan terlebih dahulu untuk memperoleh nomor pendaftaran.</p>
            <a href="pendaftaran.php" class="btn btn-warning btn-sm">Mulai Pendaftaran</a>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>

    <?php if ($p['status'] === 'Diterima'): ?>
      <div class="alert alert-success d-flex align-items-start gap-3 mb-4">
        <div>
          <h5 class="alert-heading h6 fw-bold mb-1">Selamat! Anda Dinyatakan DITERIMA</h5>
          <div>Selamat kepada ananda <strong><?= e($p['nama_lengkap']) ?></strong> telah lolos seleksi penerimaan pada kompetensi keahlian <strong><?= e($p['nama_jurusan']) ?></strong>. Silakan pantau pengumuman daftar ulang resmi sekolah.</div>
        </div>
      </div>
    <?php elseif ($p['status'] === 'Ditolak'): ?>
      <div class="alert alert-danger d-flex align-items-start gap-3 mb-4">
        <div>
          <h5 class="alert-heading h6 fw-bold mb-1">Mohon Maaf, Pendaftaran Belum Diterima</h5>
          <div>Pendaftaran Anda saat ini belum memenuhi kriteria penerimaan atau kuota kompetensi keahlian telah terpenuhi.</div>
        </div>
      </div>
    <?php elseif ($p['status'] === 'Diverifikasi'): ?>
      <div class="alert alert-info d-flex align-items-start gap-3 mb-4">
        <div>
          <h5 class="alert-heading h6 fw-bold mb-1">Berkas Terverifikasi</h5>
          <div>Berkas persyaratan Anda telah diperiksa dan dinyatakan sah oleh panitia. Saat ini pendaftaran menunggu penetapan hasil akhir penerimaan.</div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
        <div>
          <h5 class="alert-heading h6 fw-bold mb-1">Menunggu Pemeriksaan Panitia</h5>
          <div>Pendaftaran Anda telah diterima di sistem kami. Panitia PPDB sedang memverifikasi kelengkapan berkas fisik/digital Anda.</div>
        </div>
      </div>
    <?php endif; ?>

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Rincian Pendaftaran</span>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4 text-muted">No. Pendaftaran</dt>
          <dd class="col-sm-8 fw-bold text-primary"><?= e($p['nomor_pendaftaran']) ?></dd>

          <dt class="col-sm-4 text-muted">Nama Calon Siswa</dt>
          <dd class="col-sm-8 fw-semibold"><?= e($p['nama_lengkap']) ?></dd>

          <dt class="col-sm-4 text-muted">Pilihan Jurusan</dt>
          <dd class="col-sm-8">
            <span class="badge-code me-1"><?= e($p['kode_jurusan']) ?></span>
            <?= e($p['nama_jurusan']) ?>
          </dd>

          <dt class="col-sm-4 text-muted">Tanggal Pendaftaran</dt>
          <dd class="col-sm-8"><?= e(date('d F Y - H:i', strtotime($p['tanggal_daftar']))) ?> WIB</dd>
        </dl>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <i class="bi bi-folder-check me-1 text-primary"></i> Berkas Persyaratan Terdaftar
      </div>
      <div class="card-body p-0">
        <?php if (!$berkas): ?>
          <p class="text-muted p-3 mb-0">Belum ada berkas terunggah. <a href="upload_berkas.php">Unggah berkas sekarang</a>.</p>
        <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($berkas as $b): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold">
                  <i class="bi bi-file-earmark-check text-success me-2"></i>
                  <?= e($b['jenis_berkas']) ?>
                </span>
                <span class="small text-muted">
                  <?= e(date('d-m-Y H:i', strtotime($b['tanggal_upload']))) ?> WIB
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <h5 class="h6 text-muted text-uppercase fw-bold mb-3">Catatan &amp; Riwayat Verifikasi</h5>
    <?php if (!$riwayat): ?>
      <div class="card p-4 text-center text-muted">
        <p class="mb-0">Belum ada catatan perubahan status dari panitia PPDB.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th style="width: 170px;">Waktu Verifikasi</th>
              <th style="width: 220px;">Perubahan Status</th>
              <th>Catatan Panitia</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($riwayat as $h): ?>
              <tr>
                <td class="small text-muted"><?= e(date('d-m-Y H:i', strtotime($h['tanggal_perubahan']))) ?> WIB</td>
                <td>
                  <span class="text-muted small"><?= e($h['status_sebelumnya']) ?></span>
                  <i class="bi bi-arrow-right mx-1 text-muted"></i>
                  <strong><?= e($h['status_baru']) ?></strong>
                </td>
                <td><?= e($h['catatan'] ?: '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
