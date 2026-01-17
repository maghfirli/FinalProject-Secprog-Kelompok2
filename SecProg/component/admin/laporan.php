<?php
session_start();
require "../../controllers/connection.php";

if (!isset($_SESSION['is_login']) || $_SESSION['userRole'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$adminName = $_SESSION['userName'] ?? 'Admin';

$filterType = $_GET['type'] ?? 'all'; 
$startDate  = $_GET['start'] ?? '';
$endDate    = $_GET['end'] ?? '';

$whereDate = "";
if ($startDate && $endDate) {
    $start = $con->real_escape_string($startDate);
    $end   = $con->real_escape_string($endDate);
    $whereDate = "AND Tanggal BETWEEN '$start 00:00:00' AND '$end 23:59:59'";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>📄 Laporan Transaksi Barang</title>
<link rel="stylesheet" href="../../assets/global.css">
<style>
body{margin:0;font-family:'Poppins',sans-serif;background:#f3f4f6;display:flex;}
.sidebar{
  width:250px;height:100vh;background:linear-gradient(to bottom,#050A24,#0f1c4a,#1e3a8a);
  color:#fff;position:fixed;left:0;top:0;display:flex;flex-direction:column;align-items:center;
  padding-top:20px;box-shadow:3px 0 12px rgba(0,0,0,0.2);
}
.sidebar h2{color:#60a5fa;margin-bottom:20px;}
.sidebar ul{list-style:none;padding:0;width:100%;}
.sidebar ul li a{
  color:#e5e7eb;text-decoration:none;display:block;padding:12px 25px;border-radius:8px;transition:all .3s;
}
.sidebar ul li a:hover,.sidebar ul li a.active{
  background:rgba(255,255,255,0.15);color:#fff;
}
.profile-header{text-align:center;margin-bottom:25px;}
.profile-header img{
  width:80px;height:80px;border-radius:50%;border:3px solid #60a5fa;margin-bottom:10px;
}

.main-content{margin-left:260px;padding:30px;width:100%;}
h1{color:#1e3a8a;font-weight:700;margin-bottom:20px;}

.filter-box{
  background:#fff;border-left:6px solid #2563eb;border-radius:10px;padding:15px 20px;
  margin-bottom:20px;box-shadow:0 3px 10px rgba(0,0,0,0.08);
}
.filter-box label{font-weight:600;color:#1e3a8a;margin-right:10px;}
.filter-box input,.filter-box select{
  padding:8px 10px;border:1px solid #ccc;border-radius:6px;margin-right:10px;
}
.filter-box button{
  background:#2563eb;color:white;border:none;border-radius:6px;padding:8px 16px;cursor:pointer;font-weight:600;
}
.filter-box button:hover{background:#1e40af;}

.table-container{background:white;padding:20px;border-radius:12px;box-shadow:0 3px 10px rgba(0,0,0,0.08);margin-bottom:25px;}
.table-container h3{margin:0 0 10px;color:#1e3a8a;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #e5e7eb;padding:10px;text-align:left;}
th{background:#1e3a8a;color:white;}
tr:nth-child(even){background:#f9fafb;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="profile-header">
    <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png" alt="Admin">
    <h3><?= htmlspecialchars($adminName) ?></h3>
  </div>
  <ul>
    <li><a href="dashboard.php">📊 Dashboard</a></li>
    <li><a href="masterdata.php">📦 Master Data</a></li>
    <li><a href="inventory.php">📑 Inventory</a></li>
    <li><a href="users.php">👥 Users</a></li>
    <li><a href="laporan.php" class="active">📄 Laporan</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <h1>📄 Laporan Barang Masuk & Keluar</h1>

  <div class="filter-box">
    <form method="GET">
      <label>Jenis Transaksi:</label>
      <select name="type">
        <option value="all"   <?= $filterType==='all'   ? 'selected' : '' ?>>Semua</option>
        <option value="masuk" <?= $filterType==='masuk' ? 'selected' : '' ?>>Barang Masuk</option>
        <option value="keluar"<?= $filterType==='keluar'? 'selected' : '' ?>>Barang Keluar</option>
      </select>

      <label>Periode:</label>
      <input type="date" name="start" value="<?= htmlspecialchars($startDate) ?>">
      <input type="date" name="end"   value="<?= htmlspecialchars($endDate) ?>">

      <button type="submit">Terapkan Filter</button>
    </form>
  </div>

  <?php

  if ($filterType === 'all' || $filterType === 'masuk') {
      echo '<div class="table-container">';
      echo '<h3>📥 Barang Masuk</h3>';
      echo '<table>
              <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Nama</th>
              </tr>';

      $sqlMasuk = "SELECT b.NamaBarang, bm.Jumlah, bm.Tanggal, u.userName AS Nama
                   FROM BarangMasuk bm
                   JOIN MsBarang b ON bm.BarangID = b.BarangID
                   JOIN MsUser   u ON bm.UserID   = u.UserID
                   WHERE 1=1 $whereDate
                   ORDER BY bm.Tanggal DESC";

      $resMasuk = $con->query($sqlMasuk);

      if ($resMasuk && $resMasuk->num_rows > 0) {
          $no = 1;
          while ($row = $resMasuk->fetch_assoc()) {
              echo "<tr>
                      <td>{$no}</td>
                      <td>".htmlspecialchars($row['NamaBarang'])."</td>
                      <td>{$row['Jumlah']}</td>
                      <td>{$row['Tanggal']}</td>
                      <td>".htmlspecialchars($row['Nama'])."</td>
                    </tr>";
              $no++;
          }
      } else {
          echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data</td></tr>";
      }

      echo '</table>';
      echo '</div>';
  }

  if ($filterType === 'all' || $filterType === 'keluar') {
      echo '<div class="table-container">';
      echo '<h3>📤 Barang Keluar</h3>';
      echo '<table>
              <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Nama</th>
              </tr>';

      $sqlKeluar = "SELECT b.NamaBarang, bk.Jumlah, bk.Tanggal, u.userName AS Nama
                    FROM BarangKeluar bk
                    JOIN MsBarang b ON bk.BarangID = b.BarangID
                    JOIN MsUser   u ON bk.UserID   = u.UserID
                    WHERE 1=1 $whereDate
                    ORDER BY bk.Tanggal DESC";

      $resKeluar = $con->query($sqlKeluar);

      if ($resKeluar && $resKeluar->num_rows > 0) {
          $no = 1;
          while ($row = $resKeluar->fetch_assoc()) {
              echo "<tr>
                      <td>{$no}</td>
                      <td>".htmlspecialchars($row['NamaBarang'])."</td>
                      <td>{$row['Jumlah']}</td>
                      <td>{$row['Tanggal']}</td>
                      <td>".htmlspecialchars($row['Nama'])."</td>
                    </tr>";
              $no++;
          }
      } else {
          echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data</td></tr>";
      }

      echo '</table>';
      echo '</div>';
  }
  ?>
</div>
</body>
</html>
