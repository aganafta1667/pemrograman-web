<?php
require_once '../includes/auth.php';
require_role('siswa');
$uid = $_SESSION['user']['id'];
$errors = [];

$st = $pdo->prepare('SELECT * FROM siswa WHERE id_user = ?');
$st->execute([$uid]);
$s = $st->fetch();

$lengkap = true;
foreach (['nisn','nama_lengkap','jenis_kelamin','tempat_lahir','tanggal_lahir','alamat','asal_sekolah','no_hp'] as $f)
    if (empty($s[$f])) $lengkap = false;

$st = $pdo->prepare('SELECT p.*, j.nama_jurusan FROM pendaftaran p
                     JOIN jurusan j USING(id_jurusan) WHERE id_siswa=? AND tahun_ajaran=?');
$st->execute([$s['id_siswa'], TAHUN_AJARAN]);
$daftar = $st->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$daftar && $lengkap) {
    csrf_check();
    $idj = (int)($_POST['id_jurusan'] ?? 0);
    $cek = $pdo->prepare('SELECT 1 FROM jurusan WHERE id_jurusan=? AND is_active=1');
    $cek->execute([$idj]);
    if (!$cek->fetch()) $errors[] = 'Jurusan tidak valid.';
    else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO pendaftaran (id_siswa,id_jurusan,tahun_ajaran) VALUES (?,?,?)')
                ->execute([$s['id_siswa'], $idj, TAHUN_AJARAN]);
            $id = $pdo->lastInsertId();
            $nomor = 'PPDB-' . substr(TAHUN_AJARAN, 0, 4) . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
            $pdo->prepare('UPDATE pendaftaran SET nomor_pendaftaran=? WHERE id_pendaftaran=?')
                ->execute([$nomor, $id]);
            $pdo->commit();
            flash("Pendaftaran terkirim. Nomor Anda: $nomor");
            header('Location: pendaftaran.php'); exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Gagal menyimpan pendaftaran.';
        }
    }
}
$jurusan = $pdo->query('SELECT * FROM jurusan WHERE is_active=1 ORDER BY nama_jurusan')->fetchAll();

$judul = 'Pendaftaran PPDB';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4" style="max-width: 800px;">
  <div class="mb-3">
    <h3 class="h4 mb-1">Pendaftaran PPDB <?= e(TAHUN_AJARAN) ?></h3>
    <p class="text-muted small">Pilih program keahlian untuk melanjutkan tahapan verifikasi</p>
  </div>

  <?= flash() ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger py-2 mb-3">
      <ul class="mb-0 ps-3">
        <?php foreach ($errors as $er): ?>
          <li><?= e($er) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($daftar): ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Bukti Pengajuan Pendaftaran</span>
        <span class="badge-status badge-status-menunggu"><?= e($daftar['status']) ?></span>
      </div>
      <div class="card-body">
        <div class="p-3 bg-light rounded-3 mb-3">
          <div class="small text-muted">Nomor Pendaftaran Resmi:</div>
          <div class="fs-4 fw-bold text-primary"><?= e($daftar['nomor_pendaftaran']) ?></div>
        </div>
        <dl class="row mb-0">
          <dt class="col-sm-4 text-muted">Pilihan Jurusan</dt>
          <dd class="col-sm-8 fw-semibold"><?= e($daftar['nama_jurusan']) ?></dd>
          
          <dt class="col-sm-4 text-muted">Tahun Ajaran</dt>
          <dd class="col-sm-8"><?= e($daftar['tahun_ajaran']) ?></dd>
          
          <dt class="col-sm-4 text-muted">Tanggal Kirim</dt>
          <dd class="col-sm-8"><?= e(date('d-m-Y H:i', strtotime($daftar['tanggal_daftar']))) ?> WIB</dd>
        </dl>
      </div>
      <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
        <span class="small text-muted"><i class="bi bi-check-circle text-success me-1"></i> Data pendaftaran telah terkunci.</span>
        <a href="upload_berkas.php" class="btn btn-sm btn-primary">
          <i class="bi bi-cloud-arrow-up"></i> Upload Berkas Sekarang
        </a>
      </div>
    </div>

  <?php elseif (!$lengkap): ?>
    <div class="card border-warning">
      <div class="card-body">
        <div class="d-flex align-items-start gap-3">
          <div>
            <h5 class="h6 text-warning-emphasis mb-1">Biodata Belum Lengkap</h5>
            <p class="text-muted small mb-3">
              Anda wajib melengkapi seluruh informasi biodata diri dan kontak sebelum dapat memilih jurusan dan mengirim pendaftaran.
            </p>
            <a href="biodata.php" class="btn btn-warning btn-sm">
              <i class="bi bi-pencil-square"></i> Lengkapi Biodata
            </a>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    <div class="card">
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          
          <div class="mb-4">
            <label class="form-label" for="id_jurusan">Pilih Program Keahlian (Jurusan)</label>
            <select name="id_jurusan" id="id_jurusan" class="form-select" required>
              <option value="">-- Pilih Program Keahlian --</option>
              <?php foreach ($jurusan as $j): ?>
                <option value="<?= $j['id_jurusan'] ?>">
                  <?= e($j['kode_jurusan'].' - '.$j['nama_jurusan']) ?> (Sisa Kuota: <?= (int)$j['kuota'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Pastikan pilihan jurusan sudah sesuai dengan bakat dan minat Anda.</div>
          </div>

          <div class="alert alert-info small mb-4">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Peringatan Penting:</strong> Setelah formulir pendaftaran dikirim, pilihan jurusan dan biodata tidak dapat diubah kembali secara mandiri.
          </div>

          <button type="submit" class="btn btn-accent w-100" onclick="return confirm('Kirim pendaftaran? Data dan pilihan jurusan tidak dapat diubah setelah dikirim.')">
            <i class="bi bi-send-check"></i> Kirim Pendaftaran Sekarang
          </button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
