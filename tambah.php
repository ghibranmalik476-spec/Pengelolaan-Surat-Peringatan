<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $role     = $_POST['role'];
    $nik      = trim($_POST['nik']);
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $jurusan  = trim($_POST['jurusan'] ?? '');

    if (!$nik || !$role) {
        $_SESSION['error'] = "NIK dan Role wajib diisi!";
        header("Location: daftar.php");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {

        // Cek NIK sudah ada di user
        $cek_user = mysqli_query($koneksi, "SELECT nik FROM user WHERE nik='$nik'");
        if (mysqli_num_rows($cek_user) > 0) {
            throw new Exception("NIK sudah terdaftar!");
        }

        $password = password_hash($nik, PASSWORD_DEFAULT);

        // =====================
        // MAHASISWA
        // =====================
        if ($role === 'mahasiswa') {

            if (!$nama || !$jurusan) {
                throw new Exception("Nama dan jurusan wajib diisi!");
            }

            // insert mahasiswa
            mysqli_query($koneksi, "
                INSERT INTO mahasiswa (nik, nama, jurusan)
                VALUES ('$nik','$nama','$jurusan')
            ");

            // insert user mahasiswa
            mysqli_query($koneksi, "
                INSERT INTO user (nik, username, password, role)
                VALUES ('$nik','$nama','$password','mahasiswa')
            ");

        }
        // =====================
        // ADMIN / STAFF
        // =====================
        else {

            if (!$username) {
                throw new Exception("Username wajib diisi!");
            }

            mysqli_query($koneksi, "
                INSERT INTO user (nik, username, password, role)
                VALUES ('$nik','$username','$password','$role')
            ");
        }

        mysqli_commit($koneksi);

        $_SESSION['success'] =
            "User berhasil ditambahkan.
            Username: " . ($role === 'mahasiswa' ? $nama : $username) . "
            | Password awal: $nik";

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: daftar.php");
exit;
