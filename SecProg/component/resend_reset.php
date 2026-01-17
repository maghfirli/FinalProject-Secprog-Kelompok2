<?php
session_start();
require "../controllers/connection.php";
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;

if (empty($_SESSION['reset_email']) && isset($_GET['token'])) {

    $token = $_GET['token'];

    $stmt = $con->prepare("SELECT userEmail FROM msuser WHERE resetToken=? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
        $_SESSION['reset_email'] = $row['userEmail'];
    }
}

if (empty($_SESSION['reset_email'])) {
    die("
        <h2>Reset gagal</h2>
        <a href='forgot_password.php'>Reset dari awal</a>
    ");
}

$email = $_SESSION['reset_email'];

$stmt = $con->prepare("SELECT UserID, userName FROM msuser WHERE userEmail=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Email tidak ditemukan.<br><a href='forgot_password.php'>Reset ulang</a>");
}

$token = bin2hex(random_bytes(32));
$expire = date("Y-m-d H:i:s", strtotime('+1 minutes'));

$upd = $con->prepare("UPDATE msuser SET resetToken=?, resetExpiry=? WHERE UserID=?");
$upd->bind_param("ssi", $token, $expire, $user['UserID']);
$upd->execute();

$link = "http://localhost/SecProg/component/reset_password.php?token=$token";

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host       = "smtp.gmail.com";
$mail->SMTPAuth   = true;
$mail->Username   = "warehouseapp10@gmail.com";
$mail->Password   = "ogythbjdudgzolze";
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;

$mail->setFrom("warehouseapp10@gmail.com", "SecProg Reset Password");
$mail->addAddress($email);
$mail->isHTML(true);

$mail->Subject = "Link Reset Password Baru (1 Menit)";
$mail->Body = "
    <p>Halo, {$user['userName']}!</p>
    <p>Ini link reset password baru (berlaku 1 menit):</p>
    <p><a href='$link'>$link</a></p>
";

$mail->send();

unset($_SESSION['reset_email']);

echo "
<!DOCTYPE html>
<html>
<head>
<style>
body {
    background:#050A24;
    color:white;
    font-family:Poppins;
    text-align:center;
    padding-top:80px;
}
.box {
    display:inline-block;
    padding:30px 40px;
    background:rgba(255,255,255,0.12);
    border-radius:15px;
    border:1px solid rgba(255,255,255,0.15);
}
a { color:#00A8FF; }
</style>
</head>
<body>

<div class='box'>
    <h2>Link Baru Telah Dikirim</h2>
    <p>Silakan cek email Anda.</p>
    <a href='login.php'>Kembali ke Login</a>
</div>

</body>
</html>
";
