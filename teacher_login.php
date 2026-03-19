<?php
session_start();
include('config/db.php');

$message = ""; // Variable to hold feedback messages

/* ===========================
   REGISTER TEACHER (FIRST TIME)
   =========================== */
if(isset($_POST['register'])){
    $code = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_teacher = mysqli_query($conn, "SELECT * FROM teachers WHERE unique_code='$code'");

    if(mysqli_num_rows($check_teacher) > 0){
        $teacher = mysqli_fetch_assoc($check_teacher);
        $teacher_id = $teacher['id'];

        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$code' AND role='teacher'");

        if(mysqli_num_rows($check_user) > 0){
            $message = "Teacher already registered. Please login.";
        } else {
            mysqli_query($conn,"INSERT INTO users(username,password,role,teacher_id) VALUES('$code','$pass','teacher','$teacher_id')");
            $message = "Registration Successful. You can now login.";
        }
    } else {
        $message = "Teacher Code not found in system.";
    }
}

/* ===========================
   LOGIN TEACHER
   =========================== */
if(isset($_POST['login'])){
    $code = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    $query = mysqli_query($conn,"SELECT * FROM users WHERE username='$code' AND role='teacher'");

    if(mysqli_num_rows($query) > 0){
        $user = mysqli_fetch_assoc($query);
        if(password_verify($pass, $user['password'])){
            $_SESSION['teacher_id'] = $user['teacher_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            $teacher_id = $user['teacher_id'];
            mysqli_query($conn,"INSERT INTO system_logs(user_role,user_id,activity) VALUES('teacher','$teacher_id','Logged into the system')");

            header("Location: teacher_dashboard.php");
            exit();
        } else {
            $message = "Invalid Password.";
        }
    } else {
        $message = "Teacher account not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal | Login</title>
    <style>
        :root {
            --primary-navy: #1a2a3a;
            --accent-blue: #34495e;
            --bg-soft: #f8f9fa;
            --text-main: #2d3436;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #dfe6e9;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .auth-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-top: 6px solid var(--primary-navy);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h1 {
            margin: 0;
            font-size: 26px;
            color: var(--primary-navy);
            letter-spacing: -0.5px;
        }

        .auth-header p {
            color: #636e72;
            font-size: 14px;
            margin-top: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: var(--primary-navy);
            text-transform: uppercase;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-navy);
            box-shadow: 0 0 0 3px rgba(26, 42, 58, 0.1);
        }

        .alert {
            background-color: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #ffeeba;
        }

        .btn-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        button[name="login"] {
            background-color: var(--primary-navy);
            color: #ffffff;
        }

        button[name="register"] {
            background-color: #ffffff;
            color: var(--primary-navy);
            border: 1px solid var(--primary-navy);
        }

        button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .footer-links {
            text-align: center;
            margin-top: 25px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .btn-back {
            text-decoration: none;
            color: #636e72;
            font-size: 14px;
        }

        .btn-back:hover {
            color: var(--primary-navy);
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-header">
        <h1>Teacher Portal</h1>
        <p>Teacher Information & Management System</p>
    </div>

    <?php if($message != ""): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Unique Code</label>
            <input type="text" name="username" placeholder="Enter your code" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="btn-stack">
            <button type="submit" name="login">Login to Dashboard</button>
            <button type="submit" name="register">First Time Registration</button>
        </div>
    </form>

    <div class="footer-links">
        <a href="index.php" class="btn-back">← Back to Main Page</a>
    </div>
</div>

</body>
</html>