<?php
require_once __DIR__ . '/../config/env.php';
// --- login.php ---
$modul = isset($_GET['modul']) ? trim($_GET['modul']) : '';

$client_id     = env('AZURE_CLIENT_ID');
$client_secret = env('AZURE_CLIENT_SECRET');
$tenant_id     = env('AZURE_TENANT_ID');
$redirect_uri  = env('AZURE_REDIRECT_URI');
$scope         = 'openid profile email User.Read';

// Simpan modul di state (dalam bentuk base64 JSON)
$state = base64_encode(json_encode(['modul' => $modul]));

// URL login Microsoft
$auth_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize?" . http_build_query([
    'client_id'     => $client_id,
    'response_type' => 'code',
    'redirect_uri'  => $redirect_uri,
    'response_mode' => 'query',
    'scope'         => $scope,
    'state'         => $state
]);

// Redirect user ke Microsoft login
header("Location: $auth_url");
exit;
