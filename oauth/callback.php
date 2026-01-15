<?php
/**
 * oauth/callback.php
 * SSO Microsoft O365 UT → Ambil email → Enrich profil via SRS → Insert ke rbv_logs → Redirect ke viewer
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// ob_start();

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/api.php';

// ===== 1) Konfigurasi OAuth Microsoft =====
$client_id     = env('AZURE_CLIENT_ID');
$client_secret = env('AZURE_CLIENT_SECRET');
$tenant_id     = env('AZURE_TENANT_ID');
$redirect_uri  = env('AZURE_REDIRECT_URI');

// ===== 2) Ambil kode dari Microsoft =====
if (empty($_GET['code'])) {
    http_response_code(400);
    die("Kode autentikasi tidak ditemukan.");
}

// ===== 3) Decode state =====
$modul = '';
if (!empty($_GET['state'])) {
    $decoded = json_decode(base64_decode($_GET['state']), true);
    if (isset($decoded['modul'])) {
        $modul = trim($decoded['modul']);
    }
}

// ===== 4) Tukar code dengan access token =====
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
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($token_data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20
]);
$response = curl_exec($ch);
$curlErr  = curl_error($ch);
curl_close($ch);


if (!$response) {
    http_response_code(502);
    die("Gagal mendapatkan token akses: $curlErr");
}

$token = json_decode($response, true);
if (empty($token['access_token'])) {
    http_response_code(502);
    die("Gagal mendapatkan token akses.");
}

$user_info_url = "https://graph.microsoft.com/v1.0/me";
$ch = curl_init($user_info_url);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER     => ["Authorization: Bearer " . $token['access_token']],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20
]);
$user_response = curl_exec($ch);
$curlErrUser   = curl_error($ch);
curl_close($ch);

if (!$user_response) {
    http_response_code(502);
    die("Gagal membaca data pengguna dari Graph: $curlErrUser");
}

$user = json_decode($user_response, true);
if (empty($user['userPrincipalName'])) {
    http_response_code(502);
    die("Gagal membaca userPrincipalName dari Graph.");
}

$emailGraph   = strtolower(trim($user['userPrincipalName']));
$displayName  = $user['displayName'] ?? '';

$isMahasiswa = (bool)preg_match('/^(\d{6,})@ecampus\.ut\.ac\.id$/i', $emailGraph);
$isPegawai   = !$isMahasiswa && (bool)preg_match('/@ecampus\.ut\.ac\.id$/i', $emailGraph);

echo "<script>";
echo "console.log('isMahasiswa:', " . json_encode($isMahasiswa) . ");";
echo "console.log('isPegawai:', " . json_encode($isPegawai) . ");";
echo "</script>";

$srs_email    = env('SRS_EMAIL');
$srs_password = env('SRS_PASSWORD');
$srs_token    = null;
if ($srs_email && $srs_password) {
    $srs_token = srs_auth_token($srs_email, $srs_password);
}

date_default_timezone_set('Asia/Jakarta');
$start_time        = date('Y-m-d H:i:s');
$email_or_username = $emailGraph;
$NIM = $NIP = $nama_lengkap = $prodi = $fakultas = $unit_kerja = null;

if ($isMahasiswa && $srs_token) {
    preg_match('/^(\d{6,})@ecampus\.ut\.ac\.id$/i', $emailGraph, $m);
    $nim = $m[1] ?? null;
   

    if ($nim) {
        $mhs = srs_fetch_mahasiswa($nim, $srs_token);
        echo "<script>console.log('NIM parsed:', " . json_encode($nim) . ");</script>";
        echo "<script>console.log('Mahasiswa API Response:', " . json_encode($mhs) . ");</script>";

        if (!empty($mhs)) {
            
            $NIM               = $mhs['nim'] ?? $nim;
            $email_or_username = $NIM . '@ecampus.ut.ac.id';
            $nama_lengkap      = $mhs['nama_mahasiswa'] ?? null;

           
            $prodi    = $mhs['info_ut']['program_studi']['nama_program_studi']
                        ?? $mhs['info_ut']['nama_program_studi']
                        ?? null;
            $fakultas = $mhs['info_ut']['program_studi']['fakultas']['nama_fakultas']
                        ?? $mhs['info_ut']['fakultas']['nama_fakultas']
                        ?? null;

            echo "<script>console.log('Mapping Mahasiswa:', " . json_encode([
                'NIM'          => $NIM,
                'nama_lengkap' => $nama_lengkap,
                'prodi'        => $prodi,
                'fakultas'     => $fakultas
            ]) . ");</script>";
        }
    }
} elseif ($isPegawai && $srs_token) {
    $peg = srs_fetch_pegawai_by_email($emailGraph, $srs_token);
    echo "<script>console.log('Pegawai API Response:', " . json_encode($peg) . ");</script>";
    if ($peg) {
        $NIP          = $peg['nip'] ?? null;
        $nama_lengkap = $peg['nama'] ?? null;
        $unit_kerja   = $peg['nama_unit'] ?? null;
        $fakultas     = 'nan';
        $prodi        = 'nan';
    }
}

$hasData = ($isMahasiswa && !empty($mhs)) || ($isPegawai && !empty($peg));
echo "<script>console.log('HasData:', " . json_encode($hasData) . ");</script>";

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
        $_SESSION['log_id'] = (int)$pdo->lastInsertId();
        echo "<script>console.log('Insert rbv_logs success, ID:', " . json_encode($_SESSION['log_id']) . ");</script>";
    } catch (Exception $e) {
        error_log("Insert rbv_logs gagal: " . $e->getMessage());
        echo "<script>console.log('Insert rbv_logs gagal:', " . json_encode($e->getMessage()) . ");</script>";
    }
} else {
    echo "<script>console.log('Skip insert: API tidak ada data, tidak insert ke rbv_logs');</script>";
}

// ===== 11) Simpan session login aplikasi =====
$_SESSION['loggedin']      = true;
$_SESSION['flash_ut']      = $_SERVER["HTTP_HOST"];
$_SESSION['flash_ref']     = $_SERVER['HTTP_REFERER'] ?? '';
$_SESSION['UID']           = $email_or_username;
$_SESSION['displayName']   = $displayName ?: ($nama_lengkap ?: 'Mahasiswa/Pegawai');
$_SESSION['current_modul'] = $modul;

echo "<script>";
echo "console.log('Session UID:', " . json_encode($_SESSION['UID']) . ");";
echo "console.log('Session displayName:', " . json_encode($_SESSION['displayName']) . ");";
echo "console.log('Session current_modul:', " . json_encode($_SESSION['current_modul']) . ");";
echo "</script>";

// ===== 12) Redirect kembali ke viewer =====
$redirect_url = "https://pustaka.ut.ac.id/reader/index.php";
if (!empty($modul)) {
     $redirect_url .= "?modul=" . urlencode($modul);
}
echo "<script>";
echo "console.log('Redirect URL:', " . json_encode($redirect_url) . ");";
echo "</script>";

header("Location: $redirect_url");
// ob_end_flush();
exit;
