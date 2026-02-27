<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$parent_id = $_SESSION['user_id'];

if(!isset($_GET['student_id'])){
    echo "Invalid Access";
    exit();
}

$student_id = $_GET['student_id'];

/* Get teacher who last messaged about this student */
$teacher_query = mysqli_query($conn,"
    SELECT sender_id FROM messages
    WHERE student_id='$student_id'
    AND sender_role='teacher'
    ORDER BY created_at DESC
    LIMIT 1
");

$teacher = mysqli_fetch_assoc($teacher_query);
$teacher_id = $teacher['sender_id'];

/* SEND REPLY */
if(isset($_POST['reply'])){

    $message = mysqli_real_escape_string($conn,$_POST['message']);

    mysqli_query($conn,"
        INSERT INTO messages(student_id,sender_role,sender_id,receiver_role,receiver_id,message)
        VALUES('$student_id','parent','$parent_id','teacher','$teacher_id','$message')
    ");

    echo "Reply sent successfully.";
}
?>

<h2>Reply to Teacher</h2>

<form method="POST">
<label>Reply Message:</label><br>
<textarea name="message" required></textarea><br><br>
<button type="submit" name="reply">Send Reply</button>
</form>