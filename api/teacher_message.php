<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

/* SEND MESSAGE */
if(isset($_POST['send'])){

    $student_id = $_POST['student_id'];
    $message = mysqli_real_escape_string($conn,$_POST['message']);

    /* 🔎 GET PARENT FROM USERS TABLE (NOT students table) */
    $parent_query = mysqli_query($conn,"
        SELECT id FROM users
        WHERE role='parent'
        AND student_id='$student_id'
    ");

    $parent = mysqli_fetch_assoc($parent_query);

    if($parent){

        $parent_id = $parent['id'];

        mysqli_query($conn,"
            INSERT INTO messages(student_id,sender_role,sender_id,receiver_role,receiver_id,message)
            VALUES('$student_id','teacher','$teacher_id','parent','$parent_id','$message')
        ");

        echo "Message sent successfully.";

    } else {
        echo "No parent account found for this student.";
    }
}
?>

<h2>Send Message to Parent</h2>

<form method="POST">

<label>Select Class:</label>
<select name="class_id" onchange="this.form.submit()">
<option value="">--Select Class--</option>

<?php
$class_query = mysqli_query($conn,"SELECT * FROM classes");
while($class = mysqli_fetch_assoc($class_query)){
    $selected = (isset($_POST['class_id']) && $_POST['class_id']==$class['id']) ? "selected" : "";
    echo "<option value='".$class['id']."' $selected>".$class['class_name']."</option>";
}
?>
</select>

<br><br>

<?php
if(isset($_POST['class_id']) && $_POST['class_id']!=""){

    $class_id = $_POST['class_id'];

    echo "<label>Select Student:</label>";
    echo "<select name='student_id' required>";

    $student_query = mysqli_query($conn,"
        SELECT * FROM students WHERE class_id='$class_id'
    ");

    while($student = mysqli_fetch_assoc($student_query)){
        echo "<option value='".$student['id']."'>".$student['name']."</option>";
    }

    echo "</select><br><br>";
}
?>

<label>Message:</label><br>
<textarea name="message" required></textarea><br><br>

<button type="submit" name="send">Send Message</button>
<br>
<li><a href="teacher_inbox.php">inbox</a></li>

</form>