<?php
session_start();
include('config/db.php');

/* REGISTER PARENT */
if(isset($_POST['register'])){

    $adm = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn,
        "SELECT * FROM students WHERE admission_no='$adm'"
    );

    if(mysqli_num_rows($check) > 0){

        mysqli_query($conn,
            "INSERT INTO users(username,password,role)
             VALUES('$adm','$pass','parent')"
        );

        echo "Registration Successful. Please Login.";

    } else {
        echo "Admission Number not found.";
    }
}

/* LOGIN PARENT */
if(isset($_POST['login'])){

    $adm = $_POST['username'];
    $pass = $_POST['password'];

    $query = mysqli_query($conn,
        "SELECT * FROM users 
         WHERE username='$adm' AND role='parent'"
    );

    $user = mysqli_fetch_assoc($query);

    if($user && password_verify($pass,$user['password'])){

        // SET SESSION VARIABLES
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'parent';
        $_SESSION['admission_no'] = $adm;

        header("Location: parent_dashboard.php");
        exit();

    } else {
        echo "Invalid Login";
    }
}
?>

<h2>Parent Login</h2>

<form method="POST">
<input type="text" name="username" placeholder="Admission Number" required><br><br>
<input type="password" name="password" placeholder="Password" required><br><br>

<button name="login">Login</button>
<button name="register">Register First Time</button>
</form>