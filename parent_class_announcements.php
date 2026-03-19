<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: index.php");
    exit();
}

// Assuming parent is linked to student, and student has a class_id
$student_id = $_SESSION['student_id']; 
$student_q = mysqli_query($conn, "SELECT class_id FROM students WHERE id='$student_id'");
$student_data = mysqli_fetch_assoc($student_q);
$class_id = $student_data['class_id'];

// Fetch announcements for this specific class
$announcements = mysqli_query($conn, "
    SELECT ca.*, t.name as teacher_name 
    FROM class_announcements ca
    JOIN teachers t ON ca.teacher_id = t.id
    WHERE ca.class_id = '$class_id'
    ORDER BY ca.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Class Announcements</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .announcement-card { background: #fff; border-left: 5px solid #007bff; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .announcement-card h3 { margin-top: 0; color: #007bff; }
        .meta { font-size: 0.85em; color: #666; margin-bottom: 10px; }
    </style>
</head>
<body>

<h2>Announcements for Your Child's Class</h2>
<hr>

<?php if(mysqli_num_rows($announcements) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($announcements)): ?>
        <div class="announcement-card">
            <h3><?php echo $row['title']; ?></h3>
            <div class="meta">
                Posted by Teacher: <strong><?php echo $row['teacher_name']; ?></strong> | 
                Date: <?php echo date('M d, Y - h:i A', strtotime($row['created_at'])); ?>
            </div>
            <p><?php echo nl2br($row['message']); ?></p>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No announcements have been posted for this class yet.</p>
<?php endif; ?>

<br>
<a href="parent_dashboard.php"><button>Back</button></a>

</body>
</html>