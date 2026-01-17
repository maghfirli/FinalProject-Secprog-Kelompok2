<?php
require "../../controllers/connection.php";
session_start();
if ($_SESSION['userRole'] != 'admin') exit("Akses ditolak.");

$res = $con->query("SELECT * FROM msuser WHERE isVerified=1 AND isApproved=0");
while ($row = $res->fetch_assoc()) {
  echo "<p>{$row['userName']} - {$row['userEmail']} 
        <a href='approve_action.php?id={$row['UserID']}'>[Approve]</a></p>";
}
?>
