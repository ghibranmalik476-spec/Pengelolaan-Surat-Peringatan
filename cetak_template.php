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
$print = isset($_GET['print']) ? true : false;

// Jika download, set header untuk PDF
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
    <title>Template SP<?= $jenis ?> - Kosong (1 Lembar)</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 100%;
            max-width: 18cm;
            margin: 0 auto;
            min-height: 26cm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .kop-surat {
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 5px;
        }
        
        .nomor-surat {
            text-align: center;
            margin: 10px 0;
            font-size: 11pt;
        }
        
        .content {
            text-align: justify;
            margin-top: 15px;
        }
        
        .identitas table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .identitas td {
            vertical-align: top;
            padding: 3px 0;
            font-size: 11pt;
        }
        
        .identitas td:first-child {
            width: 120px;
        }
        
        .paragraf {
            margin: 10px 0;
            text-indent: 40px;
        }
        
        .ttd {
            text-align: right;
            margin-top: 40px;
        }
        
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 40px;
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
        
        /* Untuk form fillable */
        .form-field {
            border: none;
            border-bottom: 1px dotted #666;
            background: transparent;
            padding: 1px 3px;
            margin: 0 3px;
            min-width: 150px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            display: inline-block;
        }
        
        .form-field:focus {
            outline: none;
            border-bottom: 1px solid #007bff;
            background-color: #f0f8ff;
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
            height: 60px;
            border: 1px dotted #666;
            padding: 3px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            background: transparent;
            margin-top: 5px;
        }
        
        .form-textarea:focus {
            outline: none;
            border: 1px solid #007bff;
            background-color: #f0f8ff;
        }
        
        .instruction {
            color: #666;
            font-size: 9pt;
            font-style: italic;
            margin-top: 2px;
        }
        
        /* Untuk print */
        @media print {
            body {
                font-size: 11pt;
            }
            
            .no-print {
                display: none !important;
            }
            
            .form-field {
                border-bottom: 1px solid #000;
            }
            
            .form-textarea {
                border: 1px solid #000;
            }
            
            .instruction {
                display: none;
            }
            
            .container {
                max-width: 100%;
                min-height: auto;
            }
        }
        
        /* Tombol untuk web view */
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
        
        .tembusan {
            margin-top: 15px;
            font-size: 10pt;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }
        
        /* Compact layout */
        .compact-list {
            margin: 5px 0 5px 20px;
            padding: 0;
        }
        
        .compact-list li {
            margin-bottom: 3px;
            font-size: 11pt;
        }
        
        /* Make it fit in one page */
        .page-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$download && !$print): ?>
        <div class="action-buttons no-print">
            <button onclick="fillAllFields()" class="btn btn-fill">📝 Isi Contoh</button>
            <button onclick="clearAllFields()" class="btn btn-clear">🗑️ Kosongkan</button>
            <a href="javascript:window.print()" class="btn btn-print">🖨️ Cetak</a>
            <a href="cetak_template_kosong.php?jenis=<?= $jenis ?>&download=1" class="btn btn-download">📥 Download PDF</a>
            <a href="index.php?action=rekap&pdf_action=template" class="btn btn-back">↩ Kembali</a>
        </div>
        
        <div class="form-instruction no-print">
            <strong><i class="bi bi-info-circle"></i> Template SP<?= $jenis ?> (1 Lembar)</strong><br>
            Isi semua field yang ditandai garis bawah, lalu cetak atau download sebagai PDF.
        </div>
        <?php endif; ?>
        
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <div class="header">
                <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 5px;">
                    <div>
                        <img src="poltek.png" alt="Logo" class="logo">
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 14pt;">POLITEKNIK NEGERI</h4>
                        <h3 style="margin: 0; font-size: 16pt;">
                            <input type="text" class="form-field form-field-large center" id="institusi" placeholder="Nama Universitas" value="" style="font-size: 16pt; font-weight: bold;">
                        </h3>
                    </div>
                </div>
                <p style="margin: 3px 0; font-size: 10pt;">
                    <input type="text" class="form-field form-field-large" id="alamat" placeholder="Alamat Lengkap" value="" style="font-size: 10pt; min-width: 300px;">
                </p>
                <p style="margin: 3px 0; font-size: 10pt;">
                    Telp: <input type="text" class="form-field form-field-small" id="telepon" placeholder="(021) ..." value="" style="font-size: 10pt;">
                    | Email: <input type="text" class="form-field form-field-medium" id="email" placeholder="email@ac.id" value="" style="font-size: 10pt;">
                </p>
            </div>
        </div>
        
        <!-- NOMOR SURAT -->
        <div class="nomor-surat">
            <p><strong>Nomor</strong> : 
                <input type="text" class="form-field form-field-medium" id="nomor_surat" placeholder="XXX/SP<?= $jenis ?>/AK/<?= date('Y') ?>" value="">
            </p>
            <p><strong>Perihal</strong> : <span class="bold">SURAT PERINGATAN (SP<?= $jenis ?>)</span></p>
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
            
            <div style="margin: 8px 0; padding: 5px; border: 1px dotted #666; border-radius: 3px;">
                <textarea class="form-textarea" id="alasan" placeholder="Jelaskan jenis pelanggaran secara singkat dan jelas..."></textarea>
                <div class="instruction">Contoh: Tidak hadir kuliah 3x berturut-turut tanpa keterangan</div>
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
        </div>
        
        <!-- TANDA TANGAN DAN TEMBUSAN DALAM SATU BARIS -->
        <div style="display: flex; justify-content: space-between; margin-top: 30px;">
            <!-- Tembusan -->
            <div class="tembusan" style="flex: 1; margin-right: 20px;">
                <strong>Tembusan:</strong>
                <ol class="compact-list">
                    <li>Arsip</li>
                    <li>Mahasiswa ybs.</li>
                    <li>Wali Dosen</li>
                    <li>Buku Kasus</li>
                </ol>
            </div>
            
            <!-- Tanda Tangan -->
            <div class="ttd" style="flex: 1; text-align: center;">
                <div style="margin-bottom: 40px;">
                    <input type="text" class="form-field form-field-medium" id="kota" placeholder="Kota" value="" style="text-align: center;">
                    , 
                    <input type="text" class="form-field form-field-medium" id="tanggal" placeholder="<?= date('d/m/Y') ?>" value="<?= date('d/m/Y') ?>" style="text-align: center;">
                </div>
                
                <div id="display_jabatan" style="font-weight: bold; margin-bottom: 50px;"></div>
                
                <div class="ttd-nama" id="display_nama" style="margin-bottom: 5px;"></div>
                <div style="font-size: 10pt;">NIP. <span id="display_nip"></span></div>
            </div>
        </div>
        
        <?php if (!$download && !$print): ?>
        <div style="margin-top: 20px; padding: 8px; background: #f8f9fa; border-radius: 3px; font-size: 10pt; text-align: center;" class="no-print">
            <strong>Template SP<?= $jenis ?> - 1 Lembar</strong> | Isi semua field lalu cetak
        </div>
        
        <div class="action-buttons no-print" style="margin-top: 15px;">
            <button onclick="fillAllFields()" class="btn btn-fill">📝 Isi Contoh</button>
            <button onclick="clearAllFields()" class="btn btn-clear">🗑️ Kosongkan</button>
            <a href="javascript:window.print()" class="btn btn-print">🖨️ Cetak</a>
            <a href="cetak_template_kosong.php?jenis=<?= $jenis ?>&download=1" class="btn btn-download">📥 Download PDF</a>
            <a href="index.php?action=rekap&pdf_action=template" class="btn btn-back">↩ Kembali</a>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Data contoh untuk 1 lembar
        const contohData = {
            institusi: 'UNIVERSITAS CONTOH',
            alamat: 'Jl. Pendidikan No. 123, Kota Contoh',
            telepon: '(021) 123456',
            email: 'info@contoh.ac.id',
            nomor_surat: '001/SP<?= $jenis ?>/AK/<?= date("Y") ?>',
            ttd_nama: 'Dr. SUTARJO, M.Kom.',
            ttd_jabatan: 'Ketua Jurusan',
            nip: '196512101992031001',
            nama_mahasiswa: 'BUDI SANTOSO',
            nim: '20231001',
            jurusan: 'TEKNIK INFORMATIKA',
            semester: 'IV',
            alasan: 'Tidak hadir dalam perkuliahan lebih dari 3 kali berturut-turut tanpa keterangan.',
            dasar: 'Pasal 5 ayat 2 Peraturan Akademik',
            kota: 'Kota Contoh',
            tanggal: '<?= date("d/m/Y") ?>'
        };

        // Fungsi untuk mengisi semua field dengan contoh
        function fillAllFields() {
            for (const [key, value] of Object.entries(contohData)) {
                const element = document.getElementById(key);
                if (element) {
                    element.value = value;
                }
            }
            updateDisplay();
        }

        // Fungsi untuk mengosongkan semua field
        function clearAllFields() {
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.value = '';
            });
            updateDisplay();
        }

        // Update display fields
        function updateDisplay() {
            const nama = document.getElementById('ttd_nama').value;
            const jabatan = document.getElementById('ttd_jabatan').value;
            const nip = document.getElementById('nip').value;
            
            document.getElementById('display_nama').textContent = nama;
            document.getElementById('display_jabatan').textContent = jabatan;
            document.getElementById('display_nip').textContent = nip;
        }

        // Auto update display ketika field diubah
        document.addEventListener('DOMContentLoaded', function() {
            const fieldsToWatch = ['ttd_nama', 'ttd_jabatan', 'nip'];
            fieldsToWatch.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', updateDisplay);
                }
            });
            
            // Set title
            document.title = "Template SP<?= $jenis ?> (1 Lembar)";
            
            // Auto print jika parameter print=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('print')) {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
            
            // Initial update
            updateDisplay();
            
            // Auto focus ke field pertama
            const firstField = document.getElementById('institusi');
            if (firstField && !window.location.search.includes('print')) {
                firstField.focus();
            }
        });

        // Fitur shortcut untuk cepat isi
        document.addEventListener('keydown', function(e) {
            // Ctrl + F = Fill all
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                fillAllFields();
            }
            // Ctrl + C = Clear all
            if (e.ctrlKey && e.key === 'c') {
                e.preventDefault();
                clearAllFields();
            }
            // Ctrl + P = Print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>