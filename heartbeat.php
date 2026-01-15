<?php
session_start();
require_once __DIR__ . '/lib/db.php';

if (isset($_SESSION['log_id_modul'])) {
    $stmt = $pdo->prepare("UPDATE rbv_logs 
        SET last_seen = NOW() 
        WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['log_id_modul']]);
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'no_log']);
}
