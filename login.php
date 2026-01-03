<?php
session_start();
require 'koneksi.php';
$error = "";

if (isset($_POST['login'])) {
    $nik      = $_POST['nik'];
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM user WHERE nik='$nik' LIMIT 1");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        if (password_verify($password, $data['password'])) {

            $_SESSION['nik'] = $data['nik'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = $data['role'];

            if ($data['role'] == "staff") {
                header("Location: dashboard_staff.php"); exit;
                
            } else if ($data['role'] == "mahasiswa") {
                header("Location: dashboard_mhs.php"); exit;
                
            }
            else if ($data['role'] == "admin") {
                header("Location: dashboard_admin.php"); exit;
            }

        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    body {
      background: url('https://learning-if.polibatam.ac.id/pluginfile.php/1/theme_moove/sliderimage1/1756270195/DJI_0066.JPG%20%282%29.jpg') 
                  no-repeat center center fixed;
      background-size: cover;
    }

    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.4);
    }
  </style>
</head>
<body>

<div class="container vh-100 d-flex justify-content-center align-items-center">

    <div class="card shadow p-4" style="max-width: 380px; width: 100%;">

        <h4 class="text-center mb-3 fw-bold">
            SELAMAT DATANG <br>
            SISTEM SURAT PERINGATAN
        </h4>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">NIK/NIM</label>
                <input type="text" class="form-control" name="nik" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100">
                MASUK
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="register.php">Belum punya akun? Daftar</a>
        </div>

    </div>

</div>

</body>
</html>
