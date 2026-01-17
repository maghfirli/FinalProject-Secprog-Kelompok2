<?php
require "../../controllers/session_check.php";
require_login("user");
require "../../controllers/connection.php";

$userName = $_SESSION['userName'] ?? 'User';
$userID = $_SESSION['userID'] ?? null;

$photo = '';
if ($userID) {
  $stmt = $con->prepare("SELECT Photo FROM MsUser WHERE UserID = ?");
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $photo = $stmt->get_result()->fetch_assoc()['Photo'] ?? '';
  $stmt->close();
}

$totalBarang = $con->query("SELECT COUNT(*) AS total FROM msbarang")->fetch_assoc()['total'] ?? 0;
$totalKritis = $con->query("SELECT COUNT(*) AS total FROM msinventory WHERE Stock <= MinStock")->fetch_assoc()['total'] ?? 0;

$barangMenipis = $con->query("
  SELECT b.NamaBarang, i.Stock, i.MinStock
  FROM msinventory i
  JOIN msbarang b ON i.BarangID = b.BarangID
  WHERE i.Stock <= i.MinStock
  ORDER BY i.Stock ASC
  LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Gudang</title>
  <link rel="stylesheet" href="../../assets/user/dashboard.css">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: #f5f6fa;
      display: flex;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      background: linear-gradient(to bottom, #0a143f, #122866, #1e3a8a);
      color: #fff;
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 20px;
      box-shadow: 3px 0 15px rgba(0,0,0,0.2);
    }

    .sidebar h2 {
      text-align: center;
      color: #60a5fa;
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .profile-header {
      text-align: center;
      margin-bottom: 25px;
    }

    .profile-header img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border: 3px solid #60a5fa;
      object-fit: cover;
      margin-bottom: 8px;
    }

    .profile-header h3 {
      font-size: 15px;
      color: #fff;
      margin: 0;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 15px 0 0 0;
      width: 100%;
    }

    .sidebar ul li {
      width: 100%;
    }

    .sidebar ul li a {
      color: #e5e7eb;
      text-decoration: none;
      display: block;
      padding: 12px 25px;
      border-radius: 8px;
      transition: all 0.3s;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
      background: rgba(255,255,255,0.15);
      color: #fff;
    }

    .main-content {
      margin-left: 250px;
      padding: 40px 60px;
      flex: 1;
    }

    .dashboard-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 40px;
    }

    .dashboard-header h1 {
      font-size: 26px;
      font-weight: 700;
      color: #111827;
    }

    .dashboard-header p {
      color: #6b7280;
      margin-top: 8px;
    }

    .header-img {
      width: 160px;
      height: auto;
    }

    .stats {
      display: flex;
      gap: 20px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }

    .stat-card {
      flex: 1;
      min-width: 200px;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .stat-card h3 {
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 10px;
    }

    .stat-card p {
      font-size: 22px;
      font-weight: 700;
      color: #2563eb;
    }

    .blue { border-left: 5px solid #2563eb; }
    .red { border-left: 5px solid #ef4444; }
    .green { border-left: 5px solid #10b981; }

    .critical-section {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }

    .critical-section table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    .critical-section th, .critical-section td {
      padding: 10px;
      border-bottom: 1px solid #e5e7eb;
      text-align: left;
    }

    .critical-section th {
      background: #f9fafb;
      font-weight: 600;
    }

    .critical-section h2 {
      color: #dc2626;
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <h2>User Panel</h2>

    <div class="profile-header">
      <img src="<?= !empty($photo) ? '../../uploads/user_photos/' . htmlspecialchars($photo) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png' ?>" alt="Foto Profil">
      <h3><?= htmlspecialchars($userName) ?></h3>
    </div>

    <ul>
      <li><a href="user_profile.php">👤 Profil Saya</a></li>
      <li><a href="dashboard.php" class="active">🏠 Dashboard</a></li>
      <li><a href="cekstok.php">📦 Cek Stok</a></li>
      <li><a href="../logout.php">🚪 Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <header class="dashboard-header">
      <div class="welcome">
        <h1>Selamat Datang, <?= htmlspecialchars($userName) ?> 👋</h1>
        <p>Kelola dan pantau stok barangmu dengan tampilan baru yang lebih modern.</p>
      </div>
      <img src="https://cdn-icons-png.flaticon.com/512/1048/1048949.png" alt="Warehouse" class="header-img">
    </header>

    <div class="stats">
      <div class="stat-card blue">
        <h3>📦 Total Barang</h3>
        <p><?= $totalBarang ?></p>
      </div>
      <div class="stat-card red">
        <h3>⚠ Barang Kritis</h3>
        <p><?= $totalKritis ?></p>
      </div>
      <div class="stat-card green">
        <h3>🕒 Update Terakhir</h3>
        <p><?= date("d M Y H:i") ?></p>
      </div>
    </div>

    <section class="menu-section">
      <h2>Menu Aksi Cepat</h2>
      <div class="menu-grid">
        <div class="menu-card">
          <img src="https://cdn-icons-png.flaticon.com/512/992/992651.png" alt="">
          <h3>Barang Masuk</h3>
          <p>Tambah stok baru yang masuk ke gudang.</p>
          <a href="cekstok.php" class="btn">Tambah</a>
        </div>
        <div class="menu-card">
          <img src="https://cdn-icons-png.flaticon.com/512/992/992703.png" alt="">
          <h3>Barang Keluar</h3>
          <p>Catat barang yang keluar dari gudang.</p>
          <a href="cekstok.php" class="btn red">Kurangi</a>
        </div>
        <div class="menu-card">
          <img src="https://cdn-icons-png.flaticon.com/512/1183/1183672.png" alt="">
          <h3>Cek Stok</h3>
          <p>Lihat seluruh stok barang yang tersedia.</p>
          <a href="cekstok.php" class="btn blue">Lihat</a>
        </div>
        <div class="menu-card">
          <img src="https://cdn-icons-png.flaticon.com/512/906/906175.png" alt="">
          <h3>Request Barang</h3>
          <p>Ajukan barang baru ke admin.</p>
          <a href="cekstok.php" class="btn green">Request</a>
        </div>
      </div>
    </section>

    <section class="critical-section">
      <h2>⚠ Barang Menipis</h2>
      <?php if ($barangMenipis->num_rows > 0): ?>
        <table>
          <tr><th>Nama Barang</th><th>Stok</th><th>Min Stok</th></tr>
          <?php while($row = $barangMenipis->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['NamaBarang']) ?></td>
              <td><?= $row['Stock'] ?></td>
              <td><?= $row['MinStock'] ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
      <?php else: ?>
        <p>✅ Tidak ada barang yang menipis.</p>
      <?php endif; ?>
    </section>
  </div>
</body>
</html>
