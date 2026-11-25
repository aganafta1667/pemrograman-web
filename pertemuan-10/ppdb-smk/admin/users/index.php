<?php
require_once '../../includes/auth.php';
require_once '../../includes/users.php';
require_role(AKSES['users']);
$meId = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'toggle') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $st = $pdo->prepare('SELECT id_user, nama, email, role, is_active FROM users WHERE id_user = ?');
    $st->execute([$id]);
    $t = $st->fetch();

    if (!$t) {
        flash('Pengguna tidak ditemukan.', 'danger');
    } elseif ($id === $meId) {
        flash('Anda tidak dapat menonaktifkan akun Anda sendiri.', 'danger');
    } elseif ($t['role'] === 'admin' && $t['is_active'] && jumlah_admin_aktif($id) === 0) {
        flash('Admin aktif terakhir tidak boleh dinonaktifkan.', 'danger');
    } else {
        $baru = $t['is_active'] ? 0 : 1;
        $pdo->prepare('UPDATE users SET is_active = ? WHERE id_user = ?')->execute([$baru, $id]);
        log_activity(($baru ? 'Mengaktifkan' : 'Menonaktifkan') . " akun {$t['email']} (#$id)");
        flash('Akun ' . $t['nama'] . ' berhasil ' . ($baru ? 'diaktifkan.' : 'dinonaktifkan.'));
    }
    header('Location: ' . $_SERVER['REQUEST_URI']); exit;
}

$q      = trim($_GET['q'] ?? '');
$role   = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
if (!isset(ROLE_LABEL[$role])) $role = '';
if (!in_array($status, ['aktif', 'nonaktif'], true)) $status = '';

$where = []; $params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    if (ctype_digit($q)) {
        $where[] = '(nama LIKE ? OR email LIKE ? OR id_user = ?)';
        array_push($params, $like, $like, (int)$q);
    } else {
        $where[] = '(nama LIKE ? OR email LIKE ?)';
        array_push($params, $like, $like);
    }
}
if ($role !== '')   { $where[] = 'role = ?'; $params[] = $role; }
if ($status !== '') { $where[] = 'is_active = ?'; $params[] = $status === 'aktif' ? 1 : 0; }
$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$perHalaman = 10;
$c = $pdo->prepare('SELECT COUNT(*) FROM users' . $sqlWhere);
$c->execute($params);
$total = (int)$c->fetchColumn();
$totalHalaman = max(1, (int)ceil($total / $perHalaman));
$hal = min(max(1, (int)($_GET['hal'] ?? 1)), $totalHalaman);
$offset = ($hal - 1) * $perHalaman;

$st = $pdo->prepare('SELECT id_user, nama, email, role, is_active, created_at FROM users' . $sqlWhere
    . ' ORDER BY id_user DESC LIMIT ' . (int)$perHalaman . ' OFFSET ' . (int)$offset);
$st->execute($params);
$users = $st->fetchAll();

$perRole = $pdo->query('SELECT role, COUNT(*) FROM users GROUP BY role')->fetchAll(PDO::FETCH_KEY_PAIR);
$totalSemua = array_sum($perRole);

function url_hal(int $n): string {
    return '?' . http_build_query(array_merge($_GET, ['hal' => $n]));
}

