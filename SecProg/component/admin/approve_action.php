<?php
require "../../controllers/connection.php";
require "../../controllers/auth/mail_config.php";

$id = $_GET['id'];
$stmt = $con->prepare("UPDATE msuser SET isApproved=1 WHERE UserID=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$user = $con->query("SELECT userEmail FROM msuser WHERE UserID=$id")->fetch_assoc();
sendMail($user['userEmail'], "Akun Disetujui", "<p>Akun Anda telah disetujui oleh Superadmin. Silakan login ke sistem.</p>");

echo "<script>alert('Akun berhasil disetujui. Email notifikasi terkirim.');window.location.href='approve_user.php';</script>";
?>
