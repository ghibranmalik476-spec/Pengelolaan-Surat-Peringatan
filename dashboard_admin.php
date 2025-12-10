<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
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
  <title>Dashboard Staff</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    body { background-color: #f8f9fa; }

    .content {
      padding: 20px;
      transition: margin-left 0.3s ease;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-light bg-white shadow-sm px-3">
    <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
        <i class="bi bi-list" style="font-size: 1.5rem;"></i>
    </button>

    <h5 class="ms-3 mt-1">Dashboard Staff</h5>
</nav>

<!-- SIDEBAR (OFFCANVAS) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" style="width:270px;" data-bs-backdrop="false">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Menu Staff</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">

        <img src="logo.png" alt="Logo" class="mb-3" width="140">

        <ul class="nav flex-column">

            <li class="nav-item">
                <a class="nav-link active" href="dashboard_staff.php">Dashboard</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">Beranda</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#submenuSPMahasiswa">
                    Data
                </a>
                <div class="collapse" id="submenuSPMahasiswa">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item"><a class="nav-link" href="daftar.php">Kelola Data </a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Rekap / Daftar SP</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Kelola SP</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#submenuSP">
                    Pencarian SP
                </a>
                <div class="collapse" id="submenuSP">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item"><a class="nav-link" href="#">Cari SP</a></li>
                        <li class="nav-item"><a class="nav-link" href="test.php">Cari Data Mahasiswa</a></li>
                    </ul>
                </div>
            </li>

        </ul>

        <!-- PROFILE -->
        <div class="mt-auto p-2 border-top">
            <div class="dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="45" class="me-2">
                    <div class="me-auto">
                        <strong><?= $username ?></strong><br>
                        <small>Admin</small>
                    </div>
                    <i class="bi bi-chevron-down ms-2"></i>
                </div>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#peraturanModal">Peraturan</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- CONTENT -->
<div class="content">
    <h1>Welcome to Our System, Sir <?= $username ?></h1>
    <p>Sistem Surat Peringatan Mahasiswa</p>
</div>

<!-- Modal Peraturan -->
<div class="modal fade" id="peraturanModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Peraturan</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <h6>Peraturan Penggunaan Sistem</h6>
      <ol>
        <li>Semua pengguna harus menggunakan akun pribadi dan tidak boleh membagikan kredensial login.</li>
        <li>Data yang dimasukkan harus akurat dan sesuai dengan kebijakan universitas.</li>
        <li>Dilarang mengakses atau mengubah data tanpa izin.</li>
        <li>Pelanggaran terhadap peraturan ini dapat mengakibatkan penangguhan atau penghentian akses.</li>
        <li>Untuk pertanyaan lebih lanjut, hubungi administrator sistem.</li>
      </ol>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
    </div>
  </div></div>
</div>

<!-- Modal Change Password -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form action="change_password.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>Password Lama</label>
        <input type="password" name="old_password" class="form-control mb-2" required>
        <label>Password Baru</label>
        <input type="password" name="new_password" class="form-control mb-2" required>
        <label>Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" class="form-control mb-2" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div></div>
</div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SCRIPT AGAR SIDEBAR MENDORONG DASHBOARD -->
<script>
    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');

    // Saat sidebar dibuka (push content)
    sidebar.addEventListener('shown.bs.offcanvas', function () {
        content.style.marginLeft = "270px"; 
    });

    // Saat sidebar ditutup (kembali normal)
    sidebar.addEventListener('hidden.bs.offcanvas', function () {
        content.style.marginLeft = "0";
    });
</script>

</body>
</html>
