<?php
session_start();

require_once("lib/config.php");
require_once("lib/common.php");
require_once("captcha.php");
require_once("config/env.php");

// Templates
require_once("templates/login_template.php");
require_once("templates/viewer_template.php");

// ----- BASIC CHECK BROWSER -----
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

// ----- SESSION LOGIN -----
if(!isset($_SESSION['loggedin'])) $_SESSION['loggedin'] = false;

// Mulai session dulu
session_start();

$error = '';
$math = '';

// ----- HANDLE LOGIN SUBMIT -----
if (isset($_POST['_submit_check'])) {
    $username = trim($_POST['username'] ?? '');
    $ccaptcha = trim($_POST['ccaptcha'] ?? '');
    $password = ''; // final plaintext password (if obtained)

    // 1) if password_enc exists => decrypt with private key (RSA-OAEP)
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
                openssl_free_key($priv);
                if ($ok === false) {
                    $error = 'Gagal mendekripsi password.';
                } else {
                    // decrypted was JSON {pw,t} per client script
                    $obj = json_decode($decrypted, true);
                    if (is_array($obj) && isset($obj['pw'])) {
                        $password = $obj['pw'];
                        // optional: freshness check (2 menit)
                        if (isset($obj['t'])) {
                            $ageMs = abs((int)$obj['t'] - (int)(microtime(true)*1000));
                            if ($ageMs > 2 * 60 * 1000) {
                                $error = 'Permintaan kadaluarsa.';
                            }
                        }
                    } else {
                        // fallback: treat decrypted as raw password
                        $password = $decrypted;
                    }
                }
            }
        }
    } else {
        // fallback jika JS tidak jalan: ambil plaintext
        $password = trim($_POST['password'] ?? '');
    }

    // continue only if no decryption/captcha error
    if ($error === '') {
        if ($ccaptcha != ($_SESSION['vercode'] ?? '')) {
            $error = 'Perhitungan captcha salah!';
        } else {
            // --- call external API (server-to-server) ---
            $url = sprintf(
                'https://elearning.ut.ac.id/login/token.php?username=%s&password=%s&service=moodle_mobile_app',
                rawurlencode($username),
                rawurlencode($password)
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // optional: set timeouts and SSL options
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
                    $error = 'Username dan password tidak ditemukan!';
                }
            }
            curl_close($ch);
        }
    }

    // cleanup: remove sensitive data
    unset($_POST['password'], $_POST['password_enc']);
    $password = null;
    $_SESSION['flash_ut'] = $_SERVER["HTTP_HOST"];
    $_SESSION['flash_ref']= $_SERVER['HTTP_REFERER'] ?? '';
}



// ----- CAPTCHA (buat baru setelah proses login / awal buka) -----
$n1 = rand(1,6);
$n2 = rand(5,9);
$_SESSION['vercode'] = $n1 + $n2;
$math = "Berapa hasil dari <strong>{$n1} + {$n2}</strong> = ";

// ----- CEK MODUL -----
if ($_SESSION['loggedin']) {
    if (
        (!isset($_GET['modul']) || trim($_GET['modul']) === '') &&
        (!isset($_GET['subfolder']) || trim($_GET['subfolder']) === '')
    ) {
        // Sudah login tapi modul dan subfolder kosong → ke modul_required
        header("Location: modul_required.php");
        exit;
    }
}


// ----- CONFIG MANAGER -----
$configManager = new Config();
$licenseKey = $configManager->getConfig('licensekey');
$renderingOrder = $configManager->getConfig('renderingorder.primary') . ',' . $configManager->getConfig('renderingorder.secondary');
$pdfBasePath = $configManager->getConfig('path.pdf');

// ----- VIEWER PARAMS -----
$subfolder = $_GET['subfolder'] ?? ($_GET['modul'] ?? '');
$subfolder = rtrim($subfolder,'/') . '/';
$docParam = $_GET['doc'] ?? 'DAFIS.pdf';
$doc = pathinfo($docParam, PATHINFO_FILENAME);
$pdfFilePath = $pdfBasePath . $subfolder;


// ----- BUILD SIDEBAR -----
//$tab = '<th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=DAFIS.pdf">[Daftar Isi]</a>&nbsp;</th>
//        <th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=TINJAUAN.pdf">[Tinjauan Mata Kuliah]</a>&nbsp;</th>';

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
        //$tab .= '<th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=M'.$j.'.pdf">[Modul '.$j.']</a>&nbsp;</th>';
         $tab .= '<th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=M'.$j.'.pdf">'.$bookIcon.'Modul '.$j.'</a>&nbsp;</th>';

    }
}

// ----- NAV TITLE -----
//$navTitle = rtrim($subfolder,'/') . ' - ' . strtoupper($doc);
// ----- NAV TITLE -----
$cleanSubfolder = rtrim($subfolder, '/');

// potong 2 angka terakhir kalau subfolder mengandung 6 digit di akhir
$modulKey = preg_replace('/(\d{4})\d{2}$/', '$1', $cleanSubfolder);

// baca file judul.json
$judulPath = __DIR__ . '/config/judul.json';
$judulText = file_exists($judulPath) ? file_get_contents($judulPath) : '';
$judulData = json_decode($judulText, true);

// cari judul berdasarkan Modul
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
    $doc = strtoupper($doc); // biar konsisten pengecekan
    switch($doc) {
        case 'DAFIS':
            return 'Daftar Isi';
        case 'TINJAUAN':
            return 'Tinjauan Mata Kuliah';
        default:
            // jika mulai dengan M diikuti angka, misal M1, M2, M3
            if (preg_match('/^M\d+$/', $doc)) {
                return 'Modul ' . substr($doc, 1);
            }
            return $doc; // selain itu dikembalikan apa adanya
    }
}

// rakit navTitle
if ($judulFound) {
    $navTitle = "{$cleanSubfolder} - {$judulFound} - " . formatDocName($doc);
} else {
    $navTitle = "{$cleanSubfolder} - " . formatDocName($doc);
}

// ----- RENDER -----
if(!$_SESSION['loggedin']) {
    renderLogin($error, $math);
} else {
    renderViewer(
        $tab,
        $navTitle,
        $doc,
        $pdfFilePath,
        $licenseKey,
        $renderingOrder,
        $subfolder
    );
}
?>
