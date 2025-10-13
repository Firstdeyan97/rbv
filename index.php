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

// ----- CAPTCHA -----
$n1 = rand(1,6);
$n2 = rand(5,9);
$answer = $n1 + $n2;
$_SESSION['vercode'] = $answer;
$math = "Berapa hasil dari <strong>{$n1} + {$n2}</strong> = ";

// ----- HANDLE LOGIN SUBMIT -----
$error = '';
if(isset($_POST['_submit_check'])) {
    $url = sprintf(
        'https://elearning.ut.ac.id/login/token.php?username=%s&password=%s&service=moodle_mobile_app',
        rawurlencode($_POST['username']),
        rawurlencode($_POST['password'])
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $final = json_decode($result);

    if(!empty($final->token)) {
        $_SESSION['loggedin'] = true;
        $_SESSION['UID'] = $_POST['username'] . time();
    } else {
        $_SESSION['loggedin'] = false;
        $error = 'Username dan password tidak ditemukan!';
    }

    // CAPTCHA check
    if($_POST['ccaptcha'] != $_SESSION['vercode']) {
        $_SESSION['loggedin'] = false;
        $error = 'Perhitungan captcha salah!';
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
$tab = '<th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=DAFIS.pdf">[Daftar Isi]</a>&nbsp;</th>
        <th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=TINJAUAN.pdf">[Tinjauan Mata Kuliah]</a>&nbsp;</th>';

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
        $tab .= '<th>&nbsp;<a href="index.php?subfolder='.$subfolder.'&doc=M'.$j.'.pdf">[Modul '.$j.']</a>&nbsp;</th>';
    }
}

// ----- NAV TITLE -----
$navTitle = rtrim($subfolder,'/') . ' - ' . strtoupper($doc);

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
