<?php
require_once '../includes/auth.php';
require_role('siswa');
$uid = $_SESSION['user']['id'];

$st = $pdo->prepare('SELECT p.id_pendaftaran, p.nomor_pendaftaran, p.status, p.tanggal_daftar, p.tahun_ajaran,
                            s.id_siswa, s.nisn, s.nama_lengkap, s.jenis_kelamin, s.tempat_lahir, s.tanggal_lahir,
                            s.alamat, s.asal_sekolah, s.no_hp, s.foto,
                            u.email, j.kode_jurusan, j.nama_jurusan
                     FROM siswa s
                     JOIN users u ON u.id_user = s.id_user
                     JOIN pendaftaran p ON p.id_siswa = s.id_siswa AND p.tahun_ajaran = ?
                     JOIN jurusan j ON j.id_jurusan = p.id_jurusan
                     WHERE s.id_user = ?');
$st->execute([TAHUN_AJARAN, $uid]);
$p = $st->fetch();

if (!$p) {
    flash('Bukti pendaftaran tersedia setelah Anda mengirim pendaftaran.', 'warning');
    header('Location: pendaftaran.php'); exit;
}

$b = $pdo->prepare('SELECT jenis_berkas FROM berkas WHERE id_pendaftaran = ?');
$b->execute([$p['id_pendaftaran']]);
$berkasAda = $b->fetchAll(PDO::FETCH_COLUMN);
$labelBerkas = ['KK' => 'Kartu Keluarga', 'Akta' => 'Akta Kelahiran', 'Rapor' => 'Rapor', 'Foto' => 'Pas Foto'];

$jk = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
$tglLahir = $p['tanggal_lahir'] ? date('d-m-Y', strtotime($p['tanggal_lahir'])) : '-';

$judul = 'Bukti Pendaftaran';
$css_prefix = '../';
require_once '../includes/header.php';
?>

<style>
  .bukti-wrap { max-width: 820px; margin: 0 auto; }
  .bukti { background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; }
  .bukti-head { background: var(--color-primary); color: #fff; padding: 20px 28px;
                display: flex; justify-content: space-between; align-items: center; gap: 16px; }
  .bukti-head .sekolah { font-family: var(--font-heading); font-weight: 800; font-size: 1.15rem; letter-spacing: .3px; }
  .bukti-head small { opacity: .85; }
  .bukti-body { padding: 28px; }
  .bukti-judul { font-family: var(--font-heading); font-weight: 800; font-size: 1.25rem; margin: 0 0 4px; }
  .bukti-nomor { background: var(--color-primary-light); border: 1px solid var(--border-color);
                 border-radius: var(--radius-md); padding: 14px 18px; margin: 18px 0 24px; }
  .bukti-nomor small { display: block; color: var(--text-muted); font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; }
  .bukti-nomor strong { font-family: var(--font-heading); font-size: 1.5rem; color: var(--color-primary); overflow-wrap: anywhere; }
  .bukti-grid { display: grid; grid-template-columns: 3cm 1fr; gap: 24px; align-items: start; }
  .bukti-foto { width: 3cm; height: 4cm; object-fit: cover; border: 1px solid var(--border-color); display: block; background: #fff; }
  .bukti-foto-kosong { width: 3cm; height: 4cm; border: 1px dashed var(--border-color); display: flex; align-items: center;
                       justify-content: center; text-align: center; font-size: .75rem; color: var(--text-muted); }
  .bukti-tabel { width: 100%; border-collapse: collapse; }
  .bukti-tabel td { padding: 6px 4px; border-bottom: 1px solid var(--border-color); vertical-align: top; }
  .bukti-tabel td:first-child { width: 38%; color: var(--text-muted); }
  .bukti-sub { font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; font-weight: 700;
               color: var(--color-primary); margin: 26px 0 8px; }
  .bukti-catatan { font-size: .8rem; color: var(--text-muted); margin-top: 24px; }
  .bukti-foot { border-top: 1px solid var(--border-color); padding: 12px 28px; display: flex;
                justify-content: space-between; gap: 12px; font-size: .75rem; color: var(--text-muted); }
  @media (max-width: 575px) {
    .bukti-head, .bukti-body { padding: 18px; }
    .bukti-grid { grid-template-columns: 1fr; }
    .bukti-foot { flex-direction: column; padding: 12px 18px; }
  }
  @media print {
    @page { size: A4; margin: 14mm; }
    body { background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 11pt; }
    .navbar-custom, .footer-custom, .no-print { display: none !important; }
    .container { max-width: none !important; padding: 0 !important; }
    .bukti-wrap { max-width: none; }
    .bukti { border-color: #999; border-radius: 0; break-inside: avoid; }
    .bukti-grid { grid-template-columns: 3cm 1fr; }
  }
</style>

<div class="container py-4">
  <div class="bukti-wrap">
    <div class="no-print d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h3 class="h4 mb-0">Bukti Pendaftaran</h3>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-accent" onclick="cetakBukti()">
          <i class="bi bi-download"></i> Unduh PDF
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="cetakBukti()">
          <i class="bi bi-printer"></i> Cetak
        </button>
      </div>
    </div>
    <?= flash() ?>
    <p class="no-print text-muted small">
      Klik <strong>Unduh PDF</strong>, lalu pada jendela cetak pilih tujuan <strong>Simpan sebagai PDF</strong> (Save as PDF).
    </p>

    <article class="bukti">
      <header class="bukti-head">
        <div>
          <div class="sekolah">SMK NUSANTARA</div>
          <small>Penerimaan Peserta Didik Baru &middot; Tahun Ajaran <?= e($p['tahun_ajaran']) ?></small>
        </div>
        <div class="text-end small">PPDB ONLINE</div>
      </header>

      <div class="bukti-body">
        <h2 class="bukti-judul">Bukti Pendaftaran</h2>
        <div class="text-muted small">Simpan bukti ini sebagai tanda bahwa pendaftaran Anda telah tercatat.</div>

        <div class="bukti-nomor">
          <small>Nomor Pendaftaran</small>
          <strong><?= e($p['nomor_pendaftaran']) ?></strong>
        </div>

        <div class="bukti-grid">
          <div>
            <?php if (!empty($p['foto'])): ?>
              <img class="bukti-foto" src="../lihat-foto.php?id=<?= (int)$p['id_siswa'] ?>" alt="Pas foto">
            <?php else: ?>
              <div class="bukti-foto-kosong">Foto belum diunggah</div>
            <?php endif; ?>
          </div>
          <table class="bukti-tabel">
            <tr><td>Nama lengkap</td><td><strong><?= e($p['nama_lengkap']) ?></strong></td></tr>
            <tr><td>NISN</td><td><?= e($p['nisn']) ?></td></tr>
            <tr><td>Jenis kelamin</td><td><?= e($jk[$p['jenis_kelamin']] ?? '-') ?></td></tr>
            <tr><td>Tempat, tanggal lahir</td><td><?= e($p['tempat_lahir']) ?>, <?= e($tglLahir) ?></td></tr>
            <tr><td>Asal sekolah</td><td><?= e($p['asal_sekolah']) ?></td></tr>
            <tr><td>Nomor HP</td><td><?= e($p['no_hp']) ?></td></tr>
            <tr><td>Email</td><td><?= e($p['email']) ?></td></tr>
            <tr><td>Alamat</td><td><?= e($p['alamat']) ?></td></tr>
          </table>
        </div>

        <div class="bukti-sub">Informasi Pendaftaran</div>
        <table class="bukti-tabel">
          <tr><td>Pilihan jurusan</td><td><strong><?= e($p['kode_jurusan'] . ' - ' . $p['nama_jurusan']) ?></strong></td></tr>
          <tr><td>Tanggal pendaftaran</td><td><?= e(date('d-m-Y H:i', strtotime($p['tanggal_daftar']))) ?> WIB</td></tr>
          <tr><td>Status saat dicetak</td>
              <td><span class="<?= e(strtolower($p['status'])) ?>"><?= e($p['status']) ?></span></td></tr>
          <tr><td>Berkas terunggah</td>
              <td>
                <?php
                  $ada = [];
                  foreach ($labelBerkas as $k => $v) if (in_array($k, $berkasAda, true)) $ada[] = $v;
                  echo $ada ? e(implode(', ', $ada)) : 'Belum ada berkas';
                ?>
              </td></tr>
        </table>

        <p class="bukti-catatan mb-0">
          Bukti ini bukan surat keputusan penerimaan. Keputusan akhir mengikuti hasil verifikasi dan seleksi sekolah.
          Pantau status melalui menu Status pada akun Anda.
        </p>
      </div>

      <footer class="bukti-foot">
        <span>PPDB Online SMK Nusantara &middot; <?= e($p['tahun_ajaran']) ?></span>
        <span>Dicetak: <?= e(date('d-m-Y H:i')) ?> WIB</span>
      </footer>
    </article>
  </div>
</div>

<script>
function cetakBukti() {
  var judulAsli = document.title;
  document.title = 'Bukti-Pendaftaran-<?= e($p['nomor_pendaftaran']) ?>';
  window.print();
  setTimeout(function () { document.title = judulAsli; }, 500);
}
</script>

<?php require_once '../includes/footer.php'; ?>
