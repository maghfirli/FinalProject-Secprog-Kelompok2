<?php
require "connection.php";
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("Location: ../component/login.php");
    exit;
}

$email = trim($_POST['email']);
$pass  = $_POST['password'];

$stmt = $con->prepare("SELECT * FROM msuser WHERE userEmail = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../component/login.php?error=Email tidak terdaftar");
    exit;
}

if (!password_verify($pass, $user['userPassword'])) {
    header("Location: ../component/login.php?error=Password salah");
    exit;
}

if ($user['userRole'] === 'user') {
    if ((int)$user['isVerified'] === 0) {
        header("Location: ../component/login.php?error=Email belum diverifikasi");
        exit;
    }
    if ((int)$user['isApproved'] === 0) {
        header("Location: ../component/login.php?error=Akun belum di-approve admin");
        exit;
    }
}

$otp    = random_int(100000, 999999);
$expire = date("Y-m-d H:i:s", time() + 300);

$upd = $con->prepare("UPDATE msuser SET otpCode = ?, otpExpiry = ? WHERE UserID = ?");
$upd->bind_param("ssi", $otp, $expire, $user['UserID']);
$upd->execute();

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = "smtp.gmail.com";
$mail->SMTPAuth   = true;
$mail->Username   = "warehouseapp10@gmail.com";
$mail->Password   = "ogythbjdudgzolze";
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;

$mail->setFrom("warehouseapp10@gmail.com", "SecProg OTP");
$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = "Kode OTP Login SecProg";
$mail->Body    = "<p>Kode OTP kamu:</p><h2>$otp</h2><p>Berlaku 5 menit.</p>";
$mail->send();

$_SESSION['pending_email'] = $email;

header("Location: ../component/otp.php");
exit;
