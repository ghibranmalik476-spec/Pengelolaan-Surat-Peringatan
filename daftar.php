<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';
$nik = $_SESSION['nik'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body { background-color: #f8f9fa; }
.content { padding: 20px; transition: margin-left 0.3s ease; }
</style>
</head>
<body>

<nav class="navbar navbar-light bg-white shadow-sm px-3">
    <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
        <i class="bi bi-list" style="font-size: 1.5rem;"></i>
    </button>
    <h5 class="ms-3 mt-1">Dashboard Admin</h5>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" style="width:270px;" data-bs-backdrop="false">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <img src="logo.png" alt="Logo" class="mb-3" width="140">
        <ul class="nav flex-column">

            <li class="nav-item">
                <a class="nav-link" href="dashboard_staff.php">Dashboard</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">Beranda</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#submenuSPMahasiswa">
                    Data
                </a>
                <div class="collapse" id="submenuSPMahasiswa">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item"><a class="nav-link active" href="daftar.php">Kelola Data </a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Rekap / Daftar SP</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Kelola SP</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#submenuSP">
                    Pencarian SP
                </a>
                <div class="collapse" id="submenuSP">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item"><a class="nav-link" href="#">Cari SP</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Cari Data Mahasiswa</a></li>
                    </ul>
                </div>
            </li>

        </ul>
        <div class="mt-auto p-2 border-top">
            <div class="dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="45" class="me-2">
                    <div class="me-auto"><strong><?= htmlspecialchars($nik) ?></strong><br><small>Admin</small></div>
                    <i class="bi bi-chevron-down ms-2"></i>
                </div>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#peraturanModal">Peraturan</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <h3><i class="fas fa-users me-2"></i> Daftar User</h3>
    <hr>
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
        <i class="fas fa-user-plus me-2"></i> Tambah User
    </button>

    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr><th>NIK</th><th>SEBAGAI</th><th>NAMA</th><th>AKSI</th></tr>
        </thead>
        <tbody>
        <?php
        $q = mysqli_query($koneksi, "SELECT id, nik, role, username FROM user ORDER BY role ASC, nik ASC");
        while($u = mysqli_fetch_assoc($q)):
        ?>
        <tr>
            <td><?= htmlspecialchars($u['nik']); ?></td>
            <td><span class="text-<?php echo $u['role'] == 'admin' ? 'danger' : ($u['role'] == 'staff' ? 'primary' : 'success'); ?> fw-bold"><?= htmlspecialchars($u['role']); ?></span></td>
            <td><?= htmlspecialchars($u['username']); ?></td>
            <td>
                <button class="btn btn-success btn-sm editBtn" 
                        data-bs-toggle="modal" data-bs-target="#editUserModal"
                        data-id="<?= $u['id']; ?>" 
                        data-nik="<?= $u['nik']; ?>"
                        data-role="<?= $u['role']; ?>"
                        data-username="<?= $u['username']; ?>">
                        <i class="fas fa-edit"></i>
                </button>
                <a href="hapus.php?id=<?= $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus user ini?')">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="tambahUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="tambah.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <label>Role</label>
          <select name="role" id="role" class="form-control mb-2" required>
            <option value="">Pilih Role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff Akademik</option>
            <option value="mahasiswa">Mahasiswa</option>
          </select>

          <label>NIK</label>
          <input type="text" name="nik" class="form-control mb-2" required>

          <label>Username</label>
          <input type="text" name="username" class="form-control mb-2" required>

          <!-- KHUSUS MAHASISWA -->
          <div id="mahasiswaFields" style="display:none;">
            <label>Nama Mahasiswa</label>
            <input type="text" name="nama" class="form-control mb-2">

            <label>Jurusan</label>
            <input type="text" name="jurusan" class="form-control mb-2">
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form action="edit.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit-id" name="id">
        <label>Role</label>
        <select id="edit-role" name="role" class="form-control mb-2" required>
          <option value="">Pilih Role</option>
          <option value="admin">Admin</option>
          <option value="staff">Staff Akademik</option>
          <option value="mahasiswa">Mahasiswa</option>
        </select>
        <label>NIK</label>
        <input type="number" id="edit-nik" name="nik" class="form-control mb-2" required>
        <label>Username</label>
        <input type="text" id="edit-username" name="username" class="form-control mb-2" required>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div></div>
</div>

<!-- Modal Peraturan -->
<div class="modal fade" id="peraturanModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Peraturan</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <h6>Peraturan Penggunaan Sistem</h6>
      <ol>
        <li>Semua pengguna harus menggunakan akun pribadi dan tidak boleh membagikan kredensial login.</li>
        <li>Data yang dimasukkan harus akurat dan sesuai dengan kebijakan universitas.</li>
        <li>Dilarang mengakses atau mengubah data tanpa izin.</li>
        <li>Pelanggaran terhadap peraturan ini dapat mengakibatkan penangguhan atau penghentian akses.</li>
        <li>Untuk pertanyaan lebih lanjut, hubungi administrator sistem.</li>
      </ol>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
    </div>
  </div></div>
</div>

<!-- Modal Change Password -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form action="change_password.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label>Password Lama</label>
        <input type="password" name="old_password" class="form-control mb-2" required>
        <label>Password Baru</label>
        <input type="password" name="new_password" class="form-control mb-2" required>
        <label>Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" class="form-control mb-2" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('role').addEventListener('change', function () {
    const mhs = document.getElementById('mahasiswaFields');
    if (this.value === 'mahasiswa') {
        mhs.style.display = 'block';
        mhs.querySelectorAll('input').forEach(i => i.required = true);
    } else {
        mhs.style.display = 'none';
        mhs.querySelectorAll('input').forEach(i => i.required = false);
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.editBtn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-nik').value = this.dataset.nik;
            document.getElementById('edit-role').value = this.dataset.role;
            document.getElementById('edit-username').value = this.dataset.username;
        });
    });

    const sidebar = document.getElementById('sidebar');
    const content = document.querySelector('.content');
    sidebar.addEventListener('shown.bs.offcanvas', () => content.style.marginLeft = "270px");
    sidebar.addEventListener('hidden.bs.offcanvas', () => content.style.marginLeft = "0");
});
</script>
</body>
</html>
