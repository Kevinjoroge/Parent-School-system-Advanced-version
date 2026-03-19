<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

/* GRADE CALCULATION FUNCTION - LOGIC PRESERVED */
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

$status_msg = "";
/* SAVE OR UPDATE GRADES - LOGIC PRESERVED */
if(isset($_POST['save'])){
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $term_id = $_POST['term_id'];
    $exam_id = $_POST['exam_id'];

    foreach($_POST['marks'] as $student_id => $mark){
        if($mark === "") continue; 

        $mark = intval($mark);
        list($grade,$points) = calculateGrade($mark);

        $check = mysqli_query($conn,"
            SELECT * FROM grades 
            WHERE student_id='$student_id'
            AND subject_id='$subject_id'
            AND term_id='$term_id'
            AND exam_id='$exam_id'
        ");

        if(mysqli_num_rows($check) > 0){
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
            mysqli_query($conn,"
                INSERT INTO grades(student_id,class_id,subject_id,term_id,exam_id,marks,grade,points)
                VALUES('$student_id','$class_id','$subject_id','$term_id','$exam_id','$mark','$grade','$points')
            ");
        }
    }
    $status_msg = "Grades saved/updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Score Entry | SIS</title>
    <style>
        :root {
            --teacher-primary: #0f3460;
            --accent: #e94560;
            --white: #ffffff;
            --bg-body: #f0f2f5;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background: rgba(15, 52, 96, 0.85);
            min-height: 100vh;
            padding: 30px 20px;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
        }

        .container {
            background: var(--white);
            width: 100%;
            max-width: 1000px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        h2 { 
            color: var(--teacher-primary); 
            text-transform: uppercase; 
            border-bottom: 3px solid var(--teacher-primary);
            padding-bottom: 10px;
            margin-top: 0;
            font-size: 24px;
        }

        /* Input Controls */
        .selection-panel {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        select {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 100%;
        }

        .btn-load {
            background: var(--teacher-primary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-load:hover { background: var(--accent); }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: var(--teacher-primary);
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover { background: #fdfdfd; }

        input[type=number] {
            width: 80px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            -moz-appearance: textfield;
        }
        
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none; margin: 0;
        }

        .btn-save {
            background: #27ae60;
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #666;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <h2>Enter Examination Scores</h2>

        <?php if($status_msg): ?>
            <div class="alert success"><?php echo $status_msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="selection-panel">
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

                <button name="load" class="btn-load">Load Student List</button>
            </div>
        </form>

        <?php
        if(isset($_POST['load'])){
            $class_id = $_POST['class_id'];
            $subject_id = $_POST['subject_id'];
            $term_id = $_POST['term_id'];
            $exam_id = $_POST['exam_id'];

            $students = mysqli_query($conn,"SELECT * FROM students WHERE class_id='$class_id'");

            $existing_grades_query = mysqli_query($conn,"
                SELECT student_id, marks FROM grades 
                WHERE class_id='$class_id' AND subject_id='$subject_id' AND term_id='$term_id' AND exam_id='$exam_id'
            ");
            $existing_grades = [];
            while($eg = mysqli_fetch_assoc($existing_grades_query)){
                $existing_grades[$eg['student_id']] = $eg['marks'];
            }

            if(!empty($existing_grades)){
                echo "<div class='alert info'>Record Found: You are currently in <strong>Editing Mode</strong> for this selection.</div>";
            }

            echo "<form method='POST'>";
            echo "<input type='hidden' name='class_id' value='$class_id'>";
            echo "<input type='hidden' name='subject_id' value='$subject_id'>";
            echo "<input type='hidden' name='term_id' value='$term_id'>";
            echo "<input type='hidden' name='exam_id' value='$exam_id'>";
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Student Full Name</th>
                        <th>Admission No</th>
                        <th>Mark (0-100)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($st=mysqli_fetch_assoc($students)){ 
                        $pre_filled_mark = $existing_grades[$st['id']] ?? ''; ?>
                        <tr>
                            <td><strong><?php echo $st['name']; ?></strong></td>
                            <td><?php echo $st['admission_no']; ?></td>
                            <td>
                                <input type="number" name="marks[<?php echo $st['id']; ?>]" 
                                       min="0" max="100" step="1" 
                                       value="<?php echo $pre_filled_mark; ?>" required>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div style="text-align: right;">
                <button name="save" class="btn-save">Submit & Secure Grades</button>
            </div>
            </form>
        <?php } ?>

        <hr style="margin-top: 40px; border: 0; border-top: 1px solid #eee;">
        <a href="teacher_dashboard.php" class="back-btn"><button>Dashboard</button></a>
    </div>
</div>

</body>
</html>