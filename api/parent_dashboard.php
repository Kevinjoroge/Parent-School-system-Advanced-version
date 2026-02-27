<?php
session_start();
include('config/db.php');

/* SECURITY CHECK */
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

if(!isset($_SESSION['admission_no'])){
    header("Location: parent_login.php");
    exit();
}

$admission_no = $_SESSION['admission_no'];

/* FETCH STUDENT DETAILS */
$student_query = mysqli_query($conn,
    "SELECT students.*, classes.class_name
     FROM students
     JOIN classes ON students.class_id = classes.id
     WHERE students.admission_no='$admission_no'"
);

$student = mysqli_fetch_assoc($student_query);

if(!$student){
    echo "Student record not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Parent Dashboard</title>
</head>
<body>

<h2>Parent Dashboard</h2>
<hr>

<h3>Student Information</h3>

<p><strong>Name:</strong> <?php echo $student['name']; ?></p>
<p><strong>Admission No:</strong> <?php echo $student['admission_no']; ?></p>
<p><strong>Class:</strong> <?php echo $student['class_name']; ?></p>

<hr>

<h3>Modules</h3>


<a href="parent_view_attendance.php"><button>View Attendance</button></a>
<br>
<br>
<a href="parent_view_grades.php"><button>View Grades</button></a>
<br>
<br>
<a href="parent_view_discipline.php"><button>View Discipline Records</button></a>
<br>
<br>
<a href="parent_announcements.php"><button>View Announcements</button></a>
<br>
<br>
<a href="parent_message.php"><button>Messaging</button></a>
<br>
<br>
<a href="parent_fees.php"><button>Fees Status</button></a>


<hr>
<br>
<a href="logout.php"><button>Logout</button></a>

</body>
</html>