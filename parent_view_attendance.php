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
    echo "Student not found.";
    exit();
}

$student_id = $student['id'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Attendance Records</title>
<style>
table {
    border-collapse: collapse;
    width: 100%;
}
table, th, td {
    border: 1px solid black;
    padding: 6px;
}
</style>
</head>
<body>

<h2>Attendance Records</h2>
<hr>

<h3>Student Information</h3>

<p><strong>Name:</strong> <?php echo $student['name']; ?></p>
<p><strong>Admission No:</strong> <?php echo $student['admission_no']; ?></p>
<p><strong>Class:</strong> <?php echo $student['class_name']; ?></p>

<hr>

<h3>Attendance History</h3>

<table>
<tr>
<th>Date</th>
<th>Class</th>
<th>Status</th>
</tr>

<?php
$attendance = mysqli_query($conn,"
    SELECT attendance.*, classes.class_name
    FROM attendance
    JOIN classes ON attendance.class_id = classes.id
    WHERE attendance.student_id='$student_id'
    ORDER BY attendance.attendance_date DESC
");

if(mysqli_num_rows($attendance) > 0){

    while($row = mysqli_fetch_assoc($attendance)){
        echo "<tr>";
        echo "<td>".$row['attendance_date']."</td>";
        echo "<td>".$row['class_name']."</td>";
        echo "<td>".$row['status']."</td>";
        echo "</tr>";
    }

} else {
    echo "<tr><td colspan='3'>No attendance records found.</td></tr>";
}
?>

</table>

<br><hr>
<a href="parent_dashboard.php">⬅ Back to Dashboard</a> |
<a href="logout.php">🚪 Logout</a>

</body>
</html>