$judul = 'Manajemen Pengguna';
$css_prefix = '../../';
require_once '../../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
    <div>
      <h3 class="h4 mb-1">Manajemen Pengguna</h3>
      <p class="text-muted small mb-0">Kelola akun dan hak akses pengguna PPDB</p>
    </div>
    <div class="d-flex gap-2">
      <a href="logs.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-clock-history"></i> Log Aktivitas</a>
      <a href="create.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Pengguna</a>
    </div>
  </div>

  <?= flash() ?>

  <div class="row g-4 mb-4">
    <div class="col-5 col-lg-2"><div class="stat-card">
      <span class="stat-label">Total Pengguna</span>
      <div class="stat-value text-primary"><?= (int)$totalSemua ?></div></div></div>
    <?php foreach (ROLE_LABEL as $k => $lbl): ?>
      <div class="col-5 col-lg-2"><div class="stat-card">
        <span class="stat-label"><?= e($lbl) ?></span>
        <div class="stat-value"><?= (int)($perRole[$k] ?? 0) ?></div></div></div>
    <?php endforeach; ?>
  </div>

  <form method="get" class="row g-2 mb-3">
    <div class="col-md-5">
      <input type="search" name="q" class="form-control" placeholder="Cari nama, email, atau ID" value="<?= e($q) ?>" aria-label="Cari pengguna">
    </div>
    <div class="col-6 col-md-2">
      <select name="role" class="form-select" aria-label="Filter role">
        <option value="">Semua role</option>
        <?php foreach (ROLE_LABEL as $k => $lbl): ?>
          <option value="<?= e($k) ?>" <?= $role === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <select name="status" class="form-select" aria-label="Filter status">
        <option value="">Semua status</option>
        <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
        <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
      <a href="index.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width:56px">No</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th class="text-end" style="width:150px">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$users): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pengguna yang cocok.</td></tr>
        <?php endif; ?>
        <?php foreach ($users as $i => $u): $self = ((int)$u['id_user'] === $meId); ?>
          <tr>
            <td><?= $offset + $i + 1 ?></td>
            <td class="fw-semibold">
              <?= e($u['nama']) ?>
              <?php if ($self): ?><span class="text-muted fw-normal small">(Anda)</span><?php endif; ?>
              <div class="text-muted fw-normal small">ID <?= (int)$u['id_user'] ?></div>
            </td>
            <td><?= e($u['email']) ?></td>
            <td><?= role_badge($u['role']) ?></td>
            <td>
              <?php if ($u['is_active']): ?>
                <span>Aktif</span>
              <?php else: ?>
                <span">Nonaktif</span>
              <?php endif; ?>
            </td>
            <td class="small text-muted"><?= e(date('d-m-Y', strtotime($u['created_at']))) ?></td>
            <td class="text-end">
              <div class="d-inline-flex gap-1">
                <a href="edit.php?id=<?= (int)$u['id_user'] ?>" class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Edit <?= e($u['nama']) ?>">
                  <i class="bi bi-pencil-square"></i></a>
                <form method="post" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="aksi" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$u['id_user'] ?>">
                  <button class="btn btn-sm btn-outline-secondary" <?= $self ? 'disabled' : '' ?>
                          title="<?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>"
                          aria-label="<?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?> <?= e($u['nama']) ?>">
                    <i class="bi <?= $u['is_active'] ? 'bi-person-slash' : 'bi-person-check' ?>"></i></button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-danger" <?= $self ? 'disabled' : '' ?>
                        data-bs-toggle="modal" data-bs-target="#modalHapus"
                        data-id="<?= (int)$u['id_user'] ?>" data-nama="<?= e($u['nama']) ?>" data-email="<?= e($u['email']) ?>"
                        title="Hapus" aria-label="Hapus <?= e($u['nama']) ?>">
                  <i class="bi bi-trash"></i></button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-3">
    <div class="small text-muted">
      <?php if ($total > 0): ?>
        Menampilkan <?= $offset + 1 ?> - <?= $offset + count($users) ?> dari <?= $total ?> pengguna
      <?php else: ?>
        0 pengguna
      <?php endif; ?>
    </div>
    <?php if ($totalHalaman > 1): ?>
    <nav aria-label="Halaman">
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $hal <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(url_hal($hal - 1)) ?>">&laquo;</a></li>
        <?php for ($n = max(1, $hal - 2); $n <= min($totalHalaman, $hal + 2); $n++): ?>
          <li class="page-item <?= $n === $hal ? 'active' : '' ?>"><a class="page-link" href="<?= e(url_hal($n)) ?>"><?= $n ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= $hal >= $totalHalaman ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(url_hal($hal + 1)) ?>">&raquo;</a></li>
      </ul>
    </nav>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="delete.php">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" id="hapusId" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalHapusLabel">Hapus Pengguna</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2">Anda akan menghapus akun berikut secara permanen:</p>
          <div class="fw-semibold" id="hapusNama"></div>
          <div class="text-muted small" id="hapusEmail"></div>
          <div class="alert alert-warning small mt-3 mb-0">
            Akun yang memiliki riwayat pendaftaran atau riwayat verifikasi tidak dapat dihapus.
            Gunakan tombol Nonaktifkan untuk akun seperti itu.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Ya, Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalHapus').addEventListener('show.bs.modal', function (ev) {
  var b = ev.relatedTarget;
  document.getElementById('hapusId').value = b.dataset.id;
  document.getElementById('hapusNama').textContent = b.dataset.nama;
  document.getElementById('hapusEmail').textContent = b.dataset.email;
});
</script>

<?php require_once '../../includes/footer.php'; ?>
