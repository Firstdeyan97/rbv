<?php
function requireLogin() {
    if (empty($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }
}
