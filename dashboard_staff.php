<?php
session_start();
require 'koneksi.php';

// Cek login dan role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Inisialisasi variabel
$action = $_GET['action'] ?? 'dashboard';
$id_sp = $_GET['id'] ?? 0;
$message = $_GET['message'] ?? '';
$search = $_GET['search'] ?? '';
$pdf_action = $_GET['pdf_action'] ?? '';
$nim_search = $_GET['nim_search'] ?? '';

// FUNGSI UTAMA
function getSPData($koneksi, $search = '') {
    $query = "SELECT sp.*, m.nama as nama_mahasiswa, m.jurusan, m.email FROM surat_peringatan sp LEFT JOIN mahasiswa m ON sp.nim = m.nim WHERE 1=1";
    if (!empty($search)) $query .= " AND (sp.nim LIKE '%$search%' OR m.nama LIKE '%$search%')";
    $query .= " ORDER BY sp.tanggal DESC, sp.id DESC";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    if ($result) while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
    return $data;
}

function getMahasiswaData($koneksi) {
    $query = "SELECT * FROM mahasiswa ORDER BY nama ASC";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    if ($result) while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
    return $data;
}

function getSPDetail($koneksi, $id) {
    $query = "SELECT sp.*, m.nama as nama_mahasiswa, m.email, m.jurusan, m.alamat FROM surat_peringatan sp LEFT JOIN mahasiswa m ON sp.nim = m.nim WHERE sp.id = '$id'";
    $result = mysqli_query($koneksi, $query);
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

function getDashboardStats($koneksi) {
    $stats = [];
    $queries = [
        'total_mahasiswa' => "SELECT COUNT(*) as total FROM mahasiswa",
        'total_sp' => "SELECT COUNT(*) as total FROM surat_peringatan",
        'sp_aktif' => "SELECT COUNT(*) as aktif FROM surat_peringatan WHERE status = 'Aktif'",
        'sp_hari_ini' => "SELECT COUNT(*) as hari_ini FROM surat_peringatan WHERE DATE(tanggal) = '" . date('Y-m-d') . "'"
    ];
    foreach ($queries as $key => $sql) {
        $result = mysqli_query($koneksi, $sql);
        $stats[$key] = $result ? mysqli_fetch_assoc($result)[$key == 'sp_aktif' ? 'aktif' : ($key == 'sp_hari_ini' ? 'hari_ini' : 'total')] : 0;
    }
    $jurusan_query = "SELECT m.jurusan, COUNT(sp.id) as jumlah FROM surat_peringatan sp LEFT JOIN mahasiswa m ON sp.nim = m.nim WHERE m.jurusan != '' GROUP BY m.jurusan ORDER BY jumlah DESC LIMIT 1";
    $jurusan_result = mysqli_query($koneksi, $jurusan_query);
    if ($jurusan_result && mysqli_num_rows($jurusan_result) > 0) {
        $row = mysqli_fetch_assoc($jurusan_result);
        $stats['jurusan_terbanyak'] = $row['jurusan'] ?: 'Belum ada data';
        $stats['jurusan_terbanyak_jumlah'] = $row['jumlah'];
    } else {
        $stats['jurusan_terbanyak'] = 'Belum ada data';
        $stats['jurusan_terbanyak_jumlah'] = 0;
    }
    return $stats;
}

function getSPTerbaru($koneksi) {
    $query = "SELECT sp.*, m.nama as nama_mahasiswa FROM surat_peringatan sp LEFT JOIN mahasiswa m ON sp.nim = m.nim ORDER BY sp.tanggal DESC, sp.id DESC LIMIT 5";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    if ($result) while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
    return $data;
}

function getMahasiswaDetail($koneksi, $nim) {
    $query = "SELECT * FROM mahasiswa WHERE nim = '$nim'";
    $result = mysqli_query($koneksi, $query);
    return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
}

// PROSES CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_sp'])) {
        $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
        $jenis_sp = mysqli_real_escape_string($koneksi, $_POST['jenis_sp']);
        $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan']);
        $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
        $status = 'Aktif';
        $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? '');
        
        // Cek mahasiswa
        $cek_mahasiswa = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nim = '$nim'");
        if (mysqli_num_rows($cek_mahasiswa) == 0) {
            $nama = mysqli_real_escape_string($koneksi, $_POST['nama_mahasiswa'] ?? 'Mahasiswa Baru');
            $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan'] ?? 'Belum diisi');
            mysqli_query($koneksi, "INSERT INTO mahasiswa (nim, nama, jurusan, created_at) VALUES ('$nim', '$nama', '$jurusan', NOW())");
        }
        
        $query = "INSERT INTO surat_peringatan (nim, jenis_sp, tanggal, alasan, status, keterangan) VALUES ('$nim', '$jenis_sp', '$tanggal', '$alasan', '$status', '$keterangan')";
        if (mysqli_query($koneksi, $query)) {
            header("Location: ?action=rekap&message=" . urlencode("SP berhasil ditambahkan untuk NIM $nim"));
            exit;
        } else {
            $message = "Gagal menambahkan SP: " . mysqli_error($koneksi);
        }
    }
    
    if (isset($_POST['edit_sp'])) {
        $id = mysqli_real_escape_string($koneksi, $_POST['id']);
        $jenis_sp = mysqli_real_escape_string($koneksi, $_POST['jenis_sp']);
        $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan']);
        $status = mysqli_real_escape_string($koneksi, $_POST['status']);
        $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? '');
        
        $query = "UPDATE surat_peringatan SET jenis_sp = '$jenis_sp', alasan = '$alasan', status = '$status', keterangan = '$keterangan' WHERE id = '$id'";
        if (mysqli_query($koneksi, $query)) {
            header("Location: ?action=kelola&message=" . urlencode("Data SP berhasil diperbarui"));
            exit;
        } else {
            $message = "Gagal mengupdate data: " . mysqli_error($koneksi);
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM surat_peringatan WHERE id = '$id'";
    if (mysqli_query($koneksi, $query)) {
        header("Location: ?action=kelola&message=" . urlencode("Data SP berhasil dihapus"));
        exit;
    } else {
        $message = "Gagal menghapus data: " . mysqli_error($koneksi);
    }
}

