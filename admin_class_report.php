<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}

$class_id = $_GET['class_id'];

$class = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM classes WHERE id='$class_id'"));

/* Grade mapping function */
function gradeFromPoints($points){
    if($points >= 11.5) return "A";
    elseif($points >= 10.5) return "A-";
    elseif($points >= 9.5) return "B+";
    elseif($points >= 8.5) return "B";
    elseif($points >= 7.5) return "B-";
    elseif($points >= 6.5) return "C+";
    elseif($points >= 5.5) return "C";
    elseif($points >= 4.5) return "C-";
    elseif($points >= 3.5) return "D+";
    elseif($points >= 2.5) return "D";
    elseif($points >= 1.5) return "D-";
    else return "E";
}

/* Fetch students with mean points */
$query = mysqli_query($conn,"
SELECT students.id, students.name, students.admission_no,
AVG(grades.points) AS mean_points
FROM students
LEFT JOIN grades ON students.id=grades.student_id
WHERE students.class_id='$class_id'
GROUP BY students.id
ORDER BY mean_points DESC
");

$students = [];
$total_points = 0;
$total_students = 0;

while($row = mysqli_fetch_assoc($query)){
    $students[] = $row;
    $total_points += $row['mean_points'];
    $total_students++;
}

/* Calculate class mean points and grade */
$class_mean_points = $total_students > 0 ? $total_points / $total_students : 0;
$class_mean_grade = gradeFromPoints($class_mean_points);
?>

<h2><?php echo $class['class_name']; ?> Class Report</h2>
<a href="export_class_report_excel.php?class_id=<?php echo $class_id; ?>">
<button>Download Excel</button>
</a>
<hr>

<table border="1" cellpadding="5">
<tr>
<th>Position</th>
<th>Student Name</th>
<th>Admission No</th>
<th>Mean Grade</th>
<th>Mean Points</th>
</tr>

<?php
$position = 1;
$rank = 1;
$prev_points = null;

foreach($students as $student){
    $mean_points = round($student['mean_points'], 2);
    $mean_grade = gradeFromPoints($mean_points);

    /* Handle ties */
    if($prev_points !== null && $mean_points < $prev_points){
        $rank = $position;
    }

    echo "<tr>
    <td>".$rank."</td>
    <td>".$student['name']."</td>
    <td>".$student['admission_no']."</td>
    <td>".$mean_grade."</td>
    <td>".$mean_points."</td>
    </tr>";

    $prev_points = $mean_points;
    $position++;
}
?>

</table>

<div style="margin-top:20px; font-weight:bold;">
<p>Class Mean Points: <?php echo round($class_mean_points,2); ?></p>
<p>Class Mean Grade: <?php echo $class_mean_grade; ?></p>
</div>

<br><br>
<a href="admin_reports.php"><button>Back</button></a>