<?php
session_start();
if (!isset($_SESSION['nik']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$id = $_GET['id'] ?? '';
if ($id) {
    mysqli_query($conn, "DELETE FROM user WHERE id='$id'");
    $_SESSION['success'] = "User berhasil dihapus!";
}

header("Location: daftar.php");
exit;
