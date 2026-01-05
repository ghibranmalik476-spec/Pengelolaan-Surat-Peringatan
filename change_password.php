<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['nik'])) {
    echo "<script>alert('Session habis, silakan login ulang');window.location='login.php';</script>";
    exit;
}

$nik = $_SESSION['nik'];

$old = $_POST['old_password'];
$new = $_POST['new_password'];
$confirm = $_POST['confirm_password'];

$q = mysqli_query($koneksi, "SELECT password FROM user WHERE nik='$nik'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<script>alert('User tidak ditemukan');history.back();</script>";
    exit;
}

if (!password_verify($old, $data['password'])) {
    echo "<script>alert('Password lama salah');history.back();</script>";
    exit;
}

if ($new !== $confirm) {
    echo "<script>alert('Konfirmasi password tidak cocok');history.back();</script>";
    exit;
}

$hash = password_hash($new, PASSWORD_DEFAULT);
mysqli_query($koneksi, "UPDATE user SET password='$hash' WHERE nik='$nik'");

echo "<script>
alert('Password berhasil diubah, silakan login ulang');
window.location='login.php';
</script>";
