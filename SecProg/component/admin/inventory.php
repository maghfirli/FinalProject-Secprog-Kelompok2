<?php
session_start();
require "../../controllers/connection.php";

if (!isset($_SESSION['is_login']) || ($_SESSION['userRole'] ?? '') !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$adminID   = $_SESSION['userID'] ?? '';
$adminName = $_SESSION['userName'] ?? 'Admin';
$filter    = $_GET['filter'] ?? 'all';

$sqlInv = "
  SELECT b.BarangID, b.NamaBarang, b.Kategori, b.Harga, b.LokasiRak, b.IsDeleted,
         i.Stock, i.MinStock, i.LastUpdate
  FROM MsInventory i
  JOIN MsBarang b ON i.BarangID = b.BarangID
";

if     ($filter === "aman")    $sqlInv .= " WHERE i.Stock > i.MinStock AND b.IsDeleted = 0";
elseif ($filter === "menipis") $sqlInv .= " WHERE i.Stock > 0 AND i.Stock <= i.MinStock AND b.IsDeleted = 0";
elseif ($filter === "habis")   $sqlInv .= " WHERE i.Stock <= 0 AND b.IsDeleted = 0";
elseif ($filter === "dihapus") $sqlInv .= " WHERE b.IsDeleted = 1";
else                           $sqlInv .= " WHERE b.IsDeleted = 0";

$sqlInv .= " ORDER BY b.NamaBarang ASC";
$inventory = $con->query($sqlInv);

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['barang_masuk'])) {
  $barangId = $_POST['barangId']; $jumlah = (int)$_POST['jumlah'];
  $con->query("UPDATE MsInventory SET Stock=Stock+$jumlah, LastUpdate=NOW() WHERE BarangID='$barangId'");
  $con->query("INSERT INTO BarangMasuk (BarangID,Jumlah,UserID) VALUES ('$barangId',$jumlah,'$adminID')");
  $_SESSION['flash']=['type'=>'success','msg'=>'Barang masuk berhasil ditambahkan!'];
  header("Location: inventory.php"); exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['barang_keluar'])) {
  $barangId = $_POST['barangId']; $jumlah = (int)$_POST['jumlah'];
  $stok = $con->query("SELECT Stock FROM MsInventory WHERE BarangID='$barangId'")->fetch_assoc()['Stock'] ?? 0;
  if($stok<$jumlah){$_SESSION['flash']=['type'=>'error','msg'=>'Stok tidak cukup!'];header("Location: inventory.php");exit;}
  $con->query("UPDATE MsInventory SET Stock=Stock-$jumlah, LastUpdate=NOW() WHERE BarangID='$barangId'");
  $con->query("INSERT INTO BarangKeluar (BarangID,Jumlah,UserID) VALUES ('$barangId',$jumlah,'$adminID')");
  $_SESSION['flash']=['type'=>'success','msg'=>'Barang keluar berhasil dikurangi!'];
  header("Location: inventory.php"); exit;
}

if (isset($_POST['hapus_barang'])) {
  $barangId = $_POST['hapus_id'];
  $con->query("UPDATE MsBarang SET IsDeleted=1 WHERE BarangID='$barangId'");
  $_SESSION['flash']=['type'=>'error','msg'=>'Barang berhasil ditandai sebagai dihapus.'];
  header("Location: inventory.php"); exit;
}

if (isset($_POST['save_approve_new'])) {
  $reqID=$_POST['approve_new']; $kategori=$_POST['kategori_baru']; $lokasi=$_POST['lokasi_baru']; $harga=(int)$_POST['harga_baru'];
  $req=$con->query("SELECT NamaBarangBaru,Jumlah,UserID FROM msrequest WHERE RequestID='$reqID' AND Type='new' AND Status='pending'")->fetch_assoc();
  if($req){
    $newID="BRG".date("Ymd").rand(100,999);
    $con->query("INSERT INTO MsBarang (BarangID,NamaBarang,Kategori,LokasiRak,Harga,UserID) VALUES ('$newID','{$req['NamaBarangBaru']}','$kategori','$lokasi',$harga,'{$req['UserID']}')");
    $con->query("INSERT INTO MsInventory (BarangID,Stock,MinStock,LastUpdate) VALUES ('$newID',{$req['Jumlah']},5,NOW())");
    $con->query("UPDATE msrequest SET Status='approved',AdminID='$adminID',ApproveDate=NOW() WHERE RequestID='$reqID'");
    $_SESSION['flash']=['type'=>'success','msg'=>'Barang baru disetujui dan ditambahkan.'];
  }
  header("Location: inventory.php"); exit;
}

