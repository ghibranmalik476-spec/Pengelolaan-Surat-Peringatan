<?php
// ---- DATA ABSENSI ----
$total_alpha = 3;      // Alpha diverifikasi (warna hijau)
$total_sesi  = 16;     // Total Sesi Rencana

// ---- HITUNG PERSENTASE ----
$persentase = ($total_alpha / $total_sesi) * 100;

// ---- TENTUKAN SP ----
if ($persentase >= 40) {
    $sp = "SP 3";
} elseif ($persentase >= 20) {
    $sp = "SP 2";
} elseif ($persentase >= 5) {
    $sp = "SP 1";
} else {
    $sp = "-";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Keterangan SP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #ffc400;
            padding: 12px;
            font-size: 20px;
            font-weight: bold;
            color: #003087;
        }
        .box {
            border: 2px solid #ffc400;
            padding: 20px;
        }
        .left-line {
            border-left: 4px solid #000;
            padding-left: 20px;
            margin-top: 10px;
        }
        ul {
            font-size: 18px;
        }
        .label {
            font-size: 20px;
            margin-top: 20px;
        }
        .value {
            font-size: 22px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="header">KETERANGAN SP</div>

<div class="box">

    <div class="left-line">
        <b>Persentase ketidakhadiran:</b>
        <ul>
            <li>5% &nbsp; Mendapatkan SP 1</li>
            <li>20% Mendapatkan SP 2</li>
            <li>40% Mendapatkan SP 3</li>
        </ul>
    </div>

    <br><br>

    <div class="label">SP : <span class="value"><?= $sp ?></span></div>

    <br><br>

    <div class="left-line">
        <b>Rumus Hitung:</b>
        <ul>
            <li>SP = Total Alpha / Total Sesi Rencana * 100%</li>
        </ul>
        <i>*Hanya menghitung absensi yang sudah diverifikasi/warna hijau.</i>
    </div>

</div>

</body>
</html>
