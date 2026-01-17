<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout = 1800; 
if (isset($_SESSION['LAST_ACTIVE']) && time() - $_SESSION['LAST_ACTIVE'] > $timeout) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVE'] = time();

date_default_timezone_set("Asia/Jakarta");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "secprogasg";

$con = @new mysqli($host, $user, $pass, $db);


if ($con->connect_errno) {
    die("Koneksi database gagal.");
}
?>
