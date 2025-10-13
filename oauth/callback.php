<?php

// --- callback.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../config/env.php';

// ===== Konfigurasi OAuth =====
$client_id     = env('AZURE_CLIENT_ID');
$client_secret = env('AZURE_CLIENT_SECRET');
$tenant_id     = env('AZURE_TENANT_ID');
$redirect_uri  = env('AZURE_REDIRECT_URI');

// ===== Ambil kode dari Microsoft =====
if (empty($_GET['code'])) {
    die("Kode autentikasi tidak ditemukan.");
}

// ===== Decode state (ambil modul yang dikirim dari login.php) =====
$modul = '';
if (!empty($_GET['state'])) {
    $decoded = json_decode(base64_decode($_GET['state']), true);
    if (isset($decoded['modul'])) {
        $modul = trim($decoded['modul']);
    }
}

// ===== Tukar code dengan access token =====
$token_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
$token_data = [
    'client_id'     => $client_id,
    'scope'         => 'openid profile email User.Read',
    'code'          => $_GET['code'],
    'redirect_uri'  => $redirect_uri,
    'grant_type'    => 'authorization_code',
    'client_secret' => $client_secret
];

$ch = curl_init($token_url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($token_data),
    CURLOPT_RETURNTRANSFER => true
]);
$response = curl_exec($ch);
curl_close($ch);

$token = json_decode($response, true);

if (empty($token['access_token'])) {
    echo "<pre>=== DEBUG TOKEN ===\n$response\n==================</pre>";
    die("Gagal mendapatkan token akses.");
}

// ===== Ambil profil pengguna =====
$user_info_url = "https://graph.microsoft.com/v1.0/me";
$ch = curl_init($user_info_url);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $token['access_token']],
    CURLOPT_RETURNTRANSFER => true
]);
$user_response = curl_exec($ch);
curl_close($ch);

$user = json_decode($user_response, true);

if (empty($user['userPrincipalName'])) {
    die("Gagal membaca data pengguna.");
}


// ===== Simpan session login =====
$_SESSION['loggedin']  = true;
$_SESSION['flash_ut'] = $_SERVER["HTTP_HOST"];
$_SESSION['flash_ref']= $_SERVER['HTTP_REFERER'];
$_SESSION['UID']       = $email;
$_SESSION['displayName'] = $user['displayName'] ?? 'Mahasiswa';


// ===== Redirect kembali ke viewer =====
$redirect_url = "https://pustaka.ut.ac.id/readerdev/index.php";
if (!empty($modul)) {
    $redirect_url .= "?modul=" . urlencode($modul);
}

// Debug sementara (opsional, aktifkan kalau mau cek alur)
if (isset($_GET['debug'])) {
    echo "<pre>DEBUG:
Modul: " . htmlspecialchars($modul) . "
Email: " . htmlspecialchars($email) . "
Redirect: " . htmlspecialchars($redirect_url) . "
</pre>";
    exit;
}

// Redirect
header("Location: $redirect_url");
exit;
