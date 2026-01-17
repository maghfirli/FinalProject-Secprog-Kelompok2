<?php
require "connection.php";
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;

$email = trim($_POST['email']);

$stmt = $con->prepare("SELECT UserID, userName FROM msuser WHERE userEmail = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../component/forgot.php?error=Email tidak ditemukan");
    exit;
}

$token  = bin2hex(random_bytes(32));
$expire = date("Y-m-d H:i:s", strtotime('+3 minutes'));

$upd = $con->prepare("
    UPDATE msuser
    SET resetToken = ?, resetExpiry = ?
    WHERE UserID = ?
");
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

$mail->Subject = "Reset Password (Berlaku 3 Menit)";
$mail->Body = "
<p>Halo, {$user['userName']}!</p>
<p>Klik link berikut untuk reset password (berlaku 3 menit):</p>
<p><a href='$link'>$link</a></p>
";

$mail->send();

header("Location: ../component/forgot_password.php?msg=Silakan cek email kamu untuk reset password.");
exit;