if (isset($_POST['reject_request'])) {
  $reqID=$_POST['reject_id']; $reason=$_POST['reject_reason'];
  $con->query("UPDATE msrequest SET Status='rejected',RejectReason='$reason',AdminID='$adminID',ApproveDate=NOW() WHERE RequestID='$reqID'");
  $_SESSION['flash']=['type'=>'error','msg'=>"Request $reqID ditolak."];
  header("Location: inventory.php"); exit;
}

if (isset($_GET['approve'])) {
  $reqID=$_GET['approve'];
  $r=$con->query("SELECT * FROM msrequest WHERE RequestID='$reqID' AND Status='pending'")->fetch_assoc();
  if($r){
    $barang=$r['BarangID']; $jumlah=(int)$r['Jumlah']; $user=$r['UserID'];
    if($r['Type']=='in'){
      $con->query("UPDATE MsInventory SET Stock=Stock+$jumlah,LastUpdate=NOW() WHERE BarangID='$barang'");
      $con->query("INSERT INTO BarangMasuk (BarangID,Jumlah,UserID) VALUES ('$barang',$jumlah,'$user')");
    } else {
      $cek=$con->query("SELECT Stock FROM MsInventory WHERE BarangID='$barang'")->fetch_assoc()['Stock'];
      if($cek>=$jumlah){
        $con->query("UPDATE MsInventory SET Stock=Stock-$jumlah,LastUpdate=NOW() WHERE BarangID='$barang'");
        $con->query("INSERT INTO BarangKeluar (BarangID,Jumlah,UserID) VALUES ('$barang',$jumlah,'$user')");
      } else {$_SESSION['flash']=['type'=>'error','msg'=>'Stok tidak cukup!'];header("Location: inventory.php");exit;}
    }
    $con->query("UPDATE msrequest SET Status='approved',AdminID='$adminID',ApproveDate=NOW() WHERE RequestID='$reqID'");
    $_SESSION['flash']=['type'=>'success','msg'=>"Request $reqID disetujui!"];
  }
  header("Location: inventory.php"); exit;
}

