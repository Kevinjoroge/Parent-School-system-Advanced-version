<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$admission_no = $_SESSION['admission_no'];

$student_query = mysqli_query($conn,"SELECT id FROM students WHERE admission_no='$admission_no'");
$student = mysqli_fetch_assoc($student_query);
$student_id = $student['id'];

if(isset($_POST['send'])){

    $teacher_id = $_POST['teacher_id'];
    $message = mysqli_real_escape_string($conn,$_POST['message']);

    mysqli_query($conn,"
        INSERT INTO messages
        (student_id,sender_role,sender_id,receiver_role,receiver_id,message)
        VALUES
        ('$student_id','parent','$student_id','teacher','$teacher_id','$message')
    ");

    echo "<p style='color:green;'>Message sent successfully!</p>";
}
?>

<h2>Message Teacher</h2>

<form method="POST">

<select name="teacher_id" required>
<option value="">Select Teacher</option>
<?php
$teachers = mysqli_query($conn,"SELECT * FROM teachers");
while($t=mysqli_fetch_assoc($teachers)){
    echo "<option value='".$t['id']."'>".$t['name']."</option>";
}
?>
</select>

<br><br>

<textarea name="message" rows="5" required placeholder="Type message..."></textarea><br><br>

<button name="send">Send</button>
<br>

<li><a href="parent_inbox.php">inbox</a></li>

</form>

<br>
<a href="parent_dashboard.php"><button>Back</button></a>