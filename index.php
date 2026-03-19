<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrated Parent–School Information System</title>
    <style>
        :root {
            --primary-blue: #0a2a66;
            --secondary-blue: #1c448e;
            --light-bg: #f4f7f6;
            --white: #ffffff;
            --text-dark: #333333;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* Flex ensures the background covers the full viewport */
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
        }

        /* MODERN SEMI-TRANSPARENT OVERLAY */
        .overlay {
            background: linear-gradient(135deg, rgba(10, 42, 102, 0.8) 0%, rgba(0, 0, 0, 0.6) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* FORMAL LOGIN CARD */
        .container {
            background: var(--white);
            padding: 50px 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            border-top: 5px solid var(--primary-blue);
        }

        .logo-placeholder {
            font-size: 40px;
            color: var(--primary-blue);
            margin-bottom: 10px;
        }

        .system-title {
            font-size: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 5px;
            line-height: 1.2;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        /* FORMAL BUTTONS */
        .role-link {
            text-decoration: none;
            display: block;
            margin-bottom: 15px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--primary-blue);
            border-radius: 8px;
            background: var(--white);
            color: var(--primary-blue);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: var(--primary-blue);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(10, 42, 102, 0.2);
        }

        /* Footer Info */
        .footer-text {
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <div class="logo-placeholder">🏫</div>
        <div class="system-title">School Information System</div>
        <div class="subtitle">Secure Gateway &sdot; Select Your Role</div>

        <a href="parent_login.php" class="role-link"><button class="btn">Parent Portal</button></a>
        <a href="teacher_login.php" class="role-link"><button class="btn">Teacher Portal</button></a>
        <a href="admin_login.php" class="role-link"><button class="btn">Administrative Access</button></a>

        <div class="footer-text">
            © 2026 Integrated School Management. All rights reserved.
        </div>
    </div>
</div>

</body>
</html>