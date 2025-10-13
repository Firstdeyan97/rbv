<?php
/**
 * Load environment variables from .env file
 */

$envPath = __DIR__ . '/.env';

// Jika tidak ditemukan di lokasi utama, coba lokasi saat ini
if (!file_exists($envPath)) {
    $envPath = __DIR__ . '/.env';
}

if (!file_exists($envPath)) {
    die("⚠️ File .env tidak ditemukan di: $envPath");
}

// Parse file .env
$env = parse_ini_file($envPath);
foreach ($env as $key => $value) {
    putenv("$key=$value");
}


if (!$env) {
    die("❌ Gagal membaca isi file .env di: $envPath");
}

/**
 * Helper untuk ambil nilai ENV
 */
function env($key, $default = null) {
    $val = getenv($key);
    return $val !== false ? $val : $default;
}
