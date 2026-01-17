<?php
session_start();
if (!isset($_SESSION['reset_email'])) {
    die("
    <html><body style='font-family:Poppins;background:#050A24;color:white;text-align:center;padding-top:80px'>
        <h2>Sesi Kadaluarsa</h2>
        <a href='forgot_password.php' style='color:#00A8FF;'>Reset Password</a>
    </body></html>");
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Link Kadaluarsa</title>

<style>
body {
    margin:0;
    padding:0;
    min-height:100vh;
    font-family:Poppins;
    background: radial-gradient(circle at bottom, rgba(0,168,255,0.25), transparent),
                linear-gradient(135deg, #050A24, #0A1A47);
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
}

.card {
    width:420px;
    padding:40px;
    background:rgba(255,255,255,0.1);
    backdrop-filter:blur(14px);
    border-radius:20px;
    border:1px solid rgba(255,255,255,0.2);
    text-align:center;
    box-shadow:0 15px 40px rgba(0,0,0,.4);
}

.btn {
    padding:12px 22px;
    background:#00A8FF;
    border-radius:12px;
    color:white;
    text-decoration:none;
    font-weight:600;
    display:inline-block;
    margin-top:15px;
}
.btn:hover {
    background:#0089d1;
}
</style>

</head>
<body>

<div class="card">
    <h2 style="color:#00A8FF;">Link Sudah Kadaluarsa</h2>
    <p>Untuk melanjutkan reset password, silakan kirim link baru.</p>

    <a href="resend_reset.php" class="btn">Kirim Ulang Link Baru</a>
</div>

</body>
</html>
