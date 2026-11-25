<?php
if (!isset($v, $errors)) { http_response_code(404); exit; }
?>
<?php if ($errors): ?>
  <div class="alert alert-danger py-2 mb-3">
    <ul class="mb-0 ps-3">
      <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($isSelf): ?>
  <div class="alert alert-info py-2 mb-3 small">
    Ini akun Anda sendiri. Role dan status aktif dikunci agar Anda tidak kehilangan akses.
  </div>
<?php endif; ?>

<form method="post" class="row g-3" autocomplete="off">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

  <div class="col-12">
    <label class="form-label" for="nama">Nama Lengkap <span class="text-danger">*</span></label>
    <input type="text" id="nama" name="nama" class="form-control" maxlength="100" value="<?= e($v['nama']) ?>" required>
  </div>

  <div class="col-12">
    <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
    <input type="email" id="email" name="email" class="form-control" maxlength="100" value="<?= e($v['email']) ?>" required>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="password">
      <?= $isEdit ? 'Password Baru' : 'Password' ?> <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?>
    </label>
    <div class="input-group">
      <input type="password" id="password" name="password" class="form-control" minlength="8"
             placeholder="<?= $isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' ?>"
             autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
      <button type="button" class="btn btn-outline-secondary" id="tglPass" aria-label="Tampilkan password">
        <i class="bi bi-eye"></i>
      </button>
    </div>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="konfirmasi">Konfirmasi Password</label>
    <input type="password" id="konfirmasi" name="konfirmasi" class="form-control" autocomplete="new-password" <?= $isEdit ? '' : 'required' ?>>
  </div>

  <div class="col-md-6">
    <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
    <select id="role" name="role" class="form-select" required <?= $isSelf ? 'disabled' : '' ?>>
      <option value="">-- Pilih Role --</option>
      <?php foreach (ROLE_LABEL as $k => $lbl): ?>
        <option value="<?= e($k) ?>" <?= $v['role'] === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-6 d-flex align-items-end">
    <div class="form-check form-switch mb-2">
      <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
             <?= $v['aktif'] ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?>>
      <label class="form-check-label" for="is_active">Akun aktif</label>
    </div>
  </div>

  <div class="col-12">
    <div class="small text-muted border rounded p-2" style="background:var(--bg-surface-subtle)">
      <strong>Hak akses:</strong>
      Admin mengelola seluruh sistem &middot; Panitia memantau pendaftaran dan laporan &middot;
      Verifikator memeriksa dokumen dan status pendaftaran &middot; Siswa mengelola data pendaftarannya sendiri.
    </div>
  </div>

  <div class="col-12 d-flex gap-2 pt-1">
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> <?= e($labelSimpan) ?></button>
    <a href="index.php" class="btn btn-outline-secondary">Batal</a>
  </div>
</form>

<script>
document.getElementById('tglPass').addEventListener('click', function () {
  var p = document.getElementById('password');
  var k = document.getElementById('konfirmasi');
  var tampil = p.type === 'password';
  p.type = k.type = tampil ? 'text' : 'password';
  this.innerHTML = tampil ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
});
</script>
