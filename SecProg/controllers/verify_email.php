<?php
require "connection.php";

if (!isset($_GET['token'])) {
    die("
    <html><body style='background:#050A24;color:white;font-family:Poppins;text-align:center;padding-top:100px'>
        <h1>Token Tidak Valid</h1>
        <a href='../component/login.php' style='color:#00A8FF'>Kembali ke Login</a>
    </body></html>
    ");
}

$token = $_GET['token'];

$stmt = $con->prepare("SELECT UserID FROM msuser WHERE verifyToken = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("
    <html><body style='background:#050A24;color:white;font-family:Poppins;text-align:center;padding-top:100px'>
        <h1>Token Tidak Ditemukan</h1>
        <p>Link verifikasi ini tidak valid atau sudah digunakan.</p>
        <a href='../component/login.php' style='color:#00A8FF'>Kembali ke Login</a>
    </body></html>
    ");
}

$row = $res->fetch_assoc();
$userId = $row['UserID'];

$upd = $con->prepare("
    UPDATE msuser 
    SET isVerified = 1,
        email_verified_at = NOW(),
        status = 'pending'
    WHERE UserID = ?
");
$upd->bind_param("i", $userId);
$upd->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Email Terverifikasi</title>
<style>
body {
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #050A24, #0A1A47);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Poppins', sans-serif;
    color: white;
}

.card {
    width: 430px;
    background: rgba(255,255,255,0.12);
    padding: 40px 35px;
    border-radius: 18px;
    text-align: center;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.18);
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    animation: fadeIn .7s ease;
}

@keyframes fadeIn {
    from {opacity:0; transform: translateY(20px);}
    to   {opacity:1; transform: translateY(0);}
}

h2 {
    color: #00A8FF;
    margin-bottom: 10px;
}

p {
    color: #d8e8ff;
    margin-bottom: 25px;
}

.btn {
    display: inline-block;
    padding: 12px 22px;
    background: #00A8FF;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    text-decoration: none;
    transition: .3s;
}

.btn:hover {
    background: #0089d1;
}
</style>
</head>

<body>
<div class="card">
    <h2>Email Berhasil Diverifikasi!</h2>
    <p>Akun kamu sedang menunggu persetujuan dari Superadmin.</p>
    <a href="../component/login.php" class="btn">Ke Halaman Login</a>
</div>
</body>
</html>
