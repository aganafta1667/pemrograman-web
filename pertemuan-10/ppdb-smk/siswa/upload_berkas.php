<?php
require_once '../includes/auth.php';
require_role('siswa');
$uid = $_SESSION['user']['id'];

$st = $pdo->prepare('SELECT p.id_pendaftaran, p.status FROM pendaftaran p
                     JOIN siswa s USING(id_siswa)
                     WHERE s.id_user = ? AND p.tahun_ajaran = ?');
$st->execute([$uid, TAHUN_AJARAN]);
$p = $st->fetch();
if (!$p) {
    flash('Kirim pendaftaran terlebih dahulu sebelum mengunggah berkas.', 'warning');
    header('Location: pendaftaran.php'); exit;
}
$idp = $p['id_pendaftaran'];
$bisaUpload = ($p['status'] === 'Menunggu');   // setelah diverifikasi, berkas dikunci

$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
$jenisList = ['KK' => 'Kartu Keluarga', 'Akta' => 'Akta Kelahiran', 'Rapor' => 'Rapor', 'Foto' => 'Pas Foto'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $jenis = $_POST['jenis_berkas'] ?? '';
    $f = $_FILES['file'] ?? null;

    if (!$bisaUpload) {
        flash('Berkas tidak dapat diubah karena pendaftaran sudah diproses.', 'danger');
    } elseif (!isset($jenisList[$jenis])) {
        flash('Jenis berkas tidak valid.', 'danger');
    } elseif (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        flash('Pilih file terlebih dahulu.', 'danger');
    } elseif ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE || $f['size'] > 2 * 1024 * 1024) {
        flash('Ukuran file maksimal 2 MB.', 'danger');
    } elseif ($f['error'] !== UPLOAD_ERR_OK) {
        flash('Upload gagal (kode error ' . $f['error'] . ').', 'danger');
    } else {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!isset($allowed[$mime])) {
            flash('Format harus JPG, PNG, atau PDF.', 'danger');
        } else {
            $folder = __DIR__ . '/../uploads/';
            if (!is_dir($folder)) mkdir($folder, 0755, true);

            $nama = $idp . '_' . $jenis . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($f['tmp_name'], $folder . $nama)) {
                flash('Gagal menyimpan file ke server. Periksa folder uploads/.', 'danger');
            } else {
                try {
                    $pdo->beginTransaction();
                    $lama = $pdo->prepare('SELECT id_berkas, nama_file FROM berkas
                                           WHERE id_pendaftaran = ? AND jenis_berkas = ?');
                    $lama->execute([$idp, $jenis]);
                    $hapusFile = [];
                    foreach ($lama->fetchAll() as $row) {
                        $pdo->prepare('DELETE FROM berkas WHERE id_berkas = ?')->execute([$row['id_berkas']]);
                        $hapusFile[] = $row['nama_file'];
                    }
                    $pdo->prepare('INSERT INTO berkas (id_pendaftaran, jenis_berkas, nama_file) VALUES (?,?,?)')
                        ->execute([$idp, $jenis, $nama]);
                    $pdo->commit();
                    foreach ($hapusFile as $hf) @unlink($folder . basename($hf));
                    flash($jenisList[$jenis] . ' berhasil diunggah.');
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    @unlink($folder . $nama);
                    flash('Gagal menyimpan data berkas.', 'danger');
                }
            }
        }
    }
    header('Location: upload_berkas.php'); exit;
}

$b = $pdo->prepare('SELECT * FROM berkas WHERE id_pendaftaran = ?');
$b->execute([$idp]);
$terunggah = [];
foreach ($b->fetchAll() as $row) $terunggah[$row['jenis_berkas']] = $row;

$judul = 'Upload Berkas Persyaratan';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4" style="max-width: 1200px;">
  <div class="mb-3">
    <h3 class="h4 mb-1">Unggah Berkas Persyaratan</h3>
    <p class="text-muted small">Unggah dokumen asli yang dapat terbaca jelas untuk proses verifikasi panitia</p>
  </div>

  <?= flash() ?>

  <?php if ($bisaUpload): ?>
    <div class="card mb-4">
      <div class="card-header">
        <i class="bi bi-cloud-arrow-up me-1 text-primary"></i> Formulir Unggah Dokumen
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="row g-3">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          
          <div class="col-md-5">
            <label class="form-label" for="jenis_berkas">Pilih Jenis Dokumen</label>
            <select name="jenis_berkas" id="jenis_berkas" class="form-select" required>
              <option value="">-- Pilih Jenis Dokumen --</option>
              <?php foreach ($jenisList as $k => $v): ?>
                <option value="<?= $k ?>"><?= e($v) ?> <?= isset($terunggah[$k]) ? '(Sudah Ada)' : '' ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-7">
            <label class="form-label" for="file">Pilih Berkas (JPG, PNG, PDF &ndash; maks 2MB)</label>
            <input type="file" name="file" id="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
          </div>

          <div class="col-12 pt-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-upload"></i> Unggah Dokumen
            </button>
            <span class="text-muted small ms-2">Mengunggah ulang dokumen yang sama akan menimpa file lama.</span>
          </div>
        </form>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-lock-fill"></i>
      <div>Pendaftaran Anda sudah diverifikasi atau diproses panitia. Berkas dokumen telah dikunci dan tidak dapat diubah lagi.</div>
    </div>
  <?php endif; ?>

  <h5 class="h6 text-muted text-uppercase fw-bold mb-3">Daftar Kelengkapan Dokumen</h5>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Nama Dokumen</th>
          <th>Status Kelengkapan</th>
          <th>Waktu Unggah</th>
          <th style="width: 200px;" class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jenisList as $k => $v): $r = $terunggah[$k] ?? null; ?>
          <tr>
            <td class="fw-semibold">
              <i class="bi bi-file-earmark-text text-muted me-1"></i> <?= e($v) ?>
            </td>
            <td>
              <?php if ($r): ?>
                <span class="badge-status">Lengkap</span>
              <?php else: ?>
                <span class="badge-status">Belum Ada</span>
              <?php endif; ?>
            </td>
            <td class="small text-muted">
              <?= $r ? e(date('d-m-Y H:i', strtotime($r['tanggal_upload']))) . ' WIB' : '-' ?>
            </td>
            <td class="text-center">
              <?php if ($r): ?>
                <a class="btn btn-sm btn-outline-primary" target="_blank" href="../download.php?id=<?= (int)$r['id_berkas'] ?>">
                  <i class="bi bi-box-arrow-up-right"></i> Buka File
                </a>
              <?php else: ?>
                <span class="text-muted small">&ndash;</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
