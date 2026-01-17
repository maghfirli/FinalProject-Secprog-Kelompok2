<?php
require "../../controllers/session_check.php";
require_login("user");
require "../../controllers/connection.php";

$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    header("Location: ../login.php");
    exit;
}

$stmt = $con->prepare("SELECT userName, userEmail, Photo FROM MsUser WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) die("<h3>Data user tidak ditemukan.</h3>");

if (isset($_POST['update'])) {
    $name = trim($_POST['userName']);
    $photoName = $user['Photo'];

    if (!empty($_FILES['userPhoto']['name'])) {
        $targetDir = "../../uploads/user_photos/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        $fileTmp = $_FILES['userPhoto']['tmp_name'];
        $fileName = basename($_FILES['userPhoto']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedExt)) {
            if ($_FILES['userPhoto']['size'] <= 2 * 1024 * 1024) {
                $newName = uniqid("photo_") . "." . $fileExt;
                move_uploaded_file($fileTmp, $targetDir . $newName);
                $photoName = $newName;
            } else echo "<script>alert('Ukuran foto maksimal 2MB!');</script>";
        } else echo "<script>alert('Format file tidak diizinkan!');</script>";
    }

    $stmt = $con->prepare("UPDATE MsUser SET userName=?, Photo=? WHERE UserID=?");
    $stmt->bind_param("ssi", $name, $photoName, $userID);

    if ($stmt->execute()) {
        $_SESSION['userName'] = $name;
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='user_profile.php';</script>";
        exit;
    } else {
        echo "<script>alert('Terjadi kesalahan saat menyimpan.');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profil Saya</title>
<link rel="stylesheet" href="../../assets/user/dashboard.css">
<style>
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background: #f3f4f6;
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
}
.sidebar h2 {
  text-align: center;
  color: #60a5fa;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 15px;
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
  margin-bottom: 10px;
  cursor: pointer;
}
.profile-header h3 {
  font-size: 15px;
  color: #fff;
  margin: 0;
}
.sidebar ul {
  list-style: none;
  padding: 0;
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
  padding: 40px;
  flex: 1;
}

.profile-card {
  background: #fff;
  border-radius: 10px;
  padding: 40px;
  width: 80%;
  margin: 0 auto;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.profile-top {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 40px;
}
.profile-top img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 3px solid #2563eb;
  object-fit: cover;
  cursor: pointer;
}
.profile-top .info h2 {
  font-size: 20px;
  font-weight: 500;
  margin: 0;
}
.profile-top .info p {
  font-size: 14px;
  color: #6b7280;
  margin: 3px 0 0 0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px 40px;
}
label {
  font-weight: 500;
  font-size: 14px;
  color: #000;
  opacity: 0.8;
}
input {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  background: #f9f9f9;
}

.password-container {
  position: relative;
}
.password-container input {
  width: 100%;
  padding-right: 40px;
}
.password-container .toggle-eye {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 18px;
  color: #6b7280;
}

button {
  margin-top: 30px;
  background: #2563eb;
  color: #fff;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}
button:hover {
  background: #1e40af;
}
</style>
</head>
<body>

<div class="sidebar">
  <h2>User Panel</h2>
  <div class="profile-header">
    <img id="sidebarPhoto" src="<?= !empty($user['Photo']) ? '../../uploads/user_photos/' . htmlspecialchars($user['Photo']) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png' ?>" alt="Foto Profil" onclick="document.getElementById('userPhoto').click()">
    <h3><?= htmlspecialchars($user['userName']) ?></h3>
  </div>
  <ul>
    <li><a href="user_profile.php" class="active">👤 Profil Saya</a></li>
    <li><a href="dashboard.php">🏠 Dashboard</a></li>
    <li><a href="cekstok.php">📦 Cek Stok</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <div class="profile-card">
    <div class="profile-top">
      <img id="preview" src="<?= !empty($user['Photo']) ? '../../uploads/user_photos/' . htmlspecialchars($user['Photo']) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png' ?>" alt="Foto Profil" onclick="document.getElementById('userPhoto').click()">
      <div class="info">
        <h2><?= htmlspecialchars($user['userName']) ?></h2>
        <p><?= htmlspecialchars($user['userEmail']) ?></p>
      </div>
    </div>

   <form method="POST" enctype="multipart/form-data">
  <input type="file" name="userPhoto" id="userPhoto" accept=".jpg,.jpeg,.png,.gif"
         style="display:none" onchange="previewImage(event)">

  <div class="form-grid">

    <div>
      <label>Nama Lengkap</label>
      <input type="text" name="userName"
             value="<?= htmlspecialchars($user['userName']) ?>" required>
    </div>

    <div style="grid-column: span 2; text-align:right;">
      <button type="submit" name="update">💾 Simpan Perubahan</button>
    </div>

  </div>
</form>


<script>
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = () => {
    document.getElementById('preview').src = reader.result;
    document.getElementById('sidebarPhoto').src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
}

function togglePassword() {
  const input = document.getElementById('userPassword');
  const eye = document.querySelector('.toggle-eye');
  if (input.type === 'password') {
    input.type = 'text';
    eye.textContent = '👁️';
  } else {
    input.type = 'password';
    eye.textContent = '👁️';
  }
}
</script>
</body>
</html>
