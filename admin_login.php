<?php
session_start();
include('config/db.php');

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    if($username == "admin2026@gmail.com" && $password == "12345678"){

        // Create session
        $_SESSION['admin_id'] = 1;
        $_SESSION['role'] = 'admin';

        header("Location: admin_dashboard.php");
        exit();

    } else {
        echo "<p style='color:red;'>Invalid Admin Login</p>";
    }
}
?>

<h2>Admin Login</h2>

<form method="POST">
<input type="text" name="username" placeholder="Admin Email" required><br><br>
<input type="password" name="password" placeholder="Password" required><br><br>
<button name="login">Login</button>
</form>