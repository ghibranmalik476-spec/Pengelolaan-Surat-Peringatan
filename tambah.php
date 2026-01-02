<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: daftar.php");
    exit;
}

$role     = $_POST['role'] ?? '';
$nik      = trim($_POST['nik'] ?? '');
$username = trim($_POST['username'] ?? '');
$nama     = trim($_POST['nama'] ?? '');
$jurusan  = trim($_POST['jurusan'] ?? '');

if (!$role || !$nik || !$username) {
    $_SESSION['error'] = "Data wajib belum lengkap";
    header("Location: daftar.php");
    exit;
}

// password awal = nik
$password = password_hash($nik, PASSWORD_DEFAULT);

mysqli_begin_transaction($koneksi);

try {

    // =============================
    // CEK DUPLIKAT NIK / USERNAME
    // =============================
    $cek = $koneksi->prepare(
        "SELECT id FROM user WHERE nik = ? OR username = ?"
    );
    $cek->bind_param("ss", $nik, $username);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        throw new Exception("NIK atau Username sudah terdaftar");
    }

    // =============================
    // ROLE MAHASISWA
    // =============================
    if ($role === 'mahasiswa') {

        if (!$nama || !$jurusan) {
            throw new Exception("Nama dan Jurusan wajib diisi untuk mahasiswa");
        }

        // insert mahasiswa
        $stmt = $koneksi->prepare(
            "INSERT INTO mahasiswa (nik, nama, jurusan)
             VALUES (?,?,?)"
        );
        $stmt->bind_param("sss", $nik, $nama, $jurusan);
        $stmt->execute();
    }

    // =============================
    // INSERT USER (SEMUA ROLE)
    // =============================
    $stmt = $koneksi->prepare(
        "INSERT INTO user (nik, username, password, role)
         VALUES (?,?,?,?)"
    );
    $stmt->bind_param("ssss", $nik, $username, $password, $role);
    $stmt->execute();

    mysqli_commit($koneksi);

    $_SESSION['success'] = "User berhasil ditambahkan";

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['error'] = $e->getMessage();
}

header("Location: daftar.php");
exit;
