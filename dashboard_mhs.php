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

// PROSES EKSPOR PDF
if (isset($_GET['export_pdf'])) {
    require_once('tcpdf/tcpdf.php'); // Pastikan library TCPDF sudah diinstall
    
    // Buat objek PDF baru
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set dokumen meta
    $pdf->SetCreator('Sistem SP Mahasiswa');
    $pdf->SetAuthor('Politeknik');
    $pdf->SetTitle('Riwayat Surat Peringatan - ' . $mhs['nama']);
    $pdf->SetSubject('Riwayat SP');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Judul
    $pdf->Cell(0, 10, 'RIWAYAT SURAT PERINGATAN', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Mahasiswa: ' . $mhs['nama'], 0, 1, 'C');
    $pdf->Cell(0, 10, 'NIK: ' . $nik . ' | Jurusan: ' . $mhs['jurusan'], 0, 1, 'C');
    $pdf->Cell(0, 10, 'Tanggal Cetak: ' . date('d/m/Y H:i'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Ambil data SP
    $surat_peringatan = mysqli_query($koneksi, "
        SELECT * FROM surat_peringatan
        WHERE nik='$nik' 
        ORDER BY tanggal DESC
    ");
    
    if (mysqli_num_rows($surat_peringatan) == 0) {
        $pdf->SetFont('helvetica', 'I', 12);
        $pdf->Cell(0, 10, 'Tidak ada riwayat Surat Peringatan', 0, 1, 'C');
    } else {
        // Header tabel
        $pdf->SetFont('helvetica', 'B', 10);
        $header = array('No', 'Jenis SP', 'Alasan', 'Tanggal', 'Status', 'Keterangan');
        $w = array(10, 15, 60, 25, 20, 40);
        
        for($i=0; $i<count($header); $i++) {
            $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        }
        $pdf->Ln();
        
        // Data tabel
        $pdf->SetFont('helvetica', '', 9);
        $no = 1;
        while($row = mysqli_fetch_assoc($surat_peringatan)) {
            $pdf->Cell($w[0], 6, $no++, 1);
            $pdf->Cell($w[1], 6, 'SP' . $row['jenis_sp'], 1);
            $pdf->Cell($w[2], 6, substr($row['alasan'], 0, 50), 1);
            $pdf->Cell($w[3], 6, date('d/m/Y', strtotime($row['tanggal'])), 1);
            $pdf->Cell($w[4], 6, $row['status'], 1);
            $pdf->Cell($w[5], 6, substr($row['keterangan'] ?? '-', 0, 30), 1);
            $pdf->Ln();
        }
    }
    
    // Tambahkan catatan
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->MultiCell(0, 5, 'Catatan: Dokumen ini dicetak secara otomatis dari Sistem Surat Peringatan. Keaslian dokumen dapat diverifikasi melalui staff akademik.', 0, 'L');
    
    // Output PDF
    $pdf->Output('Riwayat_SP_' . $nik . '_' . date('Ymd') . '.pdf', 'D');
    exit;
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
                    <p class="mb-1"><strong>Nama:</strong> <?= $mhs['nama'] ?></p>
                    <p class="mb-0"><strong>Jurusan:</strong> <?= $mhs['jurusan'] ?></p>
                </div>
            </div>
        </div>

        <!-- CARD SP -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted mb-0">Riwayat Surat Peringatan</h6>
                        <?php
                        // Cek apakah ada riwayat SP
                        $cek_sp = mysqli_query($koneksi, "
                            SELECT COUNT(*) as total FROM surat_peringatan
                            WHERE nik='$nik'
                        ");
                        $total_sp = mysqli_fetch_assoc($cek_sp)['total'];
                        
                        if ($total_sp > 0): ?>
                        <a href="?export_pdf=1" class="btn btn-danger btn-sm">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                        </a>
                        <?php endif; ?>
                    </div>
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
                                            <span class="status-<?= strtolower($r['status']) ?>">
                                                <?= $r['status'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- INFO PDF -->
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Anda dapat mengekspor riwayat SP ini ke format PDF dengan mengklik tombol <strong>"Export PDF"</strong> di atas.
                            Dokumen PDF dapat digunakan untuk keperluan administrasi atau arsip pribadi.
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
</script>
</body>
</html>