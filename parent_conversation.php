<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$parent_id = $_SESSION['user_id'];

if(!isset($_GET['student_id'])){
    echo "No student selected.";
    exit();
}

$student_id = $_GET['student_id'];

/* Get teacher id (latest teacher in conversation) */
$teacher_query = mysqli_query($conn,"
    SELECT sender_id FROM messages
    WHERE student_id='$student_id'
    AND sender_role='teacher'
    ORDER BY created_at DESC
    LIMIT 1
");

$teacher = mysqli_fetch_assoc($teacher_query);
$teacher_id = $teacher['sender_id'];

/* Send Message */
if(isset($_POST['send'])){
    $message = mysqli_real_escape_string($conn,$_POST['message']);

    mysqli_query($conn,"
        INSERT INTO messages(student_id,sender_role,sender_id,receiver_role,receiver_id,message)
        VALUES('$student_id','parent','$parent_id','teacher','$teacher_id','$message')
    ");
}

/* Fetch conversation */
$chat = mysqli_query($conn,"
    SELECT * FROM messages
    WHERE student_id='$student_id'
    ORDER BY created_at ASC
");
?>

<h2>Conversation</h2>

<div style="border:1px solid #ccc; padding:10px; height:400px; overflow-y:scroll;">

<?php
while($row = mysqli_fetch_assoc($chat)){

    if($row['sender_role'] == 'parent'){
        echo "<div style='text-align:right; margin:10px; background:#f2f2f2; padding:5px;'>
                <b>You:</b><br>
                ".$row['message']."<br>
                <small>".$row['created_at']."</small>
              </div>";
    } else {
        echo "<div style='text-align:left; margin:10px;'>
                <b>Teacher:</b><br>
                ".$row['message']."<br>
                <small>".$row['created_at']."</small>
              </div>";
    }
}
?>

</div>

<br>

<form method="POST">
<textarea name="message" required style="width:100%; height:60px;"></textarea><br><br>
<button type="submit" name="send">Send</button>
</form>