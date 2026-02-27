<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$parent_id = $_SESSION['user_id'];
?>

<h2>Sent Messages</h2>

<?php
$query = mysqli_query($conn,"
    SELECT messages.*, students.name AS student_name
    FROM messages
    JOIN students ON messages.student_id = students.id
    WHERE sender_role='parent'
    AND sender_id='$parent_id'
    ORDER BY created_at DESC
");

if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){
        echo "<hr>";
        echo "<b>Student:</b> ".$row['student_name']."<br>";
        echo "<b>Message:</b> ".$row['message']."<br>";
        echo "<b>Date:</b> ".$row['created_at']."<br>";
    }
} else {
    echo "No sent messages.";
}
?>