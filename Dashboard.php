<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Hitung statistik
$total_query = "SELECT COUNT(*) as total FROM surat_peringatan";
$total_result = mysqli_query($conn, $total_query);
$total = mysqli_fetch_assoc($total_result)['total'];

$aktif_query = "SELECT COUNT(*) as aktif FROM surat_peringatan WHERE status_sp = 'Aktif'";
$aktif_result = mysqli_query($conn, $aktif_query);
$aktif = mysqli_fetch_assoc($aktif_result)['aktif'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Staff</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    
    .stats-card {
      background: #ffffff;
      border: 1px solid #dee2e6;
      border-radius: 0px;
      padding: 20px;
      margin-bottom: 20px;
      text-align: center;
    }
    
    .stats-number {
      font-size: 2rem;
      font-weight: bold;
      color: #212529;
      margin-bottom: 5px;
    }
    
    .stats-label {
      color: #6c757d;
      font-size: 14px;
    }
  </style>
</head>
<body>

  <div class="sidebar d-flex flex-column">
    <img src="logo.png" alt="Logo" class="mb-3" width="120">

    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="dashboard.php">Dashboard</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="#">Beranda</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#submenuSPMahasiswa">
          SP mahasiswa
        </a>
        <div class="collapse submenu" id="submenuSPMahasiswa">
          <ul class="nav flex-column ms-3">
            <li class="nav-item">
                <a class="nav-link" href="daftar_sp_staff.php?page=tambah">
                    Buat SP
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="daftar_sp_staff.php?page=daftar">
                    Rekap / Daftar SP
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="daftar_sp_staff.php?page=daftar">
                    Kelola SP
                </a>
            </li>
          </ul>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#submenuSP">
          Pencarian SP
        </a>
        <div class="collapse submenu" id="submenuSP">
          <ul class="nav flex-column ms-3">
            <li class="nav-item">
                <a class="nav-link" href="daftar_sp_staff.php?page=cari">
                    Cari SP
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    Cari Data Mahasiswa
                </a>
            </li>
          </ul>
        </div>
      </li>
    </ul>

    <!-- PROFILE + LOGOUT ICON -->
    <div class="profile mt-auto">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="40" class="me-2">

      <div class="me-auto">
        <strong><?= $username ?></strong><br>
        <small>Staff</small>
      </div>

      <!-- LOGOUT ICON -->
      <a href="logout.php" class="text-danger ms-3" title="Logout">
        <i class="bi bi-box-arrow-right" style="font-size: 26px;"></i>
      </a>
    </div>

  </div>

  <div class="content">
    <h1>Welcome to Our System , Sir <?= $username ?></h1>
    <p>Sistem Surat Peringatan Mahasiswa</p>
    
    <!-- STATS CARDS -->
    <div class="row mt-4">
      <div class="col-md-3">
        <div class="stats-card">
          <div class="stats-number"><?= $total ?></div>
          <div class="stats-label">Total SP</div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="stats-card">
          <div class="stats-number"><?= $aktif ?></div>
          <div class="stats-label">SP Aktif</div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="stats-card">
          <div class="stats-number">Staff</div>
          <div class="stats-label"><?= htmlspecialchars($username) ?></div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="stats-card">
          <div class="stats-number">Menu</div>
          <div class="stats-label">
            <a href="daftar_sp_staff.php?page=tambah" class="btn btn-sm" style="margin-top: 5px;">+ Buat SP</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>