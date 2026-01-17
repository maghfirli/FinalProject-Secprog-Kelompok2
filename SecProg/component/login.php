<?php

$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ware House - Login</title>
    <link rel="stylesheet" href="../assets/global.css">
    <link rel="stylesheet" href="../assets/navbar.css">
    <link rel="stylesheet" href="../assets/footer.css">
    <script src="../script/jquery.js"></script>
    <script src="../script/login.js"></script>
    <style>

        body {
            background: #050A24; 
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
        }

 
        .navbar {
            position: absolute; 
            width: 100%;
            top: 0;
            left: 0;
            background: transparent; 
            z-index: 10;
        }
        .navbar a#logo, .navbar .kiri ul li a {
            color: #FFFFFF !important; 
        }
        .footer {
            background: transparent; 
            color: #FFFFFF;
            padding: 20px 0;
            width: 100%;
            margin-top: auto;
        }
        .footer p {
             color: #FFFFFF;
        }
        .footer img {
            filter: invert(1);
        }

        .newLoginContainer {
            position: relative;
            width: 100%;
            min-height: 900px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 100px; 
            padding-bottom: 100px;
            z-index: 1; 
        }

        .blur-ellipse-1 {
            position: absolute;
            width: 379px;
            height: 379px;
            left: 0px;
            top: 898px;
            background: #2D55FB;
            filter: blur(275px);
            z-index: 0;
        }
        .blur-ellipse-2 {
            position: absolute;
            width: 379px;
            height: 379px;
            right: 0px; 
            top: 0px;
            background: #2D55FB;
            filter: blur(275px);
            z-index: 0;
        }

        .loginFormBox {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 48px 72px;
            gap: 32px;
            width: 540px;
            box-sizing: border-box;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 5;
        }

        .loginTitleSection {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            width: 100%;
        }

        .loginTitleSection h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 28px;
            line-height: 100%;
            color: #101828;
            margin: 0;
        }

        .inputGroup {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 24px;
            width: 100%;
        }

        .inputField {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            width: 100%;
        }

        .inputField label {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 16px;
            line-height: 100%;
            color: #344054;
            text-transform: capitalize;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .inputField .forgot-link {
            font-weight: 400;
            font-size: 16px;
            line-height: 100%;
            color: #1570EF;
            text-decoration: none;
            cursor: pointer;
        }

        .inputWrapper {
            box-sizing: border-box;
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 12px 16px;
            gap: 5px;
            width: 100%;
            border: 1px solid #D0D5DD;
            border-radius: 8px;
        }
        .inputField:first-child .inputWrapper {
            border: 3px solid #D1E9FF;
        }

        .inputWrapper input[type="text"],
        .inputWrapper input[type="password"] {
            flex-grow: 1;
            border: none;
            outline: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 14px;
            line-height: 100%;
            color: #344054;
            padding: 0;
            background: none;
        }
        .inputWrapper input::placeholder {
             color: #98A2B3; 
        }


        .actionSection {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            width: 100%;
        }

        .loginbtn {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            padding: 16px;
            gap: 5px;
            width: 100%;
            height: 52px;
            background: #1570EF;
            border-radius: 8px;
            border: none;
            cursor: pointer;

            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            line-height: 100%;
            color: #FCFCFD;
            text-decoration: none;
            margin-top: 10px;
        }

        .signUpSection {

            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 8px;
        }

        .signUpSection p {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 16px;
            line-height: 100%;
            color: #98A2B3;
            margin: 0;
            text-transform: capitalize;
        }

        .signUpSection a {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 16px;
            line-height: 100%;
            color: #1570EF;
            text-decoration: none;
            text-transform: capitalize;
        }

    </style>
</head>
<body>

    <div class="blur-ellipse-1"></div>
    <div class="blur-ellipse-2"></div>

    <nav class="navbar">
        <div class="kiri">
            <ul>
                <li><a href="./home.php" id="logo" style="font-family: 'Poppins'; font-style: lily script one; font-weight: 700; font-size: 28px;">Ware<span style="color: #007DFA;">House</span></a></li>
            </ul>
        </div>
        <div class="kanan">
            <ul>
                <li><input type="checkbox" id="tri"></li>
                <li><p id="userlogged" style="color: #FFFFFF;"></p></li>
                <li><label for="tri" id="segitiga" style="color: #FFFFFF;">▼</label></li>
                <li><button id="LogOut" onclick="doLogOut()" style="color: #FFFFFF;">LogOut</button></li>
                <div id="loginRegister">
                    <li><a href="./login.php">Login</a></li>
                    <li><a href="./register.php">Register</a></li>
                </div>
            </ul> 
        </div>
        <input type="checkbox" id="hamburger">
        <label for="hamburger" id="burgerbtn" style="color: #FFFFFF;">&equiv;</label>
        <div class="navrespon">
            <div class="ddatas">
                <a href="./category.php">Category</a>
                <a href="./aboutus.php">About Us</a>
            </div>
            <a href="./login.php">Login</a>
            <a href="./register.php">Register</a>
        </div>
    </nav>

    <div class="newLoginContainer">
        <div class="loginFormBox">
            <div class="loginTitleSection">
                <h2>Login</h2>
                <?php if ($error_message): ?>
                    <p style="color: red; font-size: 14px;"><?= $error_message ?></p>
                <?php endif; ?>
            </div>

            <form action="../controllers/login_process.php" method="POST">
                <div class="inputGroup">
                    
                    <div class="inputField">
                        <label for="email">Email Address</label>
                        <div class="inputWrapper">
                            <input type="text" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                    </div>

                    <div class="inputField">
    <label>
        Password
        <a href="./forgot_password.php" class="forgot-link">Forgot?</a>
    </label>
    <div class="inputWrapper">
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

    </div>
</div>


                <div class="actionSection">
                    <button type="submit" class="loginbtn">Login now</button>
                    
                    <div class="signUpSection">
                        <p>Don't have an account?</p>
                        <a href="./register.php">Sign up</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="footercontent">
            <div class="text">
                <p>Copyright © 2025 Secure Programming. All Rights Reserved</p>
            </div>
        </div>
    </footer>

    <script src="../script/status.js"></script>
</body>
</html>