// AMBIL DATA
$sp_data = getSPData($koneksi, $search);
$mahasiswa_data = getMahasiswaData($koneksi);
$sp_detail = $id_sp > 0 ? getSPDetail($koneksi, $id_sp) : null;
$stats = getDashboardStats($koneksi);
$sp_terbaru = getSPTerbaru($koneksi);

// HITUNG STATISTIK
$total_sp = count($sp_data);
$sp_aktif = 0;
$sp_selesai = 0;
foreach ($sp_data as $sp) {
    if ($sp['status'] == 'Aktif') $sp_aktif++;
    if ($sp['status'] == 'Selesai') $sp_selesai++;
}

// DATA MAHASISWA DASHBOARD
$mahasiswa_detail = null;
if ($action == 'dashboard' && !empty($nim_search)) {
    $mahasiswa_detail = getMahasiswaDetail($koneksi, $nim_search);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Surat Peringatan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .sidebar { width: 250px; height: 100vh; background: #fff; border-right: 1px solid #ddd; position: fixed; padding: 20px; box-shadow: 2px 0 5px rgba(0,0,0,0.1); z-index: 100; }
    .sidebar .nav-link { color: #333; font-weight: 500; padding: 10px 15px; border-radius: 5px; margin-bottom: 5px; transition: all 0.2s; }
    .sidebar .nav-link:hover { background-color: #f0f7ff; color: #0d6efd; }
    .sidebar .nav-link.active { color: #0d6efd; background-color: #f0f7ff; border-left: 3px solid #0d6efd; }
    .content { margin-left: 270px; padding: 20px; min-height: 100vh; }
    .profile { position: absolute; bottom: 20px; left: 20px; right: 20px; display: flex; align-items: center; background: #f8f9fa; padding: 10px; border-radius: 8px; }
    .dashboard-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; padding: 30px; margin-bottom: 30px; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .stats-card { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center; transition: transform 0.3s; }
    .stats-card:hover { transform: translateY(-5px); }
    .stats-number { font-size: 2.2rem; font-weight: bold; color: #333; margin-bottom: 5px; }
    .stats-label { font-size: 14px; color: #666; font-weight: 500; }
    .badge-sp1 { background-color: #ffc107; color: #000; }
    .badge-sp2 { background-color: #fd7e14; color: #fff; }
    .badge-sp3 { background-color: #dc3545; color: #fff; }
    .table-container, .form-section { background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 30px; }
    .info-box { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .info-box h5 { color: #0d6efd; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
    .info-item { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #f5f5f5; }
    .info-label { font-weight: 600; color: #555; }
    .info-value { color: #333; }
    .attendance-summary { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px; }
    .attendance-item { flex: 1; min-width: 120px; text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; }
    .attendance-number { font-size: 24px; font-weight: bold; color: #0d6efd; }
    .sp-history-item { padding: 10px 0; border-bottom: 1px solid #eee; }
  </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
  <div class="logo-text text-center mb-4">
    <img src="poltek.png" alt="Logo" class="mb-3" width="120">
  </div>
  <ul class="nav flex-column">
    <li class="nav-item"><a class="nav-link <?= ($action == 'dashboard') ? 'active' : '' ?>" href="?action=dashboard"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
    <li class="nav-item"><a class="nav-link <?= ($action == 'rekap' && $pdf_action != 'template') ? 'active' : '' ?>" href="?action=rekap"><i class="bi bi-list-ul me-2"></i>Rekap SP</a></li>
    <li class="nav-item"><a class="nav-link <?= ($pdf_action == 'template') ? 'active' : '' ?>" href="?action=rekap&pdf_action=template"><i class="bi bi-file-earmark-pdf me-2"></i>E-Document SP</a></li>
    <li class="nav-item"><a class="nav-link <?= ($action == 'kelola') ? 'active' : '' ?>" href="?action=kelola"><i class="bi bi-gear me-2"></i>Kelola SP</a></li>
  </ul>
  <div class="profile mt-auto">
    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="40" class="me-2 rounded-circle">
    <div class="me-auto"><strong><?= htmlspecialchars($username) ?></strong><br><small>Staff Akademik</small></div>
    <a href="logout.php" class="text-danger" title="Logout"><i class="bi bi-box-arrow-right" style="font-size: 20px;"></i></a>
  </div>
</div>

<div class="content">
  <div class="dashboard-header">
    <h1>Welcome to Our System, Sir <?= htmlspecialchars($username) ?></h1>
    <p>Sistem Surat Peringatan Mahasiswa</p>
  </div>
  
  <?php if ($message): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if ($action == 'dashboard'): ?>
    <!-- DASHBOARD -->
    <div class="table-container">
      <h4 class="mb-4"><i class="bi bi-house-door me-2"></i>Dashboard Monitoring Mahasiswa</h4>
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="card-title">Cari Data Mahasiswa</h5>
          <form method="GET" class="row g-3">
            <input type="hidden" name="action" value="dashboard">
            <div class="col-md-8"><input type="text" name="nim_search" class="form-control" placeholder="Masukkan NIM Mahasiswa" value="<?= htmlspecialchars($nim_search) ?>" required></div>
            <div class="col-md-4"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Cari Mahasiswa</button></div>
          </form>
        </div>
      </div>
      
      <?php if (!empty($nim_search)): ?>
        <?php if ($mahasiswa_detail): ?>
          <?php 
          $riwayat_sp = getSPData($koneksi, $mahasiswa_detail['nim']);
          $total_sp_mahasiswa = count($riwayat_sp);
          $sp_aktif_mahasiswa = 0;
          $sp_selesai_mahasiswa = 0;
          foreach ($riwayat_sp as $sp) {
              if ($sp['status'] == 'Aktif') $sp_aktif_mahasiswa++;
              if ($sp['status'] == 'Selesai') $sp_selesai_mahasiswa++;
          }
          ?>
          
          <div class="info-box mb-4">
            <h5><i class="bi bi-person-badge me-2"></i>Data Identitas Mahasiswa</h5>
            <div class="row">
              <div class="col-md-6">
                <div class="info-item"><span class="info-label">NIM:</span><span class="info-value float-end"><?= htmlspecialchars($mahasiswa_detail['nim']) ?></span></div>
                <div class="info-item"><span class="info-label">Nama:</span><span class="info-value float-end"><?= htmlspecialchars($mahasiswa_detail['nama']) ?></span></div>
                <div class="info-item"><span class="info-label">Jurusan:</span><span class="info-value float-end"><?= htmlspecialchars($mahasiswa_detail['jurusan'] ?? 'Belum diisi') ?></span></div>
              </div>
              <div class="col-md-6">
                <div class="info-item"><span class="info-label">Email:</span><span class="info-value float-end"><?= htmlspecialchars($mahasiswa_detail['email'] ?? '-') ?></span></div>
                <div class="info-item"><span class="info-label">Alamat:</span><span class="info-value float-end"><?= htmlspecialchars($mahasiswa_detail['alamat'] ?? '-') ?></span></div>
                <div class="info-item"><span class="info-label">Tanggal Daftar:</span><span class="info-value float-end"><?= date('d/m/Y', strtotime($mahasiswa_detail['created_at'])) ?></span></div>
              </div>
            </div>
          </div>
          
          <div class="info-box mb-4">
            <h5><i class="bi bi-calendar-check me-2"></i>Ringkasan Kehadiran</h5>
            <div class="attendance-summary">
              <?php 
              $kehadiran = ['Kehadiran' => rand(70, 90).'%', 'Sakit' => rand(5, 15), 'Izin' => rand(1, 8), 'Tanpa Keterangan' => rand(2, 10)];
              foreach ($kehadiran as $label => $value): ?>
                <div class="attendance-item"><div class="attendance-number"><?= $value ?></div><div class="attendance-label"><?= $label ?></div></div>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div class="info-box mb-4">
            <h5><i class="bi bi-file-earmark-text me-2"></i>Riwayat Surat Peringatan</h5>
            <?php if ($total_sp_mahasiswa > 0): ?>
              <div class="mb-3">
                <div class="row">
                  <div class="col-md-4"><div class="text-center p-2 bg-light rounded"><div class="fs-4 fw-bold"><?= $total_sp_mahasiswa ?></div><div class="text-muted">Total SP</div></div></div>
                  <div class="col-md-4"><div class="text-center p-2 bg-light rounded"><div class="fs-4 fw-bold text-success"><?= $sp_aktif_mahasiswa ?></div><div class="text-muted">SP Aktif</div></div></div>
                  <div class="col-md-4"><div class="text-center p-2 bg-light rounded"><div class="fs-4 fw-bold text-secondary"><?= $sp_selesai_mahasiswa ?></div><div class="text-muted">SP Selesai</div></div></div>
                </div>
              </div>
              <?php foreach ($riwayat_sp as $sp): ?>
                <div class="sp-history-item">
                  <div class="d-flex justify-content-between">
                    <div><strong>SP<?= $sp['jenis_sp'] ?> - <?= date('d/m/Y', strtotime($sp['tanggal'])) ?></strong><div class="text-muted"><?= htmlspecialchars(substr($sp['alasan'], 0, 100)) ?></div></div>
                    <div><span class="badge <?= $sp['status'] == 'Aktif' ? 'bg-success' : 'bg-secondary' ?>"><?= $sp['status'] ?></span></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-4"><i class="bi bi-check-circle display-4 text-success"></i><h5 class="mt-3">Tidak Ada Surat Peringatan</h5><p class="text-muted">Mahasiswa ini tidak memiliki riwayat SP</p></div>
            <?php endif; ?>
          </div>
          
          <div class="info-box">
            <h5><i class="bi bi-clipboard-data me-2"></i>Status Akademik & Pelanggaran</h5>
            <div class="row">
              <div class="col-md-6">
                <div class="info-item"><span class="info-label">Status Akademik:</span><span class="info-value float-end"><span class="badge bg-success">Aktif</span></span></div>
                <div class="info-item"><span class="info-label">IPK:</span><span class="info-value float-end"><?= number_format(rand(250, 375) / 100, 2) ?></span></div>
                <div class="info-item"><span class="info-label">SKS Tempuh:</span><span class="info-value float-end"><?= rand(80, 144) ?></span></div>
              </div>
              <div class="col-md-6">
                <div class="info-item"><span class="info-label">Status Pelanggaran:</span><span class="info-value float-end">
                  <?php if ($total_sp_mahasiswa == 0): ?><span class="badge bg-success">Tidak Ada</span>
                  <?php elseif ($total_sp_mahasiswa == 1): ?><span class="badge bg-warning">Ringan</span>
                  <?php elseif ($total_sp_mahasiswa == 2): ?><span class="badge bg-warning text-dark">Sedang</span>
                  <?php else: ?><span class="badge bg-danger">Berat</span><?php endif; ?>
                </span></div>
                <?php 
                $tingkat_tertinggi = 0;
                foreach ($riwayat_sp as $sp) if ($sp['jenis_sp'] > $tingkat_tertinggi) $tingkat_tertinggi = $sp['jenis_sp'];
                ?>
                <div class="info-item"><span class="info-label">Tingkat SP Tertinggi:</span><span class="info-value float-end">
                  <?php if ($tingkat_tertinggi == 0): ?><span class="badge bg-success">Tidak Ada</span>
                  <?php else: ?><span class="badge bg-danger">SP<?= $tingkat_tertinggi ?></span><?php endif; ?>
                </span></div>
                <div class="info-item"><span class="info-label">Rekomendasi:</span><span class="info-value float-end">
                  <?php if ($total_sp_mahasiswa == 0): ?><span class="badge bg-success">Lanjutkan</span>
                  <?php elseif ($total_sp_mahasiswa == 1): ?><span class="badge bg-warning">Peringatan</span>
                  <?php elseif ($total_sp_mahasiswa == 2): ?><span class="badge bg-warning text-dark">Pembinaan</span>
                  <?php else: ?><span class="badge bg-danger">Evaluasi Khusus</span><?php endif; ?>
                </span></div>
              </div>
            </div>
          </div>
          
        <?php else: ?>
          <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Mahasiswa dengan NIM <strong><?= htmlspecialchars($nim_search) ?></strong> tidak ditemukan.</div>
        <?php endif; ?>
      <?php else: ?>
        <!-- STATISTIK SISTEM -->
        <div class="row mb-4">
          <?php 
          $stat_items = [
            ['number' => $stats['total_mahasiswa'], 'label' => 'Total Mahasiswa', 'color' => '#0d6efd', 'text' => 'Terdaftar di sistem'],
            ['number' => $stats['total_sp'], 'label' => 'Total SP', 'color' => '#dc3545', 'text' => 'Semua jenis SP'],
            ['number' => $stats['sp_aktif'], 'label' => 'SP Aktif', 'color' => '#198754', 'text' => 'Masih dalam proses'],
            ['number' => $stats['sp_hari_ini'], 'label' => 'SP Hari Ini', 'color' => '#ffc107', 'text' => 'Tanggal ' . date('d/m/Y')]
          ];
          foreach ($stat_items as $item): ?>
            <div class="col-md-3">
              <div class="stats-card" style="border-left: 4px solid <?= $item['color'] ?>;">
                <div class="stats-number"><?= $item['number'] ?></div>
                <div class="stats-label"><?= $item['label'] ?></div>
                <small class="text-muted"><?= $item['text'] ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Sistem</h5></div>
              <div class="card-body">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center"><span>Jurusan dengan SP terbanyak:</span><strong><?= htmlspecialchars($stats['jurusan_terbanyak']) ?> (<?= $stats['jurusan_terbanyak_jumlah'] ?> SP)</strong></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center"><span>Role Akun:</span><span class="badge bg-info"><?= htmlspecialchars($role) ?></span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center"><span>Tanggal Hari Ini:</span><strong><?= date('d F Y') ?></strong></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center"><span>Total Data dalam Sistem:</span><strong><?= $stats['total_mahasiswa'] + $stats['total_sp'] ?> data</strong></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header bg-warning text-dark"><h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru</h5></div>
              <div class="card-body">
                <?php if (empty($sp_terbaru)): ?>
                  <div class="text-center py-3"><i class="bi bi-inbox display-6 text-muted"></i><p class="mt-2 text-muted">Belum ada data SP</p></div>
                <?php else: foreach ($sp_terbaru as $sp): ?>
                  <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between"><h6 class="mb-1"><?= htmlspecialchars($sp['nama_mahasiswa'] ?? 'Mahasiswa') ?></h6><small class="text-muted"><?= date('d/m/Y', strtotime($sp['tanggal'])) ?></small></div>
                    <p class="mb-1"><?= htmlspecialchars(substr($sp['alasan'], 0, 60)) . (strlen($sp['alasan']) > 60 ? '...' : '') ?></p>
                    <small><span class="badge bg-primary">SP<?= $sp['jenis_sp'] ?></span><span class="badge <?= $sp['status'] == 'Aktif' ? 'bg-success' : 'bg-secondary' ?>"><?= $sp['status'] ?></span></small>
                  </div>
                <?php endforeach; endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
    
  <?php elseif ($action == 'rekap' && $pdf_action == 'template'): ?>
    <!-- TEMPLATE PDF -->
    <div class="table-container">
      <h4 class="mb-4"><i class="bi bi-file-earmark-pdf me-2"></i>Dokumen Resmi Surat Peringatan</h4>
      <div class="row">
        <?php for ($i = 1; $i <= 3; $i++): $colors = [1 => 'primary', 2 => 'warning', 3 => 'danger']; ?>
          <div class="col-md-6">
            <div class="card mb-4">
              <div class="card-header bg-<?= $colors[$i] ?> <?= $i == 2 ? 'text-dark' : 'text-white' ?>"><h5 class="mb-0">Format SP <?= $i ?> <?= $i == 1 ? '(Peringatan)' : ($i == 2 ? '(Peringatan Keras)' : '(Skorsing)') ?></h5></div>
              <div class="card-body">
                <p>Format standar Surat Peringatan tingkat <?= $i ?></p>
                <div class="d-flex justify-content-between">
                  <a href="cetak_template.php?jenis=<?= $i ?>" class="btn btn-<?= $colors[$i] ?>" target="_blank"><i class="bi bi-eye me-2"></i>Lihat Format</a>
                  <a href="cetak_template.php?jenis=<?= $i ?>&download=1" class="btn btn-success"><i class="bi bi-download me-2"></i>Download</a>
                </div>
              </div>
            </div>
          </div>
        <?php endfor; ?>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-info text-white"><h5 class="mb-0">Generator SP Otomatis</h5></div>
            <div class="card-body">
              <p>Generate surat peringatan otomatis dari data SP</p>
              <form method="GET" action="cetak_pdf.php">
                <select name="id" class="form-select mb-3" required><option value="">Pilih Data SP</option>
                  <?php foreach ($sp_data as $sp): ?><option value="<?= $sp['id'] ?>">SP<?= $sp['jenis_sp'] ?> - <?= $sp['nim'] ?> - <?= $sp['nama_mahasiswa'] ?></option><?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-printer me-2"></i>Generate & Cetak SP</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  <?php elseif ($action == 'rekap'): ?>
    <!-- REKAP SP -->
    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-list-ul me-2"></i>Rekap Surat Peringatan</h4>
        <form method="GET" class="d-flex me-2" style="max-width: 300px;">
          <input type="hidden" name="action" value="rekap">
          <input type="text" name="search" class="form-control me-2" placeholder="Cari NIM/Nama..." value="<?= htmlspecialchars($search) ?>">
          <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        </form>
      </div>
      <div class="row mb-4">
        <?php 
        $rekap_stats = [
          ['number' => $total_sp, 'label' => 'Total SP'],
          ['number' => $sp_aktif, 'label' => 'SP Aktif'],
          ['number' => $sp_selesai, 'label' => 'SP Selesai'],
          ['number' => date('Y'), 'label' => 'Tahun Akademik']
        ];
        foreach ($rekap_stats as $stat): ?>
          <div class="col-md-3"><div class="stats-card"><div class="stats-number"><?= $stat['number'] ?></div><div class="stats-label"><?= $stat['label'] ?></div></div></div>
        <?php endforeach; ?>
      </div>
      <?php if (empty($sp_data)): ?>
        <div class="alert alert-info text-center py-4"><i class="bi bi-info-circle me-2"></i>Tidak ada data surat peringatan.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light"><tr><th>No</th><th>NIM</th><th>Nama</th><th>Jenis SP</th><th>Tanggal</th><th>Alasan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
              <?php foreach ($sp_data as $index => $sp): 
                $status_class = match($sp['status']) {
                  'Aktif' => 'badge-status-aktif',
                  'Selesai' => 'badge-status-selesai',
                  'Dicabut' => 'badge-status-dicabut',
                  default => 'badge-secondary'
                };
              ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><strong><?= htmlspecialchars($sp['nim']) ?></strong></td>
                  <td><?= htmlspecialchars($sp['nama_mahasiswa'] ?? 'Tidak diketahui') ?></td>
                  <td><span class="badge badge-sp<?= $sp['jenis_sp'] ?>">SP<?= $sp['jenis_sp'] ?></span></td>
                  <td><?= date('d/m/Y', strtotime($sp['tanggal'])) ?></td>
                  <td><?= htmlspecialchars(substr($sp['alasan'], 0, 50)) . (strlen($sp['alasan']) > 50 ? '...' : '') ?></td>
                  <td><span class="badge <?= $status_class ?>"><?= htmlspecialchars($sp['status']) ?></span></td>
                  <td>
                    <div class="btn-group">
                      <a href="cetak_pdf.php?id=<?= $sp['id'] ?>" class="btn btn-sm btn-danger" target="_blank" title="Cetak PDF"><i class="bi bi-printer"></i></a>
                      <a href="?action=kelola&view=<?= $sp['id'] ?>" class="btn btn-sm btn-info" title="Detail"><i class="bi bi-eye"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
    
  <?php elseif ($action == 'kelola'): ?>
    <!-- KELOLA SP -->
    <div class="row">
      <div class="col-md-12">
        <div class="form-section">
          <h4 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Tambah Surat Peringatan</h4>
          <form method="POST">
            <input type="hidden" name="tambah_sp" value="1">
            <div class="row mb-3">
              <div class="col-md-6"><label class="form-label">NIM Mahasiswa *</label><input type="text" name="nim" class="form-control" required placeholder="Contoh: 20231001"></div>
              <div class="col-md-6"><label class="form-label">Jenis SP *</label><select name="jenis_sp" class="form-select" required><option value="">Pilih Jenis SP</option><option value="1">SP 1 (Peringatan)</option><option value="2">SP 2 (Peringatan Keras)</option><option value="3">SP 3 (Skorsing)</option></select></div>
            </div>
            <div class="mb-3"><label class="form-label">Alasan Pelanggaran *</label><textarea name="alasan" class="form-control" rows="3" required placeholder="Jelaskan alasan penerbitan SP"></textarea></div>
            <div class="row mb-3">
              <div class="col-md-6"><label class="form-label">Tanggal Terbit *</label><input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
              <div class="col-md-6"><label class="form-label">Catatan / Tindak Lanjut</label><textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional"></textarea></div>
            </div>
            <div class="mb-3"><small class="text-muted">* Data mahasiswa akan otomatis dibuat jika belum ada dalam sistem.</small></div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan SP</button>
          </form>
        </div>
        
        <div class="table-container mt-4">
          <?php if (empty($sp_data)): ?>
            <div class="alert alert-info text-center py-4"><i class="bi bi-info-circle me-2"></i>Belum ada data SP untuk dikelola.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light"><tr><th>No</th><th>NIM</th><th>Nama</th><th>Jenis SP</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                  <?php foreach ($sp_data as $index => $sp): ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars($sp['nim']) ?></td>
                      <td><?= htmlspecialchars($sp['nama_mahasiswa'] ?? 'N/A') ?></td>
                      <td><span class="badge badge-sp<?= $sp['jenis_sp'] ?>">SP<?= $sp['jenis_sp'] ?></span></td>
                      <td><span class="badge <?= $sp['status'] == 'Aktif' ? 'badge-status-aktif' : 'badge-status-selesai' ?>"><?= $sp['status'] ?></span></td>
                      <td>
                        <div class="btn-group">
                          <a href="cetak_pdf.php?id=<?= $sp['id'] ?>" class="btn btn-sm btn-danger" target="_blank" title="Cetak PDF"><i class="bi bi-printer"></i></a>
                          <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $sp['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                          <a href="?action=kelola&delete=<?= $sp['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus SP ini?')" title="Hapus"><i class="bi bi-trash"></i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- MODAL EDIT -->
                    <div class="modal fade" id="editModal<?= $sp['id'] ?>">
                      <div class="modal-dialog"><div class="modal-content">
                        <form method="POST">
                          <input type="hidden" name="id" value="<?= $sp['id'] ?>">
                          <input type="hidden" name="edit_sp" value="1">
                          <div class="modal-header"><h5 class="modal-title">Edit SP - <?= $sp['nim'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                          <div class="modal-body">
                            <div class="mb-3">
                              <label class="form-label">Jenis SP</label>
                              <select name="jenis_sp" class="form-select" required>
                                <?php for ($i = 1; $i <= 3; $i++): ?><option value="<?= $i ?>" <?= $sp['jenis_sp'] == $i ? 'selected' : '' ?>>SP <?= $i ?></option><?php endfor; ?>
                              </select>
                            </div>
                            <div class="mb-3"><label class="form-label">Alasan</label><textarea name="alasan" class="form-control" rows="3" required><?= $sp['alasan'] ?></textarea></div>
                            <div class="mb-3">
                              <label class="form-label">Status</label>
                              <select name="status" class="form-select" required>
                                <option value="Aktif" <?= $sp['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="Selesai" <?= $sp['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="Dicabut" <?= $sp['status'] == 'Dicabut' ? 'selected' : '' ?>>Dicabut</option>
                              </select>
                            </div>
                            <div class="mb-3"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="2"><?= $sp['keterangan'] ?? '' ?></textarea></div>
                          </div>
                          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
                        </form>
                      </div></div>
                    </div>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
setTimeout(() => document.querySelectorAll('.alert').forEach(a => new bootstrap.Alert(a).close()), 5000);
document.addEventListener('DOMContentLoaded', () => {
  const tanggalInput = document.querySelector('input[name="tanggal"]');
  if (tanggalInput && !tanggalInput.value) tanggalInput.value = new Date().toISOString().split('T')[0];
});
</script>
</body>
</html>