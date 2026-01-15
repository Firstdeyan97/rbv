<?php
// lib/srs.php
// Kumpulan fungsi untuk akses API SRS (auth, mahasiswa, tutor)

require_once __DIR__ . '/../config/env.php'; // fungsi env()
require_once __DIR__ . '/../lib/db.php';     // $pdo (PDO MySQL)

$srs_email    = env('SRS_EMAIL');    // contoh: pusaka@ecampus.ut.ac.id
$srs_password = env('SRS_PASSWORD'); // contoh: PU5l4T@!!4rfv25!
if (!$srs_email || !$srs_password) {
    // Tetap izinkan login, tapi insert log dengan data minimum
    error_log('SRS_EMAIL/SRS_PASSWORD belum dikonfigurasi di environment.');
}


// ===== 8) Fungsi helper SRS (Auth + Fetch) =====
function srs_auth_token(string $email, string $password): ?string {
    $url = 'https://api-mahasiswa-srs.ut.ac.id/api-srs-mahasiswa/v1/auth';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode(['email' => $email, 'password' => $password]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    if (!$res) return null;
    $data = json_decode($res, true);
    if (!is_array($data) || empty($data['status'])) return null;
    return $data['token'] ?? null;
}

function srs_fetch_mahasiswa(string $nim, string $token): ?array {
    $url = "https://api-mahasiswa-srs.ut.ac.id/api-srs-mahasiswa/v1/data-pribadi/" . rawurlencode($nim);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    // echo "<script>";
    // echo "console.log('Mahasiswa API Request URL:', " . json_encode($url) . ");";
    // echo "console.log('Mahasiswa API Raw Response:', " . json_encode($res) . ");";
    // echo "</script>";

    if (!$res) return null;

    $data = json_decode($res, true);

    // echo "<script>";
    // echo "console.log('Mahasiswa API Decoded:', " . json_encode($data) . ");";
    // echo "</script>";

    // Perbaikan: data adalah object, bukan array
    return (!empty($data['status']) && !empty($data['data'])) ? $data['data'] : null;
}



function srs_fetch_pegawai_by_email(string $email, string $token): ?array {
    $url = "https://api-mahasiswa-srs.ut.ac.id/api-srs-mahasiswa/v1/hris/pegawai-aktif?email=" . rawurlencode($email);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    // === DEBUG OUTPUT ===
    // echo "<pre>DEBUG Pegawai API Request URL: $url</pre>";
    // echo "<pre>DEBUG Pegawai API Raw Response:\n$res\n</pre>";

    if (!$res) return null;

    $data = json_decode($res, true);
    // echo "<pre>DEBUG Pegawai API Decoded:\n";
    // print_r($data);
    // echo "</pre>";

    return (!empty($data['status']) && !empty($data['data'][0])) ? $data['data'][0] : null;
}

function srs_fetch_tutor($id_tutor, $token) {
    $url = "https://api-mahasiswa-srs.ut.ac.id/api-srs-mahasiswa/v1/tutor?id_tutor=" . urlencode($id_tutor);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    if (!$res) {
        echo "<script>console.log('Tutor API: no response');</script>";
        return null;
    }

    $data = json_decode($res, true);

    // === DEBUG OUTPUT ke console ===
    // echo "<script>";
    // echo "console.log('Tutor API Request URL: " . addslashes($url) . "');";
    // echo "console.log('Tutor API Raw Response:', " . json_encode($res) . ");";
    // echo "console.log('Tutor API Decoded:', " . json_encode($data) . ");";
    // echo "</script>";

    return (!empty($data['status']) && !empty($data['data'][0])) ? $data['data'][0] : null;
}