<?php
include('config/db.php');

$admission = $_GET['admission_no'];

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=student_report.xls");

$student_query = mysqli_query($conn,"
SELECT * FROM students
JOIN classes ON students.class_id=classes.id
WHERE admission_no='$admission'
");

$student = mysqli_fetch_assoc($student_query);
$student_id = $student['id'];

echo "Student Name\tAdmission No\tClass\n";
echo $student['name']."\t".$student['admission_no']."\t".$student['class_name']."\n\n";

echo "Subject\tMarks\tGrade\tPoints\n";

$grades = mysqli_query($conn,"
SELECT grades.*, subjects.subject_name
FROM grades
JOIN subjects ON grades.subject_id=subjects.id
WHERE student_id='$student_id'
");

while($g=mysqli_fetch_assoc($grades)){

echo $g['subject_name']."\t".
     $g['marks']."\t".
     $g['grade']."\t".
     $g['points']."\n";

}
?>