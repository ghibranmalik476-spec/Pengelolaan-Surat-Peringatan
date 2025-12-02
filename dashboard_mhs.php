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

    .dashboard-header {
      background: #ffffff;
      border-radius: 10px;
      padding: 25px;
      margin-bottom: 30px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      border-left: 4px solid #0d6efd;
    }

    .dashboard-header h1 {
      color: #333;
      font-weight: bold;
      margin: 0;
    }

    .dashboard-header p {
      color: #666;
      margin: 10px 0 0 0;
      font-size: 16px;
    }

    .stats-card {
      background: #ffffff;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      border: 1px solid #eee;
    }

    .stats-number {
      font-size: 2rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 5px;
    }

    .stats-label {
      font-size: 14px;
      color: #666;
    }

    .stats-icon {
    width: 50px;
    height: 50px;
    background: #f0f7ff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    color: #0d6efd;
    }

    .recent-activity {
      background: #ffffff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      border: 1px solid #eee;
    }

    .activity-item {
      border-bottom: 1px solid #eee;
      padding: 15px 0;
    }

    .activity-item:last-child {
      border-bottom: none;
    }

    .activity-title {
      font-weight: 500;
      color: #333;
      margin-bottom: 5px;
    }

    .activity-desc {
      color: #666;
      font-size: 14px;
      margin-bottom: 0px;
    }

    .activity-time {
      font-size: 12px;
      color: #999;
    }

    .col-md-3 {
      padding: 0 10px;
    }

    .row {
      margin: 0 -10px;
    }
  </style>
</head>
<body>

  <div class="sidebar d-flex flex-column">
    <img src="logo.png" alt="Logo" class="mb-3" width="120">

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
    <!-- DASHBOARD HEADER -->
    <div class="dashboard-header">
      <h1>Welcome, <?= $username ?></h1>
      <p>Selamat datang di Sistem Surat Peringatan Mahasiswa</p>
    </div>

    <!-- Stats Cards -->
    <div class="row">
      <div class="col-md-3">
        <div class="stats-card">
          <div class="stats-icon">
            <i class="bi bi-calender-check"></i>
          </div>
          <div class="stats-number">95%</div>
          <div class="stats-label">Kehadiran</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stats-card">
          <div class="stats-icon">
            <i class="bi bi-envelope-exclamation"></i>
          </div>
          <div class="stats-number">1</div>
          <div class="stats-label">Surat Peringatan</div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stats-card">
          <div class="stats-icon">
            <i class="bi bi-clock-history"></i>
          </div>
          <div class="stats-number">45</div>
          <div class="stats-label">Hari Aktif</div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stats-card">
          <div class="stats-icon">
            <i class="bi bi-person-check"></i>
          </div>
          <div class="stats-number">A</div>
          <div class="stats-label">Status Akademik</div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity mt-4">
      <h5 class="mb-3">Aktivitas Terbaru</h5>

      <div class="activity-item">
        <div class="activity-title">Absensi hari ini</div>
        <p class="activity-desc">Anda hadir tepat waktu pada sesi pagi</p>
        <div class="activity-time">10:30 AM</div>
      </div>

      <div class="activity-item">
        <div class="activity-title">Surat Peringatan 1</div>
        <p class="activity-desc">Telah diterbitkan SP untuk kehadiran</p>
        <div class="activity-time">Kemarin</div>
      </div>

      <div class="activity-item">
        <div class="activity-title">Perbaikan Absensi</div>
        <p class="activity-desc">Absensi minggu lalu telah diperbaiki</p>
        <div class="activity-time">3 hari lalu</div>
      </div>
    </div>
  </div>
  

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
