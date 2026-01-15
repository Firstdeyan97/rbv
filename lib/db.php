<?php
$dsn = "mysql:host=localhost;dbname=dbcpust;charset=utf8mb4";
$user = "cpust";
$pass = "DX1lOHNgmgAE6F0L";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$pdo = new PDO($dsn, $user, $pass, $options);
