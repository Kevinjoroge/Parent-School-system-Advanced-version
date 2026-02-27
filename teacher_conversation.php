<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

if(!isset($_GET['student_id'])){
    echo "No student selected.";
    exit();
}

$student_id = $_GET['student_id'];

/* Get parent user id */
$parent_query = mysqli_query($conn,"
    SELECT id FROM users
    WHERE role='parent' AND student_id='$student_id'
");
$parent = mysqli_fetch_assoc($parent_query);
$parent_id = $parent['id'];

/* Send Message */
if(isset($_POST['send'])){
    $message = mysqli_real_escape_string($conn,$_POST['message']);

    mysqli_query($conn,"
        INSERT INTO messages(student_id,sender_role,sender_id,receiver_role,receiver_id,message)
        VALUES('$student_id','teacher','$teacher_id','parent','$parent_id','$message')
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

    if($row['sender_role'] == 'teacher'){
        echo "<div style='text-align:left; margin:10px;'>
                <b>You:</b><br>
                ".$row['message']."<br>
                <small>".$row['created_at']."</small>
              </div>";
    } else {
        echo "<div style='text-align:right; margin:10px; background:#f2f2f2; padding:5px;'>
                <b>Parent:</b><br>
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