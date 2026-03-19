<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Handle Form Submission
if(isset($_POST['post_announcement'])){
    $class_id = $_POST['class_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO class_announcements (teacher_id, class_id, title, message) 
            VALUES ('$teacher_id', '$class_id', '$title', '$message')";
    
    if(mysqli_query($conn, $sql)){
        $success = "Announcement posted successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post Class Announcement</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { max-width: 600px; background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #28a745; color: white; padding: 10px 15px; border: none; cursor: pointer; border-radius: 4px; }
        .msg { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<h2>Post Class Announcement</h2>
<hr>

<div class="form-container">
    <?php if(isset($success)) echo "<div class='msg success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='msg error'>$error</div>"; ?>

    <form method="POST">
        <label>Select Class:</label>
        <select name="class_id" required>
            <option value="">-- Choose Class --</option>
            <?php
            // Fetch classes assigned to this teacher or all classes
            $classes = mysqli_query($conn, "SELECT * FROM classes");
            while($c = mysqli_fetch_assoc($classes)){
                echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
            }
            ?>
        </select>

        <label>Announcement Title:</label>
        <input type="text" name="title" placeholder="e.g. Upcoming Class Trip" required>

        <label>Message:</label>
        <textarea name="message" rows="5" placeholder="Enter details for parents..." required></textarea>

        <button type="submit" name="post_announcement">Post Announcement</button>
    </form>
</div>

<br>
<a href="teacher_dashboard.php"><button style="background:#6c757d;">Dashboard</button></a>

</body>
</html>