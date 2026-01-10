<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// ========== FUNGSI UTAMA ==========

// 1. TAMBAH SP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_sp'])) {
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama_mahasiswa = mysqli_real_escape_string($koneksi, $_POST['nama_mahasiswa']);
    $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);
    $semester = mysqli_real_escape_string($koneksi, $_POST['semester']);
    $jenis_sp = mysqli_real_escape_string($koneksi, $_POST['jenis_sp']);
    $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan']);
    $tanggal_terbit = date('Y-m-d');
    $status_sp = 'Aktif';
    
    $query = "INSERT INTO surat_peringatan 
              (nim, nama_mahasiswa, prodi, semester, jenis_sp, alasan, tanggal_terbit, status_sp, created_by) 
              VALUES ('$nim', '$nama_mahasiswa', '$prodi', '$semester', '$jenis_sp', '$alasan', '$tanggal_terbit', '$status_sp', '$username')";
    
    if (mysqli_query($koneksi, $query)) {
        $success = "SP berhasil ditambahkan!";
    } else {
        $error = "Gagal: " . mysqli_error($koneksi);
    }
}

// 2. EDIT SP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_sp'])) {
    $id = $_POST['id'];
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $nama_mahasiswa = mysqli_real_escape_string($koneksi, $_POST['nama_mahasiswa']);
    $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);
    $jenis_sp = mysqli_real_escape_string($koneksi, $_POST['jenis_sp']);
    $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan']);
    
    $query = "UPDATE surat_peringatan SET 
              nim = '$nim', 
              nama_mahasiswa = '$nama_mahasiswa', 
              prodi = '$prodi', 
              jenis_sp = '$jenis_sp', 
              alasan = '$alasan' 
              WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        $success = "SP berhasil diupdate!";
    } else {
        $error = "Gagal: " . mysqli_error($koneksi);
    }
}

// 3. HAPUS SP
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM surat_peringatan WHERE id = $id";
    if (mysqli_query($koneksi, $query)) {
        $success = "SP berhasil dihapus!";
    }
}

// ========== AMBIL DATA ==========
$page = isset($_GET['page']) ? $_GET['page'] : 'tambah'; // GANTI INI
$action = isset($_GET['action']) ? $_GET['action'] : 'daftar';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk daftar SP
if (!empty($search)) {
    $query = "SELECT * FROM surat_peringatan 
              WHERE nim LIKE '%$search%' 
                 OR nama_mahasiswa LIKE '%$search%'
                 OR prodi LIKE '%$search%'
              ORDER BY tanggal_terbit DESC";
} else {
    $query = "SELECT * FROM surat_peringatan ORDER BY tanggal_terbit DESC";
}

$result = mysqli_query($koneksi, $query);

// Ambil data untuk edit/detail
$sp_data = null;
if (isset($_GET['id']) && ($action == 'edit' || $action == 'detail')) {
    $id = $_GET['id'];
    $query_detail = "SELECT * FROM surat_peringatan WHERE id = $id";
    $result_detail = mysqli_query($koneksi, $query_detail);
    $sp_data = mysqli_fetch_assoc($result_detail);
}

// Hitung statistik
$total_query = "SELECT COUNT(*) as total FROM surat_peringatan";
$total_result = mysqli_query($koneksi, $total_query);
$total = mysqli_fetch_assoc($total_result)['total'];

