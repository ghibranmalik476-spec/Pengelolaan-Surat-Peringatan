<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$nik = $_SESSION['nik'];
$id  = $_GET['id'] ?? '';

$q = mysqli_query($koneksi, "
    SELECT sp.*, m.nama, m.jurusan
    FROM surat_peringatan sp
    JOIN mahasiswa m ON sp.nik = m.nik
    WHERE sp.id='$id' AND sp.nik='$nik'
");

$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("Akses ditolak");
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Surat Peringatan</title>
<style>
body { font-family: Arial; }
h2 { text-align:center; }
</style>
</head>
<body onload="window.print()">

<h2>SURAT PERINGATAN</h2>

<p>Nama: <?= $data['nama'] ?></p>
<p>NIK: <?= $data['nik'] ?></p>
<p>Jurusan: <?= $data['jurusan'] ?></p>
<hr>

<p><strong>Jenis SP:</strong> <?= $data['jenis_sp'] ?></p>
<p><strong>Tanggal:</strong> <?= $data['tanggal'] ?></p>
<p><strong>Alasan:</strong><br><?= nl2br($data['alasan']) ?></p>

</body>
</html>
