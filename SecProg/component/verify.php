<?php
session_start();
require_once __DIR__ . '/../controllers/connection.php';
require_once __DIR__ . '/../controllers/otp_helper.php';

$purpose = $_SESSION['pending_verify_purpose'] ?? $_GET['purpose'] ?? 'register';
$userId  = $_SESSION['pending_verify_user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    if (otp_verify($con, (int)$userId, $otp, $purpose)) {
        if ($purpose === 'register') {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $con->query("UPDATE MsUser SET status='active', email_verified_at='{$now}' WHERE userID={$userId}");
            header("Location: login.php"); exit;
        }
        if ($purpose === 'login') {
            $_SESSION['is_login'] = true;
            header("Location: user/dashboard.php"); exit;
        }
        if ($purpose === 'reset') {
            $_SESSION['allow_reset_user_id'] = $userId;
            header("Location: reset_password.php"); exit;
        }
    } else {
        $error = "Kode OTP salah atau sudah kadaluarsa.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Verifikasi OTP</title></head>
<body>
<h2>Verifikasi OTP (<?= htmlspecialchars($purpose) ?>)</h2>
<?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
<form method="POST">
  <input type="text" name="otp" maxlength="6" pattern="\d{6}" placeholder="Masukkan 6 Digit OTP" required>
  <button type="submit">Verifikasi</button>
</form>
</body>
</html>