$aktif_query = "SELECT COUNT(*) as aktif FROM surat_peringatan WHERE status_sp = 'Aktif'";
$aktif_result = mysqli_query($koneksi, $aktif_query);
$aktif = mysqli_fetch_assoc($aktif_result)['aktif'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem SP Staff - Polibatam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            color: #212529;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 0px;
            border: 1px solid #dee2e6;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .table {
            border: 1px solid #dee2e6;
        }
        
        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        .table td, .table th {
            border: 1px solid #dee2e6;
            padding: 12px;
        }
        
        .btn {
            border-radius: 0px;
            border: 1px solid #dee2e6;
            background: #ffffff;
            color: #212529;
            padding: 8px 16px;
        }
        
        .btn:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.875rem;
        }
        
        .badge {
            border-radius: 0px;
            padding: 4px 8px;
            font-weight: 500;
        }
        
        .form-control {
            border-radius: 0px;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
        }
        
        .form-control:focus {
            border-color: #adb5bd;
            box-shadow: none;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .alert {
            border-radius: 0px;
            border: 1px solid #dee2e6;
        }
        
        .card {
            border: 1px solid #dee2e6;
            border-radius: 0px;
            background: #ffffff;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #212529;
            font-weight: 600;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- NOTIFIKASI -->
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <!-- KONTEN UTAMA -->
        <div class="mt-4">
            
            <?php if($page == 'tambah'): ?>
                <!-- HALAMAN TAMBAH SP -->
                <div class="back-btn">
                    <a href="dashboard.php" class="btn">← Kembali ke Dashboard</a>
                </div>
                
                <h3>Tambah Surat Peringatan Baru</h3>
                
                <div class="form-container">
                    <form method="POST">
                        <input type="hidden" name="tambah_sp" value="1">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">NIM Mahasiswa *</label>
                                <input type="text" name="nim" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Mahasiswa *</label>
                                <input type="text" name="nama_mahasiswa" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Program Studi *</label>
                                <select name="prodi" class="form-control" required>
                                    <option value="">Pilih Prodi</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Sistem Informasi">Sistem Informasi</option>
                                    <option value="Teknik Elektro">Teknik Elektro</option>
                                    <option value="Teknik Mesin">Teknik Mesin</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Semester *</label>
                                <select name="semester" class="form-control" required>
                                    <option value="">Pilih Semester</option>
                                    <?php for($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?= $i ?>">Semester <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Jenis SP *</label>
                                <select name="jenis_sp" class="form-control" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="SP1">SP 1 (Peringatan Ringan)</option>
                                    <option value="SP2">SP 2 (Peringatan Berat)</option>
                                    <option value="SP3">SP 3 (Skorsing)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select name="status_sp" class="form-control" required>
                                    <option value="Aktif" selected>Aktif</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alasan Pemberian SP *</label>
                            <textarea name="alasan" class="form-control" rows="4" required></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn">Simpan SP</button>
                            <a href="dashboard.php" class="btn">Batal</a>
                        </div>
                    </form>
                </div>
            
            <?php elseif($page == 'daftar'): ?>
                <!-- HALAMAN DAFTAR SP -->
                <div class="back-btn">
                    <a href="dashboard.php" class="btn">← Kembali ke Dashboard</a>
                </div>
                
                <h3>Daftar Surat Peringatan</h3>
                
                <!-- Search Form -->
                <form method="GET" class="row g-3 mb-4">
                    <input type="hidden" name="page" value="daftar">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari NIM, Nama, atau Prodi..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn w-100">Cari</button>
                    </div>
                </form>
                
                <!-- Tabel SP -->
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Prodi</th>
                                    <th>Jenis SP</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($row['nim']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                                        <td><?= htmlspecialchars($row['prodi']) ?></td>
                                        <td><span class="badge"><?= $row['jenis_sp'] ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal_terbit'])) ?></td>
                                        <td><span class="badge"><?= $row['status_sp'] ?></span></td>
                                        <td>
                                            <a href="?page=detail&id=<?= $row['id'] ?>" class="btn btn-sm">Lihat</a>
                                            <a href="?page=edit&id=<?= $row['id'] ?>" class="btn btn-sm">Edit</a>
                                            <a href="?page=daftar&hapus=<?= $row['id'] ?>" 
                                               class="btn btn-sm" 
                                               onclick="return confirm('Hapus SP ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Tidak ada data surat peringatan.
                    </div>
                <?php endif; ?>
            
            <?php elseif($page == 'edit' && isset($_GET['id'])): ?>
                <!-- HALAMAN EDIT SP -->
                <?php
                $id = $_GET['id'];
                $query = "SELECT * FROM surat_peringatan WHERE id = $id";
                $result = mysqli_query($koneksi, $query);
                $sp_data = mysqli_fetch_assoc($result);
                
                if (!$sp_data) {
                    echo "<p>Data tidak ditemukan</p>";
                } else {
                ?>
                
                <div class="back-btn">
                    <a href="?page=daftar" class="btn">← Kembali ke Daftar</a>
                </div>
                
                <h3>Edit Surat Peringatan</h3>
                
                <div class="form-container">
                    <form method="POST">
                        <input type="hidden" name="edit_sp" value="1">
                        <input type="hidden" name="id" value="<?= $sp_data['id'] ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">NIM Mahasiswa *</label>
                                <input type="text" name="nim" class="form-control" 
                                       value="<?= htmlspecialchars($sp_data['nim']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Mahasiswa *</label>
                                <input type="text" name="nama_mahasiswa" class="form-control" 
                                       value="<?= htmlspecialchars($sp_data['nama_mahasiswa']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Program Studi *</label>
                                <select name="prodi" class="form-control" required>
                                    <option value="Teknik Informatika" <?= $sp_data['prodi'] == 'Teknik Informatika' ? 'selected' : '' ?>>Teknik Informatika</option>
                                    <option value="Sistem Informasi" <?= $sp_data['prodi'] == 'Sistem Informasi' ? 'selected' : '' ?>>Sistem Informasi</option>
                                    <option value="Teknik Elektro" <?= $sp_data['prodi'] == 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis SP *</label>
                                <select name="jenis_sp" class="form-control" required>
                                    <option value="SP1" <?= $sp_data['jenis_sp'] == 'SP1' ? 'selected' : '' ?>>SP 1</option>
                                    <option value="SP2" <?= $sp_data['jenis_sp'] == 'SP2' ? 'selected' : '' ?>>SP 2</option>
                                    <option value="SP3" <?= $sp_data['jenis_sp'] == 'SP3' ? 'selected' : '' ?>>SP 3</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alasan Pemberian SP *</label>
                            <textarea name="alasan" class="form-control" rows="4" required><?= htmlspecialchars($sp_data['alasan']) ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn">Update SP</button>
                            <a href="?page=daftar" class="btn">Batal</a>
                        </div>
                    </form>
                </div>
                
                <?php } ?>
            
            <?php elseif($page == 'detail' && isset($_GET['id'])): ?>
                <!-- HALAMAN DETAIL SP -->
                <?php
                $id = $_GET['id'];
                $query = "SELECT * FROM surat_peringatan WHERE id = $id";
                $result = mysqli_query($koneksi, $query);
                $sp_data = mysqli_fetch_assoc($result);
                
                if (!$sp_data) {
                    echo "<p>Data tidak ditemukan</p>";
                } else {
                ?>
                
                <div class="back-btn">
                    <a href="?page=daftar" class="btn">← Kembali ke Daftar</a>
                </div>
                
                <h3>Detail Surat Peringatan</h3>
                
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <th width="40%">NIM</th>
                                        <td><?= htmlspecialchars($sp_data['nim']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nama Mahasiswa</th>
                                        <td><?= htmlspecialchars($sp_data['nama_mahasiswa']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Program Studi</th>
                                        <td><?= htmlspecialchars($sp_data['prodi']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Semester</th>
                                        <td>Semester <?= $sp_data['semester'] ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <th width="40%">Jenis SP</th>
                                        <td><span class="badge"><?= $sp_data['jenis_sp'] ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Terbit</th>
                                        <td><?= date('d F Y', strtotime($sp_data['tanggal_terbit'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><span class="badge"><?= $sp_data['status_sp'] ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat Oleh</th>
                                        <td><?= htmlspecialchars($sp_data['created_by']) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Alasan Pemberian SP</h5>
                            <div class="border p-3">
                                <?= nl2br(htmlspecialchars($sp_data['alasan'])) ?>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <a href="?page=edit&id=<?= $sp_data['id'] ?>" class="btn">Edit</a>
                        </div>
                    </div>
                </div>
                
                <?php } ?>
            
            <?php elseif($page == 'cari'): ?>
                <!-- HALAMAN CARI SP -->
                <div class="back-btn">
                    <a href="dashboard.php" class="btn">← Kembali ke Dashboard</a>
                </div>
                
                <h3>Cari Surat Peringatan</h3>
                
                <form method="GET" class="row g-3 mb-4">
                    <input type="hidden" name="page" value="cari">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Masukkan NIM, Nama, atau Prodi..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn w-100">Cari</button>
                    </div>
                </form>
                
                <?php if(isset($_GET['search']) && !empty($search)): ?>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <h5>Hasil Pencarian</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Prodi</th>
                                    <th>Jenis SP</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($result, 0); ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nim']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                                        <td><?= htmlspecialchars($row['prodi']) ?></td>
                                        <td><span class="badge"><?= $row['jenis_sp'] ?></span></td>
                                        <td>
                                            <a href="?page=detail&id=<?= $row['id'] ?>" class="btn btn-sm">Lihat</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Tidak ditemukan hasil pencarian untuk "<?= htmlspecialchars($search) ?>"
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            
            <?php else: ?>
                <div class="alert alert-warning">
                    Halaman tidak ditemukan.
                </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>