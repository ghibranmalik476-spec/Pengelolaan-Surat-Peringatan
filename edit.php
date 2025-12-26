<?php
session_start();
if (!isset($_SESSION['nik']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$id = $_POST['id'] ?? '';
$nik = $_POST['nik'] ?? '';
$username = $_POST['username'] ?? '';
$role = $_POST['role'] ?? '';

// Password tetap sama seperti sebelumnya (tidak berubah)
if ($id && $nik && $username && $role) {

    $query = "UPDATE user 
              SET nik='$nik', username='$username', role='$role'
              WHERE id='$id'";

    mysqli_query($koneksi, $query);

    $_SESSION['success'] = "User berhasil diperbarui!";
}

header("Location: daftar.php");
exit;
