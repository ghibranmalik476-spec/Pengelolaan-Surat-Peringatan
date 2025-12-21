<?php
session_start();
require 'koneksi.php';

// Cek login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit;
}

$jenis = $_GET['jenis'] ?? '1';
$download = isset($_GET['download']) ? true : false;

// Jika download, set header untuk PDF
if ($download) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="SP'.$jenis.'_Template.pdf"');
}

// Data contoh untuk template
$data_contoh = [
    'nomor_surat' => '001/SP'.$jenis.'/AK/'.date('Y'),
    'tanggal' => date('d F Y'),
    'nim' => '20231001',
    'nama' => 'BUDI SANTOSO',
    'jurusan' => 'TEKNIK INFORMATIKA',
    'semester' => 'IV (Empat)',
    'alasan' => 'Tidak hadir dalam perkuliahan lebih dari 3 (tiga) kali berturut-turut tanpa keterangan yang jelas.',
    'poin_pelanggaran' => 'Pasal 5 ayat 2 Peraturan Akademik',
    'tenggat_waktu' => date('d F Y', strtotime('+7 days')),
    'ttd_nama' => 'Dr. SUTARJO, M.Kom.',
    'ttd_jabatan' => 'Ketua Jurusan Teknik Informatika'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template SP<?= $jenis ?></title>
    <style>
        @page {
            margin: 2cm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
        }
        
        .container {
            width: 100%;
            max-width: 21cm;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .kop-surat {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .judul {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 25px 0;
            text-decoration: underline;
        }
        
        .nomor-surat {
            text-align: center;
            margin: 20px 0;
        }
        
        .content {
            text-align: justify;
        }
        
        .identitas {
            margin: 20px 0;
        }
        
        .identitas table {
            width: 100%;
        }
        
        .identitas td {
            vertical-align: top;
            padding: 2px 0;
        }
        
        .identitas td:first-child {
            width: 150px;
        }
        
        .paragraf {
            margin: 15px 0;
            text-indent: 50px;
        }
        
        .footer {
            margin-top: 50px;
        }
        
        .ttd {
            text-align: right;
            margin-top: 50px;
        }
        
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 80px;
        }
        
        .ttd-jabatan {
            font-size: 11pt;
        }
        
        .perihal {
            margin: 20px 0;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .underline {
            text-decoration: underline;
        }
        
        .center {
            text-align: center;
        }
        
        .right {
            text-align: right;
        }
        
        /* Untuk print */
        @media print {
            body {
                font-size: 11pt;
            }
            
            .no-print {
                display: none;
            }
        }
        
        /* Tombol untuk web view */
        .action-buttons {
            margin: 20px 0;
            text-align: center;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        
        .btn {
            padding: 10px 20px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        
        .btn-print {
            background: #007bff;
            color: white;
        }
        
        .btn-download {
            background: #28a745;
            color: white;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$download): ?>
        <div class="action-buttons no-print">
            <a href="cetak_template.php?jenis=<?= $jenis ?>&download=1" class="btn btn-download">
                📥 Download PDF
            </a>
            <a href="javascript:window.print()" class="btn btn-print">
                🖨️ Cetak
            </a>
            <a href="index.php?action=rekap&pdf_action=template" class="btn btn-back">
                ↩ Kembali
            </a>
        </div>
        <?php endif; ?>
        
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <div class="header">
                <h3 style="margin-bottom: 5px;">POLITEKNIK NEGERI</h3>
                <h2 style="margin-top: 0; margin-bottom: 5px;">UNIVERSITAS CONTOH</h2>
                <p style="margin: 0;">Jalan Pendidikan No. 123, Kota Contoh - 12345</p>
                <p style="margin: 0;">Telp: (021) 12345678 | Email: info@poltekcontoh.ac.id</p>
                <p style="margin: 0;">Website: www.poltekcontoh.ac.id</p>
            </div>
        </div>
        
        <!-- NOMOR SURAT -->
        <div class="nomor-surat">
            <p>Nomor : <?= $data_contoh['nomor_surat'] ?></p>
            <p>Perihal : <span class="bold">SURAT PERINGATAN <?= $jenis ?></span></p>
        </div>
        
        <!-- ISI SURAT -->
        <div class="content">
            <div class="paragraf">
                Yang bertanda tangan di bawah ini:
            </div>
            
            <div class="identitas">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>: <?= $data_contoh['ttd_nama'] ?></td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>: <?= $data_contoh['ttd_jabatan'] ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="paragraf">
                Dengan ini menerangkan bahwa:
            </div>
            
            <div class="identitas">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>: <?= $data_contoh['nama'] ?></td>
                    </tr>
                    <tr>
                        <td>NIM</td>
                        <td>: <?= $data_contoh['nim'] ?></td>
                    </tr>
                    <tr>
                        <td>Jurusan</td>
                        <td>: <?= $data_contoh['jurusan'] ?></td>
                    </tr>
                    <tr>
                        <td>Semester</td>
                        <td>: <?= $data_contoh['semester'] ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="paragraf">
                Telah melakukan pelanggaran terhadap Peraturan Akademik Politeknik dengan rincian sebagai berikut:
            </div>
            
            <div class="paragraf">
                <strong>Jenis Pelanggaran:</strong> <?= $data_contoh['alasan'] ?>
            </div>
            
            <div class="paragraf">
                <strong>Dasar:</strong> <?= $data_contoh['poin_pelanggaran'] ?>
            </div>
            
            <div class="paragraf">
                Sehubungan dengan pelanggaran tersebut, maka kami mengeluarkan <strong>SURAT PERINGATAN <?= $jenis ?></strong> dengan ketentuan sebagai berikut:
            </div>
            
            <ol style="margin: 15px 0 15px 40px;">
                <li>Mahasiswa tersebut diharapkan untuk segera memperbaiki perilaku dan mematuhi peraturan yang berlaku.</li>
                <li>Surat peringatan ini merupakan bentuk peringatan resmi dari institusi.</li>
                <li>Apabila pelanggaran serupa terulang kembali, maka akan dikenakan sanksi yang lebih berat berupa Surat Peringatan <?= ($jenis + 1) ?>.</li>
                <li>Surat peringatan ini berlaku sejak tanggal diterbitkan.</li>
            </ol>
            
            <div class="paragraf">
                Demikian surat peringatan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
            </div>
        </div>
        
        <!-- TANDA TANGAN -->
        <div class="footer">
            <div class="ttd">
                <div>Kota Contoh, <?= $data_contoh['tanggal'] ?></div>
                <div><?= $data_contoh['ttd_jabatan'] ?></div>
                
                <div class="ttd-nama">
                    <?= $data_contoh['ttd_nama'] ?>
                </div>
                <div class="ttd-jabatan">
                    NIP. 196512101992031001
                </div>
            </div>
            
            <div style="margin-top: 30px; font-size: 10pt; border-top: 1px dashed #ccc; padding-top: 10px;">
                <strong>Tembusan:</strong>
                <ol style="margin: 5px 0 0 20px;">
                    <li>Arsip</li>
                    <li>Mahasiswa yang bersangkutan</li>
                    <li>Wali Dosen</li>
                    <li>Buku Kasus Mahasiswa</li>
                </ol>
            </div>
            
            <div style="margin-top: 20px; padding: 10px; background: #f5f5f5; border-left: 4px solid #007bff; font-size: 10pt;">
                <strong>Catatan:</strong> Template ini adalah contoh format resmi Surat Peringatan <?= $jenis ?>. 
                Data akan otomatis terisi dari sistem ketika digunakan untuk kasus sebenarnya.
            </div>
        </div>
        
        <?php if (!$download): ?>
        <div class="action-buttons no-print" style="margin-top: 30px;">
            <a href="cetak_template.php?jenis=<?= $jenis ?>&download=1" class="btn btn-download">
                📥 Download PDF
            </a>
            <a href="javascript:window.print()" class="btn btn-print">
                🖨️ Cetak
            </a>
            <a href="index.php?action=rekap&pdf_action=template" class="btn btn-back">
                ↩ Kembali
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto print jika parameter print=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('print')) {
            window.print();
        }
        
        // Set title untuk tab browser
        document.title = "Template SP<?= $jenis ?> - Politeknik Negeri";
    </script>
</body>
</html>