<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$nik = $_SESSION['nik'];

// ambil data mahasiswa
$q = mysqli_query($koneksi, "
    SELECT nama, jurusan 
    FROM mahasiswa 
    WHERE nik='$nik'
");
$mhs = mysqli_fetch_assoc($q);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Mahasiswa</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
body { background-color: #f8f9fa; }

.content {
    padding: 20px;
    transition: margin-left 0.3s ease;
}

.profile-img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #0d6efd;
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-light bg-white shadow-sm px-3">
    <button class="btn btn-outline-primary" type="button"
        data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        <i class="bi bi-list fs-4"></i>
    </button>
    <h5 class="ms-3 mt-1">Dashboard Mahasiswa</h5>
</nav>

<!-- SIDEBAR -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar"
     style="width:270px;" data-bs-backdrop="false">

    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column align-items-center text-center">

        <!-- FOTO PROFIL -->
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
             class="profile-img mb-3">

        <h5 class="mb-0"><?= htmlspecialchars($mhs['nama']) ?></h5>
        <small class="text-muted mb-3"><?= htmlspecialchars($mhs['jurusan']) ?></small>

        <hr class="w-100">

        <!-- MENU -->
        <ul class="nav flex-column w-100">
            <li class="nav-item">
                <a class="nav-link text-center" href="#"
                   data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key me-2"></i> Change Password
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger text-center" href="logout.php"
                   onclick="return confirm('Logout sekarang?')">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- CONTENT -->
<div class="content">

    <h4 class="mb-4">👋 Selamat Datang, <?= htmlspecialchars($mhs['nama']) ?></h4>

    <div class="row g-3">

        <!-- CARD PROFIL -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Data Mahasiswa</h6>
                    <hr>
                    <p class="mb-1"><strong>NIM:</strong> <?= $nik ?></p>
                    <p class="mb-1"><strong>Nama:</strong> <?= $mhs['nama'] ?></p>
                    <p class="mb-0"><strong>Jurusan:</strong> <?= $mhs['jurusan'] ?></p>
                </div>
            </div>
        </div>

        <!-- CARD SP -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Riwayat Surat Peringatan</h6>
                    <hr>

                    <?php
                    $surat_peringatan = mysqli_query($koneksi, "
                        SELECT * FROM surat_peringatan
                        WHERE nik='$nik' 
                        ORDER BY tanggal DESC
                    ");

                    if (mysqli_num_rows($surat_peringatan) == 0):
                    ?>
                        <div class="alert alert-success mb-0">
                            🎉 Anda belum memiliki Surat Peringatan
                        </div>
                    <?php else: ?>
                        <table class="table table-sm table-bordered text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>Jenis SP</th>
                                    <th>Keterangan</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while($r = mysqli_fetch_assoc($surat_peringatan)): ?>
                                <tr>
                                    <td><?= $r['jenis_sp'] ?></td>
                                    <td><?= $r['keterangan'] ?></td>
                                    <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL CHANGE PASSWORD -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="change_password.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Change Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="password" name="old_password" class="form-control mb-2" placeholder="Password Lama" required>
          <input type="password" name="new_password" class="form-control mb-2" placeholder="Password Baru" required>
          <input type="password" name="confirm_password" class="form-control mb-2" placeholder="Konfirmasi Password" required>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sidebar = document.getElementById('sidebar');
const content = document.querySelector('.content');

sidebar.addEventListener('shown.bs.offcanvas', () => {
    content.style.marginLeft = "270px";
});
sidebar.addEventListener('hidden.bs.offcanvas', () => {
    content.style.marginLeft = "0";
});
</script>
</body>
</html>
