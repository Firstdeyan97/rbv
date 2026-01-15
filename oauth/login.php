<?php
// --- login.php ---
$modul = isset($_GET['modul']) ? trim($_GET['modul']) : '';

$client_id     = '0b6f20f2-55b3-4f11-ae91-cfbddcce48a7';
$tenant_id     = '508916a0-7b89-43a1-af4e-72fe15aba5b9';
$redirect_uri  = 'https://pustaka.ut.ac.id/reader/oauth/callback.php';
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
