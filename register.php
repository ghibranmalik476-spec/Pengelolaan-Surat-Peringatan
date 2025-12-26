<?php
session_start();
require 'koneksi.php';

$error = $success = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $ulang    = $_POST['ulang'];
    $role     = $_POST['role'] ?? 'mahasiswa';

    if ($password != $ulang) {
        $error = "Password tidak sama!";
    } else {
        $q = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
        if (mysqli_num_rows($q) > 0) {
            $error = "Username sudah dipakai!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_query($koneksi, "
                INSERT INTO user (username, password, role)
                VALUES ('$username', '$hash', '$role')
            ");

            if ($insert) {
                $success = "Pendaftaran berhasil. Silakan login.";
                echo "<script>
                    setTimeout(function(){
                    window.location.href = 'login.php';
                    }, 1000);
                    </script>";
            } else {
                $error = "Gagal daftar!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <style>
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
<body class="bg-light">

<div class="container vh-100 d-flex justify-content-center align-items-center">
<div class="overlay"></div>
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">

        <h4 class="text-center mb-3 fw-bold">REGISTER AKUN</h4>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php } ?>

        <?php if ($success != "") { ?>
            <div class="alert alert-success text-center"><?= $success ?></div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Ulangi Password</label>
                <input type="password" name="ulang" class="form-control" required>
            </div>

            <button type="submit" name="register" class="btn btn-success w-100">
                DAFTAR
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php">Sudah punya akun? Login</a>
        </div>

    </div>

</div>

</body>
</html>
