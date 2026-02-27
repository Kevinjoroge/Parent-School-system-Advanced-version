<?php
session_start();
include('config/db.php');

$teacher_id = $_SESSION['user_id'];

$messages = mysqli_query($conn,"
    SELECT m.*, s.name as student_name
    FROM messages m
    JOIN students s ON m.student_id = s.id
    WHERE m.sender_id='$teacher_id'
    AND m.sender_role='teacher'
    ORDER BY m.created_at DESC
");

echo "<h2>Sent Messages</h2><hr>";

while($msg=mysqli_fetch_assoc($messages)){
    echo "<div style='border:1px solid #ccc;padding:10px;margin-bottom:10px;'>";
    echo "<strong>To Parent of:</strong> ".$msg['student_name']."<br>";
    echo "<p>".$msg['message']."</p>";
    echo "<small>".$msg['created_at']."</small>";
    echo "</div>";
}
?>