<?php
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

$error_message = '';
$success_message = '';

if (isset($_GET['error'])) {
    $error_message = sanitize_input($_GET['error']);
} elseif (isset($_GET['success'])) {
    $success_message = sanitize_input($_GET['success']);
}

$input_name = isset($_GET['name']) ? sanitize_input($_GET['name']) : '';
$input_email = isset($_GET['email']) ? sanitize_input($_GET['email']) : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ware House - Register</title>
    <link rel="stylesheet" href="../assets/global.css">
    <link rel="stylesheet" href="../assets/navbar.css">
    <link rel="stylesheet" href="../assets/footer.css">
    <script src="../script/jquery.js"></script>
    <script src="..//script/register.js"></script>
    <style>
        
        body {
            background: #FFFFFF;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .regisContainer {
            width: 100%;
            display: flex;
            min-height: 900px;
            flex: 1;
        }

        .left-bg {
            position: relative;
            width: 720px;
            height: 1074px;
            background: linear-gradient(0deg, #050A24, #050A24);
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .navbar {
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
            background: transparent;
            z-index: 10;
        }
        .navbar .kiri ul li a,
        .navbar #userlogged,
        .navbar #segitiga,
        .navbar #LogOut,
        .navbar #burgerbtn {
            color: #FFFFFF !important;
        }
        .navbar a#logo {
            font-family: 'Poppins';
            font-style: italic;
            font-weight: 700;
            font-size: 28px;
            color: #FFFFFF !important;
        }
        .navbar a#logo span {
            color: #007DFA !important;
        }

        .left-bg .logo {
            position: absolute;
            left: 80px;
            top: 64px;
            display: flex;
            gap: 8px;
        }
        
        .left-bg .welcome-text {
            position: absolute;
            width: 568px;
            left: 80px;
            top: 485px;
            font-family: 'Poppins', sans-serif;
            font-style: italic;
            font-weight: 300;
            font-size: 56px;
            line-height: 120%;
            background: linear-gradient(180deg, #FFFFFF 0%, rgba(255, 255, 255, 0.44) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .blur-ellipse-1 {
            position: absolute;
            width: 379px;
            height: 379px;
            left: 0px;
            top: 765px;
            background: #2D55FB;
            filter: blur(275px);
            z-index: 0;
        }
        .blur-ellipse-2 {
            position: absolute;
            width: 379px;
            height: 379px;
            left: 585px;
            top: -136px;
            background: #2D55FB;
            filter: blur(275px);
            z-index: 0;
        }

        .regisBox {
            position: relative;
            width: 500px;
            margin: auto;
            padding-top: 236px;
            padding-bottom: 50px;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }
        
        .regisTitle {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .regisTitle p {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 28px;
            line-height: 100%;
            color: #101828;
            margin: 0;
        }

        .inputanregis {
            display: flex;
            flex-direction: column;
            gap: 24px;
            width: 100%;
        }

        .inputField {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 400px;
        }
        
        .inputField label {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 16px;
            line-height: 100%;
            color: #344054;
            text-transform: capitalize;
        }
        
        .inputWrapper {
            box-sizing: border-box;
            display: flex;
            align-items: center;
            padding: 12px 16px;
            width: 100%;
            height: 48px;
            border: 1px solid #D0D5DD;
            border-radius: 8px;
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

        .inputField:nth-child(1) .inputWrapper,
        .inputField:nth-child(2) .inputWrapper {
            border: 3px solid #D1E9FF;
        }

        .agreement {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 400px;
            margin-top: 10px;
        }
        .agreement label {
            font-size: 14px;
            line-height: normal;
            color: #98A2B3;
        }

        .regisbawah {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 400px;
        }
        .regisbawah button.regisbtn {
            margin-bottom: 0;
        }


        .regisbtn {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 16px;
            width: 100%;
            height: 48px;
            background: #1570EF;
            border-radius: 8px;
            border: none;
            cursor: pointer;

            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            line-height: 100%;
            color: #FCFCFD;
        }
        
        .login-link-section {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            width: 400px;
            margin-top: 24px;
        }
        
        .login-link-section p {
            font-weight: 400;
            font-size: 16px;
            line-height: 100%;
            color: #98A2B3;
            margin: 0;
            text-transform: capitalize;
        }
        
        .login-link-section a {
            font-weight: 400;
            font-size: 16px;
            line-height: 100%;
            color: #1570EF;
            text-decoration: none;
            text-transform: capitalize;
        }
        
        .footer {
            background: #FFFFFF;
            color: #000000;
            width: 100%;
            padding: 20px 0;
            margin-top: auto;
        }
        .footer p {
             color: #000000;
        }
        
        .footer .icons {
             display: none;
        }

        @media (max-width: 1200px) {
            .left-bg {
                display: none;
            }
            .regisBox {
                width: 90%;
                max-width: 400px;
                margin: 50px auto;
                padding-top: 50px;
            }
            .inputField, .regisbawah, .agreement, .login-link-section {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    
    <div class="blur-ellipse-1"></div>
    <div class="blur-ellipse-2"></div>
    
    <nav class="navbar">
        <div class="kiri">
            <ul>
                <a href="./home.php" id="logo">Wear<span style="color: #007DFA;">House</span></a>
                <div class="catabout">

                </div>
            </ul>
        </div>
        <div class="kanan">
            <ul>
                <li><input type="checkbox" id="tri"></li>
                <li><p id="userlogged"></p></li>
                <li><label for="tri" id="segitiga">▼</label></li>
                <button id="LogOut" onclick="doLogOut()">LogOut</button>
                <div id="loginRegister">
                    <li><a href="./login.php">Login</a></li>
                    <li><a href="./register.php">Register</a></li>
                </div>
            </ul> 
        </div>
        <input type="checkbox" id="hamburger">
        <label for="hamburger" id="burgerbtn">&equiv;</label>
        <div class="navrespon">
            <div class="ddatas">
                <a href="./category.php">category</a>
                <a href="./aboutus.php">About Us</a>
            </div>
            <a href="./login.php">Login</a>
            <a href="./register.php">Register</a>
        </div>
    </nav>
    
    <div class="regisContainer">
        
        <div class="left-bg">
            <p class="welcome-text">Welcome. Start your journey now with our management system!</p>
        </div>
        
        <div class="regisBox">
            <div class="regisTitle">
                <p>Register</p>
                
                <?php if ($error_message): ?>
                    <p style="color: red; font-size: 14px; margin-top: -10px;">Error: <?= $error_message ?></p>
                <?php elseif ($success_message): ?>
                    <p style="color: green; font-size: 14px; margin-top: -10px;">Success: <?= $success_message ?></p>
                <?php endif; ?>
                
            </div>
            
            <form action="../controllers/register_process.php" method="POST">
                <div class="inputanregis">
                    
                    <div class="inputField">
                        <label for="name">Name</label>
                        <div class="inputWrapper">
                            <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?= $input_name ?>" required>
                        </div>
                    </div>
                    
                    <div class="inputField">
                        <label for="email">Email Address</label>
                        <div class="inputWrapper">
                            <input type="text" id="email" name="email" placeholder="Enter your email" value="<?= $input_email ?>" required>
                        </div>
                    </div>
                    
                    <div class="inputField">
                        <label for="password">Password</label>
                        <div class="inputWrapper">
                            <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
                        </div>
                    </div>
                    
                    <div class="inputField">
                        <label for="confirm"> Confirm Password</label>
                        <div class="inputWrapper">
                            <input type="password" id="confirm" name="confirm" placeholder="Re-enter your password" required>
                        </div>
                    </div>
                    
                    <div class="agreement">
                        <input type="checkbox" id="kotak" name="kotak" required>
                        <label for="kotak" id="ag">By clicking register, I agree to the **terms and conditions**</label>
                    </div>
                    
                    <div class="regisbawah">
                        <button type="submit" class="regisbtn">Register</button>
                    </div>
                    
                    <div class="login-link-section">
                        <p>Already have an account?</p>
                        <a href="./login.php">Log in</a>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footercontent">
            <div class="text">
                <p>Copyright &copy; 2025 Secure Programming Kelompok. All Rights Reserved</p>
            </div>
        </div>
    </footer>
    
    <script>
        if (typeof $('.homecontainer').hide === 'function') {
             $('.homecontainer').hide();
        }
    </script>
    <script src="../script/status.js"></script>
</body>
</html>