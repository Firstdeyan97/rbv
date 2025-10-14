<?php
// pubkey.php - serve public key for client-side encryption
header('Content-Type: text/plain; charset=utf-8');
// adjust path jika keys di lokasi lain
$pubPath = __DIR__ . '/keys/public.pem';
if (!file_exists($pubPath)) {
    http_response_code(404);
    echo "public key not found";
    exit;
}
echo file_get_contents($pubPath);
