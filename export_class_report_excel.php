<?php
include('config/db.php');

$class_id = $_GET['class_id'];

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=class_report.xls");

$query = mysqli_query($conn,"
SELECT students.name, students.admission_no,
AVG(grades.points) AS mean_points
FROM students
LEFT JOIN grades ON students.id=grades.student_id
WHERE students.class_id='$class_id'
GROUP BY students.id
ORDER BY mean_points DESC
");

echo "Position\tStudent\tAdmission No\tMean Points\n";

$position=1;

while($row=mysqli_fetch_assoc($query)){

echo $position."\t".
$row['name']."\t".
$row['admission_no']."\t".
round($row['mean_points'],2)."\n";

$position++;
}
?>