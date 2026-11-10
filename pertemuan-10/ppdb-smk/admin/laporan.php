<?php
require_once '../includes/auth.php';
require_role('admin');

$statusList = ['Menunggu', 'Diverifikasi', 'Diterima', 'Ditolak'];
$status = $_GET['status'] ?? '';
$jur    = (int)($_GET['jurusan'] ?? 0);
$q      = trim($_GET['q'] ?? '');

$sql = 'SELECT p.nomor_pendaftaran, s.nisn, s.nama_lengkap, s.jenis_kelamin, s.asal_sekolah,
               j.kode_jurusan, j.nama_jurusan, p.status, p.tanggal_daftar
        FROM pendaftaran p
        JOIN siswa s ON s.id_siswa = p.id_siswa
        JOIN jurusan j ON j.id_jurusan = p.id_jurusan
        WHERE p.tahun_ajaran = ?';
$params = [TAHUN_AJARAN];

if (in_array($status, $statusList)) { $sql .= ' AND p.status = ?'; $params[] = $status; }
else { $status = ''; }
if ($jur > 0) { $sql .= ' AND j.id_jurusan = ?'; $params[] = $jur; }
if ($q !== '') {
    $sql .= ' AND (s.nama_lengkap LIKE ? OR p.nomor_pendaftaran LIKE ? OR s.nisn LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
$sql .= ' ORDER BY p.tanggal_daftar DESC';

$st = $pdo->prepare($sql);
$st->execute($params);
$data = $st->fetchAll();

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_ppdb_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
    fputcsv($out, ['No. Pendaftaran','NISN','Nama','JK','Asal Sekolah','Jurusan','Status','Tanggal Daftar']);
    foreach ($data as $r) {
        fputcsv($out, [
            $r['nomor_pendaftaran'], "'" . $r['nisn'], $r['nama_lengkap'], $r['jenis_kelamin'],
            $r['asal_sekolah'], $r['nama_jurusan'], $r['status'], $r['tanggal_daftar']
        ]);
    }
    fclose($out);
    exit;
}

