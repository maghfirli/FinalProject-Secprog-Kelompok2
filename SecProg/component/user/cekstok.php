<?php
require "../../controllers/session_check.php";
require_login("user");
require "../../controllers/connection.php";

$userID   = $_SESSION['userID'] ?? null;
$userName = $_SESSION['userName'] ?? 'User';

$photo = '';
if ($userID) {
  $stmt = $con->prepare("SELECT Photo FROM MsUser WHERE UserID = ?");
  $stmt->bind_param("s", $userID);
  $stmt->execute();
  $photo = $stmt->get_result()->fetch_assoc()['Photo'] ?? '';
  $stmt->close();
}

$search       = $_GET['search'] ?? '';
$searchParam  = "%$search%";
$sqlStock = "
  SELECT b.BarangID, b.NamaBarang, b.Kategori, b.LokasiRak, i.Stock, i.MinStock
  FROM msinventory i
  JOIN msbarang b ON i.BarangID = b.BarangID
  WHERE (b.NamaBarang LIKE ? OR b.Kategori LIKE ?)
  ORDER BY b.NamaBarang ASC";
$stmtStock = $con->prepare($sqlStock);
$stmtStock->bind_param("ss", $searchParam, $searchParam);
$stmtStock->execute();
$resultStock = $stmtStock->get_result();

$sqlBarang = "SELECT BarangID, NamaBarang FROM msbarang ORDER BY NamaBarang ASC";
$resultBarang = $con->query($sqlBarang);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type     = $_POST['type'] ?? '';
  $jumlah   = (int)($_POST['jumlah'] ?? 0);
  $barangId = $_POST['barangId'] ?? null;
  $namaBaru = $_POST['namaBaru'] ?? null;

  if (!in_array($type, ['in','out','new'], true) || $jumlah <= 0) {
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Input tidak valid.'];
    header("Location: cekstok.php"); exit;
  }

  if ($type === 'new') {
    $barangId = null;
    if (!$namaBaru || trim($namaBaru)==='') {
      $_SESSION['flash'] = ['type'=>'error','msg'=>'Nama barang baru wajib diisi.'];
      header("Location: cekstok.php"); exit;
    }
  }

  $reqId = "REQ" . strtoupper(bin2hex(random_bytes(4)));
  $stmt = $con->prepare("INSERT INTO msrequest 
        (RequestID, BarangID, NamaBarangBaru, UserID, Type, Jumlah, Status, RequestDate)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
  $stmt->bind_param("sssssi", $reqId, $barangId, $namaBaru, $userID, $type, $jumlah);
  $stmt->execute();

  $_SESSION['flash'] = ['type'=>'success','msg'=>'✅ Request berhasil dikirim! Menunggu verifikasi admin.'];
  header("Location: cekstok.php"); exit;
}

$sqlHist = "
  SELECT r.RequestID,
         COALESCE(b.NamaBarang, r.NamaBarangBaru) AS NamaBarangTampil,
         r.Type, r.Jumlah, r.Status, r.RequestDate,
         u.userName
  FROM msrequest r
  LEFT JOIN msbarang b ON r.BarangID = b.BarangID
  LEFT JOIN MsUser u   ON r.UserID = u.UserID
  ORDER BY r.RequestDate DESC";
