<?php
session_start();
require 'koneksi.php';

// ========== FUNGSI VALIDASI ==========

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk memvalidasi sesi staff
 */
function validateStaffSession()
{
    try {
        if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
            throw new Exception('Akses ditolak: Bukan staff');
        }
        return true;
    } catch (Exception $e) {
        error_log("Session validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan username dari sesi
 */
function getSessionUsername()
{
    try {
        return $_SESSION['username'] ?? '';
    } catch (Exception $e) {
        error_log("Session username error: " . $e->getMessage());
        return '';
    }
}

// ========== FUNGSI OPERASI CRUD ==========

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menambahkan surat peringatan baru
 */
function addWarningLetter($connection, $postData, $username)
{
    try {
        // PENERAPAN ERROR HANDLING: Validasi data input
        $requiredFields = ['nim', 'nama_mahasiswa', 'prodi', 'semester', 'jenis_sp', 'alasan'];
        
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                throw new Exception("Field $field tidak boleh kosong");
            }
        }
        
        // PENERAPAN CLEAN CODE: Sanitasi input
        $sanitizedData = [
            'nim' => mysqli_real_escape_string($connection, $postData['nim']),
            'nama_mahasiswa' => mysqli_real_escape_string($connection, $postData['nama_mahasiswa']),
            'prodi' => mysqli_real_escape_string($connection, $postData['prodi']),
            'semester' => mysqli_real_escape_string($connection, $postData['semester']),
            'jenis_sp' => mysqli_real_escape_string($connection, $postData['jenis_sp']),
            'alasan' => mysqli_real_escape_string($connection, $postData['alasan'])
        ];
        
        $currentDate = date('Y-m-d');
        $sanitizedUsername = mysqli_real_escape_string($connection, $username);
        
        // PENERAPAN ERROR HANDLING: Query database dengan try-catch
        $query = "INSERT INTO surat_peringatan 
                  (nim, nama_mahasiswa, prodi, semester, jenis_sp, alasan, tanggal_terbit, status_sp, created_by) 
                  VALUES ('{$sanitizedData['nim']}', 
                          '{$sanitizedData['nama_mahasiswa']}', 
                          '{$sanitizedData['prodi']}', 
                          '{$sanitizedData['semester']}', 
                          '{$sanitizedData['jenis_sp']}', 
                          '{$sanitizedData['alasan']}', 
                          '$currentDate', 
                          'Aktif', 
                          '$sanitizedUsername')";
        
        if (mysqli_query($connection, $query)) {
            return [
                'success' => true,
                'message' => 'SP berhasil ditambahkan!'
            ];
        } else {
            throw new Exception('Database error: ' . mysqli_error($connection));
        }
        
    } catch (Exception $e) {
        error_log("Add warning letter error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal menambahkan SP: ' . $e->getMessage()
        ];
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mengupdate surat peringatan
 */
function updateWarningLetter($connection, $postData)
{
    try {
        // PENERAPAN ERROR HANDLING: Validasi ID dan data
        if (empty($postData['id'])) {
            throw new Exception('ID SP tidak valid');
        }
        
        $requiredFields = ['nim', 'nama_mahasiswa', 'prodi', 'jenis_sp', 'alasan'];
        
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                throw new Exception("Field $field tidak boleh kosong");
            }
        }
        
        // PENERAPAN CLEAN CODE: Sanitasi input
        $sanitizedData = [
            'id' => intval($postData['id']),
            'nim' => mysqli_real_escape_string($connection, $postData['nim']),
            'nama_mahasiswa' => mysqli_real_escape_string($connection, $postData['nama_mahasiswa']),
            'prodi' => mysqli_real_escape_string($connection, $postData['prodi']),
            'jenis_sp' => mysqli_real_escape_string($connection, $postData['jenis_sp']),
            'alasan' => mysqli_real_escape_string($connection, $postData['alasan'])
        ];
        
        // PENERAPAN ERROR HANDLING: Query database
        $query = "UPDATE surat_peringatan SET 
                  nim = '{$sanitizedData['nim']}', 
                  nama_mahasiswa = '{$sanitizedData['nama_mahasiswa']}', 
                  prodi = '{$sanitizedData['prodi']}', 
                  jenis_sp = '{$sanitizedData['jenis_sp']}', 
                  alasan = '{$sanitizedData['alasan']}' 
                  WHERE id = {$sanitizedData['id']}";
        
        if (mysqli_query($connection, $query)) {
            return [
                'success' => true,
                'message' => 'SP berhasil diupdate!'
            ];
        } else {
            throw new Exception('Database error: ' . mysqli_error($connection));
        }
        
    } catch (Exception $e) {
        error_log("Update warning letter error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal mengupdate SP: ' . $e->getMessage()
        ];
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menghapus surat peringatan
 */
function deleteWarningLetter($connection, $letterId)
{
    try {
        // PENERAPAN ERROR HANDLING: Validasi ID
        if (empty($letterId) || !is_numeric($letterId)) {
            throw new Exception('ID SP tidak valid');
        }
        
        $sanitizedId = intval($letterId);
        
        // PENERAPAN ERROR HANDLING: Query delete dengan try-catch
        $query = "DELETE FROM surat_peringatan WHERE id = $sanitizedId";
        
        if (mysqli_query($connection, $query)) {
            return [
                'success' => true,
                'message' => 'SP berhasil dihapus!'
            ];
        } else {
            throw new Exception('Database error: ' . mysqli_error($connection));
        }
        
    } catch (Exception $e) {
        error_log("Delete warning letter error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal menghapus SP: ' . $e->getMessage()
        ];
    }
}

// ========== FUNGSI QUERY DATA ==========

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan daftar surat peringatan
 */
function getWarningLetters($connection, $search = '')
{
    try {
        // PENERAPAN CLEAN CODE: Build query dengan aman
        $query = "SELECT * FROM surat_peringatan";
        
        if (!empty($search)) {
            $sanitizedSearch = mysqli_real_escape_string($connection, $search);
            $query .= " WHERE nim LIKE '%$sanitizedSearch%' 
                         OR nama_mahasiswa LIKE '%$sanitizedSearch%'
                         OR prodi LIKE '%$sanitizedSearch%'";
        }
        
        $query .= " ORDER BY tanggal_terbit DESC";
        
        // PENERAPAN ERROR HANDLING: Eksekusi query
        $result = mysqli_query($connection, $query);
        
        if (!$result) {
            throw new Exception('Query gagal: ' . mysqli_error($connection));
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Get warning letters error: " . $e->getMessage());
        return false;
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan detail surat peringatan
 */
function getWarningLetterDetail($connection, $letterId)
{
    try {
        // PENERAPAN ERROR HANDLING: Validasi ID
        if (empty($letterId) || !is_numeric($letterId)) {
            throw new Exception('ID SP tidak valid');
        }
        
        $sanitizedId = intval($letterId);
        
        $query = "SELECT * FROM surat_peringatan WHERE id = $sanitizedId";
        $result = mysqli_query($connection, $query);
        
        if (!$result) {
            throw new Exception('Query gagal: ' . mysqli_error($connection));
        }
        
        if (mysqli_num_rows($result) === 0) {
            throw new Exception('Data tidak ditemukan');
        }
        
        return mysqli_fetch_assoc($result);
    } catch (Exception $e) {
        error_log("Get warning letter detail error: " . $e->getMessage());
        return null;
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan statistik
 */
function getWarningLetterStatistics($connection)
{
    try {
        $statistics = [
            'total' => 0,
            'active' => 0
        ];
        
        // Query total
        $totalQuery = "SELECT COUNT(*) as total FROM surat_peringatan";
        $totalResult = mysqli_query($connection, $totalQuery);
        
        if ($totalResult) {
            $totalData = mysqli_fetch_assoc($totalResult);
            $statistics['total'] = $totalData['total'] ?? 0;
        }
        
        // Query aktif
        $activeQuery = "SELECT COUNT(*) as aktif FROM surat_peringatan WHERE status_sp = 'Aktif'";
        $activeResult = mysqli_query($connection, $activeQuery);
        
        if ($activeResult) {
            $activeData = mysqli_fetch_assoc($activeResult);
            $statistics['active'] = $activeData['aktif'] ?? 0;
        }
        
        return $statistics;
    } catch (Exception $e) {
        error_log("Get statistics error: " . $e->getMessage());
        return ['total' => 0, 'active' => 0];
    }
}

// ========== FUNGSI UTAMA ==========

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk memproses semua request POST
 */
function processPostRequests($connection, $username)
{
    $response = [
        'success' => null,
        'message' => null
    ];
    
    try {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // 1. TAMBAH SP
            if (isset($_POST['tambah_sp'])) {
                $addResult = addWarningLetter($connection, $_POST, $username);
                $response = $addResult;
            }
            
            // 2. EDIT SP
            if (isset($_POST['edit_sp'])) {
                $updateResult = updateWarningLetter($connection, $_POST);
                $response = $updateResult;
            }
        }
        
        // 3. HAPUS SP (GET request)
        if (isset($_GET['hapus'])) {
            $deleteResult = deleteWarningLetter($connection, $_GET['hapus']);
            $response = $deleteResult;
        }
        
        return $response;
    } catch (Exception $e) {
        error_log("Process post requests error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan sistem'
        ];
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan parameter halaman
 */
function getPageParameters()
{
    try {
        return [
            'page' => $_GET['page'] ?? 'tambah',
            'action' => $_GET['action'] ?? 'daftar',
            'search' => $_GET['search'] ?? '',
            'id' => $_GET['id'] ?? null
        ];
    } catch (Exception $e) {
        error_log("Get page parameters error: " . $e->getMessage());
        return [
            'page' => 'tambah',
            'action' => 'daftar',
            'search' => '',
            'id' => null
        ];
    }
}

// ========== PROSES UTAMA ==========

// PENERAPAN ERROR HANDLING: Validasi sesi
try {
    if (!validateStaffSession()) {
        header("Location: login.php");
        exit;
    }
    
    $username = getSessionUsername();
    
    if (empty($username)) {
        throw new Exception('Username tidak ditemukan di sesi');
    }
    
    // PENERAPAN ERROR HANDLING: Proses request
    $processResult = processPostRequests($koneksi, $username);
    
    if ($processResult['success'] !== null) {
        if ($processResult['success']) {
            $success = $processResult['message'];
        } else {
            $error = $processResult['message'];
        }
    }
    
    // PENERAPAN CLEAN CODE: Ambil parameter
    $params = getPageParameters();
    $currentPage = $params['page'];
    $currentAction = $params['action'];
    $searchTerm = $params['search'];
    $letterId = $params['id'];
    
    // PENERAPAN ERROR HANDLING: Ambil data
    $warningLetters = getWarningLetters($koneksi, $searchTerm);
    $statistics = getWarningLetterStatistics($koneksi);
    
    // PENERAPAN ERROR HANDLING: Ambil detail jika diperlukan
    $letterDetail = null;
    if ($letterId && ($currentPage == 'edit' || $currentPage == 'detail')) {
        $letterDetail = getWarningLetterDetail($koneksi, $letterId);
    }
    
} catch (Exception $e) {
    error_log("Main process error: " . $e->getMessage());
    // Tampilkan error user-friendly
    $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem SP Staff - Polibatam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            color: #212529;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 0px;
            border: 1px solid #dee2e6;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .table {
            border: 1px solid #dee2e6;
        }
        
        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        .table td, .table th {
            border: 1px solid #dee2e6;
            padding: 12px;
        }
        
        .btn {
            border-radius: 0px;
            border: 1px solid #dee2e6;
            background: #ffffff;
            color: #212529;
            padding: 8px 16px;
        }
        
        .btn:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.875rem;
        }
        
        .badge {
            border-radius: 0px;
            padding: 4px 8px;
            font-weight: 500;
        }
        
        .form-control {
            border-radius: 0px;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
        }
        
        .form-control:focus {
            border-color: #adb5bd;
            box-shadow: none;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .alert {
            border-radius: 0px;
            border: 1px solid #dee2e6;
        }
        
        .card {
            border: 1px solid #dee2e6;
            border-radius: 0px;
            background: #ffffff;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #212529;
            font-weight: 600;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- NOTIFIKASI -->
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- KONTEN UTAMA -->
        <div class="mt-4">
            
            <?php if($currentPage == 'tambah'): ?>
                <!-- HALAMAN TAMBAH SP -->
                <?php displayAddPage($username); ?>
            
            <?php elseif($currentPage == 'daftar'): ?>
                <!-- HALAMAN DAFTAR SP -->
                <?php displayListPage($warningLetters, $searchTerm, $statistics); ?>
            
            <?php elseif($currentPage == 'edit' && $letterId): ?>
                <!-- HALAMAN EDIT SP -->
                <?php displayEditPage($letterDetail); ?>
            
            <?php elseif($currentPage == 'detail' && $letterId): ?>
                <!-- HALAMAN DETAIL SP -->
                <?php displayDetailPage($letterDetail); ?>
            
            <?php elseif($currentPage == 'cari'): ?>
                <!-- HALAMAN CARI SP -->
                <?php displaySearchPage($warningLetters, $searchTerm); ?>
            
            <?php else: ?>
                <div class="alert alert-warning">
                    Halaman tidak ditemukan.
                </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php

// ========== FUNGSI TAMPILAN ==========

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menampilkan halaman tambah
 */
function displayAddPage($username)
{
    ?>
    <div class="back-btn">
        <a href="dashboard.php" class="btn">← Kembali ke Dashboard</a>
    </div>
    
    <h3>Tambah Surat Peringatan Baru</h3>
    
    <div class="form-container">
        <form method="POST">
            <input type="hidden" name="tambah_sp" value="1">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">NIM Mahasiswa *</label>
                    <input type="text" name="nim" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Mahasiswa *</label>
                    <input type="text" name="nama_mahasiswa" class="form-control" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Program Studi *</label>
                    <select name="prodi" class="form-control" required>
                        <option value="">Pilih Prodi</option>
                        <option value="Teknik Informatika">Teknik Informatika</option>
                        <option value="Sistem Informasi">Sistem Informasi</option>
                        <option value="Teknik Elektro">Teknik Elektro</option>
                        <option value="Teknik Mesin">Teknik Mesin</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Semester *</label>
                    <select name="semester" class="form-control" required>
                        <option value="">Pilih Semester</option>
                        <?php for($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>">Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Jenis SP *</label>
                    <select name="jenis_sp" class="form-control" required>
                        <option value="">Pilih Jenis</option>
                        <option value="SP1">SP 1 (Peringatan Ringan)</option>
                        <option value="SP2">SP 2 (Peringatan Berat)</option>
                        <option value="SP3">SP 3 (Skorsing)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status *</label>
                    <select name="status_sp" class="form-control" required>
                        <option value="Aktif" selected>Aktif</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Alasan Pemberian SP *</label>
                <textarea name="alasan" class="form-control" rows="4" required></textarea>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn">Simpan SP</button>
                <a href="dashboard.php" class="btn">Batal</a>
            </div>
        </form>
    </div>
    <?php
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menampilkan halaman daftar
 */
function displayListPage($warningLetters, $searchTerm, $statistics)
{
    ?>
    <div class="back-btn">
        <a href="dashboard.php" class="btn">← Kembali ke Dashboard</a>
    </div>
    
    <h3>Daftar Surat Peringatan</h3>
    
    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6>Total SP</h6>
                    <h3><?= htmlspecialchars($statistics['total']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6>SP Aktif</h6>
                    <h3><?= htmlspecialchars($statistics['active']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search Form -->
    <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="page" value="daftar">
        <div class="col-md-10">
            <input type="text" name="search" class="form-control" 
                   placeholder="Cari NIM, Nama, atau Prodi..." 
                   value="<?= htmlspecialchars($searchTerm) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn w-100">Cari</button>
        </div>
    </form>
    
    <!-- Tabel SP -->
    <?php if($warningLetters && mysqli_num_rows($warningLetters) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Prodi</th>
                        <th>Jenis SP</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; ?>
                    <?php while($row = mysqli_fetch_assoc($warningLetters)): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><strong><?= htmlspecialchars($row['nim']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                            <td><?= htmlspecialchars($row['prodi']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($row['jenis_sp']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_terbit'])) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($row['status_sp']) ?></span></td>
                            <td>
                                <a href="?page=detail&id=<?= $row['id'] ?>" class="btn btn-sm">Lihat</a>
                                <a href="?page=edit&id=<?= $row['id'] ?>" class="btn btn-sm">Edit</a>
                                <a href="?page=daftar&hapus=<?= $row['id'] ?>" 
                                   class="btn btn-sm" 
                                   onclick="return confirm('Hapus SP ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <?php if(!empty($searchTerm)): ?>
                Tidak ditemukan hasil pencarian untuk "<?= htmlspecialchars($searchTerm) ?>"
            <?php else: ?>
                Tidak ada data surat peringatan.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menampilkan halaman edit
 */
function displayEditPage($letterDetail)
{
    if (!$letterDetail) {
        echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
        return;
    }
    ?>
    <div class="back-btn">
        <a href="?page=daftar" class="btn">← Kembali ke Daftar</a>
    </div>
    
    <h3>Edit Surat Peringatan</h3>
    
    <div class="form-container">
        <form method="POST">
            <input type="hidden" name="edit_sp" value="1">
            <input type="hidden" name="id" value="<?= htmlspecialchars($letterDetail['id']) ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">NIM Mahasiswa *</label>
                    <input type="text" name="nim" class="form-control" 
                           value="<?= htmlspecialchars($letterDetail['nim']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Mahasiswa *</label>
                    <input type="text" name="nama_mahasiswa" class="form-control" 
                           value="<?= htmlspecialchars($letterDetail['nama_mahasiswa']) ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Program Studi *</label>
                    <select name="prodi" class="form-control" required>
                        <option value="Teknik Informatika" <?= $letterDetail['prodi'] == 'Teknik Informatika' ? 'selected' : '' ?>>Teknik Informatika</option>
                        <option value="Sistem Informasi" <?= $letterDetail['prodi'] == 'Sistem Informasi' ? 'selected' : '' ?>>Sistem Informasi</option>
                        <option value="Teknik Elektro" <?= $letterDetail['prodi'] == 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jenis SP *</label>
                    <select name="jenis_sp" class="form-control" required>
                        <option value="SP1" <?= $letterDetail['jenis_sp'] == 'SP1' ? 'selected' : '' ?>>SP 1</option>
                        <option value="SP2" <?= $letterDetail['jenis_sp'] == 'SP2' ? 'selected' : '' ?>>SP 2</option>
                        <option value="SP3" <?= $letterDetail['jenis_sp'] == 'SP3' ? 'selected' : '' ?>>SP 3</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Alasan Pemberian SP *</label>
                <textarea name="alasan" class="form-control" rows="4" required><?= htmlspecialchars($letterDetail['alasan']) ?></textarea>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn">Update SP</button>
                <a href="?page=daftar" class="btn">Batal</a>
            </div>
        </form>
    </div>
    <?php
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menampilkan halaman detail
 */
function displayDetailPage($letterDetail)
{
    if (!$letterDetail) {
        echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
        return;
    }
    ?>
    <div class="back-btn">
        <a href="?page=daftar" class="btn">← Kembali ke Daftar</a>
    </div>
    
    <h3>Detail Surat Peringatan</h3>
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th width="40%">NIM</th>
                            <td><?= htmlspecialchars($letterDetail['nim']) ?></td>
                        </tr>
                        <tr>
                            <th>Nama Mahasiswa</th>
                            <td><?= htmlspecialchars($letterDetail['nama_mahasiswa']) ?></td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td><?= htmlspecialchars($letterDetail['prodi']) ?></td>
                        </tr>
                        <tr>
                            <th>Semester</th>
                            <td>Semester <?= htmlspecialchars($letterDetail['semester']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th width="40%">Jenis SP</th>
                            <td><span class="badge"><?= htmlspecialchars($letterDetail['jenis_sp']) ?></span></td>
                        </tr>
                        <tr>
                            <th>Tanggal Terbit</th>
                            <td><?= date('d F Y', strtotime($letterDetail['tanggal_terbit'])) ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge"><?= htmlspecialchars($letterDetail['status_sp']) ?></span></td>
                        </tr>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td><?= htmlspecialchars($letterDetail['created_by']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Alasan Pemberian SP</h5>
                <div class="border p-3">
                    <?= nl2br(htmlspecialchars($letterDetail['alasan'])) ?>
                </div>
            </div>
            
            <div class="mt-4 text-end">
                <a href="?page=edit&id=<?= $letterDetail['id'] ?>" class="btn">Edit</a>
            </div>
        </div>
    </div>
    <?php
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menampilkan halaman pencarian
 */
function displaySearchPage($warningLetters, $searchTerm)
{
    ?>
    <div class="back-btn">
        <a href="dashboard.php" class="btn">← Kembali ke Dashboard</a>
    </div>
    
    <h3>Cari Surat Peringatan</h3>
    
    <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="page" value="cari">
        <div class="col-md-10">
            <input type="text" name="search" class="form-control" 
                   placeholder="Masukkan NIM, Nama, atau Prodi..." 
                   value="<?= htmlspecialchars($searchTerm) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn w-100">Cari</button>
        </div>
    </form>
    
    <?php if(!empty($searchTerm)): ?>
        <?php if($warningLetters && mysqli_num_rows($warningLetters) > 0): ?>
            <h5>Hasil Pencarian</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Prodi</th>
                        <th>Jenis SP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($warningLetters, 0); ?>
                    <?php while($row = mysqli_fetch_assoc($warningLetters)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nim']) ?></td>
                            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                            <td><?= htmlspecialchars($row['prodi']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($row['jenis_sp']) ?></span></td>
                            <td>
                                <a href="?page=detail&id=<?= $row['id'] ?>" class="btn btn-sm">Lihat</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">
                Tidak ditemukan hasil pencarian untuk "<?= htmlspecialchars($searchTerm) ?>"
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php
}