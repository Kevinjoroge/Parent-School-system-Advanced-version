<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['post'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $audience = $_POST['audience'];
    $event_date = $_POST['event_date'] ?: NULL;

    mysqli_query($conn,"
        INSERT INTO announcements(title,message,audience,event_date)
        VALUES('$title','$message','$audience','$event_date')
    ");

    echo "<p style='color:green;'>Announcement posted successfully!</p>";
}
?>

<h2>Post Announcement / Event</h2>

<form method="POST">
Title:<br>
<input type="text" name="title" required style="width:300px;"><br><br>

Message:<br>
<textarea name="message" required rows="5" cols="50"></textarea><br><br>

Audience:<br>
<select name="audience" required>
<option value="teachers">Teachers</option>
<option value="parents">Parents</option>
<option value="both">Both</option>
</select><br><br>

Event Date (Optional):<br>
<input type="date" name="event_date"><br><br>

<button name="post">Post Announcement</button>
</form>