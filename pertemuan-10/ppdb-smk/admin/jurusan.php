<?php
require_once '../includes/auth.php';
require_role('admin');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'tambah') {
        $kode = strtoupper(trim($_POST['kode']));
        $nama = trim($_POST['nama']);
        $kuota = (int)$_POST['kuota'];
        if ($kode === '' || $nama === '' || $kuota < 0) $errors[] = 'Data tidak valid.';
        else {
            try {
                $pdo->prepare('INSERT INTO jurusan (kode_jurusan,nama_jurusan,kuota) VALUES (?,?,?)')
                    ->execute([$kode, $nama, $kuota]);
                flash('Jurusan berhasil ditambahkan.');
            } catch (PDOException $e) { $errors[] = 'Kode jurusan sudah ada.'; }
        }
    } elseif ($aksi === 'kuota') {
        $pdo->prepare('UPDATE jurusan SET kuota=? WHERE id_jurusan=?')
            ->execute([max(0,(int)$_POST['kuota']), (int)$_POST['id']]);
        flash('Kuota berhasil diperbarui.');
    } elseif ($aksi === 'toggle') {
        $pdo->prepare('UPDATE jurusan SET is_active = 1 - is_active WHERE id_jurusan=?')
            ->execute([(int)$_POST['id']]);
        flash('Status jurusan berhasil diubah.');
    }
    if (!$errors) { header('Location: jurusan.php'); exit; }
}
$rows = $pdo->query('SELECT * FROM jurusan ORDER BY kode_jurusan')->fetchAll();

$judul = 'Kelola Program Keahlian (Jurusan)';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4">
  <div class="mb-3">
    <h3 class="h4 mb-1">Manajemen Program Keahlian</h3>
    <p class="text-muted small">Atur kuota daya tampung dan ketersediaan jurusan untuk calon siswa baru</p>
  </div>

  <?= flash() ?>
  <?php foreach ($errors as $er): ?>
    <div class="alert alert-danger py-2 mb-3"><?= e($er) ?></div>
  <?php endforeach; ?>

  <div class="card mb-4">
    <div class="card-header">
      Tambah Program Keahlian Baru
    </div>
    <div class="card-body">
      <form method="post" class="row g-2 align-items-end">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="aksi" value="tambah">
        
        <div class="col-md-2">
          <label class="form-label small" for="kode">Kode Jurusan</label>
          <input type="text" id="kode" name="kode" class="form-control" placeholder="Contoh: RPL" required>
        </div>
        <div class="col-md-5">
          <label class="form-label small" for="nama">Nama Program Keahlian</label>
          <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
        </div>
        <div class="col-md-2">
          <label class="form-label small" for="kuota">Daya Tampung</label>
          <input type="number" id="kuota" name="kuota" class="form-control" placeholder="Kuota" min="0" required>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-plus-lg"></i> Simpan Jurusan
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      Daftar Program Keahlian
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width: 100px;">Kode</th>
            <th>Nama Jurusan</th>
            <th style="width: 220px;">Daya Tampung (Kuota)</th>
            <th style="width: 140px;" class="text-center">Status</th>
            <th style="width: 160px;" class="text-center">Aksi Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><span class="badge-code"><?= e($r['kode_jurusan']) ?></span></td>
              <td class="fw-semibold"><?= e($r['nama_jurusan']) ?></td>
              <td>
                <form method="post" class="d-flex gap-2">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="aksi" value="kuota">
                  <input type="hidden" name="id" value="<?= $r['id_jurusan'] ?>">
                  <input type="number" name="kuota" value="<?= (int)$r['kuota'] ?>" class="form-control form-control-sm" min="0" style="max-width: 90px;" aria-label="Kapasitas Kuota">
                  <button type="submit" class="btn btn-sm btn-outline-primary" title="Simpan Perubahan Kuota">
                    <i class="bi bi-check-lg"></i> Ubah
                  </button>
                </form>
              </td>
              <td class="text-center">
                <?php if ($r['is_active']): ?>
                  <span class="badge-status">Aktif</span>
                <?php else: ?>
                  <span class="badge-status">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="aksi" value="toggle">
                  <input type="hidden" name="id" value="<?= $r['id_jurusan'] ?>">
                  <button type="submit" class="btn btn-sm <?= $r['is_active'] ? 'btn-outline-secondary' : 'btn-outline-primary' ?>">
                    <?= $r['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
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
