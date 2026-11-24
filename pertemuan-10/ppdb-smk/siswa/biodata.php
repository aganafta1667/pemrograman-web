<?php
require_once '../includes/auth.php';
require_role('siswa');
$uid = $_SESSION['user']['id'];
$errors = [];

$fotoTipe  = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$fotoMaks  = 2 * 1024 * 1024; // 2 MB
$folderFoto = __DIR__ . '/../uploads/foto/';

$st = $pdo->prepare('SELECT * FROM siswa WHERE id_user = ?');
$st->execute([$uid]);
$s = $st->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = array_map('trim', $_POST);
    if (!preg_match('/^\d{10}$/', $d['nisn'] ?? '')) $errors[] = 'NISN harus 10 digit angka.';
    if (!in_array($d['jenis_kelamin'] ?? '', ['L','P'])) $errors[] = 'Pilih jenis kelamin.';
    foreach (['nama_lengkap','tempat_lahir','tanggal_lahir','alamat','asal_sekolah'] as $f)
        if (empty($d[$f])) $errors[] = ucwords(str_replace('_',' ',$f)) . ' wajib diisi.';
    if (!preg_match('/^08\d{8,12}$/', $d['no_hp'] ?? '')) $errors[] = 'No HP tidak valid (contoh 08123456789).';

    $cek = $pdo->prepare('SELECT 1 FROM siswa WHERE nisn = ? AND id_user <> ?');
    $cek->execute([$d['nisn'] ?? '', $uid]);
    if ($cek->fetch()) $errors[] = 'NISN sudah digunakan.';

    $kunci = $pdo->prepare('SELECT 1 FROM pendaftaran p JOIN siswa s USING(id_siswa)
                            WHERE s.id_user = ? AND p.tahun_ajaran = ?');
    $kunci->execute([$uid, TAHUN_AJARAN]);
    if ($kunci->fetch()) $errors[] = 'Biodata tidak dapat diubah setelah pendaftaran dikirim.';

    $foto = $_FILES['foto'] ?? null;
    $adaFoto = $foto && $foto['error'] !== UPLOAD_ERR_NO_FILE;
    $ext = '';
    if ($adaFoto) {
        if ($foto['error'] === UPLOAD_ERR_INI_SIZE || $foto['error'] === UPLOAD_ERR_FORM_SIZE || $foto['size'] > $fotoMaks) {
            $errors[] = 'Ukuran foto maksimal 2 MB.';
        } elseif ($foto['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload foto gagal (kode error ' . $foto['error'] . ').';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($foto['tmp_name']);
            if (!isset($fotoTipe[$mime]) || @getimagesize($foto['tmp_name']) === false) {
                $errors[] = 'Foto harus berformat JPG atau PNG.';
            } else {
                $ext = $fotoTipe[$mime];
            }
        }
    } elseif (empty($s['foto'])) {
        $errors[] = 'Pas foto wajib diunggah.';
    }

    if (!$errors) {
        $fotoBaru = null;
        if ($adaFoto) {
            if (!is_dir($folderFoto)) mkdir($folderFoto, 0755, true);
            $fotoBaru = 'foto_' . $s['id_siswa'] . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (!move_uploaded_file($foto['tmp_name'], $folderFoto . $fotoBaru)) {
                $errors[] = 'Gagal menyimpan foto ke server. Periksa folder uploads/foto/.';
                $fotoBaru = null;
            }
        }
    }

    if (!$errors) {
        try {
            $pdo->prepare('UPDATE siswa SET nisn=?, nama_lengkap=?, jenis_kelamin=?, tempat_lahir=?,
                           tanggal_lahir=?, alamat=?, asal_sekolah=?, no_hp=?, foto=COALESCE(?, foto)
                           WHERE id_user=?')
                ->execute([$d['nisn'],$d['nama_lengkap'],$d['jenis_kelamin'],$d['tempat_lahir'],
                           $d['tanggal_lahir'],$d['alamat'],$d['asal_sekolah'],$d['no_hp'],$fotoBaru,$uid]);
            
            if ($fotoBaru && !empty($s['foto'])) @unlink($folderFoto . basename($s['foto']));
            flash('Biodata berhasil disimpan.');
            header('Location: biodata.php'); exit;
        } catch (Exception $e) {
            if ($fotoBaru) @unlink($folderFoto . $fotoBaru);
            $errors[] = 'Gagal menyimpan biodata.';
        }
    }
}

$cekKunci = $pdo->prepare('SELECT 1 FROM pendaftaran p JOIN siswa s USING(id_siswa)
                           WHERE s.id_user = ? AND p.tahun_ajaran = ?');
$cekKunci->execute([$uid, TAHUN_AJARAN]);
$terkunci = (bool)$cekKunci->fetch();

$judul = 'Biodata Siswa';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<div class="container py-4" style="max-width: 800px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="h4 mb-0">Biodata Calon Siswa</h3>
    <?php if ($terkunci): ?>
      <span class="badge bg-secondary">Data Terkunci</span>
    <?php endif; ?>
  </div>

  <?= flash() ?>

  <?php if ($terkunci): ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-info-circle-fill"></i>
      <div>Pendaftaran Anda sudah dikirim, biodata tidak dapat diubah kembali.</div>
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger py-2 mb-4">
      <ul class="mb-0 ps-3">
        <?php foreach ($errors as $er): ?>
          <li><?= e($er) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="post" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="col-12">
          <label class="form-label d-block" for="foto">Pas Foto (3x4, JPG/PNG, maks. 2 MB)</label>
          <div class="d-flex align-items-start gap-3">
            <?php if (!empty($s['foto'])): ?>
              <img id="fotoPreview" src="../lihat-foto.php?id=<?= (int)$s['id_siswa'] ?>" alt="Pas foto"
                   style="width:96px;height:128px;object-fit:cover;border:1px solid var(--border-color);border-radius:8px;background:#fff">
            <?php else: ?>
              <img id="fotoPreview" alt="Pratinjau foto" hidden
                   style="width:96px;height:128px;object-fit:cover;border:1px solid var(--border-color);border-radius:8px;background:#fff">
              <div id="fotoKosong" class="text-center text-muted small"
                   style="display:flex;align-items:center;justify-content:center;width:96px;height:128px;border:1px dashed var(--border-color);border-radius:8px;background:var(--bg-surface-subtle)">
                Belum ada foto
              </div>
            <?php endif; ?>
            <div class="flex-grow-1">
              <input type="file" id="foto" name="foto" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                     <?= empty($s['foto']) ? 'required' : '' ?> <?= $terkunci ? 'disabled' : '' ?>>
              <div class="form-text">
                Gunakan foto formal dengan wajah terlihat jelas dan latar polos.
                <?= !empty($s['foto']) ? 'Kosongkan jika tidak ingin mengganti foto.' : '' ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="nisn">NISN (10 Digit Angka)</label>
          <input type="text" id="nisn" name="nisn" class="form-control" value="<?= e($s['nisn']) ?>" maxlength="10" placeholder="Contoh: 0071234567" required <?= $terkunci ? 'disabled' : '' ?>>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="nama_lengkap">Nama Lengkap Siswa</label>
          <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= e($s['nama_lengkap']) ?>" required <?= $terkunci ? 'disabled' : '' ?>>
        </div>

        <div class="col-md-4">
          <label class="form-label" for="jenis_kelamin">Jenis Kelamin</label>
          <select id="jenis_kelamin" name="jenis_kelamin" class="form-select" required <?= $terkunci ? 'disabled' : '' ?>>
            <option value="">-- Pilih --</option>
            <option value="L" <?= $s['jenis_kelamin']==='L'?'selected':'' ?>>Laki-laki</option>
            <option value="P" <?= $s['jenis_kelamin']==='P'?'selected':'' ?>>Perempuan</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label" for="tempat_lahir">Tempat Lahir</label>
          <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control" value="<?= e($s['tempat_lahir']) ?>" placeholder="Kota / Kabupaten" required <?= $terkunci ? 'disabled' : '' ?>>
        </div>

        <div class="col-md-4">
          <label class="form-label" for="tanggal_lahir">Tanggal Lahir</label>
          <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" value="<?= e($s['tanggal_lahir']) ?>" required <?= $terkunci ? 'disabled' : '' ?>>
        </div>

        <div class="col-12">
          <label class="form-label" for="alamat">Alamat Tempat Tinggal</label>
          <textarea id="alamat" name="alamat" class="form-control" rows="2" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota" required <?= $terkunci ? 'disabled' : '' ?>><?= e($s['alamat']) ?></textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="asal_sekolah">Asal Sekolah (SMP/MTs)</label>
          <input type="text" id="asal_sekolah" name="asal_sekolah" class="form-control" value="<?= e($s['asal_sekolah']) ?>" placeholder="Nama sekolah asal" required <?= $terkunci ? 'disabled' : '' ?>>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="no_hp">Nomor Handphone / WhatsApp</label>
          <input type="text" id="no_hp" name="no_hp" class="form-control" value="<?= e($s['no_hp']) ?>" placeholder="08xxxxxxxxxx" required <?= $terkunci ? 'disabled' : '' ?>>
        </div>

        <?php if (!$terkunci): ?>
          <div class="col-12 pt-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save"></i> Simpan Biodata
            </button>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  var input = document.getElementById('foto');
  var img = document.getElementById('fotoPreview');
  var kosong = document.getElementById('fotoKosong');
  if (!input) return;
  input.addEventListener('change', function () {
    var f = input.files[0];
    if (!f) return;
    if (f.size > 2 * 1024 * 1024) {
      alert('Ukuran foto maksimal 2 MB.');
      input.value = '';
      return;
    }
    img.src = URL.createObjectURL(f);
    img.hidden = false;
    if (kosong) kosong.hidden = true;
  });
})();
</script>

<?php require_once '../includes/footer.php'; ?>
