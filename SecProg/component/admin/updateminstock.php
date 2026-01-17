<?php
session_start();
require "../../controllers/connection.php";

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $barangId = $_POST['barangId'];
    $minStock = (int) $_POST['minStock'];

    $stmt = $con->prepare("UPDATE MsInventory SET MinStock=? WHERE BarangID=?");
    $stmt->bind_param("is", $minStock, $barangId);

    if ($stmt->execute()) {
        $_SESSION['flash'] = ["type"=>"success","msg"=>"Min Stock berhasil diupdate!"];
    } else {
        $_SESSION['flash'] = ["type"=>"error","msg"=>"Gagal update: ".$con->error];
    }
}
header("Location: inventory.php");
exit;
