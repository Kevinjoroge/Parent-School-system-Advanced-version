<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

/* GRADE CALCULATION FUNCTION */
function calculateGrade($marks){
    if($marks >= 80) return ["A",12];
    elseif($marks >= 75) return ["A-",11];
    elseif($marks >= 70) return ["B+",10];
    elseif($marks >= 65) return ["B",9];
    elseif($marks >= 60) return ["B-",8];
    elseif($marks >= 55) return ["C+",7];
    elseif($marks >= 50) return ["C",6];
    elseif($marks >= 45) return ["C-",5];
    elseif($marks >= 40) return ["D+",4];
    elseif($marks >= 35) return ["D",3];
    elseif($marks >= 30) return ["D-",2];
    else return ["E",1];
}

/* SAVE OR UPDATE GRADES */
if(isset($_POST['save'])){
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $term_id = $_POST['term_id'];
    $exam_id = $_POST['exam_id'];

    foreach($_POST['marks'] as $student_id => $mark){
        if($mark === "") continue; // skip empty inputs

        $mark = intval($mark);
        list($grade,$points) = calculateGrade($mark);

        // Check if grade already exists
        $check = mysqli_query($conn,"
            SELECT * FROM grades 
            WHERE student_id='$student_id'
            AND subject_id='$subject_id'
            AND term_id='$term_id'
            AND exam_id='$exam_id'
        ");

        if(mysqli_num_rows($check) > 0){
            // UPDATE
            mysqli_query($conn,"
                UPDATE grades SET 
                marks='$mark',
                grade='$grade',
                points='$points'
                WHERE student_id='$student_id'
                AND subject_id='$subject_id'
                AND term_id='$term_id'
                AND exam_id='$exam_id'
            ");
        } else {
            // INSERT
            mysqli_query($conn,"
                INSERT INTO grades(student_id,class_id,subject_id,term_id,exam_id,marks,grade,points)
                VALUES('$student_id','$class_id','$subject_id','$term_id','$exam_id','$mark','$grade','$points')
            ");
        }
    }

    echo "<p style='color:green;'>Grades saved/updated successfully!</p>";
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Grade Entry</title>

<style>
/* Remove spinner arrows */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
table { border-collapse: collapse; }
table, th, td { border: 1px solid black; padding: 6px; }
</style>

</head>
<body>

<h2>Grade Entry Module</h2>
<hr>

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

<select name="subject_id" required>
<option value="">Select Subject</option>
<?php
$subjects = mysqli_query($conn,"SELECT * FROM subjects");
while($s=mysqli_fetch_assoc($subjects)){
    $selected = (isset($_POST['subject_id']) && $_POST['subject_id'] == $s['id']) ? "selected" : "";
    echo "<option value='".$s['id']."' $selected>".$s['subject_name']."</option>";
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

<br><br>
<button name="load">Load Students</button>
</form>

<?php
if(isset($_POST['load'])){
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $term_id = $_POST['term_id'];
    $exam_id = $_POST['exam_id'];

    $students = mysqli_query($conn,"SELECT * FROM students WHERE class_id='$class_id'");

    // Fetch existing grades for this selection
    $existing_grades_query = mysqli_query($conn,"
        SELECT student_id, marks 
        FROM grades 
        WHERE class_id='$class_id' 
        AND subject_id='$subject_id' 
        AND term_id='$term_id' 
        AND exam_id='$exam_id'
    ");
    $existing_grades = [];
    while($eg = mysqli_fetch_assoc($existing_grades_query)){
        $existing_grades[$eg['student_id']] = $eg['marks'];
    }

    if(!empty($existing_grades)){
        echo "<p style='color:blue;'>Grades already exist for this selection. You can edit and update them below.</p>";
    }

    echo "<form method='POST'>";
    echo "<input type='hidden' name='class_id' value='$class_id'>";
    echo "<input type='hidden' name='subject_id' value='$subject_id'>";
    echo "<input type='hidden' name='term_id' value='$term_id'>";
    echo "<input type='hidden' name='exam_id' value='$exam_id'>";

    echo "<table>";
    echo "<tr><th>Name</th><th>Admission No</th><th>Marks (0-100)</th></tr>";

    while($st=mysqli_fetch_assoc($students)){
        $pre_filled_mark = $existing_grades[$st['id']] ?? '';
        echo "<tr>";
        echo "<td>".$st['name']."</td>";
        echo "<td>".$st['admission_no']."</td>";
        echo "<td>
        <input type='number'
               name='marks[".$st['id']."]'
               min='0'
               max='100'
               step='1'
               value='$pre_filled_mark'
               style='width:70px;'>
        </td>";
        echo "</tr>";
    }

    echo "</table><br>";
    echo "<button name='save'>Save/Update Grades</button>";
    echo "</form>";
}
?>

<br><hr>
<a href="teacher_dashboard.php">⬅ Back to Dashboard</a>

</body>
</html>