$rekap = $pdo->prepare("SELECT COUNT(*) total,
        COALESCE(SUM(s.jenis_kelamin='L'),0) laki,
        COALESCE(SUM(s.jenis_kelamin='P'),0) perempuan,
        COALESCE(SUM(p.status='Menunggu'),0) menunggu,
        COALESCE(SUM(p.status='Diverifikasi'),0) diverifikasi,
        COALESCE(SUM(p.status='Diterima'),0) diterima,
        COALESCE(SUM(p.status='Ditolak'),0) ditolak
    FROM pendaftaran p JOIN siswa s ON s.id_siswa = p.id_siswa
    WHERE p.tahun_ajaran = ?");
$rekap->execute([TAHUN_AJARAN]);
$R = $rekap->fetch();

$perJur = $pdo->prepare("SELECT j.kode_jurusan, j.nama_jurusan, j.kuota,
        COUNT(p.id_pendaftaran) pendaftar,
        COALESCE(SUM(p.status='Diterima'),0) diterima,
        COALESCE(SUM(p.status='Ditolak'),0) ditolak
    FROM jurusan j
    LEFT JOIN pendaftaran p ON p.id_jurusan = j.id_jurusan AND p.tahun_ajaran = ?
    GROUP BY j.id_jurusan ORDER BY j.kode_jurusan");
$perJur->execute([TAHUN_AJARAN]);
$perJur = $perJur->fetchAll();

$semuaJurusan = $pdo->query('SELECT id_jurusan, kode_jurusan FROM jurusan ORDER BY kode_jurusan')->fetchAll();

$exportUrl = '?' . http_build_query(['status' => $status, 'jurusan' => $jur ?: '', 'q' => $q, 'export' => 1]);

$judul = 'Laporan & Statistik PPDB';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
    <div>
      <h3 class="h4 mb-1">Laporan PPDB Tahun Ajaran <?= e(TAHUN_AJARAN) ?></h3>
      <p class="text-muted small mb-0">Statistik demografi calon peserta didik dan status penerimaan</p>
    </div>
    <div>
      <a href="<?= e($exportUrl) ?>" class="btn btn-sm btn-primary">
        <i class="bi bi-file-earmark-spreadsheet"></i> Unduh File CSV (Excel)
      </a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <span class="stat-label">Total Pendaftar</span>
        <div class="stat-value text-primary"><?= (int)$R['total'] ?></div>
        <span class="small text-muted">Berkas masuk sistem</span>
      </div>
    </div>
    
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <span class="stat-label">Gender (L / P)</span>
        <div class="stat-value" style="font-size: 1.7rem;">
          <?= (int)$R['laki'] ?> <span class="text-muted fw-normal fs-5">/</span> <?= (int)$R['perempuan'] ?>
        </div>
        <span class="small text-muted">Laki-laki / Perempuan</span>
      </div>
    </div>
    
    <div class="col-6 col-md-3">
      <div class="stat-card" style="border-left: 4px solid var(--status-diterima-text);">
        <span class="stat-label">Siswa Diterima</span>
        <div class="stat-value" style="color: var(--status-diterima-text);"><?= (int)$R['diterima'] ?></div>
        <span class="small text-muted">Memenuhi kuota jurusan</span>
      </div>
    </div>
    
    <div class="col-6 col-md-3">
      <div class="stat-card" style="border-left: 4px solid var(--status-ditolak-text);">
        <span class="stat-label">Siswa Ditolak</span>
        <div class="stat-value" style="color: var(--status-ditolak-text);"><?= (int)$R['ditolak'] ?></div>
        <span class="small text-muted">Belum memenuhi syarat</span>
      </div>
    </div>
  </div>

  <div class="alert alert-light border d-flex flex-wrap gap-4 align-items-center mb-4 py-2 px-3 small">
    <div><i class="bi bi-clock-history text-muted me-1"></i> Status Menunggu: <strong><?= (int)$R['menunggu'] ?></strong> berkas</div>
    <div><i class="bi bi-check2-circle text-primary me-1"></i> Status Diverifikasi: <strong><?= (int)$R['diverifikasi'] ?></strong> berkas</div>
  </div>

  <div class="card mb-4">
    <div class="card-header">
      Rekapitulasi per Program Keahlian
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Program Keahlian</th>
            <th class="text-center">Kapasitas Kuota</th>
            <th class="text-center">Total Pendaftar</th>
            <th class="text-center">Diterima</th>
            <th class="text-center">Ditolak</th>
            <th class="text-center">Sisa Kuota</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($perJur as $r): 
            $sisa = max(0, (int)$r['kuota'] - (int)$r['diterima']);
          ?>
            <tr>
              <td>
                <span class="badge-code me-1"><?= e($r['kode_jurusan']) ?></span>
                <span class="fw-semibold"><?= e($r['nama_jurusan']) ?></span>
              </td>
              <td class="text-center"><?= (int)$r['kuota'] ?></td>
              <td class="text-center"><?= (int)$r['pendaftar'] ?></td>
              <td class="text-center">
                <span class="badge-status"><?= (int)$r['diterima'] ?></span>
              </td>
              <td class="text-center">
                <span class="badge-statusk"><?= (int)$r['ditolak'] ?></span>
              </td>
              <td class="text-center">
                <span class="badge-status">
                  <?= $sisa ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      Data Rinci Calon Peserta Didik
    </div>
    <div class="card-body border-bottom bg-light">
      <form method="get" class="row g-2 align-items-center">
        <div class="col-md-4">
          <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama / no. daftar / NISN..." value="<?= e($q) ?>">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select form-select-sm" aria-label="Filter status">
            <option value="">Semua Status</option>
            <?php foreach ($statusList as $s): ?>
              <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <select name="jurusan" class="form-select form-select-sm" aria-label="Filter jurusan">
            <option value="">Semua Jurusan</option>
            <?php foreach ($semuaJurusan as $j): ?>
              <option value="<?= $j['id_jurusan'] ?>" <?= $jur === (int)$j['id_jurusan'] ? 'selected' : '' ?>><?= e($j['kode_jurusan']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-sm btn-primary w-100">
            <i class="bi bi-search"></i> Cari
          </button>
          <a href="laporan.php" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width: 40px;" class="text-center">#</th>
            <th style="width: 200px;">No. Daftar</th>
            <th style="width: 110px;">NISN</th>
            <th>Nama Lengkap</th>
            <th style="width: 60px;" class="text-center">JK</th>
            <th>Asal Sekolah</th>
            <th style="width: 90px;" class="text-center">Jurusan</th>
            <th style="width: 120px;" class="text-center">Status</th>
            <th style="width: 110px;" class="text-center">Tgl Daftar</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$data): ?>
            <tr>
              <td colspan="9" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-1"></i> Tidak ditemukan data calon peserta didik yang sesuai.
              </td>
            </tr>
          <?php endif; ?>

          <?php foreach ($data as $i => $r): ?>
            <tr>
              <td class="text-center text-muted small"><?= $i + 1 ?></td>
              <td class="font-heading small fw-bold text-primary"><?= e($r['nomor_pendaftaran']) ?></td>
              <td class="font-monospace small text-muted"><?= e($r['nisn']) ?></td>
              <td class="fw-semibold text-main"><?= e($r['nama_lengkap']) ?></td>
              <td class="text-center">
                <span class="badge bg-light text-dark border"><?= e($r['jenis_kelamin']) ?></span>
              </td>
              <td class="small text-muted"><?= e($r['asal_sekolah']) ?></td>
              <td class="text-center"><span class="badge-code"><?= e($r['kode_jurusan']) ?></span></td>
              <td class="text-center">
                <span class="badge-status <?= $badgeClass ?>"><?= e($r['status']) ?></span>
              </td>
              <td class="text-center small text-muted"><?= e(date('d-m-Y', strtotime($r['tanggal_daftar']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white text-muted small py-2">
      Menampilkan <strong><?= count($data) ?></strong> data calon siswa.
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
