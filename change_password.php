<?php
session_start();
if (!isset($_SESSION['nik'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "Semua field harus diisi.";
        header("Location: daftar.php");
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Password baru dan konfirmasi tidak cocok.";
        header("Location: daftar.php");
        exit;
    }

    // Get current password from database
    $stmt = $conn->prepare("SELECT password FROM user WHERE nik = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($old_password, $user['password'])) {
        $_SESSION['error'] = "Password lama salah.";
        header("Location: daftar.php");
        exit;
    }

    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE nik = ?");
    $stmt->bind_param("ss", $hashed_password, $nik);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Password berhasil diubah.";
    } else {
        $_SESSION['error'] = "Gagal mengubah password.";
    }

    header("Location: daftar.php");
    exit;
}
?>