$resultHistory = $con->query($sqlHist);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>📦 Cek Stok Barang</title>
<link rel="stylesheet" href="../../assets/user/cek.css">
<style>
body{margin:0;font-family:'Poppins',sans-serif;background:#f5f6fa;display:flex;}
.sidebar{width:250px;height:100vh;background:linear-gradient(to bottom,#0a143f,#122866,#1e3a8a);color:#fff;position:fixed;top:0;left:0;display:flex;flex-direction:column;align-items:center;padding-top:20px;box-shadow:3px 0 15px rgba(0,0,0,0.2);}
.sidebar h2{color:#60a5fa;font-size:20px;margin-bottom:10px;}
.profile-header{text-align:center;margin-bottom:25px;}
.profile-header img{width:80px;height:80px;border-radius:50%;border:3px solid #60a5fa;object-fit:cover;margin-bottom:8px;}
.profile-header h3{color:#fff;font-size:15px;margin:0;}
.sidebar ul{list-style:none;padding:0;width:100%;}
.sidebar ul li a{color:#e5e7eb;text-decoration:none;display:block;padding:12px 25px;border-radius:8px;transition:.3s;}
.sidebar ul li a:hover,.sidebar ul li a.active{background:rgba(255,255,255,.15);color:#fff;}
.main-content{margin-left:250px;padding:40px;width:100%;}
.table-container{background:#fff;padding:25px;border-radius:12px;margin-bottom:25px;box-shadow:0 3px 10px rgba(0,0,0,.1);}
.table-header{display:flex;justify-content:space-between;align-items:center;}
.table-header h1{font-size:22px;font-weight:700;color:#111827;}
.btn-container{display:flex;gap:10px;}
.action-btn{border:none;border-radius:6px;padding:8px 16px;cursor:pointer;font-weight:600;color:#fff;transition:.3s;}
.btn-in{background:#2563eb;}
.btn-out{background:#ef4444;}
.btn-new{background:#10b981;}
.action-btn:hover{opacity:.9;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{padding:10px;border-bottom:1px solid #ddd;text-align:left;}
th{background:#174a7d;color:white;}
.badge.pending{background:#facc15;color:#000;padding:4px 8px;border-radius:6px;}
.badge.cancelled{background:#f87171;color:#fff;padding:4px 8px;border-radius:6px;}
.badge.success{background:#10b981;color:#fff;padding:4px 8px;border-radius:6px;}
.toast{padding:10px 15px;border-radius:6px;margin-top:15px;font-weight:600;}
.toast.success{background:#d1fae5;color:#065f46;}
.toast.error{background:#fee2e2;color:#991b1b;}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.4);justify-content:center;align-items:center;}
.modal-content{background:#fff;padding:25px;border-radius:10px;width:400px;position:relative;}
.close-btn{position:absolute;top:10px;right:15px;font-size:20px;cursor:pointer;}
</style>
</head>
<body>

<div class="sidebar">
  <h2>User Panel</h2>
  <div class="profile-header">
    <img src="<?= !empty($photo)?'../../uploads/user_photos/'.htmlspecialchars($photo):'https://cdn-icons-png.flaticon.com/512/149/149071.png' ?>" alt="Foto Profil">
    <h3><?= htmlspecialchars($userName) ?></h3>
  </div>
  <ul>
    <li><a href="user_profile.php">👤 Profil Saya</a></li>
    <li><a href="dashboard.php">🏠 Dashboard</a></li>
    <li><a class="active" href="cekstok.php">📦 Cek Stok</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <div class="table-container">
    <div class="table-header">
      <h1>📦 Cek Stok Barang</h1>
      <div class="btn-container">
        <button class="action-btn btn-in" onclick="openModal('in')">⬆ Barang Masuk</button>
        <button class="action-btn btn-out" onclick="openModal('out')">⬇ Barang Keluar</button>
        <button class="action-btn btn-new" onclick="openModal('new')">🆕 Barang Baru</button>
      </div>
    </div>

    <form method="GET" style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;">
      <input type="text" name="search" placeholder="🔍 Cari nama atau kategori..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Cari</button>
      <a href="cekstok.php" style="text-decoration:none;background:#f3f4f6;color:#111;padding:10px 16px;border-radius:6px;">Reset</a>
    </form>

    <?php if(isset($_SESSION['flash'])): ?>
      <div class="toast <?= $_SESSION['flash']['type'] ?>"><?= htmlspecialchars($_SESSION['flash']['msg']) ?></div>
      <script>setTimeout(()=>document.querySelector('.toast')?.remove(),3500);</script>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <table>
      <tr><th>No</th><th>Nama Barang</th><th>Kategori</th><th>Rak</th><th>Stok</th><th>Min</th></tr>
      <?php if($resultStock->num_rows>0): $no=1; while($r=$resultStock->fetch_assoc()): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($r['NamaBarang']) ?></td>
        <td><?= htmlspecialchars($r['Kategori']) ?></td>
        <td><?= htmlspecialchars($r['LokasiRak']) ?></td>
        <td><?= (int)$r['Stock'] ?></td>
        <td><?= (int)$r['MinStock'] ?></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="6" style="text-align:center;">Tidak ada data barang</td></tr>
      <?php endif; ?>
    </table>
  </div>

  <div class="table-container">
    <h2>📝 Riwayat Request Semua User</h2>
    <table>
      <tr><th>ID</th><th>Nama Barang</th><th>Jenis</th><th>Jumlah</th><th>Status</th><th>Tanggal</th><th>Dikirim Oleh</th></tr>
      <?php if($resultHistory->num_rows>0): while($h=$resultHistory->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($h['RequestID']) ?></td>
        <td><?= htmlspecialchars($h['NamaBarangTampil']) ?></td>
        <td><?= strtoupper($h['Type']) ?></td>
        <td><?= (int)$h['Jumlah'] ?></td>
        <td><span class="badge <?= strtolower($h['Status']) ?>"><?= ucfirst($h['Status']) ?></span></td>
        <td><?= htmlspecialchars($h['RequestDate']) ?></td>
        <td><?= htmlspecialchars($h['userName']) ?></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="7" style="text-align:center;">Belum ada request</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<div class="modal" id="modalForm">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h2 id="modalTitle">Form Request</h2>
    <form method="POST">
      <input type="hidden" name="type" id="reqType">
      <div id="barangSelect">
        <label>Pilih Barang</label>
        <select name="barangId">
          <option value="">-- Pilih Barang --</option>
          <?php $resultBarang->data_seek(0);
          while($b=$resultBarang->fetch_assoc()){
            echo "<option value='".htmlspecialchars($b['BarangID'])."'>".htmlspecialchars($b['NamaBarang'])."</option>";
          } ?>
        </select>
      </div>
      <div id="barangBaru" style="display:none;">
        <label>Nama Barang Baru</label>
        <input type="text" name="namaBaru" placeholder="Masukkan nama barang baru">
      </div>
      <label>Jumlah</label>
      <input type="number" name="jumlah" min="1" required>
      <button type="submit" class="action-btn btn-in" id="submitBtn">Kirim Request</button>
    </form>
  </div>
</div>

<script>
function openModal(type){
  const m=document.getElementById('modalForm');
  const t=document.getElementById('modalTitle');
  const req=document.getElementById('reqType');
  const sBtn=document.getElementById('submitBtn');
  const sel=document.getElementById('barangSelect');
  const baru=document.getElementById('barangBaru');
  m.style.display='flex'; req.value=type;
  if(type==='in'){t.textContent="⬆ Request Barang Masuk";sBtn.className="action-btn btn-in";sel.style.display='block';baru.style.display='none';}
  else if(type==='out'){t.textContent="⬇ Request Barang Keluar";sBtn.className="action-btn btn-out";sel.style.display='block';baru.style.display='none';}
  else{t.textContent="🆕 Request Barang Baru";sBtn.className="action-btn btn-new";sel.style.display='none';baru.style.display='block';}
}
function closeModal(){document.getElementById('modalForm').style.display='none';}
</script>
</body>
</html>
