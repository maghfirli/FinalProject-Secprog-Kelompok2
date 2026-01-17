<?php
require "connection.php";

$token = $_POST['token'] ?? '';
$pwd1  = $_POST['password'] ?? '';
$pwd2  = $_POST['password2'] ?? '';

if (!$token || !$pwd1 || !$pwd2) {
    header("Location: ../component/reset_password.php?token=$token&error=Data tidak lengkap.");
    exit;
}

if ($pwd1 !== $pwd2) {
    header("Location: ../component/reset_password.php?token=$token&error=Password tidak cocok.");
    exit;
}

if (strlen($pwd1) < 8) {
    header("Location: ../component/reset_password.php?token=$token&error=Password minimal 8 karakter.");
    exit;
}


$pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/";

if (!preg_match($pattern, $pwd1)) {
    header("Location: ../component/reset_password.php?token=$token&error=Password harus ada huruf besar, kecil, angka dan simbol.");
    exit;
}

$stmt = $con->prepare("
    SELECT UserID 
    FROM MsUser 
    WHERE resetToken=? AND resetExpiry >= NOW()
    LIMIT 1
");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../component/reset_password.php?error=Token invalid atau sudah kadaluarsa.");
    exit;
}

$hash = password_hash($pwd1, PASSWORD_DEFAULT);

$upd = $con->prepare("
    UPDATE MsUser
    SET userPassword=?, resetToken=NULL, resetExpiry=NULL
    WHERE UserID=?
");
$upd->bind_param("si", $hash, $user['UserID']);
$upd->execute();

header("Location: ../component/login.php?success=Password berhasil diperbarui, silakan login.");
exit;
?>
