<?php
session_start();
include "../../controllers/connection.php";

if (!isset($_SESSION['is_login']) || $_SESSION['userRole'] != 'admin') {
  header("Location: ../login.php");
  exit;
}

$adminName = $_SESSION['userName'] ?? 'Admin';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($action == "delete" && $id != "") {
  $hapusInv = $con->prepare("DELETE FROM MsInventory WHERE BarangID=?");
  $hapusInv->bind_param("s", $id);
  $hapusInv->execute();

  $hapusBarang = $con->prepare("DELETE FROM MsBarang WHERE BarangID=?");
  $hapusBarang->bind_param("s", $id);
  $hapusBarang->execute();

  $_SESSION['flash'] = ["type" => "success", "msg" => "Barang berhasil dihapus!"];
  header("Location: masterdata.php");
  exit;
}

$editData = null;
if ($action == "edit" && $id != "") {
  $ambil = $con->prepare("SELECT * FROM MsBarang WHERE BarangID=?");
  $ambil->bind_param("s", $id);
  $ambil->execute();
  $editData = $ambil->get_result()->fetch_assoc();
}

$tanggal = date("Ymd");
$cari = $con->query("SELECT BarangID FROM MsBarang WHERE BarangID LIKE 'BRG{$tanggal}-%' ORDER BY BarangID DESC LIMIT 1");
if ($cari && $row = $cari->fetch_assoc()) {
  $last = (int)substr($row['BarangID'], -3);
  $baru = str_pad($last + 1, 3, "0", STR_PAD_LEFT);
} else {
  $baru = "001";
}
$kodeBaru = "BRG{$tanggal}-{$baru}";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $barangId = $_POST['barangId'];
  $nama = $_POST['namaBarang'];
  $kategori = $_POST['kategori'];
  $harga = $_POST['harga'];
  $lokasi = $_POST['lokasiRak'];
  $minStock = isset($_POST['minStock']) ? $_POST['minStock'] : 10;

  if ($action == "edit" && $id != "") {
    $update = $con->prepare("UPDATE MsBarang SET NamaBarang=?, Kategori=?, Harga=?, LokasiRak=? WHERE BarangID=?");
    $update->bind_param("sssss", $nama, $kategori, $harga, $lokasi, $barangId);
    if ($update->execute()) {
      $_SESSION['flash'] = ["type" => "success", "msg" => "Data berhasil diupdate!"];
    } else {
      $_SESSION['flash'] = ["type" => "error", "msg" => "Gagal update data!"];
    }
  } else {
    $insert = $con->prepare("INSERT INTO MsBarang (BarangID, NamaBarang, Kategori, Harga, LokasiRak) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("sssss", $barangId, $nama, $kategori, $harga, $lokasi);
    if ($insert->execute()) {
      $con->query("INSERT INTO MsInventory (BarangID, Stock, MinStock) VALUES ('$barangId', 0, $minStock)");
      $_SESSION['flash'] = ["type" => "success", "msg" => "Barang berhasil ditambahkan!"];
    } else {
      $_SESSION['flash'] = ["type" => "error", "msg" => "Gagal menambah barang!"];
    }
  }

  header("Location: masterdata.php");
  exit;
}

$dataBarang = $con->query("SELECT * FROM MsBarang ORDER BY NamaBarang ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Master Data Barang</title>
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
    .main-content { margin-left: 240px; padding: 25px; }

    .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
    th, td { border: 1px solid #eee; padding: 10px; text-align: left; }
    th { background: #2a5298; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }

    .btn { padding: 6px 10px; border-radius: 6px; text-decoration:none; font-size:13px; }
    .btn-add { background: green; color: white; }
    .btn-edit { background: orange; color:white; }
    .btn-del { background: red; color:white; }

    .form-container { background: white; padding: 20px; border-radius: 10px; width: 400px; margin: 30px auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    label { display: block; margin-top: 10px; }
    input[type=text], input[type=number] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; margin-top: 5px; }
    button { background: #2a5298; color: white; padding: 8px 15px; border: none; border-radius: 6px; margin-top: 10px; cursor: pointer; }
    button:hover { background: #1e3c72; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="profile-header">
    <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png" alt="Admin">
    <h3><?=htmlspecialchars($adminName)?></h3>
  </div>
  <ul>
    <li><a href="dashboard.php">📊 Dashboard</a></li>
    <li><a href="masterdata.php"class="active">📦 Master Data</a></li>
    <li><a href="inventory.php">📑 Inventory</a></li>
    <li><a href="users.php">👥 Users</a></li>
    <li><a href="laporan.php">📄 Laporan</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
  </ul>
</div>

  <div class="main-content">
    <h1>📦 Master Data Barang</h1>

    <?php if(isset($_SESSION['flash'])): ?>
      <div style="background: <?= $_SESSION['flash']['type'] == 'success' ? '#b9f6ca' : '#ffcdd2'; ?>; 
                  padding:10px; border-radius:5px; margin-bottom:15px;">
        <?= $_SESSION['flash']['msg']; unset($_SESSION['flash']); ?>
      </div>
    <?php endif; ?>

    <?php if ($action == "add" || $action == "edit") { ?>
      <div class="form-container">
        <h3><?= $action == "edit" ? "Edit Barang" : "Tambah Barang" ?></h3>
        <form method="POST">
          <label>Kode Barang</label>
          <input type="text" name="barangId" value="<?= $editData['BarangID'] ?? $kodeBaru ?>" readonly>

          <label>Nama Barang</label>
          <input type="text" name="namaBarang" value="<?= $editData['NamaBarang'] ?? '' ?>" required>

          <label>Kategori</label>
          <input type="text" name="kategori" value="<?= $editData['Kategori'] ?? '' ?>" required>

          <label>Harga</label>
          <input type="number" name="harga" value="<?= $editData['Harga'] ?? '' ?>" required>

          <label>Lokasi Rak</label>
          <input type="text" name="lokasiRak" value="<?= $editData['LokasiRak'] ?? '' ?>" required>

          <?php if ($action == "add") { ?>
            <label>Min Stok</label>
            <input type="number" name="minStock" value="10" min="0">
          <?php } ?>

          <button type="submit"><?= $action == "edit" ? "Update" : "Simpan" ?></button>
          <a href="masterdata.php" class="btn" style="background: gray; color:white;">Batal</a>
        </form>
      </div>
    <?php } else { ?>
      <a href="masterdata.php?action=add" class="btn btn-add">+ Tambah Barang</a>
      <div class="table-container">
        <table>
          <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Lokasi Rak</th>
            <th>Aksi</th>
          </tr>
          <?php
          $no = 1;
          if ($dataBarang->num_rows > 0) {
            while ($row = $dataBarang->fetch_assoc()) {
              echo "<tr>
                <td>".$no++."</td>
                <td>".$row['BarangID']."</td>
                <td>".$row['NamaBarang']."</td>
                <td>".$row['Kategori']."</td>
                <td>Rp ".number_format($row['Harga'],0,',','.')."</td>
                <td>".$row['LokasiRak']."</td>
                <td>
                  <a class='btn btn-edit' href='masterdata.php?action=edit&id=".$row['BarangID']."'>Edit</a>
                  <a class='btn btn-del' href='masterdata.php?action=delete&id=".$row['BarangID']."' onclick='return confirm(\"Yakin hapus data ini?\")'>Hapus</a>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='7' style='text-align:center;'>Belum ada data barang</td></tr>";
          }
          ?>
        </table>
      </div>
    <?php } ?>
  </div>
</body>
</html>
