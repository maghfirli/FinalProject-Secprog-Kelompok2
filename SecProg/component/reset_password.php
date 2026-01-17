<?php
session_start();
require "../controllers/connection.php";

$token = $_GET['token'] ?? '';

$stmt = $con->prepare("
    SELECT UserID, userEmail, resetExpiry 
    FROM msuser
    WHERE resetToken = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("
        <h2 style='color:white;font-family:Poppins;text-align:center;margin-top:80px;'>
            Token Tidak Valid
        </h2>
        <center><a href='forgot_password.php' style='color:#00A8FF;'>Reset password kembali</a></center>
    ");
}

$expiry = strtotime($user['resetExpiry']);
$now    = time();


if ($now > $expiry) {
    $_SESSION['reset_email'] = $user['userEmail'];
}

$remaining = $expiry - $now;
if ($remaining < 0) $remaining = 0;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reset Password</title>

<style>
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #050A24, #0A1A47);
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
}

.card {
    width: 430px;
    padding: 40px 35px;
    background: rgba(255,255,255,0.12);
    border-radius: 20px;
    backdrop-filter: blur(18px);
    border: 1px solid rgba(255,255,255,0.18);
    box-shadow: 0 15px 40px rgba(0,0,0,.4);
    animation: fadeIn .7s ease;
}

@keyframes fadeIn {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0);}
}

h2 {
    text-align:center;
    color:#00A8FF;
    margin-bottom:10px;
}

.timer-box {
    background: rgba(0,168,255,0.15);
    padding:10px;
    border-radius:12px;
    margin-bottom:20px;
    font-size:18px;
    text-align:center;
    border:1px solid rgba(0,168,255,0.3);
}

input {
    width:100%;
    padding:13px;
    border-radius:12px;
    border:none;
    background: rgba(255,255,255,0.15);
    color:#00E0FF;
    font-size:15px;
    margin-bottom:20px;
    outline:none;
}

.btn {
    width:100%;
    padding:12px;
    background:#00A8FF;
    color:white;
    border:none;
    border-radius:12px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}
.btn:hover {
    background:#0089d1;
}

#resendBtn {
    display:none;
    width:100%;
    padding:12px;
    margin-top:15px;
    background:#ff4d4d;
    color:white;
    border:none;
    font-size:15px;
    font-weight:600;
    border-radius:12px;
    cursor:pointer;
}
#resendBtn:hover {
    background:#cc0000;
}
</style>

</head>
<body>

<div class="card">
    <h2>Reset Password</h2>

    <div class="timer-box">
        Waktu tersisa: <span id="countdown"></span>
    </div>

    <form id="resetForm" action="../controllers/reset_process.php" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label>Password Baru</label>
        <input type="password" name="password" placeholder="••••••••" required>

        <label>Ulangi Password</label>
        <input type="password" name="password2" placeholder="••••••••" required>

        <button class="btn">Ubah Password</button>
    </form>

    <button id="resendBtn"
            onclick="window.location.href='resend_reset.php?token=<?= urlencode($token) ?>'">
        🔁 Kirim Ulang Link Reset Password
    </button>

    <p style="text-align:center;margin-top:15px;">
        <a href="login.php" style="color:#8EC9FF;">← Kembali ke Login</a>
    </p>
</div>

<script>
let remain = <?= $remaining ?>;

function countdown() {
    let min = Math.floor(remain / 60);
    let sec = remain % 60;

    document.getElementById("countdown").textContent =
        `${min}:${sec.toString().padStart(2, '0')}`;

    remain--;

    if (remain < 0) {
        document.getElementById("resetForm").style.display = "none";
        document.getElementById("resendBtn").style.display = "block";
        document.getElementById("countdown").textContent = "0:00";
        return;
    }

    setTimeout(countdown, 1000);
}

countdown();
</script>

</body>
</html>
