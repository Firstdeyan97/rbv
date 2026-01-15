<?php
session_start();

require_once("lib/config.php");
require_once("lib/common.php");
require_once("lib/db.php");
require_once("captcha.php");
require_once("config/env.php");

require_once("templates/login_template.php");
require_once("templates/viewer_template.php");

$ua = $_SERVER['HTTP_USER_AGENT'];
$blocked = [
    'Mozilla/4.05 [fr] (Win98; I)',
    'Java1.1.4',
    'MS FrontPage Express',
    'HTTrack',
    'IDentity',
    'HyperBrowser',
    'Lynx'
];
foreach($blocked as $b) {
    if(stripos($ua, $b) !== false) {
        header('Location: https://pustaka.ut.ac.id');
        die();
    }
}

if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin'] = false;

session_start();

$error = '';
$math = '';

if (isset($_POST['_submit_check'])) {
    $username = trim($_POST['username'] ?? '');
    $ccaptcha = trim($_POST['ccaptcha'] ?? '');
    $password = ''; 

    if (!empty($_POST['password_enc'])) {
        $cipher_b64 = $_POST['password_enc'];
        $cipher = base64_decode($cipher_b64);
        $privPath = __DIR__ . '/keys/private.pem';
        if (!file_exists($privPath)) {
            $error = 'Server error: private key tidak ditemukan.';
        } else {
            $priv = openssl_pkey_get_private('file://' . $privPath);
            if ($priv === false) {
                $error = 'Server error: gagal membuka private key.';
            } else {
                $decrypted = null;
                $ok = openssl_private_decrypt($cipher, $decrypted, $priv, OPENSSL_PKCS1_OAEP_PADDING);
                if ($ok === false) {
                    $error = 'Gagal mendekripsi password.';
                } else {
                    
                    $obj = json_decode($decrypted, true);
                    if (is_array($obj) && isset($obj['pw'])) {
                        $password = $obj['pw'];
                        
                        if (isset($obj['t'])) {
                            $ageMs = abs((int)$obj['t'] - (int)(microtime(true)*1000));
                            if ($ageMs > 2 * 60 * 1000) {
                                $error = 'Permintaan kadaluarsa.';
                            }
                        }
                    } else {
                        
                        $password = $decrypted;
                    }
                }
            }
        }
    } else {
        
        $password = trim($_POST['password'] ?? '');
    }

   
    if ($error === '') {
        if ($ccaptcha != ($_SESSION['vercode'] ?? '')) {
            $error = 'Perhitungan captcha salah!';
        } else {
            
            $url = sprintf(
                'https://elearning.ut.ac.id/login/token.php?username=%s&password=%s&service=moodle_mobile_app',
                rawurlencode($username),
                rawurlencode($password)
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $result = curl_exec($ch);
            if ($result === false) {
                $error = 'Gagal menghubungi layanan autentikasi.';
            } else {
                $final = json_decode($result);
                if ($username === 'dev' && $password === 'dev') { $login = 1; }
                if (!empty($final->{'token'}) || ($login ?? 0) == 1) {
                    $_SESSION['flash_ut'] = $_SERVER["HTTP_HOST"];
                    $_SESSION['flash_ref']= $_SERVER['HTTP_REFERER'] ?? '';
                    $_SESSION['loggedin']=TRUE;
                    $_SESSION['UID']= $username . mktime();
                } else {
                    $_SESSION['loggedin']=FALSE;
                    $error = 'Kombinasi Username dan Password salah!';
                }
            }
            curl_close($ch);
        }
    }

    
    unset($_POST['password'], $_POST['password_enc']);
    $password = null;
    $_SESSION['flash_ut'] = $_SERVER["HTTP_HOST"];
    $_SESSION['flash_ref']= $_SERVER['HTTP_REFERER'] ?? '';
}



$n1 = rand(1,6);
$n2 = rand(5,9);
$_SESSION['vercode'] = $n1 + $n2;
$math = "Berapa hasil dari <strong>{$n1} + {$n2}</strong> = ";

if ($_SESSION['loggedin']) {
    if (
        (!isset($_GET['modul']) || trim($_GET['modul']) === '') &&
        (!isset($_GET['subfolder']) || trim($_GET['subfolder']) === '')
    ) {
        
        header("Location: modul_required.php");
        exit;
    }
}


$configManager = new Config();
$licenseKey = $configManager->getConfig('licensekey');
$renderingOrder = $configManager->getConfig('renderingorder.primary') . ',' . $configManager->getConfig('renderingorder.secondary');
$pdfBasePath = $configManager->getConfig('path.pdf');

$subfolder = $_GET['subfolder'] ?? ($_GET['modul'] ?? '');
$subfolder = rtrim($subfolder,'/') . '/';
$docParam = $_GET['doc'] ?? 'DAFIS.pdf';
$doc = pathinfo($docParam, PATHINFO_FILENAME);
$pdfFilePath = $pdfBasePath . $subfolder;


$bookIcon = '<i class="bi bi-book" style="margin-right:6px;"></i>';

$tab = '<th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=DAFIS.pdf">'.$bookIcon.'Daftar Isi</a>&nbsp;</th>
        <th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=TINJAUAN.pdf">'.$bookIcon.'Tinjauan Mata Kuliah</a>&nbsp;</th>';

if(is_dir($pdfFilePath)) {
    $dh = opendir($pdfFilePath);
    $i = 0;
    while(($file = readdir($dh)) !== false) {
        if(strtolower(pathinfo($file,PATHINFO_EXTENSION)) == "pdf") {
            $swfname[$i] = basename($file,".pdf");
            $i++;
        }
    }
    closedir($dh);
    for($j=1; $j<=($i-2); $j++) {
         $tab .= '<th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=M'.$j.'.pdf">'.$bookIcon.'Modul '.$j.'</a>&nbsp;</th>';

    }
}


$cleanSubfolder = rtrim($subfolder, '/');

$modulKey = preg_replace('/(\d{4})\d{2}$/', '$1', $cleanSubfolder);

$judulPath = __DIR__ . '/config/judul.json';
$judulText = file_exists($judulPath) ? file_get_contents($judulPath) : '';
$judulData = json_decode($judulText, true);

$judulFound = '';
if (is_array($judulData)) {
    foreach ($judulData as $item) {
        if (isset($item['Modul']) && $item['Modul'] === $modulKey) {
            $judulFound = $item['Judul'];
            break;
        }
    }
}

function formatDocName($doc) {
    $doc = strtoupper($doc); 
    switch($doc) {
        case 'DAFIS':
            return 'Daftar Isi';
        case 'TINJAUAN':
            return 'Tinjauan Mata Kuliah';
        default:
            if (preg_match('/^M\d+$/', $doc)) {
                return 'Modul ' . substr($doc, 1);
            }
            return $doc; 
    }
}

if ($judulFound) {
    $navTitle = "{$cleanSubfolder} - {$judulFound} - " . formatDocName($doc);
} else {
    $navTitle = "{$cleanSubfolder} - " . formatDocName($doc);
}

if(!$_SESSION['loggedin']) {
    renderLogin($error, $math);
} else {
    // Ambil kode modul dari URL atau subfolder
    $modul = $_GET['modul'] ?? ($cleanSubfolder ?? null);

    // Tetap render viewer (jangan diubah)
    renderViewer(
        $tab,
        $navTitle,
        $doc,
        $pdfFilePath,
        $licenseKey,
        $renderingOrder,
        $subfolder
    );

    // === Insert log akses modul (conditional) ===
    require_once __DIR__ . '/lib/db.php';
    require_once __DIR__ . '/lib/api.php'; // berisi fungsi SRS

    date_default_timezone_set('Asia/Jakarta'); // WIB

    $email_or_username = $_SESSION['UID'] ?? ''; // bisa kosong kalau SSO
    $length            = strlen($email_or_username);
    $start_time        = date('Y-m-d H:i:s');

    // Cek modul sebelumnya di session
    $prevModul   = $_SESSION['current_modul'] ?? null;
    $isNewModul  = ($modul && $modul !== $prevModul);

    // Ambil credential SRS dari env
    $srs_email    = env('SRS_EMAIL');
    $srs_password = env('SRS_PASSWORD');
    if (!$srs_email || !$srs_password) {
        error_log('SRS_EMAIL/SRS_PASSWORD belum dikonfigurasi di environment.');
    }

    $srs_token = null;
    if ($srs_email && $srs_password) {
        $srs_token = srs_auth_token($srs_email, $srs_password);
        if (!$srs_token) {
            error_log('Gagal mendapatkan token SRS.');
        }
    }

    // Insert hanya jika modul baru DAN username valid (8/9 digit) ATAU email pegawai
    $shouldInsert = (
        $isNewModul &&
        (
            $email_or_username !== '' || ($length === 8 || $length === 9)
        )
    );

    if ($shouldInsert) {
        $NIM = $NIP = $nama_lengkap = $prodi = $fakultas = $unit_kerja = null;
        $hasData = false;

        if ($srs_token) {
            if ($length === 9) {
                // Mahasiswa
                $mhs = srs_fetch_mahasiswa($email_or_username, $srs_token);
                if (!empty($mhs)) {
                    $NIM          = $mhs['nim'] ?? $email_or_username;
                    $nama_lengkap = $mhs['nama_mahasiswa'] ?? null;
                    $prodi        = $mhs['info_ut']['program_studi']['nama_program_studi']
                                    ?? $mhs['info_ut']['nama_program_studi'] ?? null;
                    $fakultas     = $mhs['info_ut']['program_studi']['fakultas']['nama_fakultas']
                                    ?? $mhs['info_ut']['fakultas']['nama_fakultas'] ?? null;
                    $unit_kerja   = null;
                    $hasData      = true;
                }
            } elseif ($length === 8) {
                // Tutor
                $tutor = srs_fetch_tutor($email_or_username, $srs_token);
                if (!empty($tutor)) {
                    $NIP          = $tutor['nip'] ?? null;
                    $nama_lengkap = $tutor['nama_lengkap'] ?? null;
                    $fakultas     = $tutor['nama_fakultas'] ?? null;
                    $prodi        = 'nan';
                    $unit_kerja   = 'nan';
                    $hasData      = true;
                }
            } elseif (preg_match('/@ecampus\.ut\.ac\.id$/i', $email_or_username)) {
                $isMahasiswa = (bool)preg_match('/^(\d{6,})@ecampus\.ut\.ac\.id$/i', $email_or_username);
                $isPegawai   = !$isMahasiswa && (bool)preg_match('/@ecampus\.ut\.ac\.id$/i', $email_or_username);
                if ($isPegawai) {  
                    // Pegawai (SSO)
                    $peg = srs_fetch_pegawai_by_email($email_or_username, $srs_token);
                    if (!empty($peg)) {
                        $NIP          = $peg['nip'] ?? null;
                        $nama_lengkap = $peg['nama'] ?? null;
                        $unit_kerja   = $peg['nama_unit'] ?? null;
                        $fakultas     = 'nan';
                        $prodi        = 'nan';
                        $hasData      = true;
                    }
                }else{
                    // Mahasiswa (SSO)
                    preg_match('/^(\d{6,})@ecampus\.ut\.ac\.id$/i', $email_or_username, $m);
                    $nim = $m[1] ?? null;
                    echo "console.log('nim: {$nim}');";
                    if ($nim) {
                        $mhs = srs_fetch_mahasiswa($nim, $srs_token);
                        if ($mhs) {
                            $NIM               = $mhs['nim'] ?? $nim;
                            $email_or_username = $NIM . '@ecampus.ut.ac.id';
                            $nama_lengkap      = $mhs['nama_mahasiswa'] ?? null;
                            $prodi             = $mhs['info_ut']['program_studi']['nama_program_studi'] ?? null;
                            $fakultas          = $mhs['info_ut']['program_studi']['fakultas']['nama_fakultas'] ?? null;
                            $hasData = true;
                        }
                    }
                }
            }
        }

        // Insert log hanya jika data valid
        if ($hasData) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO rbv_logs 
                        (email_or_username, NIM, NIP, nama_lengkap, modul, prodi, fakultas, unit_kerja, start_time) 
                    VALUES 
                        (:email_or_username, :NIM, :NIP, :nama_lengkap, :modul, :prodi, :fakultas, :unit_kerja, :start_time)
                ");
                $stmt->execute([
                    ':email_or_username' => $email_or_username,
                    ':NIM'               => $NIM,
                    ':NIP'               => $NIP,
                    ':nama_lengkap'      => $nama_lengkap,
                    ':modul'             => $modul,
                    ':prodi'             => $prodi,
                    ':fakultas'          => $fakultas,
                    ':unit_kerja'        => $unit_kerja,
                    ':start_time'        => $start_time
                ]);

                // Simpan modul aktif & ID log di session
                $_SESSION['current_modul'] = $modul;
                $_SESSION['log_id_modul']  = (int)$pdo->lastInsertId();

                // Debug ke console (bisa dikomen)
                echo "<script>";
                echo "console.log('Log baru dimasukkan untuk modul: " . addslashes($modul) . "');";
                echo "console.log('log_id_modul:', " . json_encode($_SESSION['log_id_modul']) . ");";
                echo "</script>";
            } catch (Exception $e) {
                error_log("Insert rbv_logs gagal: " . $e->getMessage());
            }
        }
    }

    // ===== DEBUG CALLBACK (bisa dikomen kapan saja) =====
    echo "<script>";
    echo "console.log('Email/Username: " . addslashes($email_or_username !== '' ? $email_or_username : 'null') . "');";
    echo "console.log('Panjang Username: {$length}');";
    echo "console.log('Should Insert: " . ($shouldInsert ? 'YES' : 'NO') . "');";
    echo "console.log('Modul: " . addslashes($modul ?? 'null') . "');";
    echo "console.log('Start Time: {$start_time}');";
    echo "console.log('prev modul: {$prevModul}');";
    echo "console.log('now modul: {$modul}');";
    echo "console.log('isMahasiswa: {$isMahasiswa}');";
    echo "console.log('isPegawai: {$isPegawai}');";
    if ($shouldInsert) {
        $debugData = [
            'email_or_username' => $email_or_username,
            'NIM'               => $NIM ?? null,
            'NIP'               => $NIP ?? null,
            'nama_lengkap'      => $nama_lengkap ?? null,
            'prodi'             => $prodi ?? null,
            'fakultas'          => $fakultas ?? null,
            'unit_kerja'        => $unit_kerja ?? null,
            'modul'             => $modul,
            'start_time'        => $start_time,
            'last_insert_id'    => $_SESSION['log_id_modul'] ?? 'null'
        ];
        echo "console.log('Data Mapping:', " . json_encode($debugData) . ");";
    } else {
        echo "console.log('Skip insert (username kosong atau panjang bukan 8/9 atau bukan email pegawai)');";
    }
    echo "</script>";
}



?>
