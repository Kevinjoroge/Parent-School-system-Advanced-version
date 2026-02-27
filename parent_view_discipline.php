<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$admission_no = $_SESSION['admission_no'];

$student = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM students WHERE admission_no='$admission_no'"
));

$student_id = $student['id'];

$reports = mysqli_query($conn,"
    SELECT discipline_reports.*, classes.class_name
    FROM discipline_reports
    JOIN classes ON discipline_reports.class_id = classes.id
    WHERE discipline_reports.student_id='$student_id'
    ORDER BY incident_date DESC
");

echo "<h2>Discipline Reports</h2><hr>";

echo "<table border='1'>";
echo "<tr>
<th>Date</th>
<th>Class</th>
<th>Type</th>
<th>Description</th>
<th>Action Taken</th>
<th>Status</th>
</tr>";

while($row=mysqli_fetch_assoc($reports)){
echo "<tr>";
echo "<td>".$row['incident_date']."</td>";
echo "<td>".$row['class_name']."</td>";
echo "<td>".$row['incident_type']."</td>";
echo "<td>".$row['description']."</td>";
echo "<td>".$row['action_taken']."</td>";
echo "<td>".$row['status']."</td>";
echo "</tr>";
}

echo "</table>";

echo "<br><a href='parent_dashboard.php'>⬅ Back</a>";
?>