<?php
session_start();
include('config/db.php');

/* =========================
    REGISTER PARENT
   ========================= */
if(isset($_POST['register'])){
    $adm = mysqli_real_escape_string($conn,$_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT id FROM students WHERE admission_no='$adm'");

    if(mysqli_num_rows($check) > 0){
        $student_data = mysqli_fetch_assoc($check);
        $s_id = $student_data['id'];

        $user_exists = mysqli_query($conn, "SELECT id FROM users WHERE username='$adm' AND role='parent'");
        
        if(mysqli_num_rows($user_exists) == 0){
            mysqli_query($conn,
                "INSERT INTO users(username, password, role, student_id)
                 VALUES('$adm', '$pass', 'parent', '$s_id')"
            );
            echo "<script>alert('Registration Successful. Please Login.');</script>";
        } else {
            echo "<script>alert('This Admission Number is already registered.');</script>";
        }
    } else {
        echo "<script>alert('Admission Number not found.');</script>";
    }
}

/* =========================
    LOGIN PARENT
   ========================= */
if(isset($_POST['login'])){
    $adm = mysqli_real_escape_string($conn,$_POST['username']);
    $pass = $_POST['password'];

    $query = mysqli_query($conn,
        "SELECT * FROM users 
         WHERE username='$adm' AND role='parent'"
    );

    $user = mysqli_fetch_assoc($query);

    if($user && password_verify($pass,$user['password'])){
        $student_id = $user['student_id'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'parent';
        $_SESSION['admission_no'] = $adm;
        $_SESSION['student_id'] = $student_id;

        mysqli_query($conn,"
            INSERT INTO system_logs(user_role,user_id,activity)
            VALUES('parent','$student_id','Parent logged into the system')
        ");

        header("Location: parent_dashboard.php");
        exit();
    } else {
        $error_msg = "Invalid Admission Number or Password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Portal | Login</title>
    <style>
        :root {
            --primary-blue: #2c3e50;
            --accent-blue: #34495e;
            --light-gray: #f4f7f6;
            --text-dark: #333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-card {
            background: #fff;
            width: 400px;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-top: 5px solid var(--primary-blue);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-dark);
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #dcdde1;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .btn-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        button {
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        button[name="login"] {
            background-color: var(--primary-blue);
            color: white;
        }

        button[name="register"] {
            background-color: #ecf0f1;
            color: var(--primary-blue);
            border: 1px solid #bdc3c7;
        }

        button:hover {
            opacity: 0.9;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            text-decoration: none;
            color: #7f8c8d;
            font-size: 14px;
        }

        .back-link:hover {
            color: var(--primary-blue);
        }

        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="header">
        <h1>Parent Portal</h1>
        <p>Access your child's academic records</p>
    </div>

    <?php if(isset($error_msg)): ?>
        <div class="error-box"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Admission Number</label>
            <input type="text" name="username" placeholder="e.g. 1234" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="btn-container">
            <button type="submit" name="login">Sign In</button>
            <button type="submit" name="register">Register</button>
        </div>
    </form>

    <a href="index.php" class="back-link">← Return to Homepage</a>
</div>

</body>
</html>