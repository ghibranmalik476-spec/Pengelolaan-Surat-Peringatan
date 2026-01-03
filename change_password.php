<?php
session_start();
include 'koneksi.php';

$nik = $_SESSION['nik'];

$old = $_POST['old'];
$new = $_POST['new'];
$confirm = $_POST['confirm'];

$q = mysqli_query($koneksi, "SELECT password FROM user WHERE nik='$nik'");
$data = mysqli_fetch_assoc($q);

if (!password_verify($old, $data['password'])) {
    die("Password lama salah");
}

if ($new != $confirm) {
    die("Konfirmasi password tidak cocok");
}

$hash = password_hash($new, PASSWORD_DEFAULT);
mysqli_query($koneksi, "UPDATE user SET password='$hash' WHERE nik='$nik'");

header("Location: login.php");