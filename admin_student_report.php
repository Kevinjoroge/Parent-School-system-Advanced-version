<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}

$admission = $_GET['admission_no'];

$student_query = mysqli_query($conn,"
SELECT * FROM students
JOIN classes ON students.class_id=classes.id
WHERE admission_no='$admission'
");

$student = mysqli_fetch_assoc($student_query);

if(!$student){
    echo "Student not found";
    exit();
}

$student_id = $student['id'];
?>

<h2>Student Report</h2>
<button onclick="window.print()">Print</button>
<a href="export_student_report_excel.php?admission_no=<?php echo $student['admission_no']; ?>">
<button>Download Excel</button>
</a>

<h3>Student Information</h3>

Name: <?php echo $student['name']; ?><br>
Admission No: <?php echo $student['admission_no']; ?><br>
Class: <?php echo $student['class_name']; ?><br>

<hr>

<h3>Attendance Records</h3>

<table border="1">
<tr>
<th>Date</th>
<th>Status</th>
</tr>

<?php

$attendance = mysqli_query($conn,"
SELECT * FROM attendance
WHERE student_id='$student_id'
ORDER BY attendance_date DESC
");

while($a=mysqli_fetch_assoc($attendance)){

echo "<tr>
<td>".$a['attendance_date']."</td>
<td>".$a['status']."</td>
</tr>";

}
?>

</table>

<hr>

<h3>Discipline Records</h3>

<table border="1">
<tr>
<th>Date</th>
<th>Incident</th>
<th>Description</th>
<th>Action</th>
</tr>

<?php

$discipline = mysqli_query($conn,"
SELECT * FROM discipline_reports
WHERE student_id='$student_id'
ORDER BY incident_date DESC
");

while($d=mysqli_fetch_assoc($discipline)){

echo "<tr>
<td>".$d['incident_date']."</td>
<td>".$d['incident_type']."</td>
<td>".$d['description']."</td>
<td>".$d['action_taken']."</td>
</tr>";

}
?>

</table>

<hr>

<h3>Grades</h3>

<table border="1">
<tr>
<th>Subject</th>
<th>Marks</th>
<th>Grade</th>
<th>Points</th>
</tr>

<?php

$grades = mysqli_query($conn,"
SELECT grades.*, subjects.subject_name
FROM grades
JOIN subjects ON grades.subject_id=subjects.id
WHERE student_id='$student_id'
");

$total_points = 0;
$count = 0;

while($g=mysqli_fetch_assoc($grades)){

$total_points += $g['points'];
$count++;

echo "<tr>
<td>".$g['subject_name']."</td>
<td>".$g['marks']."</td>
<td>".$g['grade']."</td>
<td>".$g['points']."</td>
</tr>";

}

?>

</table>

<?php

if($count>0){

$mean = $total_points/$count;

echo "<h3>Mean Grade Point: ".round($mean,2)."</h3>";

}

?>
<hr>
<br><
<a href="admin_reports.php"><button>Back</button></a>