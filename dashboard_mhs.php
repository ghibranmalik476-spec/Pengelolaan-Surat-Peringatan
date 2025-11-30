<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap ICONS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    body { background-color: #f8f9fa; }

    .sidebar {
      width: 250px;
      height: 100vh;
      background-color: #fff;
      border-right: 1px solid #ddd;
      position: fixed;
      top: 0;
      left: 0;
      padding: 20px;
    }

    .sidebar .nav-link {
      color: #333;
      font-weight: 500;
    }

    .sidebar .nav-link.active {
      color: #0d6efd;
    }

    .submenu .nav-link {
      margin-left: 20px;
      font-size: 0.95rem;
    }

    .content {
      margin-left: 270px;
      padding: 20px;
    }

    .profile {
      position: absolute;
      bottom: 20px;
      left: 20px;
      right: 20px;
      display: flex;
      align-items: center;
    }
  </style>
</head>
<body>

  <div class="sidebar d-flex flex-column">
    <h4 class="mb-4">MAHASISWA PANEL</h4>

    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="dashboard_mahasiswa.php">Dashboard</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="#">Beranda</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#submenuAbsensi">
          Absensi
        </a>
        <div class="collapse submenu" id="submenuAbsensi">
          <ul class="nav flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="#">Lihat Absensi</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Riwayat Absensi</a></li>
          </ul>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#submenuSP">
          Surat Peringatan
        </a>
        <div class="collapse submenu" id="submenuSP">
          <ul class="nav flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="#">Lihat SP</a></li>
          </ul>
        </div>
      </li>
    </ul>

    <!-- PROFILE + LOGOUT ICON -->
    <div class="profile mt-auto">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="40" class="me-2">

      <div class="me-auto">
        <strong><?= $username ?></strong><br>
        <small>Mahasiswa</small>
      </div>

      <!-- LOGOUT BUTTON (ICON) -->
      <a href="logout.php" class="text-danger ms-3" title="Logout">
        <i class="bi bi-box-arrow-right" style="font-size: 26px;"></i>
      </a>
    </div>

  </div>

  <div class="content">
    <h1>Welcome , <?= $username ?></h1>
    <p>Selamat datang di Sistem Surat Peringatan Mahasiswa</p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
