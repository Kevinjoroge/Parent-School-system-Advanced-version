<?php
session_start();
include('config/db.php');

// -----------------
// Access Control
// -----------------
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

// -----------------
// Get student ID from parent session
// -----------------
$admission_no = $_SESSION['admission_no'] ?? null;
if(!$admission_no){
    echo "<p style='color:red;'>Error: No student linked to this parent account.</p>";
    exit();
}

// Fetch the student ID and class_id
$student_query = mysqli_query($conn, "SELECT id, class_id FROM students WHERE admission_no='$admission_no' LIMIT 1");
$student_data = mysqli_fetch_assoc($student_query);
$student_id = $student_data['id'] ?? null;
$class_id = $student_data['class_id'] ?? null;

if(!$student_id){
    echo "<p style='color:red;'>Error: Student not found for this admission number.</p>";
    exit();
}

// -----------------
// Handle term & exam selection
// -----------------
$selected_term = $_POST['term_id'] ?? "";
$selected_exam = $_POST['exam_id'] ?? "";

// -----------------
// Grade Points Mapping
// -----------------
$grade_points_map = [
    'A'=>12, 'A-'=>11, 'B+'=>10, 'B'=>9, 'B-'=>8,
    'C+'=>7, 'C'=>6, 'C-'=>5, 'D+'=>4, 'D'=>3, 'D-'=>2, 'E'=>1
];

// -----------------
// Fetch Grades for student
// -----------------
$grades = [];
$has_incomplete = false;

if($selected_term && $selected_exam){
    $grades_query = mysqli_query($conn, "
        SELECT g.marks, g.grade, g.points, s.subject_name
        FROM grades g
        JOIN subjects s ON g.subject_id = s.id
        WHERE g.student_id='$student_id'
        AND g.term_id='$selected_term'
        AND g.exam_id='$selected_exam'
    ");

    // Fetch all subjects to detect incomplete
    $subjects_query = mysqli_query($conn, "SELECT * FROM subjects");
    $subjects = [];
    while($s = mysqli_fetch_assoc($subjects_query)){
        $subjects[$s['id']] = $s['subject_name'];
    }

    $grades_assoc = [];
    while($g = mysqli_fetch_assoc($grades_query)){
        $grades_assoc[$g['subject_name']] = $g;
    }

    foreach($subjects as $subject_name){
        if(isset($grades_assoc[$subject_name])){
            $grades[] = $grades_assoc[$subject_name];
            if($grades_assoc[$subject_name]['grade'] == 'I'){
                $has_incomplete = true;
            }
        } else {
            // Incomplete grade
            $grades[] = [
                'marks' => null,
                'grade' => 'I',
                'points' => 0,
                'subject_name' => $subject_name
            ];
            $has_incomplete = true;
        }
    }
}

// -----------------
// Calculate Mean Points
// -----------------
if($has_incomplete){
    $mean_points = 0;
    $mean_grade = 'I';
} else {
    $total_points = 0;
    $num_subjects = count($grades);
    foreach($grades as $g){
        $total_points += $g['points'];
    }
    $mean_points = $num_subjects > 0 ? $total_points / $num_subjects : 0;

    // Convert mean points to letter grade
    $mean_grade = '';
    foreach($grade_points_map as $grade => $pt){
        if($mean_points >= $pt){
            $mean_grade = $grade;
            break;
        }
    }
}

// -----------------
// Calculate Class Position (exclude students with incomplete grades)
// -----------------
$position = null;
if($selected_term && $selected_exam && $class_id && !$has_incomplete){
    $class_grades_query = mysqli_query($conn, "
        SELECT student_id, SUM(points) as total_points
        FROM grades
        WHERE class_id='$class_id'
        AND term_id='$selected_term'
        AND exam_id='$selected_exam'
        GROUP BY student_id
        ORDER BY total_points DESC
    ");

    $rank = 1;
    while($row = mysqli_fetch_assoc($class_grades_query)){
        // Skip students with incomplete grades
        $student_grades_query = mysqli_query($conn, "
            SELECT grade FROM grades
            WHERE student_id='".$row['student_id']."' 
            AND term_id='$selected_term' 
            AND exam_id='$selected_exam'
        ");
        $incomplete_found = false;
        while($sg = mysqli_fetch_assoc($student_grades_query)){
            if($sg['grade'] == 'I'){
                $incomplete_found = true;
                break;
            }
        }
        if($incomplete_found) continue;

        if($row['student_id'] == $student_id){
            $position = $rank;
            break;
        }
        $rank++;
    }
}
?>

<h2>View Grades</h2>

<form method="POST">
    <label>Term:</label>
    <select name="term_id" required onchange="this.form.submit()">
        <option value="">--Select Term--</option>
        <?php
        $terms_query = mysqli_query($conn, "SELECT * FROM terms");
        while($t = mysqli_fetch_assoc($terms_query)){
            $selected = ($selected_term == $t['id']) ? "selected" : "";
            echo "<option value='".$t['id']."' $selected>".$t['term_name']."</option>";
        }
        ?>
    </select>
    <br><br>
    <label>Exam:</label>
    <select name="exam_id" required onchange="this.form.submit()">
        <option value="">--Select Exam--</option>
        <?php
        if($selected_term != ""){
            $exam_query = mysqli_query($conn, "SELECT * FROM exams WHERE term_id='$selected_term'");
            while($e = mysqli_fetch_assoc($exam_query)){
                $selected = ($selected_exam == $e['id']) ? "selected" : "";
                echo "<option value='".$e['id']."' $selected>".$e['exam_name']."</option>";
            }
        }
        ?>
    </select>
</form>

<?php if(!empty($grades) && $selected_term && $selected_exam): ?>
    <h3>Grades for Term <?php echo $selected_term; ?>, Exam <?php echo $selected_exam; ?>:</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Subject</th>
            <th>Marks</th>
            <th>Grade</th>
        </tr>
        <?php foreach($grades as $g): ?>
        <tr>
            <td><?php echo $g['subject_name']; ?></td>
            <td><?php echo is_null($g['marks']) ? 'I' : $g['marks']; ?></td>
            <td><?php echo $g['grade']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><strong>Mean Grade:</strong> <?php echo $mean_grade; ?></p>
    <p><strong>Mean Points:</strong> <?php echo round($mean_points,2); ?></p>

    <?php if(!$has_incomplete && !is_null($position)): ?>
        <p><strong>Class Position:</strong> <?php echo $position; ?> of <?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM students WHERE class_id='$class_id'")); ?></p>
    <?php elseif($has_incomplete): ?>
        <p><strong>Class Position:</strong> N/A (Incomplete grade present)</p>
    <?php endif; ?>
<?php endif; ?>