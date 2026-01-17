<?php
session_start();
require "../../controllers/connection.php";

if (!isset($_SESSION['is_login']) || $_SESSION['userRole'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$adminName = $_SESSION['userName'];

function querySafe($con, $sql) {
    $res = $con->query($sql);
    if (!$res) {
        echo "<p style='color:red;font-weight:bold;'>SQL Error: " . htmlspecialchars($con->error) . "<br>Query: $sql</p>";
        return false;
    }
    return $res;
}


$totalBarang = querySafe($con, "SELECT COUNT(*) as total FROM MsBarang");
$totalBarang = $totalBarang ? $totalBarang->fetch_assoc()['total'] : 0;

$totalStok = querySafe($con, "SELECT SUM(Stock) as total FROM MsInventory");
$totalStok = $totalStok ? $totalStok->fetch_assoc()['total'] : 0;

$kritis = querySafe($con, "
    SELECT b.NamaBarang, i.Stock, i.MinStock 
    FROM MsInventory i 
    JOIN MsBarang b ON i.BarangID = b.BarangID
    WHERE i.Stock < i.MinStock
");

$kategoriData = querySafe($con, "
    SELECT b.Kategori, SUM(i.Stock) as totalStok
    FROM MsInventory i
    JOIN MsBarang b ON i.BarangID=b.BarangID
    GROUP BY b.Kategori
");

$barangMasuk = 0;
$q1 = querySafe($con, "SHOW COLUMNS FROM BarangMasuk LIKE 'TanggalMasuk'");
if ($q1 && $q1->num_rows > 0) {
    $barangMasuk = querySafe($con, "SELECT COUNT(*) AS total FROM BarangMasuk WHERE DATE(TanggalMasuk)=CURDATE()");
    $barangMasuk = $barangMasuk ? $barangMasuk->fetch_assoc()['total'] : 0;
} else {
    $barangMasuk = querySafe($con, "SELECT COUNT(*) AS total FROM BarangMasuk");
    $barangMasuk = $barangMasuk ? $barangMasuk->fetch_assoc()['total'] : 0;
}

$barangKeluar = 0;
$q2 = querySafe($con, "SHOW COLUMNS FROM BarangKeluar LIKE 'TanggalKeluar'");
if ($q2 && $q2->num_rows > 0) {
    $barangKeluar = querySafe($con, "SELECT COUNT(*) AS total FROM BarangKeluar WHERE DATE(TanggalKeluar)=CURDATE()");
    $barangKeluar = $barangKeluar ? $barangKeluar->fetch_assoc()['total'] : 0;
} else {
    $barangKeluar = querySafe($con, "SELECT COUNT(*) AS total FROM BarangKeluar");
    $barangKeluar = $barangKeluar ? $barangKeluar->fetch_assoc()['total'] : 0;
}

$pendingReq = querySafe($con, "SELECT COUNT(*) AS total FROM msrequest WHERE Status='pending'");
$pendingReq = $pendingReq ? $pendingReq->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>📊 Dashboard Admin</title>
<link rel="stylesheet" href="../../assets/global.css">
<style>
body{margin:0;font-family:'Poppins',sans-serif;background:#f3f4f6;display:flex;}
.sidebar{
  width:250px;height:100vh;background:linear-gradient(to bottom,#050A24,#0f1c4a,#1e3a8a);
  color:#fff;position:fixed;left:0;top:0;display:flex;flex-direction:column;align-items:center;padding-top:20px;
  box-shadow:3px 0 12px rgba(0,0,0,0.2);
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

.card-container{display:flex;flex-wrap:wrap;gap:20px;margin-bottom:30px;}
.card{
  flex:1;min-width:250px;background:white;border-radius:12px;padding:20px;
  box-shadow:0 4px 12px rgba(0,0,0,0.1);text-align:center;transition:.3s;
}
.card:hover{transform:translateY(-3px);box-shadow:0 6px 15px rgba(0,0,0,0.15);}
.card h2{font-size:18px;color:#1f2937;margin-bottom:10px;}
.card p{font-size:22px;font-weight:700;color:#2563eb;margin:0;}

.alert{
  background:#fff;border-left:6px solid #2563eb;border-radius:8px;
  padding:15px 20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
.alert h3{margin:0 0 8px;font-size:16px;}
.alert p{margin:0;font-size:14px;color:#4b5563;}
.alert.critical{border-left-color:#dc2626;}
.alert.success{border-left-color:#16a34a;}
.alert.warning{border-left-color:#f59e0b;}

.progress-container{margin:10px 0;}
.progress-bar{
  height:20px;background:#2563eb;border-radius:6px;text-align:right;
  padding-right:5px;color:white;font-size:12px;transition:width .4s ease;
}
.table-wrapper{background:white;border-radius:12px;padding:20px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:10px;border:1px solid #e5e7eb;text-align:left;}
th{background:#1e3a8a;color:white;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="profile-header">
    <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png" alt="Admin">
    <h3><?=htmlspecialchars($adminName)?></h3>
  </div>
  <ul>
    <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
    <li><a href="masterdata.php">📦 Master Data</a></li>
    <li><a href="inventory.php">📑 Inventory</a></li>
    <li><a href="users.php">👥 Users</a></li>
    <li><a href="laporan.php">📄 Laporan</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <h1>📊 Dashboard Admin</h1>

  <?php if($pendingReq>0): ?>
    <div class="alert warning">
      <h3>🧾 Ada <?=$pendingReq?> request barang yang perlu diverifikasi!</h3>
      <p><a href="inventory.php" style="color:#2563eb;text-decoration:none;font-weight:600;">Klik di sini</a> untuk verifikasi sekarang.</p>
    </div>
  <?php endif; ?>

  <?php if($barangMasuk>0): ?>
    <div class="alert success">
      <h3>📥 <?=$barangMasuk?> Barang Masuk Hari Ini</h3>
      <p>Periksa data terbaru di halaman Barang Masuk.</p>
    </div>
  <?php endif; ?>

  <?php if($barangKeluar>0): ?>
    <div class="alert critical">
      <h3>📤 <?=$barangKeluar?> Barang Keluar Hari Ini</h3>
      <p>Pastikan stok diperbarui dengan benar.</p>
    </div>
  <?php endif; ?>

  <div class="card-container">
    <div class="card">
      <h2>Total Barang</h2>
      <p><?=number_format($totalBarang)?></p>
    </div>
    <div class="card">
      <h2>Total Stok</h2>
      <p><?=number_format($totalStok)?></p>
    </div>
  </div>

  <div class="card">
    <h2>📦 Stok per Kategori</h2>
    <?php if($kategoriData && $kategoriData->num_rows>0): ?>
      <?php while($row=$kategoriData->fetch_assoc()):
        $persen = ($totalStok>0)?round(($row['totalStok']/$totalStok)*100):0; ?>
        <div class="progress-container">
          <strong><?=htmlspecialchars($row['Kategori'])?> (<?=$row['totalStok']?>)</strong>
          <div style="background:#e5e7eb;border-radius:6px;">
            <div class="progress-bar" style="width:<?=$persen?>%;"><?=$persen?>%</div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Tidak ada data kategori.</p>
    <?php endif; ?>
  </div>

  <?php if($kritis && $kritis->num_rows>0): ?>
    <div class="table-wrapper">
      <h2 style="color:#dc2626;">⚠ Barang Kritis (Stok di bawah minimum)</h2>
      <table>
        <tr><th>Nama Barang</th><th>Stok</th><th>Min. Stok</th></tr>
        <?php while($row=$kritis->fetch_assoc()): ?>
          <tr>
            <td><?=htmlspecialchars($row['NamaBarang'])?></td>
            <td><?=$row['Stock']?></td>
            <td><?=$row['MinStock']?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php else: ?>
    <div class="alert success"><h3>✅ Semua stok aman</h3></div>
  <?php endif; ?>
</div>
</body>
</html>
