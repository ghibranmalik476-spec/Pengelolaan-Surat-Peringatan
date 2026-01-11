<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$id = $_GET['id'] ?? '';

if ($id) {

    $q = mysqli_query($koneksi, "SELECT nik, role FROM user WHERE id='$id'");
    $user = mysqli_fetch_assoc($q);

    if ($user) {
        mysqli_begin_transaction($koneksi);

        try {
            mysqli_query($koneksi, "DELETE FROM user WHERE id='$id'");

            if ($user['role'] === 'mahasiswa' && !empty($user['nik'])) {
                mysqli_query($koneksi, "DELETE FROM mahasiswa WHERE nik='{$user['nik']}'");
            }

            mysqli_commit($koneksi);
            $_SESSION['success'] = "User dan data mahasiswa berhasil dihapus!";

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['error'] = "Gagal menghapus data!";
        }

    } else {
        $_SESSION['error'] = "User tidak ditemukan!";
    }
}

header("Location: daftar.php");
exit;
