<?php

$msg    = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
$error  = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WareHouse</title>
    <link rel="stylesheet" href="../assets/global.css">
    <link rel="stylesheet" href="../assets/navbar.css">
    <link rel="stylesheet" href="../assets/footer.css">
    <style>
        body {
            background: #050A24;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
            min-height: 100vh;
        }

        .navbar {
            position: absolute;
            width: 100%;
            background: transparent;
            top: 0;
            left: 0;
            z-index: 10;
        }
        .navbar a#logo, .navbar .kiri ul li a {
            color: #FFFFFF !important;
        }

        .blur-ellipse-1, .blur-ellipse-2 {
            position: absolute;
            width: 379px;
            height: 379px;
            background: #2D55FB;
            filter: blur(275px);
            z-index: 0;
        }
        .blur-ellipse-1 { left: 0; bottom: 0; }
        .blur-ellipse-2 { right: 0; top: 0; }

        .forgotContainer {
            position: relative;
            width: 100%;
            min-height: 900px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 120px;
            z-index: 2;
        }

        .forgotBox {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 48px 72px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            width: 540px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            z-index: 5;
        }

        .forgotBox h2 {
            font-weight: 600;
            font-size: 28px;
            color: #101828;
            margin-bottom: 8px;
        }

        .forgotBox p {
            color: #475467;
            font-size: 15px;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .inputWrapper {
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 12px 16px;
            gap: 5px;
            width: 100%;
            border: 2px solid #D1E9FF;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .inputWrapper input {
            border: none;
            outline: none;
            flex-grow: 1;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #344054;
        }

        .submitBtn {
            width: 100%;
            height: 52px;
            background: #1570EF;
            border: none;
            border-radius: 8px;
            color: #FCFCFD;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            margin-top: 16px;
            transition: background 0.3s ease;
        }

        .submitBtn:hover { background: #125edb; }

        .backLogin {
            color: #1570EF;
            font-size: 15px;
            text-decoration: none;
            margin-top: 10px;
        }

        .footer {
            background: transparent;
            color: #FFFFFF;
            margin-top: auto;
            text-align: center;
            padding: 20px;
            width: 100%;
        }

        .message {
            font-size: 14px;
            margin-top: 5px;
        }

        .success { color: green; }
        .error { color: red; }
    </style>
</head>

<body>

    <div class="blur-ellipse-1"></div>
    <div class="blur-ellipse-2"></div>

    <nav class="navbar">
        <div class="kiri">
            <ul>
                <li><a href="./home.php" id="logo" style="font-weight: 700; font-size: 28px;">Ware<span style="color: #007DFA;">House</span></a></li>
            </ul>
        </div>
    </nav>

    <div class="forgotContainer">
        <div class="forgotBox">

            <h2>Forgot Password</h2>
            <p>Enter your registered email. We will send you a reset link.</p>

            <?php if ($msg): ?>
                <p class="message success"><?= $msg ?></p>
            <?php elseif ($error): ?>
                <p class="message error"><?= $error ?></p>
            <?php endif; ?>


            <form action="../controllers/forgot_process.php" method="POST">
                <div class="inputWrapper">
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <button type="submit" class="submitBtn">Send Reset Link</button>

                <a href="./login.php" class="backLogin">← Back to Login</a>
            </form>

        </div>
    </div>

    <footer class="footer">
        <p>Copyright © 2025 Secure Programming. All Rights Reserved</p>
    </footer>

</body>
</html>
