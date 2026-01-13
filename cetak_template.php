<?php
session_start();
require 'koneksi.php';

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk memvalidasi sesi pengguna
function validateUserSession()
{
    try {
        if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
            header("Location: login.php");
            exit;
        }
        return true;
    } catch (Exception $e) {
        error_log("Session validation error: " . $e->getMessage());
        return false;
    }
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk mendapatkan parameter dengan nilai default
function getTemplateParameters()
{
    return [
        'type' => $_GET['jenis'] ?? '1',
        'isDownload' => isset($_GET['download']),
        'isPrint' => isset($_GET['print'])
    ];
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk mengatur header download PDF
function setPdfDownloadHeaders($documentType)
{
    try {
        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Template_SP' . $documentType . '_Kosong.pdf"');
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error setting PDF headers: " . $e->getMessage());
        return false;
    }
}

// PENERAPAN ERROR HANDLING: Validasi sesi dengan try-catch
try {
    validateUserSession();
} catch (Exception $e) {
    error_log("Session validation failed: " . $e->getMessage());
    header("Location: login.php");
    exit;
}

// PENERAPAN CLEAN CODE: Menggunakan nama variabel yang deskriptif
$templateParams = getTemplateParameters();
$documentType = $templateParams['type'];
$isDownload = $templateParams['isDownload'];
$isPrint = $templateParams['isPrint'];

// PENERAPAN ERROR HANDLING: Handle download request dengan try-catch
if ($isDownload) {
    try {
        setPdfDownloadHeaders($documentType);
    } catch (Exception $e) {
        error_log("PDF download error: " . $e->getMessage());
        // Fallback jika terjadi error
        if (!headers_sent()) {
            header('Content-Type: text/html');
        }
    }
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk mendapatkan data contoh berdasarkan jenis SP
function getSampleDataByType($documentType)
{
    $currentDate = date('d F Y');
    $currentYear = date('Y');
    
    return [
        'alamat' => 'Jl. Ahmad Yani, Batam Center, Kecamatan Batam Kota, Batam 29461',
        'telepon' => '+62 778 469856 - 469860',
        'faksimile' => '+62 778 463620',
        'website' => 'www.polibatam.ac.id',
        'email' => 'info@polibatam.ac.id',
        'nomor_surat' => '001/SP' . $documentType . '/AK/' . $currentYear,
        'tempat' => 'Batam',
        'tanggal_surat' => $currentDate,
        'ttd_nama' => 'Dr. SUTARJO, M.Kom.',
        'ttd_jabatan' => 'Ketua Jurusan',
        'nip' => '196512101992031001',
        'nama_mahasiswa' => 'BUDI SANTOSO',
        'nim' => '20231001',
        'jurusan' => 'TEKNIK INFORMATIKA',
        'semester' => 'IV',
        'alasan' => 'Tidak hadir dalam perkuliahan lebih dari 3 kali berturut-turut tanpa keterangan.',
        'dasar' => 'Pasal 5 ayat 2 Peraturan Akademik'
    ];
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk menghasilkan konten HTML halaman
function generatePageContent($documentType, $isDownload, $isPrint)
{
    $sampleData = getSampleDataByType($documentType);
    $currentDate = date('d F Y');
    
    // PENERAPAN CLEAN CODE: Menggunakan heredoc untuk HTML yang besar
    $content = <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            /* STYLE CSS TETAP SAMA SEPERTI ASLINYA */
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
HTML;

    // PENERAPAN CLEAN CODE: Menghindari inline HTML yang panjang
    // Logo dan header section
    $content .= generateLogoSection();
    
    // Header surat
    $content .= generateHeaderSection($documentType, $currentDate, $sampleData);
    
    // Tempat tanggal
    $content .= generateDateLocationSection($sampleData, $currentDate);
    
    // Nomor surat
    $content .= generateDocumentNumberSection($documentType, $sampleData);
    
    // Konten utama
    $content .= generateMainContent($documentType, $sampleData);
    
    // Tanda tangan
    $content .= generateSignatureSection();
    
    // Action buttons (jika bukan download/print)
    if (!$isDownload && !$isPrint) {
        $content .= generateActionButtons($documentType);
    }
    
    $content .= <<<HTML
        </div>
        
        <script>
            // PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI: Data contoh
            const sampleData = {
                alamat: '{$sampleData['alamat']}',
                telepon: '{$sampleData['telepon']}',
                faksimile: '{$sampleData['faksimile']}',
                website: '{$sampleData['website']}',
                email: '{$sampleData['email']}',
                nomor_surat: '{$sampleData['nomor_surat']}',
                tempat: '{$sampleData['tempat']}',
                tanggal_surat: '{$sampleData['tanggal_surat']}',
                ttd_nama: '{$sampleData['ttd_nama']}',
                ttd_jabatan: '{$sampleData['ttd_jabatan']}',
                nip: '{$sampleData['nip']}',
                nama_mahasiswa: '{$sampleData['nama_mahasiswa']}',
                nim: '{$sampleData['nim']}',
                jurusan: '{$sampleData['jurusan']}',
                semester: '{$sampleData['semester']}',
                alasan: '{$sampleData['alasan']}',
                dasar: '{$sampleData['dasar']}'
            };

            // PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI: Mengisi semua field
            function fillAllFields() {
                try {
                    for (const [key, value] of Object.entries(sampleData)) {
                        const element = document.getElementById(key);
                        if (element) {
                            element.value = value;
                        }
                    }
                    updateDisplay();
                    console.log('Semua field berhasil diisi dengan data contoh');
                } catch (error) {
                    console.error('Error mengisi field:', error);
                    alert('Terjadi kesalahan saat mengisi field');
                }
            }

            // PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI: Mengosongkan semua field
            function clearAllFields() {
                try {
                    const inputs = document.querySelectorAll('input, textarea');
                    inputs.forEach(input => {
                        if (!['alamat', 'telepon', 'faksimile', 'website', 'email', 'tempat'].includes(input.id)) {
                            input.value = '';
                        }
                    });
                    document.getElementById('tanggal_surat').value = '{$currentDate}';
                    updateDisplay();
                    console.log('Semua field berhasil dikosongkan');
                } catch (error) {
                    console.error('Error mengosongkan field:', error);
                    alert('Terjadi kesalahan saat mengosongkan field');
                }
            }

            // PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI: Update display
            function updateDisplay() {
                try {
                    const nama = document.getElementById('ttd_nama').value;
                    const jabatan = document.getElementById('ttd_jabatan').value;
                    const nip = document.getElementById('nip').value;
                    
                    document.getElementById('display_nama').textContent = nama;
                    document.getElementById('display_jabatan').textContent = jabatan;
                    document.getElementById('display_nip').textContent = nip;
                } catch (error) {
                    console.error('Error updating display:', error);
                }
            }

            // PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI: Prepare print
            function preparePrint() {
                try {
                    window.print();
                } catch (error) {
                    console.error('Error preparing print:', error);
                    alert('Terjadi kesalahan saat mempersiapkan cetak');
                }
            }

            // PENERAPAN ERROR HANDLING: Event listeners dengan try-catch
            document.addEventListener('DOMContentLoaded', function() {
                try {
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
                    
                    // Set default values
                    document.getElementById('alamat').value = sampleData.alamat;
                    document.getElementById('telepon').value = sampleData.telepon;
                    document.getElementById('faksimile').value = sampleData.faksimile;
                    document.getElementById('website').value = sampleData.website;
                    document.getElementById('email').value = sampleData.email;
                    document.getElementById('tempat').value = sampleData.tempat;
                    document.getElementById('tanggal_surat').value = sampleData.tanggal_surat;
                    
                    updateDisplay();
                    
                    const firstField = document.getElementById('nomor_surat');
                    if (firstField && !window.location.search.includes('print')) {
                        firstField.focus();
                    }
                } catch (error) {
                    console.error('Error initializing document:', error);
                }
            });

            // PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI: Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                try {
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
                } catch (error) {
                    console.error('Error handling keyboard shortcut:', error);
                }
            });
        </script>
    </body>
    </html>
HTML;

    return $content;
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk menghasilkan bagian logo
function generateLogoSection()
{
    $logoSection = '';
    
    // PENERAPAN ERROR HANDLING: Cek keberadaan file logo
    try {
        $logoPath = 'poltek.png';
        $logoSection .= '<div class="kop-surat">';
        $logoSection .= '<div class="logo-container">';
        
        if (file_exists($logoPath)) {
            $logoSection .= '<img src="' . $logoPath . '" alt="Logo Politeknik Negeri Batam" class="logo">';
        } else {
            $logoSection .= '<div style="width: 80px; height: 80px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666;">';
            $logoSection .= 'LOGO<br>POLIBATAM';
            $logoSection .= '</div>';
        }
        
        $logoSection .= '</div>';
        return $logoSection;
    } catch (Exception $e) {
        error_log("Error generating logo section: " . $e->getMessage());
        return '<div class="kop-surat"><div class="logo-container">LOGO</div>';
    }
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk menghasilkan bagian header
function generateHeaderSection($documentType, $currentDate, $sampleData)
{
    return <<<HTML
    <div class="header-content">
        <div class="kop-atas">KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN<br>RISET, DAN TEKNOLOGI</div>
        <div class="kop-politeknik">POLITEKNIK NEGERI BATAM</div>
        
        <div class="alamat-kontak">
            <div class="kontak-line">
                <input type="text" class="form-field form-field-large" id="alamat" placeholder="Jl. Ahmad Yani, Batam Center, Kecamatan Batam Kota, Batam 29461" value="{$sampleData['alamat']}" style="font-size: 9pt; min-width: 350px; text-align: center; padding: 0; margin: 0;">
            </div>
            <div class="kontak-line">
                Telepon: 
                <input type="text" class="form-field form-field-small" id="telepon" placeholder="+62 778 469856 - 469860" value="{$sampleData['telepon']}" style="font-size: 9pt; padding: 0; margin: 0 3px;">
                Faksimile: 
                <input type="text" class="form-field form-field-small" id="faksimile" placeholder="+62 778 463620" value="{$sampleData['faksimile']}" style="font-size: 9pt; padding: 0; margin: 0 3px;">
            </div>
            <div class="kontak-line">
                Laman: 
                <input type="text" class="form-field form-field-medium" id="website" placeholder="www.polibatam.ac.id" value="{$sampleData['website']}" style="font-size: 9pt; padding: 0; margin: 0 3px;">
                Surel: 
                <input type="text" class="form-field form-field-medium" id="email" placeholder="info@polibatam.ac.id" value="{$sampleData['email']}" style="font-size: 9pt; padding: 0; margin: 0 3px;">
            </div>
        </div>
    </div>
</div>
HTML;
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk menghasilkan bagian tempat dan tanggal
function generateDateLocationSection($sampleData, $currentDate)
{
    return <<<HTML
<div class="tempat-tanggal">
    <input type="text" class="form-field form-field-small" id="tempat" placeholder="Batam" value="{$sampleData['tempat']}" style="text-align: right; font-size: 11pt; padding: 0; margin: 0;">
    , 
    <input type="text" class="form-field form-field-medium" id="tanggal_surat" placeholder="{$currentDate}" value="{$sampleData['tanggal_surat']}" style="text-align: right; font-size: 11pt; padding: 0; margin: 0;">
</div>
HTML;
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk menghasilkan bagian nomor surat
function generateDocumentNumberSection($documentType, $sampleData)
{
    return <<<HTML
<div class="nomor-surat">
    <p style="margin: 2px 0;"><strong>Nomor</strong> : 
        <input type="text" class="form-field form-field-medium" id="nomor_surat" placeholder="XXX/SP{$documentType}/AK/" . date('Y') . "" value="{$sampleData['nomor_surat']}" style="padding: 0; margin: 0;">
    </p>
    <p style="margin: 2px 0;"><strong>Perihal</strong> : <span class="bold">SURAT PERINGATAN (SP{$documentType})</span></p>
</div>
HTML;
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk menghasilkan konten utama
function generateMainContent($documentType, $sampleData)
{
    $nextSpLevel = intval($documentType) + 1;
    
    return <<<HTML
<!-- ISI SURAT -->
<div class="content">
    <div class="paragraf">
        Yang bertanda tangan di bawah ini:
    </div>
    
    <div class="identitas">
        <table>
            <tr>
                <td>Nama</td>
                <td>: <input type="text" class="form-field form-field-large" id="ttd_nama" placeholder="Nama Penandatangan" value="{$sampleData['ttd_nama']}"></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: <input type="text" class="form-field form-field-large" id="ttd_jabatan" placeholder="Jabatan" value="{$sampleData['ttd_jabatan']}"></td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>: <input type="text" class="form-field form-field-medium" id="nip" placeholder="Nomor Induk Pegawai" value="{$sampleData['nip']}"></td>
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
                <td>: <input type="text" class="form-field form-field-large" id="nama_mahasiswa" placeholder="Nama Mahasiswa" value="{$sampleData['nama_mahasiswa']}"></td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>: <input type="text" class="form-field form-field-medium" id="nim" placeholder="Nomor Induk" value="{$sampleData['nim']}"></td>
            </tr>
            <tr>
                <td>Jurusan</td>
                <td>: <input type="text" class="form-field form-field-large" id="jurusan" placeholder="Jurusan/Prodi" value="{$sampleData['jurusan']}"></td>
            </tr>
            <tr>
                <td>Semester</td>
                <td>: <input type="text" class="form-field form-field-small" id="semester" placeholder="Semester" value="{$sampleData['semester']}"></td>
            </tr>
        </table>
    </div>
    
    <div class="paragraf">
        <strong>Telah melakukan pelanggaran sebagai berikut:</strong>
    </div>
    
    <div>
        <textarea class="form-textarea" id="alasan" placeholder="Jelaskan jenis pelanggaran secara singkat dan jelas..." style="width: 100%; min-height: 40px; padding: 0;">{$sampleData['alasan']}</textarea>
    </div>
    
    <div class="paragraf">
        <strong>Dasar:</strong> 
        <input type="text" class="form-field form-field-large" id="dasar" placeholder="Pasal/peraturan yang dilanggar" value="{$sampleData['dasar']}">
    </div>
    
    <div class="paragraf">
        <strong>Maka diberikan SURAT PERINGATAN {$documentType} dengan ketentuan:</strong>
    </div>
    
    <ol class="compact-list">
        <li>Mahasiswa wajib memperbaiki perilaku dan mematuhi peraturan.</li>
        <li>Surat ini merupakan peringatan resmi dari institusi.</li>
        <li>Jika pelanggaran terulang, akan dikenakan sanksi lebih berat (SP{$nextSpLevel}).</li>
        <li>Surat ini berlaku sejak tanggal diterbitkan.</li>
    </ol>
    
    <div class="paragraf">
        Demikian surat peringatan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
    </div>
HTML;
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk menghasilkan bagian tanda tangan
function generateSignatureSection()
{
    return <<<HTML
    <!-- Tanda Tangan -->
    <div class="ttd">
        <div id="display_jabatan" style="font-weight: bold; margin-bottom: 40px;"></div>
        
        <div class="ttd-nama" id="display_nama" style="margin-bottom: 3px;"></div>
        <div style="font-size: 10pt;">NIP. <span id="display_nip"></span></div>
    </div>
</div>
HTML;
}

// PENERAPAN PEMECAHAN LOGIKA DENGAN FUNGSI:
// Fungsi untuk menghasilkan tombol aksi
function generateActionButtons($documentType)
{
    return <<<HTML
<div class="action-buttons no-print" style="margin-top: 15px;">
    <button onclick="fillAllFields()" class="btn btn-fill">📝 Isi Contoh</button>
    <button onclick="clearAllFields()" class="btn btn-clear">🗑️ Kosongkan</button>
    <a href="javascript:void(0)" onclick="preparePrint()" class="btn btn-print">🖨️ Cetak</a>
    <a href="cetak_template_kosong.php?jenis={$documentType}&download=1" class="btn btn-download">📥 Download PDF</a>
    <a href="dashboard_staff.php?action=dashboard" class="btn btn-back">↩ Kembali</a>
</div>
HTML;
}

// PENERAPAN ERROR HANDLING: Generate halaman dengan try-catch
try {
    $pageContent = generatePageContent($documentType, $isDownload, $isPrint);
    echo $pageContent;
} catch (Exception $e) {
    // PENERAPAN ERROR HANDLING: Fallback jika terjadi error
    error_log("Error generating page content: " . $e->getMessage());
    
    // Tampilkan halaman error sederhana
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Error - Template SP</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .error-container { 
                max-width: 600px; 
                margin: 50px auto; 
                padding: 20px; 
                border: 1px solid #ccc; 
                border-radius: 5px; 
                background: #f8d7da; 
                color: #721c24;
            }
            .btn-back { 
                display: inline-block; 
                padding: 10px 15px; 
                background: #6c757d; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px; 
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h2>Terjadi Kesalahan</h2>
            <p>Maaf, terjadi kesalahan saat memuat template surat peringatan.</p>
            <p>Silakan coba lagi beberapa saat atau hubungi administrator.</p>
            <a href="dashboard_staff.php?action=dashboard" class="btn-back">↩ Kembali ke Dashboard</a>
        </div>
    </body>
    </html>';
}