<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP</title>
    <link rel="stylesheet" href="../assets/global.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #050A24, #0A1A47);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .otp-container {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            padding: 40px 35px;
            width: 380px;
            border-radius: 18px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.4);
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .otp-container h2 {
            font-size: 26px;
            margin-bottom: 10px;
            font-weight: 600;
            color: #00A8FF;
        }

        .otp-container p {
            font-size: 14px;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .otp-input {
            width: 100%;
            font-size: 20px;
            padding: 14px;
            border-radius: 10px;
            border: none;
            outline: none;
            text-align: center;
            letter-spacing: 8px;
            background: rgba(255,255,255,0.15);
            color: #00D9FF;
            margin-bottom: 20px;
        }

        .otp-input::placeholder {
            color: rgba(255,255,255,0.4);
        }

        .btn-verify {
            width: 100%;
            background: #00A8FF;
            border: none;
            padding: 12px;
            font-size: 16px;
            color: #fff;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
        }

        .btn-verify:hover {
            background: #0090E0;
        }

        a {
            text-decoration: none;
            color: #87CEFA;
            display: inline-block;
            margin-top: 15px;
            transition: 0.3s;
        }

        a:hover {
            color: #B4DEFF;
        }

        .error-msg {
            background: rgba(255, 0, 0, 0.2);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid rgba(255,0,0,0.4);
            color: #ff6b6b;
        }
    </style>
</head>

<body>
    <div class="otp-container">

        <?php if (isset($_GET['error'])): ?>
            <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <h2>Verifikasi OTP</h2>
        <p>Masukkan kode OTP 6 digit yang telah dikirim ke email Anda</p>

        <form action="../controllers/otp_process.php" method="POST">
            <input type="text" name="otp" maxlength="6" class="otp-input" placeholder="••••••" required>
            <button type="submit" class="btn-verify">Verifikasi</button>
        </form>

        <a href="login.php">← Kembali ke login</a>
    </div>
</body>
</html>
