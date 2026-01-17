<?php

$adminName = $adminName ?? 'Admin';

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    background: linear-gradient(to bottom, #050A24, #0f1c4a, #1e3a8a);
    color: #fff;
    position: fixed;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 20px;
    box-shadow: 3px 0 12px rgba(0, 0, 0, 0.2);
}

.sidebar h2 { color: #60a5fa; margin-bottom: 20px; }
.sidebar ul { list-style: none; padding: 0; width: 100%; }

.sidebar ul li a {
    color: #e5e7eb; text-decoration: none; display: block; padding: 12px 25px;
    border-radius: 8px; transition: all .3s;
}
.sidebar ul li a:hover,
.sidebar ul li a.active {
    background: rgba(255, 255, 255, 0.15); color: #fff;
}

.profile-header { text-align: center; margin-bottom: 25px; }
.profile-header img {
    width: 80px; height: 80px; border-radius: 50%; border: 3px solid #60a5fa; margin-bottom: 10px;
}

.main-content { margin-left: 260px; padding: 30px; width: 100%; }
</style>

<div class="sidebar">
    <div class="profile-header">
        <img src="https://cdn-icons-png.flaticon.com/512/2202/2202112.png" alt="Admin">
        <h3><?=htmlspecialchars($adminName)?></h3>
    </div>
    <ul>
        <li><a href="dashboard.php" class="<?=($currentPage == 'dashboard.php' ? 'active' : '')?>">📊 Dashboard</a></li>
        <li><a href="masterdata.php" class="<?=($currentPage == 'masterdata.php' ? 'active' : '')?>">📦 Master Data</a></li>
        <li><a href="inventory.php" class="<?=($currentPage == 'inventory.php' ? 'active' : '')?>">📑 Inventory</a></li>
        <li><a href="users.php" class="<?=($currentPage == 'users.php' ? 'active' : '')?>">👥 Users</a></li>
        <li><a href="laporan.php" class="<?=($currentPage == 'laporan.php' ? 'active' : '')?>">📄 Laporan</a></li>
        
        <li><a href="../../logout.php">🚪 Logout</a></li> 
    </ul>
</div>