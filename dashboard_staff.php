<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit;
}

// PENERAPAN CLEAN CODE: Variabel dengan nama deskriptif
$username = $_SESSION['username'];
$userRole = $_SESSION['role'];

// PENERAPAN CLEAN CODE: Deklarasi variabel dengan nilai default yang jelas
$action = $_GET['action'] ?? 'dashboard';
$spId = $_GET['id'] ?? 0;
$message = $_GET['message'] ?? '';
$search = $_GET['search'] ?? '';
$pdfAction = $_GET['pdf_action'] ?? '';
$nikSearch = $_GET['nik_search'] ?? '';
$activeTab = $_GET['tab'] ?? 'data';

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan ringkasan kehadiran mahasiswa
 */
function getAttendanceSummary($connection, $nik) {
    $sanitizedNik = mysqli_real_escape_string($connection, $nik);
    
    $query = "SELECT 
                COUNT(CASE WHEN status = 'hadir' THEN 1 END) as hadir,
                COUNT(CASE WHEN status = 'sakit' THEN 1 END) as sakit,
                COUNT(CASE WHEN status = 'izin' THEN 1 END) as izin,
                COUNT(CASE WHEN status = 'tanpa_keterangan' THEN 1 END) as tanpa_keterangan,
                COUNT(*) as total 
              FROM kehadiran 
              WHERE nik = '$sanitizedNik'";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada query database
        $result = mysqli_query($connection, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            $total = $data['total'];
            $present = $data['hadir'];
            $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            
            return [
                'percentage' => $percentage,
                'present' => $present,
                'hadir' => $data['hadir'],
                'sakit' => $data['sakit'],
                'izin' => $data['izin'],
                'tanpa_keterangan' => $data['tanpa_keterangan'],
                'total' => $data['total']
            ];
        }
    } catch (Exception $e) {
        // PENERAPAN ERROR HANDLING: Log error atau handle exception
        error_log("Error getting attendance summary: " . $e->getMessage());
    }
    
    return [
        'percentage' => 0,
        'present' => 0,
        'hadir' => 0,
        'sakit' => 0,
        'izin' => 0,
        'tanpa_keterangan' => 0,
        'total' => 0
    ];
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan riwayat kehadiran
 */
