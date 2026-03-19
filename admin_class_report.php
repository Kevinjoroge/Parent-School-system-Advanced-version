<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}

/* 1. DEFINE FUNCTION ONLY ONCE AT THE TOP */
if (!function_exists('gradeFromPoints')) {
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
}

// Initialize variables
$students = [];
$total_points = 0;
$total_students_with_grades = 0;
$enrolled_students = 0;
$class_name = "";

// Process report when "View Results" is clicked
if(isset($_POST['view'])){
    $class_id = $_POST['class_id'];
    $term_id = $_POST['term_id'];
    $exam_id = $_POST['exam_id'];

    // Get Class Name and Total Enrolled Students
    $class_q = mysqli_query($conn, "SELECT class_name FROM classes WHERE id='$class_id'");
    $class_data = mysqli_fetch_assoc($class_q);
    $class_name = $class_data['class_name'] ?? "Unknown Class";

    $count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE class_id='$class_id'");
    $count_data = mysqli_fetch_assoc($count_q);
    $enrolled_students = $count_data['total'];

    // Fetch students and their Mean Points
    $query = mysqli_query($conn,"
        SELECT students.id, students.name, students.admission_no,
        AVG(grades.points) AS mean_points
        FROM students
        JOIN grades ON students.id=grades.student_id
        WHERE students.class_id='$class_id' 
        AND grades.term_id='$term_id' 
        AND grades.exam_id='$exam_id'
        GROUP BY students.id
        ORDER BY mean_points DESC
    ");

    while($row = mysqli_fetch_assoc($query)){
        $students[] = $row;
        $total_points += $row['mean_points'];
        $total_students_with_grades++;
    }
}

/* Calculate class overall performance */
$class_mean_points = $total_students_with_grades > 0 ? $total_points / $total_students_with_grades : 0;
$class_mean_grade = gradeFromPoints($class_mean_points);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Class Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        table, th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .filter-section { background: #f4f4f4; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .summary-box { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; display: inline-block; min-width: 300px; }
        .summary-box p { margin: 5px 0; font-weight: bold; }
        .btn { padding: 8px 15px; cursor: pointer; text-decoration: none; display: inline-block; color: black; background: #efefef; border: 1px solid #767676; border-radius: 2px; font-size: 13px; vertical-align: middle; }
        .btn-pdf { background: #d9534f; color: white; border: none; }
    </style>
</head>
<body>

<h2>Class Performance Report</h2>
<hr>

<div class="filter-section">
    <form method="POST">
        <select name="class_id" required>
            <option value="">Select Class</option>
            <?php
            $classes = mysqli_query($conn,"SELECT * FROM classes");
            while($c=mysqli_fetch_assoc($classes)){
                $selected = (isset($_POST['class_id']) && $_POST['class_id'] == $c['id']) ? "selected" : "";
                echo "<option value='".$c['id']."' $selected>".$c['class_name']."</option>";
            }
            ?>
        </select>

        <select name="term_id" required>
            <option value="">Select Term</option>
            <?php
            $terms = mysqli_query($conn,"SELECT * FROM terms");
            while($t=mysqli_fetch_assoc($terms)){
                $selected = (isset($_POST['term_id']) && $_POST['term_id'] == $t['id']) ? "selected" : "";
                echo "<option value='".$t['id']."' $selected>".$t['term_name']."</option>";
            }
            ?>
        </select>

        <select name="exam_id" required>
            <option value="">Select Exam Category</option>
            <?php
            $exams = mysqli_query($conn,"SELECT * FROM exams");
            while($e=mysqli_fetch_assoc($exams)){
                $selected = (isset($_POST['exam_id']) && $_POST['exam_id'] == $e['id']) ? "selected" : "";
                echo "<option value='".$e['id']."' $selected>".$e['exam_name']."</option>";
            }
            ?>
        </select>

        <button type="submit" name="view" class="btn">View Results</button>
    </form>
</div>

<?php if(isset($_POST['view'])): ?>
    <h3>Report For: <?php echo $class_name; ?></h3>
    
    <div style="margin-bottom: 10px;">


        <a href="export_class_report_excel.php?class_id=<?php echo $_POST['class_id']; ?>&term_id=<?php echo $_POST['term_id']; ?>&exam_id=<?php echo $_POST['exam_id']; ?>" class="btn">
            Download Excel
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student Name</th>
                <th>Admission No</th>
                <th>Mean Points</th>
                <th>Mean Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $position = 1;
            $rank = 1;
            $prev_points = null;

            if($total_students_with_grades > 0){
                foreach($students as $student){
                    $m_points = round($student['mean_points'], 2);
                    $m_grade = gradeFromPoints($m_points);

                    if($prev_points !== null && $m_points < $prev_points){
                        $rank = $position;
                    }

                    echo "<tr>
                            <td>$rank</td>
                            <td>".$student['name']."</td>
                            <td>".$student['admission_no']."</td>
                            <td>".$m_points."</td>
                            <td>".$m_grade."</td>
                          </tr>";

                    $prev_points = $m_points;
                    $position++;
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No grades found for this selection.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="summary-box">
        <p>Total Students Enrolled: <?php echo $enrolled_students; ?></p>
        <p>Students with Results: <?php echo $total_students_with_grades; ?></p>
        <p>Class Mean Points: <?php echo round($class_mean_points, 2); ?></p>
        <p>Class Mean Grade: <?php echo $class_mean_grade; ?></p>
    </div>

<?php else: ?>
    <p>Please select the Class, Term, and Exam Category to generate the report.</p>
<?php endif; ?>

<br><br>
<a href="admin_reports.php" class="btn">Back</a>

</body>
</html>