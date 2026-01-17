
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `barangkeluar` (
  `KeluarID` int(11) NOT NULL,
  `BarangID` varchar(225) NOT NULL,
  `Jumlah` int(11) NOT NULL,
  `Tanggal` datetime DEFAULT current_timestamp(),
  `UserID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `barangkeluar` (`KeluarID`, `BarangID`, `Jumlah`, `Tanggal`, `UserID`) VALUES
(1, 'BRG20251101-891', 1, '2025-11-01 21:07:28', NULL),
(2, 'BRG20251101-241', 1, '2025-11-01 21:08:14', NULL);


CREATE TABLE `barangmasuk` (
  `MasukID` int(11) NOT NULL,
  `BarangID` varchar(225) NOT NULL,
  `Jumlah` int(11) NOT NULL,
  `Tanggal` datetime DEFAULT current_timestamp(),
  `UserID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `msbarang` (
  `BarangID` varchar(50) NOT NULL,
  `NamaBarang` varchar(225) NOT NULL,
  `Kategori` varchar(100) DEFAULT NULL,
  `Harga` decimal(12,2) DEFAULT NULL,
  `LokasiRak` varchar(50) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `IsDeleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `msbarang` (`BarangID`, `NamaBarang`, `Kategori`, `Harga`, `LokasiRak`, `UserID`, `IsDeleted`) VALUES
('BRG001', 'Laptop Asus', 'Elektronik', '12000000.00', 'Rak A1', 2, 0),
('BRG002', 'Mouse Logitech', 'Aksesoris', '250000.00', 'Rak B1', 2, 0),
('BRG20250917-001', 'Laptop Victus', 'Elektronik', '12000000.00', 'Rak A1', 2, 0),
('BRG20251104101', 'paracetamol', 'Obat', '15.00', 'Rak Belum Ditentukan', 2, 0),
('BRG20251104302', 'kacamata', 'Aksesoris', '7.00', 'Rak A1', 2, 0),
('BRG20251119-002', 'keyboard', 'elektronik', '75000.00', 'a1', NULL, 0),
('BRG20251119-003', 'ROTI', 'MAKANAN', '12451.00', 'A', NULL, 0);


CREATE TABLE `mscategory` (
  `CategoryID` varchar(225) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Category` varchar(225) NOT NULL,
  `categorydate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `msinventory` (
  `ItemID` int(11) NOT NULL,
  `BarangID` varchar(50) NOT NULL,
  `Stock` int(11) DEFAULT 0,
  `MinStock` int(11) DEFAULT 0,
  `LastUpdate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `msinventory` (`ItemID`, `BarangID`, `Stock`, `MinStock`, `LastUpdate`) VALUES
(1, 'BRG001', 64, 5, '2025-11-04 16:43:46'),
(2, 'BRG002', 3, 5, '2025-09-23 20:42:03'),
(3, 'BRG20250917-001', 18, 5, '2025-11-04 16:44:40'),
(16, 'BRG20251104302', 37, 5, '2025-11-22 17:30:27'),
(17, 'BRG20251104101', 12, 5, '2025-11-04 20:49:11'),
(19, 'BRG20251119-002', 0, 10, '2025-11-19 22:22:46');


DELIMITER $$
CREATE TRIGGER `trg_check_negative_stock` BEFORE UPDATE ON `msinventory` FOR EACH ROW BEGIN
    IF NEW.Stock < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok tidak boleh negatif!';
    END IF;
END
$$
DELIMITER ;


CREATE TABLE `msrequest` (
  `RequestID` varchar(50) NOT NULL,
  `BarangID` varchar(225) DEFAULT NULL,
  `NamaBarangBaru` varchar(225) DEFAULT NULL,
  `KategoriBaru` varchar(100) DEFAULT NULL,
  `LokasiRakBaru` varchar(50) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Type` enum('in','out','new') NOT NULL DEFAULT 'out',
  `Jumlah` int(11) NOT NULL,
  `Status` enum('pending','approved','rejected') DEFAULT 'pending',
  `AdminID` int(11) DEFAULT NULL,
  `RequestDate` datetime DEFAULT current_timestamp(),
  `ApproveDate` datetime DEFAULT NULL,
  `RejectReason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `msuser` (
  `UserID` int(11) NOT NULL,
  `userName` varchar(225) NOT NULL,
  `userEmail` varchar(225) NOT NULL,
  `userPassword` varchar(255) NOT NULL,
  `userRole` enum('admin','user') NOT NULL DEFAULT 'user',
  `userStatus` enum('active','inactive') DEFAULT 'active',
  `resetToken` varchar(255) DEFAULT NULL,
  `resetExpiry` datetime DEFAULT NULL,
  `isVerified` tinyint(1) DEFAULT 0,
  `isApproved` tinyint(1) DEFAULT 0,
  `verifyToken` varchar(255) DEFAULT NULL,
  `otpCode` varchar(6) DEFAULT NULL,
  `otpExpiry` datetime DEFAULT NULL,
  `Photo` varchar(255) DEFAULT NULL,
  `otp_hash` varchar(255) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `otp_purpose` varchar(20) DEFAULT NULL,
  `otp_attempts` int(11) DEFAULT 0,
  `otp_last_sent_at` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `status` enum('pending','active','disabled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `barangkeluar`
  ADD PRIMARY KEY (`KeluarID`),
  ADD KEY `FK_barangkeluar_barang` (`BarangID`),
  ADD KEY `FK_barangkeluar_UserID` (`UserID`);

ALTER TABLE `barangmasuk`
  ADD PRIMARY KEY (`MasukID`),
  ADD KEY `FK_barangmasuk_barang` (`BarangID`),
  ADD KEY `FK_barangmasuk_UserID` (`UserID`);

ALTER TABLE `msbarang`
  ADD PRIMARY KEY (`BarangID`),
  ADD KEY `FK_msbarang_user` (`UserID`);

ALTER TABLE `mscategory`
  ADD PRIMARY KEY (`CategoryID`),
  ADD KEY `FK_Category_User` (`UserID`);

ALTER TABLE `msinventory`
  ADD PRIMARY KEY (`ItemID`),
  ADD KEY `BarangID` (`BarangID`);

ALTER TABLE `msrequest`
  ADD PRIMARY KEY (`RequestID`),
  ADD KEY `FK_msrequest_barang` (`BarangID`),
  ADD KEY `FK_msrequest_UserID` (`UserID`),
  ADD KEY `FK_msrequest_AdminID` (`AdminID`);

ALTER TABLE `msuser`
  ADD PRIMARY KEY (`UserID`);

ALTER TABLE `barangkeluar`
  MODIFY `KeluarID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `barangmasuk`
  MODIFY `MasukID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `msinventory`
  MODIFY `ItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `msuser`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `barangkeluar`
  ADD CONSTRAINT `FK_barangkeluar_UserID` FOREIGN KEY (`UserID`) REFERENCES `msuser` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `barangmasuk`
  ADD CONSTRAINT `FK_barangmasuk_UserID` FOREIGN KEY (`UserID`) REFERENCES `msuser` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_barangmasuk_barang` FOREIGN KEY (`BarangID`) REFERENCES `msbarang` (`BarangID`);

ALTER TABLE `msinventory`
  ADD CONSTRAINT `FK_msinventory_barang` FOREIGN KEY (`BarangID`) REFERENCES `msbarang` (`BarangID`);

ALTER TABLE `msrequest`
  ADD CONSTRAINT `FK_msrequest_AdminID` FOREIGN KEY (`AdminID`) REFERENCES `msuser` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_msrequest_UserID` FOREIGN KEY (`UserID`) REFERENCES `msuser` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_msrequest_barang` FOREIGN KEY (`BarangID`) REFERENCES `msbarang` (`BarangID`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;