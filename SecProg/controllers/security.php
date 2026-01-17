<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$fp = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
if (!isset($_SESSION['fingerprint'])) {
    $_SESSION['fingerprint'] = $fp;
} else if ($_SESSION['fingerprint'] !== $fp) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?error=Sesi tidak valid!");
    exit;
}

$timeout = 1800;
if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../login.php?error=Sesi timeout!");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
