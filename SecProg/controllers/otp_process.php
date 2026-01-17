<?php
require "connection.php";

if (!isset($_SESSION['pending_email'])) {
    header("Location: ../component/login.php?error=Sesi OTP tidak ditemukan");
    exit;
}

$email = $_SESSION['pending_email'];
$otp   = trim($_POST['otp']);

$stmt = $con->prepare("
    SELECT * FROM msuser
    WHERE userEmail = ? AND otpCode = ? AND otpExpiry >= NOW()
    LIMIT 1
");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../component/otp.php?error=OTP salah atau kadaluarsa");
    exit;
}

$upd = $con->prepare("UPDATE msuser SET otpCode = NULL, otpExpiry = NULL WHERE UserID = ?");
$upd->bind_param("i", $user['UserID']);
$upd->execute();

$_SESSION['is_login']  = true;
$_SESSION['userID']    = $user['UserID'];
$_SESSION['userEmail'] = $user['userEmail'];
$_SESSION['userName']  = $user['userName'];
$_SESSION['userRole']  = $user['userRole'];

unset($_SESSION['pending_email']);

if ($user['userRole'] === 'admin') {
    header("Location: ../component/admin/dashboard.php");
} else {
    header("Location: ../component/user/dashboard.php");
}
exit;
