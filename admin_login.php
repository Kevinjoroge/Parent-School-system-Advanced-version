<?php
session_start();
include('config/db.php');

$error = "";

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded credentials as per your logic
    if($username == "admin2026@gmail.com" && $password == "12345678"){
        $_SESSION['admin_id'] = 1;
        $_SESSION['role'] = 'admin';

        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Access Denied: Invalid Administrative Credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Administration | Login</title>
    <style>
        :root {
            --admin-dark: #0a0b10;
            --admin-primary: #1a1c23;
            --admin-accent: #3498db;
            --error-red: #ff4757;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-overlay {
            background: rgba(10, 11, 16, 0.92); /* Darker, more serious overlay */
            width: 100%;
            height: 100%;
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background: var(--admin-primary);
            width: 100%;
            max-width: 380px;
            padding: 50px 40px;
            border-radius: 4px;
            border-top: 4px solid var(--admin-accent);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            text-align: center;
            z-index: 10;
        }

        .login-box h2 {
            color: var(--white);
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 300;
        }

        .login-box p {
            color: #7f8c8d;
            font-size: 12px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        input {
            width: 100%;
            background: #2c313c;
            border: 1px solid #3d4451;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 3px;
            color: white;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--admin-accent);
            outline: none;
            background: #343b48;
        }

        button[name="login"] {
            width: 100%;
            padding: 15px;
            background: var(--admin-accent);
            color: white;
            border: none;
            border-radius: 3px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button[name="login"]:hover {
            background: #2980b9;
        }

        .error-msg {
            background: rgba(255, 71, 87, 0.1);
            color: var(--error-red);
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
            font-size: 13px;
            border: 1px solid var(--error-red);
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #7f8c8d;
            text-decoration: none;
            font-size: 13px;
            transition: 0.3s;
        }

        .back-link:hover {
            color: var(--white);
        }

        .shield-icon {
            font-size: 40px;
            color: var(--admin-accent);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="admin-overlay">
    <div class="login-box">
        <div class="shield-icon">🛡️</div>
        <h2>System Admin</h2>
        <p>Restricted Access Portal</p>

        <?php if($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Admin Username" required autocomplete="off">
            <input type="password" name="password" placeholder="System Password" required>
            
            <button type="submit" name="login">Authorize Entry</button>
        </form>

        <a href="index.php" class="back-link">Return to Public Site</a>
    </div>
</div>

</body>
</html>