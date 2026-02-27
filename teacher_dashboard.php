<?php
session_start();
include('config/db.php');

/* SECURITY CHECK */
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
</head>
<body>

<h2>Teacher Dashboard</h2>
<hr>

<h3>Classroom Management Modules</h3>


    <a href="teacher_attendance.php"><button>Mark Attendance</button></a>
    <br><br>
    <a href="teacher_grades.php"><button>Update </button></a>
    <br><br>
    <a href="teacher_discipline.php"><button>Record Discipline Cases</button></a>
    <br><br>
    <a href="teacher_announcements.php"><button>View Announcements</button></a>
    <br><br>
    <a href="teacher_message.php"><button>Send / Receive Messages</button></a>


<hr>
<br>

<a href="logout.php"><button>Logout</button></a>

</body>
</html>