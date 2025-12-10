<?php
session_start();
if (!isset($_SESSION['nik']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = trim($_POST['nik']);
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);

    if ($nik && $username && $role) {
        $nik_safe = mysqli_real_escape_string($conn, $nik);
        $username_safe = mysqli_real_escape_string($conn, $username);
        $role_safe = mysqli_real_escape_string($conn, $role);

        // Password sama dengan NIK, di-hash
        $password_hash = password_hash($nik_safe, PASSWORD_DEFAULT);

        // Cek NIK sudah ada
        $cek = mysqli_query($conn, "SELECT * FROM user WHERE nik='$nik_safe'");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['error'] = "NIK sudah terdaftar!";
        } else {
            $insert = mysqli_query($conn, "INSERT INTO user (nik, username, password, role) VALUES ('$nik_safe','$username_safe','$password_hash','$role_safe')");
            if ($insert) {
                $_SESSION['success'] = "User berhasil ditambahkan!";
            } else {
                $_SESSION['error'] = "Gagal menambahkan user: " . mysqli_error($conn);
            }
        }
    } else {
        $_SESSION['error'] = "Form belum lengkap!";
    }
}

header("Location: daftar.php");
exit;
?>
