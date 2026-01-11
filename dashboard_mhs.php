<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$nik = $_SESSION['nik'];

$q = mysqli_query($koneksi, "
    SELECT nama, jurusan 
    FROM mahasiswa 
    WHERE nik='$nik'
");
$mhs = mysqli_fetch_assoc($q);

if (isset($_GET['download_pdf'])) {
    $id_sp = $_GET['download_pdf'];
    
    $query = mysqli_query($koneksi, "
        SELECT file_pdf, jenis_sp, tanggal 
        FROM surat_peringatan 
        WHERE id = '$id_sp' AND nik = '$nik'
    ");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $file_path = $data['file_pdf'];
        
        if (!empty($file_path) && file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="SP' . $data['jenis_sp'] . '_' . date('Ymd', strtotime($data['tanggal'])) . '.pdf"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            
            ob_clean();
            flush();
            
            readfile($file_path);
            exit;
        } else {
            die("
            <div style='padding:20px;text-align:center;'>
                <h3>File PDF tidak ditemukan!</h3>
                <p>File SP yang diminta tidak tersedia di server.</p>
                <p>Silakan hubungi staff akademik untuk informasi lebih lanjut.</p>
                <a href='dashboard_mhs.php' class='btn btn-primary'>Kembali ke Dashboard</a>
            </div>");
        }
    } else {
        die("
        <div style='padding:20px;text-align:center;'>
            <h3>Data tidak ditemukan!</h3>
            <p>SP yang diminta tidak ditemukan atau tidak sesuai dengan akun Anda.</p>
            <a href='dashboard_mhs.php' class='btn btn-primary'>Kembali ke Dashboard</a>
        </div>");
    }
}

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
.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}
.status-active { background-color: #ffc107; color: #000; padding: 3px 8px; border-radius: 4px; }
.status-done { background-color: #198754; color: #fff; padding: 3px 8px; border-radius: 4px; }
.status-dicabut { background-color: #6c757d; color: #fff; padding: 3px 8px; border-radius: 4px; }
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
                    <p class="mb-1"><strong>NIK:</strong> <?= $nik ?></p>
                    <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($mhs['nama']) ?></p>
                    <p class="mb-0"><strong>Jurusan:</strong> <?= htmlspecialchars($mhs['jurusan']) ?></p>
                </div>
            </div>
        </div>

        <!-- CARD SP -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
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
                            <i class="bi bi-check-circle me-2"></i> Anda belum memiliki Surat Peringatan
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-dark">
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Jenis SP</th>
                                        <th>Alasan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>File PDF</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                $no = 1;
                                while($r = mysqli_fetch_assoc($surat_peringatan)): 
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= 
                                                $r['jenis_sp'] == 1 ? 'warning text-dark' : 
                                                ($r['jenis_sp'] == 2 ? 'danger' : 'dark')
                                            ?>">
                                                SP<?= $r['jenis_sp'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($r['alasan']) ?></td>
                                        <td class="text-center"><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $status_class = 'status-active';
                                            if ($r['status'] == 'Selesai') $status_class = 'status-done';
                                            if ($r['status'] == 'Dicabut') $status_class = 'status-dicabut';
                                            ?>
                                            <span class="<?= $status_class ?>">
                                                <?= $r['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($r['file_pdf'])): ?>
                                                <a href="?download_pdf=<?= $r['id'] ?>" class="btn btn-sm btn-success" title="Download PDF">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- LOADING INDICATOR -->
                        <div id="pdfLoading" style="display:none;" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Menyiapkan dokumen...</p>
                        </div>
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

document.addEventListener('DOMContentLoaded', function() {
    
    downloadLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const loadingDiv = document.getElementById('pdfLoading');
            if (loadingDiv) {
                loadingDiv.style.display = 'block';
            }
            
            setTimeout(function() {
                if (loadingDiv) {
                    loadingDiv.style.display = 'none';
                }
            }, 3000);
        });
    });
});

if (window.location.search.includes('download_pdf=')) {
    const loadingDiv = document.getElementById('pdfLoading');
    if (loadingDiv) {
        loadingDiv.style.display = 'block';
    }
}
</script>
</body>
</html>