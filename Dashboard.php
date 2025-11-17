<?php
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
    }
    .test{
      
    }
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
    .sidebar .submenu .nav-link {
      font-weight: normal;
      margin-left: 20px;
      font-size: 0.95rem;
    }
    .content {
      margin-left: 270px;
      padding: 20px;
    }
    .header {
      background-color: #2f2fff;
      height: 50px;
      color: white;
      padding: 10px;
    }
    .profile {
      position: absolute;
      bottom: 20px;
      left: 20px;
    }
  </style>
</head>
<body>

  <div class="sidebar d-flex flex-column">
    <img src="logo.png" alt="Logo" class="mb-3" width="120">

    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="#">Dashboard</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="#">Beranda</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#submenuAbsensi" role="button" aria-expanded="false" aria-controls="submenuAbsensi">
          Absensi
        </a>
        <div class="collapse submenu" id="submenuAbsensi">
          <ul class="nav flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="#">Entri Absen</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Rekap Mahasiswa </a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#submenuSP" role="button" aria-expanded="false" aria-controls="submenuSP">
          Surat Peringatan
        </a>
        <div class="collapse submenu" id="submenuSP">
          <ul class="nav flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="#">Rekap SP Mahasiswa</a></li>
            <li class="nav-item"><a class="nav-link" href="SP.html">Detail SP Mahasiswa</a></li>
          </ul>
        </div>
      </li>
    </ul>

    <div class="profile mt-auto d-flex align-items-center">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="40" class="me-2">
      <div>
        <strong>Sifulan</strong><br>
        <small>Admin</small>
      </div>
    </div>
  </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

?>