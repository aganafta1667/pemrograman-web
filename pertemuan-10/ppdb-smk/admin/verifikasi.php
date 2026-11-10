<?php
require_once '../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)$_POST['id'];
    $baru = $_POST['status'] ?? '';
    $catatan = trim($_POST['catatan'] ?? '');

    if (!in_array($baru, ['Menunggu','Diverifikasi','Diterima','Ditolak'])) {
        flash('Status tidak valid.', 'danger');
    } else {
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare('SELECT status, id_jurusan FROM pendaftaran WHERE id_pendaftaran=? FOR UPDATE');
            $st->execute([$id]);
            $p = $st->fetch();
            if (!$p) throw new Exception('Data tidak ditemukan.');
            if ($p['status'] === $baru) throw new Exception('Status sama dengan sebelumnya.');

            if ($baru === 'Diterima') {   // aturan: diterima <= kuota
                $q = $pdo->prepare("SELECT j.kuota,
                    (SELECT COUNT(*) FROM pendaftaran WHERE id_jurusan=j.id_jurusan AND status='Diterima') AS terisi
                    FROM jurusan j WHERE j.id_jurusan=? FOR UPDATE");
                $q->execute([$p['id_jurusan']]);
                $k = $q->fetch();
                if ($k['terisi'] >= $k['kuota']) throw new Exception('Kuota jurusan sudah penuh.');
            }
            $pdo->prepare('UPDATE pendaftaran SET status=? WHERE id_pendaftaran=?')->execute([$baru, $id]);
            $pdo->prepare('INSERT INTO riwayat_status (id_pendaftaran,status_sebelumnya,status_baru,catatan,id_admin)
                           VALUES (?,?,?,?,?)')
                ->execute([$id, $p['status'], $baru, $catatan, $_SESSION['user']['id']]);
            $pdo->commit();
            flash('Status berhasil diperbarui.');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash($e->getMessage(), 'danger');
        }
    }
    header('Location: verifikasi.php'); exit;
}

$filter = $_GET['status'] ?? '';
$sql = 'SELECT p.*, s.nama_lengkap, s.nisn, s.asal_sekolah, j.kode_jurusan
        FROM pendaftaran p JOIN siswa s USING(id_siswa) JOIN jurusan j USING(id_jurusan)';
$params = [];
if ($filter !== '') { $sql .= ' WHERE p.status = ?'; $params[] = $filter; }
$st = $pdo->prepare($sql . ' ORDER BY p.tanggal_daftar DESC');
$st->execute($params);
$rows = $st->fetchAll();

$judul = 'Verifikasi Berkas Pendaftaran';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <div>
      <h3 class="h4 mb-1">Verifikasi &amp; Seleksi Berkas</h3>
      <p class="text-muted small mb-0">Periksa dokumen calon siswa dan tetapkan status penerimaan</p>
    </div>

    <form class="d-flex align-items-center gap-2">
      <label class="form-label small mb-0 text-muted text-nowrap"><i class="bi bi-funnel"></i> Filter:</label>
      <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Semua</option>
        <?php foreach (['Menunggu','Diverifikasi','Diterima','Ditolak'] as $s): ?>
          <option value="<?= $s ?>" <?= $filter===$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <?= flash() ?>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width: 200px;">No. Daftar</th>
            <th>Data Calon Siswa &amp; Berkas</th>
            <th>NISN</th>
            <th style="width: 90px;">Jurusan</th>
            <th style="width: 130px;">Status</th>
            <th style="min-width: 300px;">Ubah Status &amp; Catatan</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-1"></i> Tidak ada data pendaftaran yang sesuai dengan filter.
              </td>
            </tr>
          <?php endif; ?>

          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="fw-bold text-primary font-heading small">
                <?= e($r['nomor_pendaftaran']) ?>
                <div class="text-muted fw-normal" style="font-size: 0.75rem;">
                  <?= e(date('d-m-Y H:i', strtotime($r['tanggal_daftar']))) ?>
                </div>
              </td>
              <td>
                <div class="fw-semibold text-main"><?= e($r['nama_lengkap']) ?></div>
                <div class="small text-muted mb-1"><?= e($r['asal_sekolah'] ?: '-') ?></div>
                
                <div class="d-flex flex-wrap gap-1 mt-1">
                  <?php
                    $bk = $pdo->prepare('SELECT id_berkas, jenis_berkas FROM berkas WHERE id_pendaftaran=?');
                    $bk->execute([$r['id_pendaftaran']]);
                    $listBerkas = $bk->fetchAll();
                  ?>
                  <?php if (!$listBerkas): ?>
                    <span class="badge bg-light text-muted border small">Belum ada berkas</span>
                  <?php else: ?>
                    <?php foreach ($listBerkas as $x): ?>
                      <a href="../download.php?id=<?= (int)$x['id_berkas'] ?>" target="_blank" class="badge bg-light text-primary border text-decoration-none" title="Lihat Berkas">
                        <i class="bi bi-file-earmark-arrow-down"></i> <?= e($x['jenis_berkas']) ?>
                      </a>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </td>
              <td class="small text-muted font-monospace"><?= e($r['nisn'] ?: '-') ?></td>
              <td><span class="badge-code"><?= e($r['kode_jurusan']) ?></span></td>
              <td>
                <span class="badge-status <?= $badgeClass ?>"><?= e($r['status']) ?></span>
              </td>
              <td>
                <form method="post" class="d-flex gap-1 align-items-center">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="id" value="<?= $r['id_pendaftaran'] ?>">
                  
                  <select name="status" class="form-select form-select-sm" style="max-width: 130px;" aria-label="Status Pendaftaran">
                    <?php foreach (['Menunggu','Diverifikasi','Diterima','Ditolak'] as $s): ?>
                      <option value="<?= $s ?>" <?= $r['status']===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                  
                  <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan untuk siswa (opsional)" aria-label="Catatan Verifikasi">
                  
                  <button type="submit" class="btn btn-sm btn-primary" title="Simpan Perubahan Status">
                    Simpan
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
