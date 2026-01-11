<?php
$total_alpha = 3;      
$total_sesi  = 16;     

$persentase = ($total_alpha / $total_sesi) * 100;

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
    <title>Absensi + Keterangan SP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        th {
            background: #f8f9fa;
            color: #8897a5;
            font-weight: bold;
        }
        .keterangan {
            background: #f8f9fa;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #e0e0e0;
            margin-bottom: 30px;
        }
        .green-box {
            width: 15px;
            height: 15px;
            background: green;
            display: inline-block;
        }
        .red-box {
            width: 15px;
            height: 15px;
            background: red;
            display: inline-block;
        }

        .header-sp {
            background-color: #ffc400;
            padding: 12px;
            font-size: 20px;
            font-weight: bold;
            color: #003087;
            margin-top: 25px;
        }
        .box-sp {
            border: 2px solid #ffc400;
            padding: 20px;
        }
        .left-line {
            border-left: 4px solid #000;
            padding-left: 20px;
            margin-top: 10px;
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

<!-- ========================= -->
<!-- BAGIAN HEADER ABSENSI     -->
<!-- ========================= -->

<table>
    <tr>
        <th rowspan="2">KODE<br>MK</th>
        <th rowspan="2">MATAKULIAH</th>
        <th rowspan="2">JENIS</th>
        <th colspan="16">MINGGU KE</th>
    </tr>
    <tr>
        <?php 
        for ($i = 1; $i <= 16; $i++) {
            echo "<th>$i</th>";
        }
        ?>
    </tr>
</table>

<div class="keterangan">
    <b>KET :</b> &nbsp;&nbsp;
    H = Hadir,&nbsp;&nbsp;
    A = Alpha,&nbsp;&nbsp;
    - = Tidak Ada Perkuliahan,&nbsp;&nbsp;

    <span class="green-box"></span> = Sudah Diverifikasi,&nbsp;&nbsp;
    <span class="red-box"></span> = Belum Diverifikasi
</div>


<!-- ========================= -->
<!-- BAGIAN KETERANGAN SP      -->
<!-- ========================= -->

<div class="header-sp">KETERANGAN SP</div>

<div class="box-sp">

    <div class="left-line">
        <b>Persentase ketidakhadiran:</b>
        <ul>
            <li>5% &nbsp; Mendapatkan SP 1</li>
            <li>20% Mendapatkan SP 2</li>
            <li>40% Mendapatkan SP 3</li>
        </ul>
    </div>

    <br>

    <div class="label">
        SP : <span class="value"><?= $sp ?></span>
        &nbsp;&nbsp;&nbsp;
        (<?= number_format($persentase, 2) ?>%)
    </div>

    <br>

    <div class="left-line">
        <b>Rumus Hitung:</b>
        <ul>
            <li>SP = Total Alpha / Total Sesi Rencana × 100%</li>
        </ul>
        <i>*Hanya menghitung absensi yang sudah diverifikasi/warna hijau.</i>
    </div>

</div>

</body>
</html>
