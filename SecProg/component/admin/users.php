<?php
session_start();
require "../../controllers/connection.php";

if (!isset($_SESSION['is_login']) || ($_SESSION['userRole'] ?? '') !== 'admin') {
    header("Location: /SecProg/component/login.php");
    exit;
}

$adminName = $_SESSION['userName'] ?? 'Admin';

$limit = 8;
$page  = max(1, (int)($_GET['page'] ?? 1));
$start = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$where = "WHERE 1=1 ";
$bindParams = [];
$bindTypes  = "";

if ($search !== '') {
    $where .= "AND (userName LIKE ? OR userEmail LIKE ?) ";
    $s = "%$search%";
    $bindParams[] = $s;
    $bindParams[] = $s;
    $bindTypes .= "ss";
}

$sql = "SELECT * FROM MsUser $where ORDER BY userRole DESC, userName ASC LIMIT ?, ?";
$stmt = $con->prepare($sql);

if ($bindTypes !== "") {
    $types2 = $bindTypes . "ii";
    $bindParams2 = array_merge($bindParams, [$start, $limit]);
    $stmt->bind_param($types2, ...$bindParams2);
} else {
    $stmt->bind_param("ii", $start, $limit);
}
$stmt->execute();
$result = $stmt->get_result();


$sqlCount = "SELECT COUNT(*) AS total FROM MsUser $where";
$countStmt = $con->prepare($sqlCount);
if ($bindTypes !== "") $countStmt->bind_param($bindTypes, ...$bindParams);
$countStmt->execute();
$totalData = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;
$totalPages = max(1, ceil($totalData / $limit));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_user'])) {
        $id    = "USR" . strtoupper(bin2hex(random_bytes(3)));
        $nama  = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $pass  = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type'=>'error','msg'=>'Email tidak valid'];
            header("Location: users.php"); exit;
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $con->prepare("
            INSERT INTO MsUser (UserID, userName, userEmail, userPassword, userRole, userStatus, isVerified, isApproved)
            VALUES (?, ?, ?, ?, ?, 'active', 1, 1)
        ");
        $stmt->bind_param("sssss", $id, $nama, $email, $hash, $role);
        $stmt->execute();

        $_SESSION['flash'] = ['type'=>'success','msg'=>'User baru berhasil ditambahkan'];
        header("Location: users.php"); exit;
    }

    if (isset($_POST['toggle_status'])) {
        $id = $_POST['user_id'];
        $status = $_POST['status'] === 'active' ? 'inactive' : 'active';

        $stmt = $con->prepare("UPDATE MsUser SET userStatus=? WHERE UserID=?");
        $stmt->bind_param("ss", $status, $id);
        $stmt->execute();

        $_SESSION['flash'] = ['type'=>'success','msg'=>'Status user berhasil diubah'];
        header("Location: users.php"); exit;
    }

    if (isset($_POST['approve_user'])) {
        $id = $_POST['approve_id'];
        $stmt = $con->prepare("UPDATE MsUser SET isApproved=1 WHERE UserID=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();

        $_SESSION['flash'] = ['type'=>'success','msg'=>'User berhasil di-approve'];
        header("Location: users.php"); exit;
    }

    if (isset($_POST['delete_user'])) {
        $id = $_POST['delete_id'];
        $stmt = $con->prepare("DELETE FROM MsUser WHERE UserID=?");
        $stmt->bind_param("s", $id);
        $stmt->execute();

        $_SESSION['flash'] = ['type'=>'success','msg'=>'User berhasil dihapus'];
        header("Location: users.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>👥 Manajemen User</title>
<link rel="stylesheet" href="../../assets/global.css">

<style>

body{margin:0;font-family:'Poppins',sans-serif;background:#f3f4f6;display:flex;}
.sidebar{
  width:250px;height:100vh;background:linear-gradient(to bottom,#050A24,#0f1c4a,#1e3a8a);
  color:#fff;position:fixed;left:0;top:0;display:flex;flex-direction:column;align-items:center;padding-top:20px;
}
.sidebar ul{list-style:none;padding:0;width:100%;}
.sidebar ul li a{
  color:#e5e7eb;text-decoration:none;display:block;padding:12px 25px;
  border-radius:8px;transition:all .3s;
}
.sidebar ul li a:hover,.sidebar ul li a.active{background:rgba(255,255,255,0.15);color:#fff;}
.profile-header{text-align:center;margin-bottom:25px;}
.profile-header img{
  width:80px;height:80px;border-radius:50%;border:3px solid #60a5fa;margin-bottom:10px;
}

.main{margin-left:260px;padding:30px;width:100%;}
h1{color:#1e3a8a;font-weight:700;margin-bottom:20px;}
.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
.btn{padding:8px 12px;border:none;border-radius:8px;cursor:pointer;font-weight:600;}
.btn-add{background:#2563eb;color:#fff;}
.btn-toggle{background:#6c5ce7;color:#fff;margin-right:5px;}
.btn-del{background:#dc2626;color:#fff;}
.btn-approve{background:#16a34a;color:#fff;margin-right:5px;}
.search-bar{display:flex;gap:10px;margin:15px 0;}
.search-bar input{padding:8px;border-radius:6px;border:1px solid #ccc;}

table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:10px;border-bottom:1px solid #e5e7eb;}
th{background:#1e3a8a;color:white;}

.status-badge{padding:4px 8px;border-radius:6px;font-size:12px;color:#fff;}
.active-badge{background:#22c55e;}
.inactive-badge{background:#9ca3af;}
.pending-badge{background:#f59e0b;}

.pagination{text-align:center;margin-top:15px;}
.pagination a{padding:8px 12px;background:#f1f1f1;margin:0 4px;border-radius:6px;text-decoration:none;color:#333;}
.pagination a.active{background:#2563eb;color:#fff;}

.modal{
  display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;
  background:rgba(0,0,0,.4);backdrop-filter:blur(4px);justify-content:center;align-items:center;
}
.modal-content{
  background:#fff;border-radius:14px;padding:25px;width:400px;
  box-shadow:0 4px 20px rgba(0,0,0,.3);animation:scaleIn .3s ease;
}
@keyframes scaleIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:scale(1)}}
</style>
</head>
<body>

<div class="sidebar">
  <div class="profile-header">
    <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png">
    <h3><?=htmlspecialchars($adminName)?></h3>
  </div>
  <ul>
    <li><a href="dashboard.php">📊 Dashboard</a></li>
    <li><a href="masterdata.php">📦 Master Data</a></li>
    <li><a href="inventory.php">📑 Inventory</a></li>
    <li><a href="users.php" class="active">👥 Users</a></li>
    <li><a href="laporan.php">📄 Laporan</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main">

  <h1>
    👥 Manajemen User
    <button class="btn btn-add" onclick="document.getElementById('modalAdd').style.display='flex'">
      + Tambah User
    </button>
  </h1>

  <div class="card">

    <table>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Role</th>
        <th>Email Verified</th>
        <th>Approved</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>

      <?php $no=$start+1; while($row=$result->fetch_assoc()): ?>

      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['userName']) ?></td>
        <td><?= htmlspecialchars($row['userEmail']) ?></td>
        <td><?= ucfirst($row['userRole']) ?></td>

        <td><?= $row['isVerified'] ? "✔️" : "<span class='pending-badge'>Pending</span>" ?></td>

        <td><?= $row['isApproved'] ? "🟢 Yes" : "<span class='pending-badge'>No</span>" ?></td>

        <td>
          <span class="status-badge <?= $row['userStatus']=='active'?'active-badge':'inactive-badge' ?>">
            <?= ucfirst($row['userStatus']) ?>
          </span>
        </td>

        <td style="white-space: nowrap;">

          <form method="POST" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $row['UserID'] ?>">
            <input type="hidden" name="status" value="<?= $row['userStatus'] ?>">
            <button class="btn btn-toggle" name="toggle_status">
              <?= $row['userStatus']=='active' ? 'Nonaktifkan' : 'Aktifkan' ?>
            </button>
          </form>

          <button class="btn btn-del" onclick="confirmDelete('<?= $row['UserID'] ?>')">
            Hapus
          </button>

          <?php if (!$row['isApproved']): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="approve_id" value="<?= $row['UserID'] ?>">
            <button class="btn btn-approve" name="approve_user">✔ Approve</button>
          </form>
          <?php endif; ?>

        </td>

      </tr>
      <?php endwhile; ?>

    </table>

  </div>

  <div class="pagination">
    <?php for($i=1;$i<=$totalPages;$i++): ?>
      <a href="users.php?page=<?= $i ?>&search=<?= urlencode($search) ?>"
         class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
</div>

<div class="modal" id="modalAdd">
  <div class="modal-content" style="padding:30px; width:420px; border-radius:18px;">
      
      <h2 style="margin-bottom:20px; color:#1e3a8a; font-weight:700; text-align:center;">
          Tambah User Baru
      </h2>

      <form method="POST" action="users.php" style="display:flex; flex-direction:column; gap:15px;">

          <div>
              <label style="font-weight:600;">Nama</label>
              <input type="text" name="nama" required
                     style="width:100%; padding:10px; border-radius:8px; border:1px solid #d1d5db;">
          </div>

          <div>
              <label style="font-weight:600;">Email</label>
              <input type="email" name="email" required
                     style="width:100%; padding:10px; border-radius:8px; border:1px solid #d1d5db;">
          </div>

          <div>
              <label style="font-weight:600;">Password</label>
              <input type="password" name="password" required
                     style="width:100%; padding:10px; border-radius:8px; border:1px solid #d1d5db;">
          </div>

          <div style="display:flex; justify-content:space-between; margin-top:10px;">
              <button type="submit" name="add_user"
                      class="btn btn-add"
                      style="flex:1; margin-right:10px; padding:10px;">
                  Simpan
              </button>

              <button type="button"
                      class="btn btn-del"
                      onclick="document.getElementById('modalAdd').style.display='none'"
                      style="flex:1; padding:10px;">
                  Batal
              </button>
          </div>

      </form>

  </div>
</div>

<form id="deleteForm" method="POST" action="users.php">
  <input type="hidden" name="delete_id" id="delete_id">
  <input type="hidden" name="delete_user" value="1">
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
<?php if(isset($_SESSION['flash'])): ?>
Swal.fire({
  toast:true,
  position:'top-end',
  showConfirmButton:false,
  timer:2500,
  icon:'<?= $_SESSION['flash']['type'] ?>',
  title:'<?= $_SESSION['flash']['msg'] ?>'
});
<?php unset($_SESSION['flash']); endif; ?>

function confirmDelete(id){
  Swal.fire({
    title:'Hapus user ini?',
    icon:'warning',
    showCancelButton:true,
    confirmButtonText:'Ya, Hapus',
    cancelButtonText:'Batal',
    confirmButtonColor:'#dc2626'
  })
  .then(res=>{
    if(res.isConfirmed){
      document.getElementById('delete_id').value=id;
      document.getElementById('deleteForm').submit();
    }
  });
}

window.onclick = function(e){
  let modal = document.getElementById('modalAdd');
  if(e.target === modal){
    modal.style.display = "none";
  }
}
</script>

</body>
</html>
