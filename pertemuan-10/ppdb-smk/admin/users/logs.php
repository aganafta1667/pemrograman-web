<?php
require_once '../../includes/auth.php';
require_once '../../includes/users.php';
require_role(AKSES['users']);

$q = trim($_GET['q'] ?? '');
$sql = 'SELECT l.aktivitas, l.ip_address, l.created_at, u.nama, u.role
        FROM activity_logs l LEFT JOIN users u ON u.id_user = l.id_user';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE l.aktivitas LIKE ? OR u.nama LIKE ?';
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $params = [$like, $like];
}
$st = $pdo->prepare($sql . ' ORDER BY l.id DESC LIMIT 200');
$st->execute($params);
$logs = $st->fetchAll();

$judul = 'Log Aktivitas';
$css_prefix = '../../';
require_once '../../includes/header.php';
?>

<div class="container py-4">
  <a href="index.php" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Kembali ke manajemen pengguna</a>
  <h3 class="h4 my-3">Log Aktivitas</h3>

  <form method="get" class="row g-2 mb-3">
    <div class="col-md-6"><input type="search" name="q" class="form-control" placeholder="Cari aktivitas atau nama pengguna" value="<?= e($q) ?>"></div>
    <div class="col-md-6 d-flex gap-2">
      <button class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
      <a href="logs.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  <div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th style="width:170px">Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>IP</th></tr></thead>
      <tbody>
      <?php if (!$logs): ?>
        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas tercatat.</td></tr>
      <?php endif; ?>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td class="small text-muted"><?= e(date('d-m-Y H:i:s', strtotime($l['created_at']))) ?></td>
          <td><?= $l['nama'] ? e($l['nama']) . ' ' . role_badge($l['role']) : '<span class="text-muted">(akun dihapus)</span>' ?></td>
          <td><?= e($l['aktivitas']) ?></td>
          <td class="small text-muted"><?= e($l['ip_address']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
  <p class="small text-muted mt-2">Menampilkan maksimal 200 aktivitas terbaru.</p>
</div>

<?php require_once '../../includes/footer.php'; ?>