function getAttendanceHistory($connection, $nik, $limit = 50) {
    $sanitizedNik = mysqli_real_escape_string($connection, $nik);
    $attendanceHistory = [];
    
    $query = "SELECT * 
              FROM kehadiran 
              WHERE nik = '$sanitizedNik' 
              ORDER BY tanggal DESC, id DESC 
              LIMIT $limit";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada query database
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $attendanceHistory[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error getting attendance history: " . $e->getMessage());
    }
    
    return $attendanceHistory;
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menyimpan data kehadiran
 */
function saveAttendance($connection, $postData) {
    $requiredFields = ['nik', 'tanggal', 'status'];
    
    // PENERAPAN CLEAN CODE: Validasi input yang lebih baik
    foreach ($requiredFields as $field) {
        if (empty($postData[$field])) {
            return false;
        }
    }
    
    $nik = mysqli_real_escape_string($connection, $postData['nik']);
    $date = mysqli_real_escape_string($connection, $postData['tanggal']);
    $status = mysqli_real_escape_string($connection, $postData['status']);
    $note = mysqli_real_escape_string($connection, $postData['keterangan'] ?? '');
    
    $query = "INSERT INTO kehadiran (nik, tanggal, status, keterangan) 
              VALUES ('$nik', '$date', '$status', '$note')";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada eksekusi query
        return mysqli_query($connection, $query);
    } catch (Exception $e) {
        error_log("Error saving attendance: " . $e->getMessage());
        return false;
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menghapus kehadiran
 */
function deleteAttendance($connection, $id) {
    $sanitizedId = mysqli_real_escape_string($connection, $id);
    $query = "DELETE FROM kehadiran WHERE id = '$sanitizedId'";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada operasi database
        return mysqli_query($connection, $query);
    } catch (Exception $e) {
        error_log("Error deleting attendance: " . $e->getMessage());
        return false;
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan data SP dengan pencarian
 */
function getWarningLetterData($connection, $search = '') {
    $warningLetters = [];
    
    $query = "SELECT sp.*, m.nama as nama_mahasiswa, m.jurusan, m.email 
              FROM surat_peringatan sp 
              LEFT JOIN mahasiswa m ON sp.nik = m.nik 
              WHERE 1=1";
    
    if (!empty($search)) {
        $sanitizedSearch = mysqli_real_escape_string($connection, $search);
        $query .= " AND (sp.nik LIKE '%$sanitizedSearch%' OR m.nama LIKE '%$sanitizedSearch%')";
    }
    
    $query .= " ORDER BY sp.tanggal DESC, sp.id DESC";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada query database
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $warningLetters[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error getting warning letters: " . $e->getMessage());
    }
    
    return $warningLetters;
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan data mahasiswa
 */
function getStudentData($connection) {
    $students = [];
    
    $query = "SELECT * FROM mahasiswa ORDER BY nama ASC";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada operasi database
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $students[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error getting student data: " . $e->getMessage());
    }
    
    return $students;
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan detail SP
 */
function getWarningLetterDetail($connection, $id) {
    $sanitizedId = mysqli_real_escape_string($connection, $id);
    
    $query = "SELECT sp.*, m.nama as nama_mahasiswa, m.email, m.jurusan, m.alamat 
              FROM surat_peringatan sp 
              LEFT JOIN mahasiswa m ON sp.nik = m.nik 
              WHERE sp.id = '$sanitizedId'";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada query database
        $result = mysqli_query($connection, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    } catch (Exception $e) {
        error_log("Error getting warning letter detail: " . $e->getMessage());
    }
    
    return null;
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan statistik dashboard
 */
function getDashboardStatistics($connection) {
    $statistics = [];
    
    // PENERAPAN CLEAN CODE: Konfigurasi query yang lebih terstruktur
    $queries = [
        'total_students' => "SELECT COUNT(*) as total FROM mahasiswa",
        'total_warning_letters' => "SELECT COUNT(*) as total FROM surat_peringatan",
        'active_warning_letters' => "SELECT COUNT(*) as aktif FROM surat_peringatan WHERE status = 'Aktif'",
        'today_warning_letters' => "SELECT COUNT(*) as hari_ini FROM surat_peringatan WHERE DATE(tanggal) = CURDATE()"
    ];
    
    foreach ($queries as $key => $sql) {
        try {
            // PENERAPAN ERROR HANDLING: Try-catch untuk setiap query
            $result = mysqli_query($connection, $sql);
            
            if ($result) {
                $data = mysqli_fetch_assoc($result);
                $statistics[$key] = $data[array_key_first($data)] ?? 0;
            } else {
                $statistics[$key] = 0;
            }
        } catch (Exception $e) {
            error_log("Error getting statistic $key: " . $e->getMessage());
            $statistics[$key] = 0;
        }
    }
    
    // Query untuk jurusan dengan SP terbanyak
    $departmentQuery = "SELECT m.jurusan, COUNT(sp.id) as jumlah 
                       FROM surat_peringatan sp 
                       LEFT JOIN mahasiswa m ON sp.nik = m.nik 
                       WHERE m.jurusan != '' 
                       GROUP BY m.jurusan 
                       ORDER BY jumlah DESC 
                       LIMIT 1";
    
    try {
        $departmentResult = mysqli_query($connection, $departmentQuery);
        
        if ($departmentResult && mysqli_num_rows($departmentResult) > 0) {
            $row = mysqli_fetch_assoc($departmentResult);
            $statistics['jurusan_terbanyak'] = $row['jurusan'] ?: 'Belum ada data';
            $statistics['jurusan_terbanyak_jumlah'] = $row['jumlah'];
        } else {
            $statistics['jurusan_terbanyak'] = 'Belum ada data';
            $statistics['jurusan_terbanyak_jumlah'] = 0;
        }
    } catch (Exception $e) {
        error_log("Error getting department statistics: " . $e->getMessage());
        $statistics['jurusan_terbanyak'] = 'Belum ada data';
        $statistics['jurusan_terbanyak_jumlah'] = 0;
    }
    
    return $statistics;
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan SP terbaru
 */
function getLatestWarningLetters($connection) {
    $latestLetters = [];
    
    $query = "SELECT sp.*, m.nama as nama_mahasiswa 
              FROM surat_peringatan sp 
              LEFT JOIN mahasiswa m ON sp.nik = m.nik 
              ORDER BY sp.tanggal DESC, sp.id DESC 
              LIMIT 5";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada query database
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $latestLetters[] = $row;
            }
        }
    } catch (Exception $e) {
        error_log("Error getting latest warning letters: " . $e->getMessage());
    }
    
    return $latestLetters;
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan detail mahasiswa
 */
function getStudentDetail($connection, $nik) {
    $sanitizedNik = mysqli_real_escape_string($connection, $nik);
    $query = "SELECT * FROM mahasiswa WHERE nik = '$sanitizedNik'";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada operasi database
        $result = mysqli_query($connection, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
    } catch (Exception $e) {
        error_log("Error getting student detail: " . $e->getMessage());
    }
    
    return null;
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mengirim notifikasi ke mahasiswa
 */
function sendNotificationToStudent($connection, $nik, $warningType, $reason) {
    $createTableQuery = "CREATE TABLE IF NOT EXISTS notifikasi_mahasiswa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nik VARCHAR(50) NOT NULL,
        pesan TEXT NOT NULL,
        tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('belum_dibaca', 'sudah_dibaca') DEFAULT 'belum_dibaca'
    )";
    
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada pembuatan tabel
        mysqli_query($connection, $createTableQuery);
        
        $message = "Anda menerima SP$warningType dengan alasan: " . substr($reason, 0, 100) . "...";
        $insertQuery = "INSERT INTO notifikasi_mahasiswa (nik, pesan, tanggal) 
                        VALUES ('$nik', '$message', NOW())";
        
        return mysqli_query($connection, $insertQuery);
    } catch (Exception $e) {
        error_log("Error sending notification: " . $e->getMessage());
        return false;
    }
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    handlePostRequest($koneksi);
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menangani semua request POST
 */
function handlePostRequest($connection) {
    // PENERAPAN CLEAN CODE: Struktur yang lebih terorganisir
    if (isset($_POST['tambah_sp'])) {
        handleAddWarningLetter($connection);
    } elseif (isset($_POST['import_pdf_sp'])) {
        handleImportPdf($connection);
    } elseif (isset($_POST['edit_sp'])) {
        handleEditWarningLetter($connection);
    } elseif (isset($_POST['simpan_kehadiran'])) {
        handleSaveAttendance($connection);
    } elseif (isset($_POST['hapus_kehadiran'])) {
        handleDeleteAttendance($connection);
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menambahkan SP baru
 */
function handleAddWarningLetter($connection) {
    try {
        // PENERAPAN ERROR HANDLING: Try-catch pada seluruh proses
        $nik = mysqli_real_escape_string($connection, $_POST['nik']);
        $warningType = mysqli_real_escape_string($connection, $_POST['jenis_sp']);
        $reason = mysqli_real_escape_string($connection, $_POST['alasan']);
        $date = mysqli_real_escape_string($connection, $_POST['tanggal']);
        $note = mysqli_real_escape_string($connection, $_POST['keterangan'] ?? '');
        
        // Cek apakah mahasiswa ada
        $checkStudentQuery = "SELECT * FROM mahasiswa WHERE nik = '$nik'";
        $checkResult = mysqli_query($connection, $checkStudentQuery);
        
        if (mysqli_num_rows($checkResult) == 0) {
            // Tambah mahasiswa baru jika tidak ada
            $name = mysqli_real_escape_string($connection, $_POST['nama_mahasiswa'] ?? 'Mahasiswa Baru');
            $department = mysqli_real_escape_string($connection, $_POST['jurusan'] ?? 'Belum diisi');
            
            $addStudentQuery = "INSERT INTO mahasiswa (nik, nama, jurusan, created_at) 
                                VALUES ('$nik', '$nama', '$jurusan', NOW())";
            mysqli_query($connection, $addStudentQuery);
        }
        
        // Tambah SP
        $addWarningQuery = "INSERT INTO surat_peringatan (nik, jenis_sp, tanggal, alasan, status, keterangan) 
                            VALUES ('$nik', '$warningType', '$date', '$reason', 'Aktif', '$note')";
        
        if (mysqli_query($connection, $addWarningQuery)) {
            sendNotificationToStudent($connection, $nik, $warningType, $reason);
            header("Location: ?action=rekap&message=SP berhasil ditambahkan untuk nik $nik dan notifikasi telah dikirim");
            exit;
        } else {
            throw new Exception("Gagal menambahkan SP: " . mysqli_error($connection));
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $message = "Terjadi kesalahan: " . $e->getMessage();
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menangani import PDF
 */
function handleImportPdf($connection) {
    try {
        // PENERAPAN ERROR HANDLING: Multi-level try-catch untuk berbagai validasi
        $nik = mysqli_real_escape_string($connection, $_POST['nik_import']);
        $warningType = mysqli_real_escape_string($connection, $_POST['jenis_sp_import']);
        $reason = mysqli_real_escape_string($connection, $_POST['alasan_import']);
        $date = mysqli_real_escape_string($connection, $_POST['tanggal_import']);
        $note = mysqli_real_escape_string($connection, $_POST['keterangan_import'] ?? '');
        
        // Validasi file upload
        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] != 0) {
            throw new Exception("File tidak ditemukan atau error pada upload");
        }
        
        $fileInfo = $_FILES['pdf_file'];
        
        if ($fileInfo['type'] != 'application/pdf') {
            throw new Exception("File harus berformat PDF");
        }
        
        // Upload file
        $uploadDir = 'uploads/pdf_sp/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $newFileName = 'SP' . $warningType . '_' . $nik . '_' . date('YmdHis') . '.pdf';
        $filePath = $uploadDir . $newFileName;
        
        if (!move_uploaded_file($fileInfo['tmp_name'], $filePath)) {
            throw new Exception("Gagal mengupload file PDF");
        }
        
        // Pastikan kolom file_pdf ada
        $checkColumnQuery = "SHOW COLUMNS FROM surat_peringatan LIKE 'file_pdf'";
        $columnResult = mysqli_query($connection, $checkColumnQuery);
        
        if (mysqli_num_rows($columnResult) == 0) {
            mysqli_query($connection, "ALTER TABLE surat_peringatan ADD COLUMN file_pdf VARCHAR(255) DEFAULT NULL AFTER keterangan");
        }
        
        // Simpan ke database
        $insertQuery = "INSERT INTO surat_peringatan (nik, jenis_sp, tanggal, alasan, status, keterangan, file_pdf) 
                        VALUES ('$nik', '$warningType', '$date', '$reason', 'Aktif', '$note', '$filePath')";
        
        if (mysqli_query($connection, $insertQuery)) {
            sendNotificationToStudent($connection, $nik, $warningType, $reason);
            $message = "✅ SP berhasil diimport dan dikirim ke mahasiswa NIK: $nik";
        } else {
            throw new Exception("Gagal menyimpan data SP: " . mysqli_error($connection));
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $message = "❌ " . $e->getMessage();
    }
}

// PENERAPAN CLEAN CODE: Mengambil data yang diperlukan
$warningLetterData = getWarningLetterData($koneksi, $search);
$studentData = getStudentData($koneksi);
$warningLetterDetail = $spId > 0 ? getWarningLetterDetail($koneksi, $spId) : null;
$dashboardStats = getDashboardStatistics($koneksi);
$latestWarningLetters = getLatestWarningLetters($koneksi);

// PENERAPAN CLEAN CODE: Perhitungan yang lebih jelas
$totalWarningLetters = count($warningLetterData);
$activeWarningLetters = 0;
$completedWarningLetters = 0;

foreach ($warningLetterData as $letter) {
    if ($letter['status'] == 'Aktif') $activeWarningLetters++;
    if ($letter['status'] == 'Selesai') $completedWarningLetters++;
}

// PENERAPAN CLEAN CODE: Inisialisasi variabel dengan nilai default
$studentDetail = null;
$attendanceSummary = null;
$attendanceHistory = null;

if ($action == 'dashboard' && !empty($nikSearch)) {
    $studentDetail = getStudentDetail($koneksi, $nikSearch);
    
    if ($studentDetail) {
        $attendanceSummary = getAttendanceSummary($koneksi, $nikSearch);
        $attendanceHistory = getAttendanceHistory($koneksi, $nikSearch, 50);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Surat Peringatan & Kehadiran</title>
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
    .attendance-number { font-size: 24px; font-weight: bold; }
    .sp-history-item { padding: 10px 0; border-bottom: 1px solid #eee; }
    .nav-tabs .nav-link { color: #495057; font-weight: 500; }
    .nav-tabs .nav-link.active { border-bottom: 3px solid #0d6efd; font-weight: 600; }
    .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
    .status-hadir { background-color: #d4edda; color: #155724; }
    .status-sakit { background-color: #fff3cd; color: #856404; }
    .status-izin { background-color: #cce5ff; color: #004085; }
    .status-tanpa_keterangan { background-color: #f8d7da; color: #721c24; }
    .persentase-kehadiran { font-size: 2.5rem; font-weight: bold; color: #0d6efd; text-align: center; margin: 10px 0; }
    .kehadiran-table th { background-color: #f8f9fa; position: sticky; top: 0; z-index: 10; }
    .btn-import-pdf { background: linear-gradient(45deg, #28a745, #20c997); border: none; color: white; padding: 10px 20px; border-radius: 5px; font-weight: 500; transition: all 0.3s; }
    .btn-import-pdf:hover { background: linear-gradient(45deg, #218838, #1ea181); transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .import-modal .modal-content { border: 2px solid #20c997; border-radius: 10px; }
    .import-modal .modal-header { background: linear-gradient(45deg, #28a745, #20c997); color: white; border-radius: 8px 8px 0 0; }
    .file-upload-area { border: 2px dashed #28a745; border-radius: 8px; padding: 30px; text-align: center; background: #f8fff9; cursor: pointer; transition: all 0.3s; }
    .file-upload-area:hover { background: #e8f5e9; border-color: #218838; }
    .file-upload-area i { font-size: 48px; color: #28a745; margin-bottom: 10px; }
  </style>
</head>
<body>

<div class="sidebar d-flex flex-column">
  <div class="logo-text text-center mb-4">
    <img src="poltek.png" alt="Logo" class="mb-3" width="120">
  </div>
  <ul class="nav flex-column">
    <li class="nav-item"><a class="nav-link <?= ($action == 'dashboard') ? 'active' : '' ?>" href="?action=dashboard"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
    <li class="nav-item"><a class="nav-link <?= ($pdfAction == 'template') ? 'active' : '' ?>" href="?action=rekap&pdf_action=template"><i class="bi bi-file-earmark-pdf me-2"></i>E-Document SP</a></li>
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
    <p>Sistem Surat Peringatan & Kehadiran Mahasiswa</p>
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
            <input type="hidden" name="tab" value="<?= $activeTab ?>">
            <div class="col-md-8"><input type="text" name="nik_search" class="form-control" placeholder="Masukkan NIM Mahasiswa" value="<?= htmlspecialchars($nikSearch) ?>" required></div>
            <div class="col-md-4"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Cari Mahasiswa</button></div>
          </form>
        </div>
      </div>
      
      <?php if (!empty($nikSearch)): ?>
        <?php if ($studentDetail): 
          $studentWarningHistory = getWarningLetterData($koneksi, $studentDetail['nik']);
          $totalStudentWarnings = count($studentWarningHistory);
          $activeStudentWarnings = 0;
          $completedStudentWarnings = 0;
          
          foreach ($studentWarningHistory as $warning) {
              if ($warning['status'] == 'Aktif') $activeStudentWarnings++;
              if ($warning['status'] == 'Selesai') $completedStudentWarnings++;
          }
          ?>
          
          <!-- Tab Navigation -->
          <ul class="nav nav-tabs mb-4">
            <li class="nav-item"><a class="nav-link <?= ($activeTab == 'data') ? 'active' : '' ?>" href="?action=dashboard&nik_search=<?= $nikSearch ?>&tab=data">Data Mahasiswa</a></li>
            <li class="nav-item"><a class="nav-link <?= ($activeTab == 'kehadiran') ? 'active' : '' ?>" href="?action=dashboard&nik_search=<?= $nikSearch ?>&tab=kehadiran">Kehadiran</a></li>
          </ul>
          
          <?php if ($activeTab == 'data'): ?>
            <!-- TAB DATA MAHASISWA -->
            <div class="info-box mb-4">
              <h5><i class="bi bi-person-badge me-2"></i>Data Identitas Mahasiswa</h5>
              <div class="row">
                <div class="col-md-6">
                  <div class="info-item"><span class="info-label">NIM:</span><span class="info-value float-end"><?= htmlspecialchars($studentDetail['nik']) ?></span></div>
                  <div class="info-item"><span class="info-label">Nama:</span><span class="info-value float-end"><?= htmlspecialchars($studentDetail['nama']) ?></span></div>
                  <div class="info-item"><span class="info-label">Jurusan:</span><span class="info-value float-end"><?= htmlspecialchars($studentDetail['jurusan'] ?? 'Belum diisi') ?></span></div>
                </div>
                <div class="col-md-6">
                  <div class="info-item"><span class="info-label">Email:</span><span class="info-value float-end"><?= htmlspecialchars($studentDetail['email'] ?? '-') ?></span></div>
                  <div class="info-item"><span class="info-label">Alamat:</span><span class="info-value float-end"><?= htmlspecialchars($studentDetail['alamat'] ?? '-') ?></span></div>
                  <div class="info-item"><span class="info-label">Tanggal Daftar:</span><span class="info-value float-end"><?= date('d/m/Y', strtotime($studentDetail['created_at'])) ?></span></div>
                </div>
              </div>
            </div>
            
            <div class="info-box mb-4">
              <h5><i class="bi bi-calendar-check me-2"></i>Ringkasan Kehadiran</h5>
              <?php if ($attendanceSummary): ?>
              <div class="attendance-summary">
                <div class="attendance-item"><div class="persentase-kehadiran"><?= $attendanceSummary['percentage'] ?>%</div><div class="attendance-label">Kehadiran</div></div>
                <div class="attendance-item"><div class="attendance-number text-warning"><?= $attendanceSummary['sakit'] ?></div><div class="attendance-label">Sakit</div></div>
                <div class="attendance-item"><div class="attendance-number text-info"><?= $attendanceSummary['izin'] ?></div><div class="attendance-label">Izin</div></div>
                <div class="attendance-item"><div class="attendance-number text-danger"><?= $attendanceSummary['tanpa_keterangan'] ?></div><div class="attendance-label">Tanpa Keterangan</div></div>
              </div>
              <div class="text-center mt-3"><small class="text-muted">Total <?= $attendanceSummary['total'] ?> catatan kehadiran</small></div>
              <?php else: ?>
              <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Belum ada data kehadiran untuk mahasiswa ini.</div>
              <?php endif; ?>
            </div>
            
            <div class="info-box mb-4">
              <h5><i class="bi bi-file-earmark-text me-2"></i>Riwayat Surat Peringatan</h5>
              <?php if ($totalStudentWarnings > 0): ?>
                <div class="mb-3">
                  <div class="row">
                    <div class="col-md-4"><div class="text-center p-2 bg-light rounded"><div class="fs-4 fw-bold"><?= $totalStudentWarnings ?></div><div class="text-muted">Total SP</div></div></div>
                    <div class="col-md-4"><div class="text-center p-2 bg-light rounded"><div class="fs-4 fw-bold text-success"><?= $activeStudentWarnings ?></div><div class="text-muted">SP Aktif</div></div></div>
                    <div class="col-md-4"><div class="text-center p-2 bg-light rounded"><div class="fs-4 fw-bold text-secondary"><?= $completedStudentWarnings ?></div><div class="text-muted">SP Selesai</div></div></div>
                  </div>
                </div>
                <?php foreach ($studentWarningHistory as $warning): ?>
                  <div class="sp-history-item">
                    <div class="d-flex justify-content-between">
                      <div><strong>SP<?= $warning['jenis_sp'] ?> - <?= date('d/m/Y', strtotime($warning['tanggal'])) ?></strong><div class="text-muted"><?= htmlspecialchars(substr($warning['alasan'], 0, 100)) ?></div></div>
                      <div><span class="badge <?= $warning['status'] == 'Aktif' ? 'bg-success' : 'bg-secondary' ?>"><?= $warning['status'] ?></span></div>
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
                  <?php $highestLevel = 0; foreach ($studentWarningHistory as $warning) if ($warning['jenis_sp'] > $highestLevel) $highestLevel = $warning['jenis_sp']; ?>
                  <div class="info-item"><span class="info-label">Status Pelanggaran:</span><span class="info-value float-end">
                    <?= $totalStudentWarnings == 0 ? '<span class="badge bg-success">Tidak Ada</span>' : ($totalStudentWarnings == 1 ? '<span class="badge bg-warning">Ringan</span>' : ($totalStudentWarnings == 2 ? '<span class="badge bg-warning text-dark">Sedang</span>' : '<span class="badge bg-danger">Berat</span>')) ?>
                  </span></div>
                  <div class="info-item"><span class="info-label">Tingkat SP Tertinggi:</span><span class="info-value float-end">
                    <?= $highestLevel == 0 ? '<span class="badge bg-success">Tidak Ada</span>' : '<span class="badge bg-danger">SP' . $highestLevel . '</span>' ?>
                  </span></div>
                  <div class="info-item"><span class="info-label">Rekomendasi:</span><span class="info-value float-end">
                    <?= $totalStudentWarnings == 0 ? '<span class="badge bg-success">Lanjutkan</span>' : ($totalStudentWarnings == 1 ? '<span class="badge bg-warning">Peringatan</span>' : ($totalStudentWarnings == 2 ? '<span class="badge bg-warning text-dark">Pembinaan</span>' : '<span class="badge bg-danger">Evaluasi Khusus</span>')) ?>
                  </span></div>
                </div>
              </div>
            </div>
            
          <?php elseif ($activeTab == 'kehadiran'): ?>
            <!-- TAB KEHADIRAN -->
            <div class="info-box mb-4">
              <h5><i class="bi bi-calendar-check me-2"></i>Ringkasan Kehadiran</h5>
              <?php if ($attendanceSummary): ?>
              <div class="attendance-summary mb-4">
                <div class="attendance-item"><div class="persentase-kehadiran"><?= $attendanceSummary['percentage'] ?>%</div><div class="attendance-label">Kehadiran</div></div>
                <div class="attendance-item"><div class="attendance-number text-warning"><?= $attendanceSummary['sakit'] ?></div><div class="attendance-label">Sakit</div></div>
                <div class="attendance-item"><div class="attendance-number text-info"><?= $attendanceSummary['izin'] ?></div><div class="attendance-label">Izin</div></div>
                <div class="attendance-item"><div class="attendance-number text-danger"><?= $attendanceSummary['tanpa_keterangan'] ?></div><div class="attendance-label">Tanpa Keterangan</div></div>
              </div>
              <div class="alert alert-success">
                <i class="bi bi-calculator me-2"></i>
                <strong>Perhitungan:</strong> Persentase = (Hadir ÷ Total) × 100% = 
                (<?= $attendanceSummary['present'] ?> ÷ <?= $attendanceSummary['total'] ?>) × 100% = <?= $attendanceSummary['percentage'] ?>%
              </div>
              <?php else: ?>
              <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Belum ada data kehadiran untuk mahasiswa ini.</div>
              <?php endif; ?>
            </div>
            
            <div class="info-box mb-4">
              <h5><i class="bi bi-plus-circle me-2"></i>Tambah Data Kehadiran</h5>
              <form method="POST">
                <input type="hidden" name="simpan_kehadiran" value="1">
                <input type="hidden" name="nik" value="<?= htmlspecialchars($studentDetail['nik']) ?>">
                
                <div class="row mb-3">
                  <div class="col-md-6"><label class="form-label">Tanggal *</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                  <div class="col-md-6"><label class="form-label">Status Kehadiran *</label>
                    <select name="status" class="form-select" required>
                      <option value="hadir">Hadir</option>
                      <option value="sakit">Sakit</option>
                      <option value="izin">Izin</option>
                      <option value="tanpa_keterangan">Tanpa Keterangan</option>
                    </select>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Keterangan (Opsional)</label>
                  <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Sakit demam, Izin keluarga, dll."></textarea>
                  <small class="text-muted">Kosongkan jika tidak ada keterangan khusus</small>
                </div>
                
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  <strong>Catatan:</strong> Data kehadiran akan selalu ditambahkan sebagai entri baru.
                  Anda bisa menambahkan multiple data untuk tanggal yang sama.
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Data Kehadiran</button>
              </form>
            </div>
            
            <div class="info-box">
              <h5><i class="bi bi-clock-history me-2"></i>Riwayat Kehadiran (Semua Data)</h5>
              <?php if (!empty($attendanceHistory)): ?>
                <div class="table-responsive">
                  <table class="table table-hover kehadiran-table">
                    <thead class="table-light">
                      <tr><th>No</th><th>Tanggal</th><th>Status</th><th>Keterangan</th><th>Tanggal Input</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                      <?php $no = 1; foreach ($attendanceHistory as $attendance): 
                        $statusClass = 'status-' . $attendance['status'];
                        $statusText = ucfirst(str_replace('_', ' ', $attendance['status']));
                      ?>
                        <tr>
                          <td><?= $no++ ?></td>
                          <td><?= date('d/m/Y', strtotime($attendance['tanggal'])) ?></td>
                          <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                          <td><?= htmlspecialchars($attendance['keterangan'] ?? '-') ?></td>
                          <td><?= date('d/m/Y H:i', strtotime($attendance['created_at'])) ?></td>
                          <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus data kehadiran ini?')">
                              <input type="hidden" name="hapus_kehadiran" value="1">
                              <input type="hidden" name="id_hapus" value="<?= $attendance['id'] ?>">
                              <input type="hidden" name="nik_hapus" value="<?= $studentDetail['nik'] ?>">
                              <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <div class="mt-3 text-center">
                  <small class="text-muted"><i class="bi bi-info-circle"></i> Total <?= count($attendanceHistory) ?> data kehadiran ditemukan.</small>
                </div>
              <?php else: ?>
                <div class="text-center py-4">
                  <i class="bi bi-calendar-x display-4 text-muted"></i>
                  <h5 class="mt-3">Belum Ada Data Kehadiran</h5>
                  <p class="text-muted">Silakan tambah data kehadiran untuk mahasiswa ini</p>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
        <?php else: ?>
          <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Mahasiswa dengan NIM <strong><?= htmlspecialchars($nikSearch) ?></strong> tidak ditemukan.</div>
        <?php endif; ?>
      <?php else: ?>
        <!-- STATISTIK SISTEM -->
        <div class="row mb-4">
          <?php 
          $statItems = [
            ['number' => $dashboardStats['total_students'], 'label' => 'Total Mahasiswa', 'color' => '#0d6efd', 'text' => 'Terdaftar di sistem'],
            ['number' => $dashboardStats['total_warning_letters'], 'label' => 'Total SP', 'color' => '#dc3545', 'text' => 'Semua jenis SP'],
            ['number' => $dashboardStats['active_warning_letters'], 'label' => 'SP Aktif', 'color' => '#198754', 'text' => 'Masih dalam proses'],
            ['number' => $dashboardStats['today_warning_letters'], 'label' => 'SP Hari Ini', 'color' => '#ffc107', 'text' => 'Tanggal ' . date('d/m/Y')]
          ];
          foreach ($statItems as $item): ?>
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
                  <li class="list-group-item d-flex justify-content-between align-items-center"><span>Jurusan dengan SP terbanyak:</span><strong><?= htmlspecialchars($dashboardStats['jurusan_terbanyak']) ?> (<?= $dashboardStats['jurusan_terbanyak_jumlah'] ?> SP)</strong></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center"><span>Role Akun:</span><span class="badge bg-info"><?= htmlspecialchars($userRole) ?></span></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center"><span>Tanggal Hari Ini:</span><strong><?= date('d F Y') ?></strong></li>
                  <li class="list-group-item d-flex justify-content-between align-items-center"><span>Total Data dalam Sistem:</span><strong><?= $dashboardStats['total_students'] + $dashboardStats['total_warning_letters'] ?> data</strong></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header bg-warning text-dark"><h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru</h5></div>
              <div class="card-body">
                <?php if (empty($latestWarningLetters)): ?>
                  <div class="text-center py-3"><i class="bi bi-inbox display-6 text-muted"></i><p class="mt-2 text-muted">Belum ada data SP</p></div>
                <?php else: foreach ($latestWarningLetters as $warning): ?>
                  <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between"><h6 class="mb-1"><?= htmlspecialchars($warning['nama_mahasiswa'] ?? 'Mahasiswa') ?></h6><small class="text-muted"><?= date('d/m/Y', strtotime($warning['tanggal'])) ?></small></div>
                    <p class="mb-1"><?= htmlspecialchars(substr($warning['alasan'], 0, 60)) . (strlen($warning['alasan']) > 60 ? '...' : '') ?></p>
                    <small><span class="badge bg-primary">SP<?= $warning['jenis_sp'] ?></span><span class="badge <?= $warning['status'] == 'Aktif' ? 'bg-success' : 'bg-secondary' ?>"><?= $warning['status'] ?></span></small>
                  </div>
                <?php endforeach; endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
    
  <?php elseif ($action == 'rekap' && $pdfAction == 'template'): ?>
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
                  <a href="cetak_template.php?jenis=<?= $i ?>" class="btn btn-<?= $colors[$i] ?>" target="_blank"><i class="bi bi-eye me-2"></i>Pengaturan Surat</a>
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
                <select name="id" class="form-select mb-3" required>
                  <option value="">Pilih Data SP</option>
                  <?php foreach ($warningLetterData as $warning): ?><option value="<?= $warning['id'] ?>">SP<?= $warning['jenis_sp'] ?> - <?= $warning['nik'] ?> - <?= $warning['nama_mahasiswa'] ?></option><?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-printer me-2"></i>Generate & Cetak SP</button>
              </form>
            </div>
          </div>
        </div>
      </div>
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
              <div class="col-md-6"><label class="form-label">NIM Mahasiswa *</label><input type="text" name="nik" class="form-control" required placeholder="Contoh: 20231001"></div>
              <div class="col-md-6"><label class="form-label">Jenis SP *</label>
                <select name="jenis_sp" class="form-select" required>
                  <option value="">Pilih Jenis SP</option>
                  <option value="1">SP 1 (Peringatan)</option>
                  <option value="2">SP 2 (Peringatan Keras)</option>
                  <option value="3">SP 3 (Skorsing)</option>
                </select>
              </div>
            </div>        
            <div class="mb-3"><label class="form-label">Alasan Pelanggaran *</label><textarea name="alasan" class="form-control" rows="3" required placeholder="Jelaskan alasan penerbitan SP"></textarea></div>
            <div class="row mb-3">
              <div class="col-md-6"><label class="form-label">Tanggal Terbit *</label><input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
              <div class="col-md-6"><label class="form-label">Catatan / Tindak Lanjut</label><textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional (contoh: Perbaiki kehadiran, Konsultasi dengan dosen, dll.)"></textarea></div>
            </div>
            <div class="mb-3"><small class="text-muted">* Data mahasiswa akan otomatis dibuat jika belum ada dalam sistem. SP akan dikirim ke mahasiswa.</small></div>
            
            <div class="d-flex gap-3">
              <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-save me-2"></i>Simpan SP & Kirim ke Mahasiswa</button>
              <button type="button" class="btn btn-import-pdf" data-bs-toggle="modal" data-bs-target="#importPdfModal"><i class="bi bi-file-earmark-pdf me-2"></i>Import PDF</button>
            </div>
          </form>
        </div>
        
        <!-- MODAL IMPORT PDF -->
        <div class="modal fade import-modal" id="importPdfModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                  <h5 class="modal-title"><i class="bi bi-file-earmark-pdf me-2"></i>Import SP dari PDF</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="alert alert-info mb-4"><i class="bi bi-info-circle me-2"></i><strong>Fitur Import PDF:</strong> Upload file PDF yang sudah dibuat, data akan otomatis tersimpan dan dikirim ke mahasiswa.</div>
                  
                  <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">NIM Mahasiswa *</label><input type="text" name="nik_import" class="form-control" required placeholder="Contoh: 20231001"></div>
                    <div class="col-md-6"><label class="form-label">Jenis SP *</label>
                      <select name="jenis_sp_import" class="form-select" required>
                        <option value="">Pilih Jenis</option>
                        <option value="1">SP 1 (Peringatan)</option>
                        <option value="2">SP 2 (Peringatan Keras)</option>
                        <option value="3">SP 3 (Skorsing)</option>
                      </select>
                    </div>
                    <div class="col-md-12"><label class="form-label">Alasan Pelanggaran *</label><textarea name="alasan_import" class="form-control" rows="2" required placeholder="Jelaskan alasan SP"></textarea></div>
                    <div class="col-md-6"><label class="form-label">Tanggal Terbit *</label><input type="date" name="tanggal_import" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Catatan / Tindak Lanjut</label><textarea name="keterangan_import" class="form-control" rows="2" placeholder="Opsional (contoh: Perbaiki kehadiran, Konsultasi dengan dosen, dll.)"></textarea></div>
                    <div class="col-md-12">
                      <label class="form-label">File PDF *</label>
                      <div class="file-upload-area" onclick="document.getElementById('pdfFileInput').click()">
                        <i class="bi bi-cloud-upload"></i>
                        <h5>Klik untuk memilih file PDF</h5>
                        <p class="text-muted">Drag & drop atau klik untuk mengupload</p>
                        <input type="file" name="pdf_file" id="pdfFileInput" accept=".pdf" style="display: none;" onchange="showFileName(this)">
                        <div id="fileNameDisplay" class="mt-2" style="display: none;"><span class="badge bg-success">File terpilih: <span id="fileNameText"></span></span></div>
                      </div>
                      <small class="text-muted">Maksimal 5MB, format PDF</small>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" name="import_pdf_sp" class="btn btn-success"><i class="bi bi-upload me-2"></i>Import & Kirim ke Mahasiswa</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <div class="table-container mt-4">
          <?php if (empty($warningLetterData)): ?>
            <div class="alert alert-info text-center py-4"><i class="bi bi-info-circle me-2"></i>Belum ada data SP untuk dikelola.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr><th>No</th><th>NIM</th><th>Nama</th><th>Jenis SP</th><th>Tanggal</th><th>Status</th><th>Keterangan</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($warningLetterData as $index => $warning) { ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars($warning['nik']) ?></td>
                      <td><?= htmlspecialchars($warning['nama_mahasiswa'] ?? 'N/A') ?></td>
                      <td><span class="badge badge-sp<?= $warning['jenis_sp'] ?>">SP<?= $warning['jenis_sp'] ?></span></td>
                      <td><?= date('d/m/Y', strtotime($warning['tanggal'])) ?></td>
                      <td><span class="badge <?= $warning['status'] == 'Aktif' ? 'bg-success' : 'bg-secondary' ?>"><?= $warning['status'] ?></span></td>
                      <td><?= htmlspecialchars($warning['keterangan'] ?? '-') ?></td>
                      <td>
                        <div class="btn-group">
                          <?php if (!empty($warning['file_pdf'])): ?><a href="<?= $warning['file_pdf'] ?>" class="btn btn-sm btn-info" target="_blank" title="Lihat PDF"><i class="bi bi-file-pdf"></i></a><?php endif; ?>
                          <a href="cetak_pdf.php?id=<?= $warning['id'] ?>" class="btn btn-sm btn-danger" target="_blank" title="Cetak PDF"><i class="bi bi-printer"></i></a>
                          <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $warning['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                          <a href="?action=kelola&delete=<?= $warning['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus SP ini?')" title="Hapus"><i class="bi bi-trash"></i></a>
                        </div>
                      </td>
                    </tr>
                    <!-- MODAL EDIT -->
                    <div class="modal fade" id="editModal<?= $warning['id'] ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST">
                            <input type="hidden" name="id" value="<?= $warning['id'] ?>">
                            <input type="hidden" name="edit_sp" value="1">
                            <div class="modal-header"><h5 class="modal-title">Edit SP - <?= $warning['nik'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                              <div class="mb-3"><label class="form-label">Jenis SP</label>
                                <select name="jenis_sp" class="form-select" required>
                                  <?php for ($i = 1; $i <= 3; $i++) { ?><option value="<?= $i ?>" <?= $warning['jenis_sp'] == $i ? 'selected' : '' ?>>SP <?= $i ?></option><?php } ?>
                                </select>
                              </div>
                              <div class="mb-3"><label class="form-label">Alasan</label><textarea name="alasan" class="form-control" rows="3" required><?= $warning['alasan'] ?></textarea></div>
                              <div class="mb-3"><label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                  <option value="Aktif" <?= $warning['status'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                  <option value="Selesai" <?= $warning['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                  <option value="Dicabut" <?= $warning['status'] == 'Dicabut' ? 'selected' : '' ?>>Dicabut</option>
                                </select>
                              </div>
                              <div class="mb-3"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="2"><?= $warning['keterangan'] ?? '' ?></textarea></div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  <?php } ?>
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
function showFileName(input) {
  const fileNameDisplay = document.getElementById('fileNameDisplay');
  const fileNameText = document.getElementById('fileNameText');
  
  if (input.files.length > 0) {
    const file = input.files[0];
    const fileSize = (file.size / 1024 / 1024).toFixed(2);
    
    fileNameText.textContent = `${file.name} (${fileSize} MB)`;
    fileNameDisplay.style.display = 'block';
    
    if (file.size > 5 * 1024 * 1024) {
      alert('File terlalu besar! Maksimal 5MB.');
      input.value = '';
      fileNameDisplay.style.display = 'none';
    }
  }
}

setTimeout(() => document.querySelectorAll('.alert').forEach(a => new bootstrap.Alert(a).close()), 5000);
document.addEventListener('DOMContentLoaded', () => {
  const tanggalInput = document.querySelector('input[name="tanggal"]');
  if (tanggalInput && !tanggalInput.value) tanggalInput.value = new Date().toISOString().split('T')[0];
});
</script>
</body>
</html>