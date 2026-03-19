<?php
session_start();
include('config/db.php');

// ---------------------------------------------------------
// START OF YOUR EXACT LOGIC - DO NOT CHANGE
// ---------------------------------------------------------
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$admission_no = $_SESSION['admission_no'] ?? null;
if(!$admission_no){
    echo "<p style='color:red;'>Error: No student linked to this parent account.</p>";
    exit();
}

$student_query = mysqli_query($conn, "SELECT id, class_id FROM students WHERE admission_no='$admission_no' LIMIT 1");
$student_data = mysqli_fetch_assoc($student_query);
$student_id = $student_data['id'] ?? null;
$class_id = $student_data['class_id'] ?? null;

if(!$student_id){
    echo "<p style='color:red;'>Error: Student not found for this admission number.</p>";
    exit();
}

$selected_term = $_POST['term_id'] ?? "";
$selected_exam = $_POST['exam_id'] ?? "";

$grade_points_map = [
    'A'=>12, 'A-'=>11, 'B+'=>10, 'B'=>9, 'B-'=>8,
    'C+'=>7, 'C'=>6, 'C-'=>5, 'D+'=>4, 'D'=>3, 'D-'=>2, 'E'=>1
];

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
            $grades[] = ['marks' => null, 'grade' => 'I', 'points' => 0, 'subject_name' => $subject_name];
            $has_incomplete = true;
        }
    }
}

if($has_incomplete){
    $mean_points = 0; $mean_grade = 'I';
} else {
    $total_points = 0;
    $num_subjects = count($grades);
    foreach($grades as $g){ $total_points += $g['points']; }
    $mean_points = $num_subjects > 0 ? $total_points / $num_subjects : 0;
    $mean_grade = '';
    foreach($grade_points_map as $grade => $pt){
        if($mean_points >= $pt){ $mean_grade = $grade; break; }
    }
}

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
        $student_grades_query = mysqli_query($conn, "SELECT grade FROM grades WHERE student_id='".$row['student_id']."' AND term_id='$selected_term' AND exam_id='$selected_exam'");
        $incomplete_found = false;
        while($sg = mysqli_fetch_assoc($student_grades_query)){
            if($sg['grade'] == 'I'){ $incomplete_found = true; break; }
        }
        if($incomplete_found) continue;
        if($row['student_id'] == $student_id){ $position = $rank; break; }
        $rank++;
    }
}
// ---------------------------------------------------------
// END OF YOUR LOGIC
// ---------------------------------------------------------
?>

<!DOCTYPE html>
<html>
<head>
    <title>Academic Results | SIS</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background: rgba(10, 42, 102, 0.8);
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
        }

        .container {
            background: white;
            width: 100%;
            max-width: 850px;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        h2 { color: #0a2a66; border-bottom: 2px solid #0a2a66; padding-bottom: 10px; }
        
        /* Form Styling */
        form { 
            background: #f4f7f6; 
            padding: 20px; 
            border-radius: 8px; 
            margin-bottom: 25px; 
            border: 1px solid #ddd;
        }
        
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        /* Table Styling */
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background: white;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th { background-color: #0a2a66; color: white; text-transform: uppercase; font-size: 13px; }

        /* Highlight Mean Stats */
        .summary-stats {
            margin-top: 20px;
            padding: 15px;
            background: #eef2f7;
            border-left: 5px solid #0a2a66;
        }

        button {
            background: #0a2a66;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover { background: #1c448e; }
        
        hr { border: 0; border-top: 1px solid #eee; margin: 30px 0; }
    </style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <h2>Academic Performance Results</h2>

        <form method="POST">
            <label><strong>Term:</strong></label>
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
            <label><strong>Exam:</strong></label>
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
            <h3>Report Summary: Term <?php echo $selected_term; ?>, Exam <?php echo $selected_exam; ?></h3>
            <table>
                <tr>
                    <th>Subject</th>
                    <th>Marks</th>
                    <th>Grade</th>
                </tr>
                <?php foreach($grades as $g): ?>
                <tr>
                    <td><?php echo $g['subject_name']; ?></td>
                    <td><?php echo is_null($g['marks']) ? 'I' : $g['marks']; ?></td>
                    <td><strong><?php echo $g['grade']; ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <div class="summary-stats">
                <p><strong>Mean Grade:</strong> <?php echo $mean_grade; ?></p>
                <p><strong>Mean Points:</strong> <?php echo round($mean_points,2); ?></p>

                <?php if(!$has_incomplete && !is_null($position)): ?>
                    <p><strong>Class Position:</strong> <?php echo $position; ?> of <?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM students WHERE class_id='$class_id'")); ?></p>
                <?php elseif($has_incomplete): ?>
                    <p><strong>Class Position:</strong> N/A (Incomplete grade present)</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <hr>
        <a href='parent_dashboard.php'><button type="button">Dashboard</button></a>
    </div>
</div>

</body>
</html>