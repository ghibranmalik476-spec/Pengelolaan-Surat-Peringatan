<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit;
}

$jenis = $_GET['jenis'] ?? '1';
$download = isset($_GET['download']) ? true : false;
$print = isset($_GET['print']) ? true : false;

if ($download) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Template_SP'.$jenis.'_Kosong.pdf"');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page {
            margin: 1.5cm;
            size: A4;
            margin: 2cm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.0;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .container {
            width: 100%;
            max-width: 18cm;
            margin: 0 auto;
            min-height: 26cm;
            position: relative;
        }
        
        /* GARIS PEMBATAS HEADER */
        .kop-surat {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
            position: relative;
            min-height: 120px;
        }
        
        
        .tempat-tanggal {
            position: absolute;
            top: 130px; 
            right: 0;
            text-align: right;
            font-size: 11pt;
            line-height: 1.0;
        }
        
        .logo-container {
            position: absolute;
            top: 30px;
            left: 0;
            width: 100px;
            height: auto;
        }
        
        .logo {
            max-width: 80px;
            height: auto;
        }
        
        .header-content {
            margin-left: 100px;
            text-align: center;
            width: calc(100% - 100px);
        }
        
        .kop-atas {
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.0;
            margin: 0;
            text-transform: uppercase;
            padding: 0;
        }
        
        .kop-politeknik {
            font-size: 16pt;
            font-weight: bold;
            margin: 2px 0 0 0; 
            letter-spacing: 0.5px;
            line-height: 1.0;
        }
        
        .alamat-kontak {
            font-size: 9pt;
            margin: 2px 0 0 0;
            line-height: 1.1;
            text-align: center;
        }
        
        .kontak-line {
            margin: 1px 0;
            line-height: 1.0;
        }
        
        .nomor-surat {
            text-align: center;
            margin: 20px 0 5px 0;
            font-size: 11pt;
            line-height: 1.0;
        }
        
        .content {
            text-align: justify;
            margin-top: 10px;
            line-height: 1.1;
        }
        
        .identitas table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        
        .identitas td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 11pt;
            line-height: 1.1;
        }
        
        .identitas td:first-child {
            width: 120px;
        }
        
        .paragraf {
            margin: 8px 0;
            text-indent: 40px;
            line-height: 1.1;
        }
        
        .ttd {
            text-align: center;
            margin-top: 30px;
            line-height: 1.1;
        }
        
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 30px;
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
        
        .form-field {
            border: none;
            background: transparent;
            padding: 1px 3px;
            margin: 0 1px;
            min-width: 150px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            display: inline-block;
            line-height: 1.0;
            outline: none;
        }
        
        .form-field-small {
            min-width: 80px;
        }
        
        .form-field-medium {
            min-width: 120px;
        }
        
        .form-field-large {
            min-width: 200px;
        }
        
        .form-textarea {
            width: 100%;
            min-height: 40px;
            border: none;
            padding: 2px 0;
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            background: transparent;
            margin-top: 3px;
            line-height: 1.1;
            resize: vertical;
            outline: none;
        }
        
        .instruction {
            color: #666;
            font-size: 8pt;
            font-style: italic;
            margin-top: 1px;
        }
        
        @media print {
            @page {
                margin: 1.5cm;
                size: A4;
            }
            
            body {
                font-size: 11pt;
                line-height: 1.0;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            @page {
                margin: 1.5cm;
                marks: none;
                size: A4;
            }
            
            @page :first {
                margin: 1.5cm;
                marks: none;
            }
            
            @page :left {
                margin: 1.5cm;
            }
            
            @page :right {
                margin: 1.5cm;
            }
            
            .no-print {
                display: none !important;
            }
            
            .container {
                max-width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
            }
            
            .form-instruction,
            .action-buttons,
            .no-print {
                display: none !important;
            }
        }
        
        .action-buttons {
            margin: 15px 0;
            text-align: center;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        
        .btn {
            padding: 8px 15px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            cursor: pointer;
            font-size: 12px;
            border: 1px solid transparent;
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
        
        .btn-fill {
            background: #ffc107;
            color: #000;
        }
        
        .btn-clear {
            background: #dc3545;
            color: white;
        }
        
        .form-instruction {
            background: #e9f7fe;
            border-left: 4px solid #007bff;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .compact-list {
            margin: 5px 0 5px 20px;
            padding: 0;
        }
        
        .compact-list li {
            margin-bottom: 2px;
            font-size: 11pt;
            line-height: 1.1;
        }
        
        .kontak-line span {
            margin: 0 3px;
        }
        
        .form-field:focus,
        .form-textarea:focus {
            background-color: #f0f8ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$download && !$print): ?>
        
        <?php endif; ?>
        
        <div class="kop-surat">
            <div class="logo-container">
                <img src="poltek.png" alt="Logo Politeknik Negeri Batam" class="logo">
                <?php if (!file_exists('poltek.png')): ?>
                <div style="width: 80px; height: 80px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666;">
                    LOGO<br>POLIBATAM
                </div>
                <?php endif; ?>
            </div>
            
            <div class="header-content">
                <div class="kop-atas">KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN<br>RISET, DAN TEKNOLOGI</div>
                <div class="kop-politeknik">POLITEKNIK NEGERI BATAM</div>
                
                <div class="alamat-kontak">
                    <div class="kontak-line">
                        <input type="text" class="form-field form-field-large" id="alamat" placeholder="Jl. Ahmad Yani, Batam Center, Kecamatan Batam Kota, Batam 29461" value="Jl. Ahmad Yani, Batam Center, Kecamatan Batam Kota, Batam 29461" style="font-size: 9pt; min-width: 350px; text-align: center; padding: 0; margin: 0;">
                    </div>
                    <div class="kontak-line">
                        Telepon: 
                        <input type="text" class="form-field form-field-small" id="telepon" placeholder="+62 778 469856 - 469860" value="+62 778 469856 - 469860" style="font-size: 9pt; padding: 0; margin: 0 3px;">
                        Faksimile: 
                        <input type="text" class="form-field form-field-small" id="faksimile" placeholder="+62 778 463620" value="+62 778 463620" style="font-size: 9pt; padding: 0; margin: 0 3px;">
                    </div>
                    <div class="kontak-line">
                        Laman: 
                        <input type="text" class="form-field form-field-medium" id="website" placeholder="www.polibatam.ac.id" value="www.polibatam.ac.id" style="font-size: 9pt; padding: 0; margin: 0 3px;">
                        Surel: 
                        <input type="text" class="form-field form-field-medium" id="email" placeholder="info@polibatam.ac.id" value="info@polibatam.ac.id" style="font-size: 9pt; padding: 0; margin: 0 3px;">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tempat-tanggal">
            <input type="text" class="form-field form-field-small" id="tempat" placeholder="Batam" value="Batam" style="text-align: right; font-size: 11pt; padding: 0; margin: 0;">
            , 
            <input type="text" class="form-field form-field-medium" id="tanggal_surat" placeholder="<?= date('d F Y') ?>" value="<?= date('d F Y') ?>" style="text-align: right; font-size: 11pt; padding: 0; margin: 0;">
        </div>
        
        <div class="nomor-surat">
            <p style="margin: 2px 0;"><strong>Nomor</strong> : 
                <input type="text" class="form-field form-field-medium" id="nomor_surat" placeholder="XXX/SP<?= $jenis ?>/AK/<?= date('Y') ?>" value="" style="padding: 0; margin: 0;">
            </p>
            <p style="margin: 2px 0;"><strong>Perihal</strong> : <span class="bold">SURAT PERINGATAN (SP<?= $jenis ?>)</span></p>
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
                        <td>: <input type="text" class="form-field form-field-large" id="ttd_nama" placeholder="Nama Penandatangan" value=""></td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>: <input type="text" class="form-field form-field-large" id="ttd_jabatan" placeholder="Jabatan" value=""></td>
                    </tr>
                    <tr>
                        <td>NIP</td>
                        <td>: <input type="text" class="form-field form-field-medium" id="nip" placeholder="Nomor Induk Pegawai" value=""></td>
                    </tr>
                </table>
            </div>
            
            <div class="paragraf">
                Menerangkan bahwa mahasiswa:
            </div>
            
            <div class="identitas">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>: <input type="text" class="form-field form-field-large" id="nama_mahasiswa" placeholder="Nama Mahasiswa" value=""></td>
                    </tr>
                    <tr>
                        <td>NIM</td>
                        <td>: <input type="text" class="form-field form-field-medium" id="nim" placeholder="Nomor Induk" value=""></td>
                    </tr>
                    <tr>
                        <td>Jurusan</td>
                        <td>: <input type="text" class="form-field form-field-large" id="jurusan" placeholder="Jurusan/Prodi" value=""></td>
                    </tr>
                    <tr>
                        <td>Semester</td>
                        <td>: <input type="text" class="form-field form-field-small" id="semester" placeholder="Semester" value=""></td>
                    </tr>
                </table>
            </div>
            
            <div class="paragraf">
                <strong>Telah melakukan pelanggaran sebagai berikut:</strong>
            </div>
            
            <div>
                <textarea class="form-textarea" id="alasan" placeholder="Jelaskan jenis pelanggaran secara singkat dan jelas..." style="width: 100%; min-height: 40px; padding: 0;"></textarea>
            </div>
            
            <div class="paragraf">
                <strong>Dasar:</strong> 
                <input type="text" class="form-field form-field-large" id="dasar" placeholder="Pasal/peraturan yang dilanggar" value="">
            </div>
            
            <div class="paragraf">
                <strong>Maka diberikan SURAT PERINGATAN <?= $jenis ?> dengan ketentuan:</strong>
            </div>
            
            <ol class="compact-list">
                <li>Mahasiswa wajib memperbaiki perilaku dan mematuhi peraturan.</li>
                <li>Surat ini merupakan peringatan resmi dari institusi.</li>
                <li>Jika pelanggaran terulang, akan dikenakan sanksi lebih berat (SP<?= ($jenis + 1) ?>).</li>
                <li>Surat ini berlaku sejak tanggal diterbitkan.</li>
            </ol>
            
            <div class="paragraf">
                Demikian surat peringatan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
            </div>
            
            <!-- Tanda Tangan -->
            <div class="ttd">
                <div id="display_jabatan" style="font-weight: bold; margin-bottom: 40px;"></div>
                
                <div class="ttd-nama" id="display_nama" style="margin-bottom: 3px;"></div>
                <div style="font-size: 10pt;">NIP. <span id="display_nip"></span></div>
            </div>
        </div>
        
        <?php if (!$download && !$print): ?>
        
        <div class="action-buttons no-print" style="margin-top: 15px;">
            <button onclick="fillAllFields()" class="btn btn-fill">📝 Isi Contoh</button>
            <button onclick="clearAllFields()" class="btn btn-clear">🗑️ Kosongkan</button>
            <a href="javascript:void(0)" onclick="preparePrint()" class="btn btn-print">🖨️ Cetak</a>
            <a href="cetak_template_kosong.php?jenis=<?= $jenis ?>&download=1" class="btn btn-download">📥 Download PDF</a>
            <a href="dashboard_staff.php?action=dashboard" class="btn btn-back">↩ Kembali</a>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Data contoh untuk 1 lembar
        const contohData = {
            alamat: 'Jl. Ahmad Yani, Batam Center, Kecamatan Batam Kota, Batam 29461',
            telepon: '+62 778 469856 - 469860',
            faksimile: '+62 778 463620',
            website: 'www.polibatam.ac.id',
            email: 'info@polibatam.ac.id',
            nomor_surat: '001/SP<?= $jenis ?>/AK/<?= date("Y") ?>',
            tempat: 'Batam',
            tanggal_surat: '<?= date("d F Y") ?>',
            ttd_nama: 'Dr. SUTARJO, M.Kom.',
            ttd_jabatan: 'Ketua Jurusan',
            nip: '196512101992031001',
            nama_mahasiswa: 'BUDI SANTOSO',
            nim: '20231001',
            jurusan: 'TEKNIK INFORMATIKA',
            semester: 'IV',
            alasan: 'Tidak hadir dalam perkuliahan lebih dari 3 kali berturut-turut tanpa keterangan.',
            dasar: 'Pasal 5 ayat 2 Peraturan Akademik'
        };

        function fillAllFields() {
            for (const [key, value] of Object.entries(contohData)) {
                const element = document.getElementById(key);
                if (element) {
                    element.value = value;
                }
            }
            updateDisplay();
        }

        function clearAllFields() {
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                if (!['alamat', 'telepon', 'faksimile', 'website', 'email', 'tempat'].includes(input.id)) {
                    input.value = '';
                }
            });
            document.getElementById('tanggal_surat').value = '<?= date("d F Y") ?>';
            updateDisplay();
        }

        function updateDisplay() {
            const nama = document.getElementById('ttd_nama').value;
            const jabatan = document.getElementById('ttd_jabatan').value;
            const nip = document.getElementById('nip').value;
            
            document.getElementById('display_nama').textContent = nama;
            document.getElementById('display_jabatan').textContent = jabatan;
            document.getElementById('display_nip').textContent = nip;
        }

        function preparePrint() {
            
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const fieldsToWatch = ['ttd_nama', 'ttd_jabatan', 'nip'];
            fieldsToWatch.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', updateDisplay);
                }
            });
            
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('print')) {
                setTimeout(() => {
                    preparePrint();
                }, 500);
            }
            
            document.getElementById('alamat').value = 'Jl. Ahmad Yani, Batam Center, Kecamatan Batam Kota, Batam 29461';
            document.getElementById('telepon').value = '+62 778 469856 - 469860';
            document.getElementById('faksimile').value = '+62 778 463620';
            document.getElementById('website').value = 'www.polibatam.ac.id';
            document.getElementById('email').value = 'info@polibatam.ac.id';
            document.getElementById('tempat').value = 'Batam';
            document.getElementById('tanggal_surat').value = '<?= date("d F Y") ?>';
            
            updateDisplay();
            
            const firstField = document.getElementById('nomor_surat');
            if (firstField && !window.location.search.includes('print')) {
                firstField.focus();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                fillAllFields();
            }
            if (e.ctrlKey && e.key === 'c') {
                e.preventDefault();
                clearAllFields();
            }
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                preparePrint();
            }
        });
    </script>
</body>
</html>