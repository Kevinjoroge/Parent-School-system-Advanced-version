<?php
session_start();
include('config/db.php');

/* ===========================
   REGISTER TEACHER (FIRST TIME)
   =========================== */
if(isset($_POST['register'])){

    $code = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if teacher exists in teachers table
    $check_teacher = mysqli_query($conn,
        "SELECT * FROM teachers WHERE unique_code='$code'"
    );

    if(mysqli_num_rows($check_teacher) > 0){

        $teacher = mysqli_fetch_assoc($check_teacher);
        $teacher_id = $teacher['id'];

        // Check if already registered
        $check_user = mysqli_query($conn,
            "SELECT * FROM users WHERE username='$code' AND role='teacher'"
        );

        if(mysqli_num_rows($check_user) > 0){
            echo "Teacher already registered. Please login.";
        } else {

            // Insert into users table with teacher_id link
            mysqli_query($conn,"
                INSERT INTO users(username,password,role,teacher_id)
                VALUES('$code','$pass','teacher','$teacher_id')
            ");

            echo "Registration Successful. You can now login.";
        }

    } else {
        echo "Teacher Code not found in system.";
    }
}


/* ===========================
   LOGIN TEACHER
   =========================== */
if(isset($_POST['login'])){

    $code = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    $query = mysqli_query($conn,"
        SELECT * FROM users
        WHERE username='$code' AND role='teacher'
    ");

    if(mysqli_num_rows($query) > 0){

        $user = mysqli_fetch_assoc($query);

        if(password_verify($pass, $user['password'])){

            // IMPORTANT: Use teacher_id not users.id
            $_SESSION['teacher_id'] = $user['teacher_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            header("Location: teacher_dashboard.php");
            exit();

        } else {
            echo "Invalid Password.";
        }

    } else {
        echo "Teacher account not found.";
    }
}
?>

<h2>Teacher Login</h2>

<form method="POST">
    <input type="text" name="username" placeholder="Teacher Code" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>

    <button type="submit" name="login">Login</button>
    <button type="submit" name="register">Register First Time</button>
</form>