$requests=$con->query("SELECT r.*,b.NamaBarang,u.userName FROM msrequest r LEFT JOIN MsBarang b ON r.BarangID=b.BarangID JOIN msuser u ON r.UserID=u.UserID ORDER BY r.RequestDate DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>📦 Inventory Admin</title>
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
.flash{padding:10px;border-radius:6px;margin-bottom:15px;}
.flash.success{background:#d4edda;color:#155724;}
.flash.error{background:#f8d7da;color:#721c24;}
h1{color:#1e3a8a;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;}
.action-buttons button{background:#2563eb;color:white;border:none;border-radius:8px;padding:10px 16px;cursor:pointer;font-weight:600;margin-left:10px;}
.action-buttons button:hover{background:#1e40af;}
.card{background:white;border-radius:10px;padding:20px;box-shadow:0 4px 10px rgba(0,0,0,.08);margin-bottom:25px;}
table{width:100%;border-collapse:collapse;}
th,td{border:1px solid #e5e7eb;padding:10px;font-size:14px;text-align:left;}
th{background:#1e3a8a;color:white;}
.modal{display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.45);backdrop-filter:blur(6px);justify-content:center;align-items:center;}
.modal-content{background:white;border-radius:12px;padding:24px;width:400px;box-shadow:0 4px 25px rgba(0,0,0,.3);}
.close-btn{float:right;font-size:20px;color:#e11d48;cursor:pointer;}
.btn-green{background:#16a34a;color:white;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;}
.btn-red{background:#dc2626;color:white;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;}
.btn-green:hover{background:#15803d;} .btn-red:hover{background:#b91c1c;}
@keyframes scaleIn {
    from { transform: scale(0.9); opacity:0; }
    to   { transform: scale(1); opacity:1; }
}

</style>
</head>
<body>
<div class="sidebar">
  <div class="profile-header">
    <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png" alt="Admin">
    <h3><?=htmlspecialchars($adminName)?></h3>
  </div>
  <ul>
    <li><a href="dashboard.php" >📊 Dashboard</a></li>
    <li><a href="masterdata.php">📦 Master Data</a></li>
    <li><a href="inventory.php"class="active">📑 Inventory</a></li>
    <li><a href="users.php">👥 Users</a></li>
    <li><a href="laporan.php">📄 Laporan</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <h1>
    📦 Inventory Management
    <div class="action-buttons">
      <button onclick="openModal('masuk')">📥 Barang Masuk</button>
      <button onclick="openModal('keluar')">📤 Barang Keluar</button>
    </div>
  </h1>

  <?php if(isset($_SESSION['flash'])):?>
  <div class="flash <?=$_SESSION['flash']['type']?>"><?=$_SESSION['flash']['msg']?></div>
  <?php unset($_SESSION['flash']);endif;?>

  <div class="card">
    <h2>📊 Daftar Stok Barang</h2>
    <form method="GET">
      <select name="filter" onchange="this.form.submit()">
        <option value="all" <?=$filter==='all'?'selected':''?>>Semua</option>
        <option value="aman" <?=$filter==='aman'?'selected':''?>>Aman</option>
        <option value="menipis" <?=$filter==='menipis'?'selected':''?>>Menipis</option>
        <option value="habis" <?=$filter==='habis'?'selected':''?>>Habis</option>
        <option value="dihapus" <?=$filter==='dihapus'?'selected':''?>>Barang Dihapus</option>
      </select>
    </form>
    <table>
      <tr><th>No</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Rak</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
      <?php if($inventory->num_rows>0){$no=1;while($r=$inventory->fetch_assoc()){
        $status=$r['IsDeleted']?'Dihapus':($r['Stock']<=0?'Habis':($r['Stock']<=$r['MinStock']?'Menipis':'Aman'));
        echo"<tr><td>$no</td><td>{$r['NamaBarang']}</td><td>{$r['Kategori']}</td><td>Rp ".number_format($r['Harga'])."</td>
        <td>{$r['LokasiRak']}</td><td>{$r['Stock']}</td><td>$status</td>
        <td>".(!$r['IsDeleted']?"<button class='btn-red' onclick=\"hapusBarang('{$r['BarangID']}')\">🗑️</button>":"-")."</td></tr>";$no++;}}?>
    </table>
  </div>

  <div class="card">
    <h2>🧾 Verifikasi Request Barang</h2>
    <table>
      <tr><th>ID</th><th>User</th><th>Barang</th><th>Jenis</th><th>Jumlah</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
      <?php if($requests->num_rows>0){while($r=$requests->fetch_assoc()){?>
      <tr><td><?=$r['RequestID']?></td><td><?=$r['userName']?></td><td><?=htmlspecialchars($r['NamaBarang']??$r['NamaBarangBaru'])?></td>
      <td><?=strtoupper($r['Type'])?></td><td><?=$r['Jumlah']?></td><td><?=$r['Status']?></td><td><?=$r['RequestDate']?></td>
      <td><?php if($r['Status']=='pending'){?>
        <?php if($r['Type']=='new'){?><button class="btn-green" onclick="openApproveModal('<?=$r['RequestID']?>','<?=$r['NamaBarangBaru']?>')">🆕 Approve</button>
        <?php }else{?><a href="?approve=<?=$r['RequestID']?>"><button class="btn-green">✅ Approve</button></a><?php }?>
        <button class="btn-red" onclick="openRejectModal('<?=$r['RequestID']?>')">❌ Reject</button>
      <?php }else echo '-';?></td></tr><?php }}?>
    </table>
  </div>
</div>

<div class="modal" id="modalForm">
  <div class="modal-content"><span class="close-btn" onclick="closeModal()">&times;</span>
    <h2 id="modalTitle"></h2>
    <form method="POST">
      <label>Pilih Barang</label>
      <select name="barangId" required>
        <option value="">-- Pilih Barang --</option>
        <?php $b=$con->query("SELECT BarangID,NamaBarang FROM MsBarang WHERE IsDeleted=0 ORDER BY NamaBarang");
        while($x=$b->fetch_assoc()){echo"<option value='{$x['BarangID']}'>{$x['NamaBarang']}</option>";}?>
      </select>
      <label>Jumlah</label>
      <input type="number" name="jumlah" min="1" required>
      <button id="submitBtn" name="barang_masuk" class="btn-green">Simpan</button>
    </form>
  </div>
</div>

<div class="modal" id="modalApprove">
    <div class="modal-content" style="
        width: 420px;
        padding: 28px;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 6px 30px rgba(0,0,0,0.25);
        animation: scaleIn .25s ease;
    ">
        <span class="close-btn" onclick="closeApproveModal()">&times;</span>

        <h2 style="text-align:center; color:#1e3a8a; font-weight:700; margin-bottom:20px;">
            🆕 Approve Barang Baru
        </h2>

        <form method="POST" style="display:flex; flex-direction:column; gap:15px;">

            <input type="hidden" name="approve_new" id="approve_new_id">

            <div>
                <label style="font-weight:600;">Nama Barang</label>
                <input id="nama_barang_baru" readonly 
                    style="
                        width:100%; 
                        padding:10px; 
                        border-radius:8px; 
                        border:1px solid #d1d5db;
                        background:#f3f4f6;
                        font-weight:600;
                    ">
            </div>

            <div>
                <label style="font-weight:600;">Kategori</label>
                <select name="kategori_baru" required
                    style="
                        width:100%; padding:10px; border-radius:8px;
                        border:1px solid #d1d5db;
                    ">
                    <option value="">-- Pilih Kategori --</option>
                    <?php 
                        $c=$con->query("SELECT DISTINCT Kategori FROM MsBarang WHERE Kategori<>''");
                        while($x=$c->fetch_assoc()){
                            echo "<option>{$x['Kategori']}</option>";
                        }
                    ?>
                </select>
            </div>

            <div>
                <label style="font-weight:600;">Lokasi Rak</label>
                <select name="lokasi_baru" required
                    style="
                        width:100%; padding:10px; 
                        border-radius:8px; 
                        border:1px solid #d1d5db;
                    ">
                    <option value="">-- Pilih Rak --</option>
                    <?php 
                        $r=$con->query("SELECT DISTINCT LokasiRak FROM MsBarang WHERE LokasiRak<>''");
                        while($x=$r->fetch_assoc()){
                            echo "<option>{$x['LokasiRak']}</option>";
                        }
                    ?>
                </select>
            </div>

            <div>
                <label style="font-weight:600;">Harga Barang</label>
                <input type="number" name="harga_baru" required
                    style="
                        width:100%; padding:10px; 
                        border-radius:8px; 
                        border:1px solid #d1d5db;
                    ">
            </div>

            <button name="save_approve_new" class="btn-green" 
                style="
                    width:100%; 
                    padding:12px; 
                    border-radius:10px;
                    background:#16a34a;
                    font-size:15px;
                    font-weight:700;
                ">
                ✅ Approve Barang
            </button>
        </form>
    </div>
</div>


<div class="modal" id="modalReject">
  <div class="modal-content"><span class="close-btn" onclick="closeRejectModal()">&times;</span>
    <h2>❌ Tolak Request</h2>
    <form method="POST"><input type="hidden" name="reject_id" id="reject_id">
      <button name="reject_request" class="btn-red">Tolak</button>
    </form>
  </div>
</div>

<div class="modal" id="modalHapus">
  <div class="modal-content"><span class="close-btn" onclick="closeHapusModal()">&times;</span>
    <h2>🗑️ Hapus Barang</h2><p>Yakin ingin menghapus barang ini?</p>
    <form method="POST"><input type="hidden" name="hapus_id" id="hapus_id"><button name="hapus_barang" class="btn-red">Ya</button></form>
  </div>
</div>

<script>
function openModal(t){const m=document.getElementById('modalForm');const tt=document.getElementById('modalTitle');const b=document.getElementById('submitBtn');m.style.display='flex';
if(t==='masuk'){tt.textContent='📥 Barang Masuk';b.name='barang_masuk';b.textContent='Tambah Stok';}else{tt.textContent='📤 Barang Keluar';b.name='barang_keluar';b.textContent='Kurangi Stok';}}
function closeModal(){document.getElementById('modalForm').style.display='none';}
function openApproveModal(id,nama){document.getElementById('modalApprove').style.display='flex';document.getElementById('approve_new_id').value=id;document.getElementById('nama_barang_baru').value=nama;}
function closeApproveModal(){document.getElementById('modalApprove').style.display='none';}
function openRejectModal(id){document.getElementById('modalReject').style.display='flex';document.getElementById('reject_id').value=id;}
function closeRejectModal(){document.getElementById('modalReject').style.display='none';}
function hapusBarang(id){document.getElementById('modalHapus').style.display='flex';document.getElementById('hapus_id').value=id;}
function closeHapusModal(){document.getElementById('modalHapus').style.display='none';}
window.onclick=(e)=>{if(e.target.classList.contains('modal')){closeModal();closeApproveModal();closeRejectModal();closeHapusModal();}}
</script>
</body>
</html>
