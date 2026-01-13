<?php
session_start();

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk memvalidasi sesi pengguna mahasiswa
 */
function validateStudentSession()
{
    try {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
            throw new Exception('Akses ditolak: Bukan mahasiswa');
        }
        return true;
    } catch (Exception $e) {
        error_log("Session validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan parameter ID dengan validasi
 */
function getValidatedIdParameter()
{
    try {
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            throw new Exception('ID tidak ditemukan');
        }
        
        // Validasi format ID (hanya angka)
        if (!preg_match('/^\d+$/', $id)) {
            throw new Exception('Format ID tidak valid');
        }
        
        return intval($id);
    } catch (Exception $e) {
        error_log("ID parameter error: " . $e->getMessage());
        return 0;
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk mendapatkan data surat peringatan
 */
function getWarningLetterData($connection, $studentNik, $letterId)
{
    try {
        // PENERAPAN ERROR HANDLING: Sanitize input
        $sanitizedNik = mysqli_real_escape_string($connection, $studentNik);
        $sanitizedId = mysqli_real_escape_string($connection, $letterId);
        
        $query = "SELECT sp.*, m.nama, m.jurusan
                  FROM surat_peringatan sp
                  JOIN mahasiswa m ON sp.nik = m.nik
                  WHERE sp.id = '$sanitizedId' 
                  AND sp.nik = '$sanitizedNik'";
        
        $result = mysqli_query($connection, $query);
        
        if (!$result) {
            throw new Exception('Query gagal: ' . mysqli_error($connection));
        }
        
        if (mysqli_num_rows($result) === 0) {
            throw new Exception('Data tidak ditemukan untuk NIK ' . $studentNik . ' dan ID ' . $letterId);
        }
        
        return mysqli_fetch_assoc($result);
    } catch (Exception $e) {
        error_log("Database query error: " . $e->getMessage());
        return null;
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk memproses permintaan dan menampilkan halaman
 */
function processWarningLetterRequest()
{
    try {
        // PENERAPAN ERROR HANDLING: Validasi sesi
        if (!validateStudentSession()) {
            header("Location: login.php");
            exit;
        }
        
        // PENERAPAN CLEAN CODE: Nama variabel yang deskriptif
        $studentNik = $_SESSION['nik'] ?? '';
        $letterId = getValidatedIdParameter();
        
        if (empty($studentNik) || $letterId === 0) {
            throw new Exception('Parameter tidak valid');
        }
        
        // PENERAPAN ERROR HANDLING: Koneksi database dengan try-catch
        include 'koneksi.php';
        
        if (!isset($koneksi) || !is_object($koneksi)) {
            throw new Exception('Koneksi database gagal');
        }
        
        // PENERAPAN ERROR HANDLING: Ambil data dengan validasi
        $warningLetterData = getWarningLetterData($koneksi, $studentNik, $letterId);
        
        if ($warningLetterData === null) {
            throw new Exception('Data surat peringatan tidak ditemukan');
        }
        
        // PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI: Tampilkan halaman
        displayWarningLetterPage($warningLetterData);
        
    } catch (Exception $e) {
        // PENERAPAN ERROR HANDLING: Tangani error dengan baik
        error_log("Process error: " . $e->getMessage());
        displayErrorPage($e->getMessage());
    }
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menampilkan halaman surat peringatan
 */
function displayWarningLetterPage($letterData)
{
    // PENERAPAN CLEAN CODE: Escape output untuk keamanan
    $studentName = htmlspecialchars($letterData['nama'] ?? '');
    $studentNik = htmlspecialchars($letterData['nik'] ?? '');
    $studentDepartment = htmlspecialchars($letterData['jurusan'] ?? '');
    $letterType = htmlspecialchars($letterData['jenis_sp'] ?? '');
    $letterDate = htmlspecialchars($letterData['tanggal'] ?? '');
    $letterReason = nl2br(htmlspecialchars($letterData['alasan'] ?? ''));
    
    // PENERAPAN CLEAN CODE: Struktur HTML yang terorganisir
    $htmlContent = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
    <title>Surat Peringatan</title>
    <style>
    body { font-family: Arial; }
    h2 { text-align:center; }
    </style>
    </head>
    <body onload="window.print()">
    
    <h2>SURAT PERINGATAN</h2>
    
    <p>Nama: {$studentName}</p>
    <p>NIK: {$studentNik}</p>
    <p>Jurusan: {$studentDepartment}</p>
    <hr>
    
    <p><strong>Jenis SP:</strong> {$letterType}</p>
    <p><strong>Tanggal:</strong> {$letterDate}</p>
    <p><strong>Alasan:</strong><br>{$letterReason}</p>
    
    </body>
    </html>
HTML;
    
    echo $htmlContent;
}

/**
 * PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
 * Fungsi untuk menampilkan halaman error
 */
function displayErrorPage($errorMessage = '')
{
    // PENERAPAN CLEAN CODE: Pesan error yang user-friendly
    $safeErrorMessage = htmlspecialchars($errorMessage);
    $defaultMessage = 'Terjadi kesalahan saat memuat surat peringatan.';
    $displayMessage = !empty($safeErrorMessage) ? $safeErrorMessage : $defaultMessage;
    
    $htmlContent = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
    <title>Error - Surat Peringatan</title>
    <style>
    body { 
        font-family: Arial; 
        margin: 50px;
        text-align: center;
    }
    .error-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ff6b6b;
        background-color: #ffe6e6;
        border-radius: 5px;
    }
    .error-title {
        color: #d63031;
        margin-bottom: 15px;
    }
    .error-message {
        color: #636e72;
        margin-bottom: 20px;
    }
    .back-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #0984e3;
        color: white;
        text-decoration: none;
        border-radius: 4px;
    }
    .back-button:hover {
        background-color: #0770c4;
    }
    </style>
    </head>
    <body>
    
    <div class="error-container">
        <h2 class="error-title">⚠️ Akses Ditolak</h2>
        <p class="error-message">{$displayMessage}</p>
        <a href="dashboard_mahasiswa.php" class="back-button">Kembali ke Dashboard</a>
    </div>
    
    </body>
    </html>
HTML;
    
    echo $htmlContent;
}

// PENERAPAN ERROR HANDLING: Jalankan proses utama dengan try-catch
try {
    processWarningLetterRequest();
} catch (Exception $e) {
    // Fallback jika ada error yang tidak tertangkap
    error_log("Uncaught exception: " . $e->getMessage());
    
    if (!headers_sent()) {
        header("HTTP/1.1 500 Internal Server Error");
    }
    
    displayErrorPage('Terjadi kesalahan sistem. Silakan coba lagi nanti.');
}