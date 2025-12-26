<?php
// koneksi database
$host = "localhost";
$username = "root";  // default XAMPP
$password = "";      // default XAMPP kosong
$database = "project2"; // sesuaikan dengan database Anda

// Buat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($koneksi, "utf8");
?>