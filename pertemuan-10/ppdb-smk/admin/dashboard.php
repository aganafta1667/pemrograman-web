<?php
require_once '../includes/auth.php';
require_role('admin');

$total = $pdo->query('SELECT COUNT(*) FROM pendaftaran')->fetchColumn();
$perStatus = $pdo->query('SELECT status, COUNT(*) n FROM pendaftaran GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$perJurusan = $pdo->query("SELECT j.kode_jurusan, j.nama_jurusan, j.kuota, COUNT(p.id_pendaftaran) daftar,
    SUM(p.status='Diterima') diterima
    FROM jurusan j LEFT JOIN pendaftaran p ON p.id_jurusan=j.id_jurusan GROUP BY j.id_jurusan")->fetchAll();

$judul = 'Dashboard Admin';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
    <div>
      <h3 class="h4 mb-1">Dashboard Administrator</h3>
      <p class="text-muted small mb-0">Ringkasan pendaftaran dan kuota PPDB SMK Nusantara</p>
    </div>
    <div class="d-flex gap-2">
      <a href="verifikasi.php" class="btn btn-primary btn-sm">
        <i class="bi bi-check2-square"></i> Verifikasi Berkas
      </a>
      <a href="laporan.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-file-earmark-bar-graph"></i> Buka Laporan
      </a>
    </div>
  </div>

  <?= flash() ?>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
      <div class="stat-card">
        <span class="stat-label">Total Pendaftar</span>
        <div class="stat-value text-primary"><?= (int)$total ?></div>
      </div>
    </div>
    
    <div class="col-6 col-md-4 col-lg-2">
      <div class="stat-card" style="border-left: 4px solid var(--status-menunggu-text);">
        <span class="stat-label">Menunggu</span>
        <div class="stat-value" style="color: var(--status-menunggu-text);"><?= (int)($perStatus['Menunggu'] ?? 0) ?></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="stat-card" style="border-left: 4px solid var(--status-diverifikasi-text);">
        <span class="stat-label">Diverifikasi</span>
        <div class="stat-value" style="color: var(--status-diverifikasi-text);"><?= (int)($perStatus['Diverifikasi'] ?? 0) ?></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="stat-card" style="border-left: 4px solid var(--status-diterima-text);">
        <span class="stat-label">Diterima</span>
        <div class="stat-value" style="color: var(--status-diterima-text);"><?= (int)($perStatus['Diterima'] ?? 0) ?></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="stat-card" style="border-left: 4px solid var(--status-ditolak-text);">
        <span class="stat-label">Ditolak</span>
        <div class="stat-value" style="color: var(--status-ditolak-text);"><?= (int)($perStatus['Ditolak'] ?? 0) ?></div>
      </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
      <div class="stat-card">
        <span class="stat-label">Jurusan Aktif</span>
        <div class="stat-value text-muted"><?= count($perJurusan) ?></div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      Ringkasan Keterisian Kuota Jurusan</span>
      <a href="jurusan.php" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-gear"></i> Kelola Jurusan
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Kode</th>
            <th>Program Keahlian</th>
            <th class="text-center">Kapasitas Kuota</th>
            <th class="text-center">Total Pelamar</th>
            <th class="text-center">Diterima</th>
            <th class="text-center">Sisa Kuota</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($perJurusan as $r): 
            $sisa = max(0, (int)$r['kuota'] - (int)$r['diterima']);
          ?>
            <tr>
              <td><span class="badge-code"><?= e($r['kode_jurusan']) ?></span></td>
              <td class="fw-semibold"><?= e($r['nama_jurusan'] ?? $r['kode_jurusan']) ?></td>
              <td class="text-center"><?= (int)$r['kuota'] ?></td>
              <td class="text-center"><?= (int)$r['daftar'] ?></td>
              <td class="text-center">
                <span class="badge-status"><?= (int)$r['diterima'] ?></span>
              </td>
              <td class="text-center">
                <span class="badge-status"><?= $sisa ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
