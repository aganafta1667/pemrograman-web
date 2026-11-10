<?php
$prefix = $css_prefix ?? '';
$pageTitle = isset($judul) ? e($judul) . ' - PPDB SMK Nusantara' : 'PPDB Online SMK Nusantara';
$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link rel="stylesheet" href="<?= $prefix ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-md navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="<?= $prefix ?>index.php">
      <span>SMK Nusantara</span>
      <?php if (!empty($currentUser['role'])): ?>
        <span class="brand-badge"><?= e($currentUser['role']) ?></span>
      <?php endif; ?>
    </a>
    
    <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list fs-3"></i>
    </button>
    
    <div class="collapse navbar-collapse" id="mainMenu">
      <ul class="navbar-nav mx-auto mb-2 mb-md-0">
        <?php if (!empty($currentUser) && $currentUser['role'] === 'admin'): ?>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'dashboard.php') ? 'active' : '' ?>" href="<?= $prefix ?>admin/dashboard.php">Dashboard</a></li>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'jurusan.php') ? 'active' : '' ?>" href="<?= $prefix ?>admin/jurusan.php">Jurusan</a></li>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'verifikasi.php') ? 'active' : '' ?>" href="<?= $prefix ?>admin/verifikasi.php">Verifikasi</a></li>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'laporan.php') ? 'active' : '' ?>" href="<?= $prefix ?>admin/laporan.php">Laporan</a></li>
        <?php elseif (!empty($currentUser) && $currentUser['role'] === 'siswa'): ?>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'dashboard.php') ? 'active' : '' ?>" href="<?= $prefix ?>siswa/dashboard.php">Dashboard</a></li>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'biodata.php') ? 'active' : '' ?>" href="<?= $prefix ?>siswa/biodata.php">Biodata</a></li>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'pendaftaran.php') ? 'active' : '' ?>" href="<?= $prefix ?>siswa/pendaftaran.php">Pendaftaran</a></li>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'upload_berkas.php') ? 'active' : '' ?>" href="<?= $prefix ?>siswa/upload_berkas.php">Berkas</a></li>
          <li class="nav-item mx-3"><a class="nav-link <?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'status.php') ? 'active' : '' ?>" href="<?= $prefix ?>siswa/status.php">Status</a></li>
        <?php else: ?>
          <li class="nav-item mx-3"><a class="nav-link" href="<?= $prefix ?>index.php#jurusan">Program Keahlian</a></li>
          <li class="nav-item mx-3"><a class="nav-link" href="<?= $prefix ?>index.php#persyaratan">Persyaratan</a></li>
          <li class="nav-item mx-3"><a class="nav-link" href="<?= $prefix ?>index.php#jadwal">Jadwal</a></li>
        <?php endif; ?>
      </ul>
      
      <div class="d-flex align-items-center gap-2">
        <?php if ($currentUser): ?>
          <span class="text-white d-none d-lg-inline small me-1">
            <i class="bi bi-person-circle me-1"></i><?= e($currentUser['nama']) ?>
          </span>
          <a href="<?= $prefix ?>auth/logout.php" class="btn btn-sm btn-outline-light">
            <i class="bi bi-box-arrow-right"></i>
          </a>
        <?php else: ?>
          <a href="<?= $prefix ?>auth/login.php" class="btn btn-sm btn-outline-light">Masuk</a>
          <a href="<?= $prefix ?>auth/register.php" class="btn btn-sm btn-accent">Daftar Sekarang</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<main class="main-content">
