<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

if(!isset($_GET['student_id'])){
    echo "Invalid Access";
    exit();
}

$student_id = $_GET['student_id'];

/* 🔎 GET PARENT FROM USERS TABLE USING student_id */
$parent_query = mysqli_query($conn,"
    SELECT id FROM users
    WHERE role='parent'
    AND student_id='$student_id'
");

$parent = mysqli_fetch_assoc($parent_query);
$parent_id = $parent['id'];

/* SEND REPLY */
if(isset($_POST['reply'])){

    $message = mysqli_real_escape_string($conn,$_POST['message']);

    mysqli_query($conn,"
        INSERT INTO messages(student_id,sender_role,sender_id,receiver_role,receiver_id,message)
        VALUES('$student_id','teacher','$teacher_id','parent','$parent_id','$message')
    ");

    echo "Reply sent successfully.";
}
?>

<h2>Reply to Parent</h2>

<form method="POST">
<label>Reply Message:</label><br>
<textarea name="message" required></textarea><br><br>
<button type="submit" name="reply">Send Reply</button>
</form>