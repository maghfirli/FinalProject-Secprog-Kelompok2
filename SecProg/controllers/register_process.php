<?php
require "connection.php";
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("Location: ../component/register.php");
    exit;
}

$name  = trim($_POST['name']);
$email = trim($_POST['email']);
$pass  = $_POST['password'];


if (!preg_match("/@gmail\.com$/", $email)) {
    header("Location: ../component/register.php?error=Gunakan email Gmail!");
    exit;
}


if (strlen($pass) < 8 ||
    !preg_match('/[A-Z]/', $pass) ||     
    !preg_match('/[a-z]/', $pass) ||     
    !preg_match('/[0-9]/', $pass) ||     
    !preg_match('/[\W]/', $pass)) {     

    header("Location: ../component/register.php?error=Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol!&name=$name&email=$email");
    exit;
}


$cek = $con->prepare("SELECT UserID FROM msuser WHERE userEmail = ?");
$cek->bind_param("s", $email);
$cek->execute();
if ($cek->get_result()->num_rows > 0) {
    header("Location: ../component/register.php?error=Email sudah terdaftar!");
    exit;
}


$hash = password_hash($pass, PASSWORD_DEFAULT);

$verifyToken = bin2hex(random_bytes(32));

$stmt = $con->prepare("
    INSERT INTO msuser (userName, userEmail, userPassword, userRole, userStatus, isVerified, isApproved, verifyToken, status)
    VALUES (?, ?, ?, 'user', 'active', 0, 0, ?, 'pending')
");
$stmt->bind_param("ssss", $name, $email, $hash, $verifyToken);
$stmt->execute();

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = "warehouseapp10@gmail.com";
    $mail->Password   = "ogythbjdudgzolze";
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom("warehouseapp10@gmail.com", "SecProg App");
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "Verifikasi Email Akun SecProg";

    $link = "http://localhost/SecProg/controllers/verify_email.php?token=$verifyToken";

    $mail->Body = "
        <h3>Verifikasi Email</h3>
        <p>Halo, $name</p>
        <p>Silakan klik link berikut untuk verifikasi email kamu:</p>
        <p><a href='$link'>$link</a></p>
    ";

    $mail->send();

    header("Location: ../component/register.php?success=Registrasi berhasil. Cek email kamu untuk verifikasi!");
    exit;

} catch (Exception $e) {
    echo "Gagal mengirim email verifikasi: " . $mail->ErrorInfo;
}
