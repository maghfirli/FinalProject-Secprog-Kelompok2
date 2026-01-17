<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login($role = null) {
    if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true) {
        header("Location: ../login.php?error=Silakan login terlebih dahulu!");
        exit;
    }

    if ($role && isset($_SESSION['userRole'])) {
        $currentRole = strtolower($_SESSION['userRole']);
        if ($currentRole !== strtolower($role)) {
            header("Location: ../login.php?error=Akses ditolak! Role tidak sesuai.");
            exit;
        }
    }
}

function is_logged_in() {
    return isset($_SESSION['is_login']) && $_SESSION['is_login'] === true;
}
?>
