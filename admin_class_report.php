<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}

$class_id = $_GET['class_id'];

$class = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM classes WHERE id='$class_id'"));
?>

<h2><?php echo $class['class_name']; ?> Class Report</h2>

<table border="1">

<tr>
<th>Position</th>
<th>Student Name</th>
<th>Admission No</th>
<th>Mean Grade</th>
</tr>

<?php

$query = mysqli_query($conn,"
SELECT students.id, students.student_name, students.admission_no,
AVG(grades.points) AS mean_points
FROM students
LEFT JOIN grades ON students.id=grades.student_id
WHERE students.class_id='$class_id'
GROUP BY students.id
ORDER BY mean_points DESC
");

$position=1;

while($row=mysqli_fetch_assoc($query)){

echo "<tr>
<td>".$position."</td>
<td>".$row['student_name']."</td>
<td>".$row['admission_no']."</td>
<td>".round($row['mean_points'],2)."</td>
</tr>";

$position++;

}

?>

</table>

<br><br>

<a href="admin_reports.php">⬅ Back</a>