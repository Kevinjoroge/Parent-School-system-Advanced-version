<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}

$admission = $_GET['admission_no'];
$view = isset($_GET['view']) ? $_GET['view'] : 'info';

// Fetch Student Basics
$student_query = mysqli_query($conn,"
    SELECT students.*, classes.class_name 
    FROM students
    JOIN classes ON students.class_id=classes.id
    WHERE admission_no='$admission'
");
$student = mysqli_fetch_assoc($student_query);

if(!$student){
    echo "Student not found";
    exit();
}

$student_id = $student['id'];
$class_id = $student['class_id'];

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Report - <?php echo $student['name']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .nav-buttons { margin: 20px 0; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        .btn { padding: 8px 15px; margin-right: 5px; cursor: pointer; background: #f4f4f4; border: 1px solid #ccc; border-radius: 4px; }
        .btn-active { background: #007bff; color: white; border-color: #004085; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        table, th, td { border: 1px solid #444; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .filter-box { background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .stats-card { background: #fff; border: 1px solid #007bff; padding: 15px; display: inline-block; margin-top: 15px; border-radius: 5px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print">
    <h2>Student Management: <?php echo $student['name']; ?></h2>
    <button class="btn" onclick="window.print()">Print</button>
    <a href="admin_reports.php"><button class="btn">Back</button></a>
</div>

<div class="nav-buttons no-print">
    <a href="?admission_no=<?php echo $admission; ?>&view=info"><button class="btn <?php echo ($view == 'info') ? 'btn-active' : ''; ?>">General Info</button></a>
    <a href="?admission_no=<?php echo $admission; ?>&view=attendance"><button class="btn <?php echo ($view == 'attendance') ? 'btn-active' : ''; ?>">Attendance</button></a>
    <a href="?admission_no=<?php echo $admission; ?>&view=discipline"><button class="btn <?php echo ($view == 'discipline') ? 'btn-active' : ''; ?>">Discipline</button></a>
    <a href="?admission_no=<?php echo $admission; ?>&view=grades"><button class="btn <?php echo ($view == 'grades') ? 'btn-active' : ''; ?>">Grades/Academic</button></a>
    <a href="?admission_no=<?php echo $admission; ?>&view=fees"><button class="btn <?php echo ($view == 'fees') ? 'btn-active' : ''; ?>">Fees Statement</button></a>
</div>

<?php if($view == 'info'): ?>
    <h3>General Information</h3>
    <table style="width: 50%;">
        <tr><th>Name</th><td><?php echo $student['name']; ?></td></tr>
        <tr><th>Admission No</th><td><?php echo $student['admission_no']; ?></td></tr>
        <tr><th>Class</th><td><?php echo $student['class_name']; ?></td></tr>
        <tr><th>Parent Contact</th><td><?php echo $student['parent_contact']; ?></td></tr>
    </table>

<?php elseif($view == 'attendance'): ?>
    <h3>Attendance History</h3>
    <table>
        <tr><th>Date</th><th>Status</th></tr>
        <?php
        $att = mysqli_query($conn,"SELECT * FROM attendance WHERE student_id='$student_id' ORDER BY attendance_date DESC");
        while($a=mysqli_fetch_assoc($att)) echo "<tr><td>".$a['attendance_date']."</td><td>".$a['status']."</td></tr>";
        ?>
    </table>

<?php elseif($view == 'discipline'): ?>
    <h3>Discipline Logs</h3>
    <table>
        <tr><th>Date</th><th>Incident</th><th>Action Taken</th></tr>
        <?php
        $disc = mysqli_query($conn,"SELECT * FROM discipline_reports WHERE student_id='$student_id' ORDER BY incident_date DESC");
        while($d=mysqli_fetch_assoc($disc)) echo "<tr><td>".$d['incident_date']."</td><td>".$d['incident_type']."</td><td>".$d['action_taken']."</td></tr>";
        ?>
    </table>

<?php elseif($view == 'grades'): ?>
    <h3>Academic Performance</h3>
    <div class="filter-box no-print">
        <form method="POST">
            <select name="term_id" required>
                <option value="">Select Term</option>
                <?php
                $terms = mysqli_query($conn,"SELECT * FROM terms");
                while($t=mysqli_fetch_assoc($terms)){
                    $sel = (isset($_POST['term_id']) && $_POST['term_id'] == $t['id']) ? "selected" : "";
                    echo "<option value='".$t['id']."' $sel>".$t['term_name']."</option>";
                }
                ?>
            </select>

            <select name="exam_id" required>
                <option value="">Select Exam</option>
                <?php
                $exams = mysqli_query($conn,"SELECT * FROM exams");
                while($e=mysqli_fetch_assoc($exams)){
                    $sel = (isset($_POST['exam_id']) && $_POST['exam_id'] == $e['id']) ? "selected" : "";
                    echo "<option value='".$e['id']."' $sel>".$e['exam_name']."</option>";
                }
                ?>
            </select>
            <button type="submit" name="filter_grades" class="btn btn-active">Load Results</button>
        </form>
    </div>

    <?php
    if(isset($_POST['filter_grades'])){
        $t_id = $_POST['term_id'];
        $e_id = $_POST['exam_id'];

        // 1. Fetch Student Grades for this filter
        $grades_q = mysqli_query($conn,"
            SELECT g.*, s.subject_name 
            FROM grades g
            JOIN subjects s ON g.subject_id=s.id
            WHERE g.student_id='$student_id' AND g.term_id='$t_id' AND g.exam_id='$e_id'
        ");

        // 2. Ranking Logic: Get all students in this class for the same period
        $rank_query = mysqli_query($conn,"
            SELECT student_id, AVG(points) as avg_p 
            FROM grades 
            WHERE class_id='$class_id' AND term_id='$t_id' AND exam_id='$e_id' 
            GROUP BY student_id 
            ORDER BY avg_p DESC
        ");
        
        $position = 0;
        $student_rank = "N/A";
        $counter = 1;
        $prev_points = -1;
        $display_rank = 1;
        $student_avg = 0;

        while($r = mysqli_fetch_assoc($rank_query)){
            if($prev_points != -1 && $r['avg_p'] < $prev_points){
                $display_rank = $counter;
            }
            if($r['student_id'] == $student_id){
                $student_rank = $display_rank;
                $student_avg = $r['avg_p'];
            }
            $prev_points = $r['avg_p'];
            $counter++;
        }

        if(mysqli_num_rows($grades_q) > 0){
            echo "<table><tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Points</th></tr>";
            while($row = mysqli_fetch_assoc($grades_q)){
                echo "<tr><td>".$row['subject_name']."</td><td>".$row['marks']."</td><td>".$row['grade']."</td><td>".$row['points']."</td></tr>";
            }
            echo "</table>";

            echo "<div class='stats-card'>";
            echo "<p>Mean Points: ".round($student_avg, 2)."</p>";
            echo "<p>Mean Grade: ".gradeFromPoints($student_avg)."</p>";
            echo "<p>Class Position: ".$student_rank." / ".(mysqli_num_rows($rank_query))."</p>";
            echo "</div>";
        } else {
            echo "<p>No records found for the selected Term/Exam.</p>";
        }
    }
    ?>

<?php elseif($view == 'fees'): ?>
    <h3>Fees Statement</h3>
    <table border="1" width="100%" style="border-collapse: collapse; margin-top: 10px;">
    <tr style="background: #f2f2f2;">
        <th>Date</th>
        <th>Description</th>
        <th>Ref No</th>
        <th>Debit (Owed)</th>
        <th>Credit (Paid)</th>
    </tr>
    <?php
    // Querying our new fee_payments table linked with categories
    $fees = mysqli_query($conn, "SELECT fp.*, fc.category_name 
                                 FROM fee_payments fp 
                                 JOIN fee_categories fc ON fp.category_id = fc.id 
                                 WHERE fp.student_id = '$student_id' 
                                 ORDER BY fp.date_paid DESC");

    $total_billed = 0; 
    $total_paid = 0;

    if(mysqli_num_rows($fees) > 0) {
        while($f = mysqli_fetch_assoc($fees)) {
            $is_charge = ($f['transaction_type'] == 'charge');
            if($is_charge) $total_billed += $f['amount_paid']; else $total_paid += $f['amount_paid'];
            
            echo "<tr>
                    <td>".$f['date_paid']."</td>
                    <td>".htmlspecialchars($f['category_name'])."</td>
                    <td>".$f['reference_no']."</td>
                    <td style='color:red;'>".($is_charge ? number_format($f['amount_paid'], 2) : '-')."</td>
                    <td style='color:green;'>".(!$is_charge ? number_format($f['amount_paid'], 2) : '-')."</td>
                  </tr>";
        }
        
        // Calculation for the final balance
        $balance = $total_billed - $total_paid;
        
        echo "<tr style='background: #fafafa; font-weight: bold;'>
                <td colspan='3' style='text-align:right;'>Totals:</td>
                <td style='color:red;'>".number_format($total_billed, 2)."</td>
                <td style='color:green;'>".number_format($total_paid, 2)."</td>
              </tr>";
        echo "<tr>
                <td colspan='5' style='text-align:right; font-size: 16px;'>
                    <strong>Current Balance Due: <span style='color:blue;'>".number_format($balance, 2)."</span></strong>
                </td>
              </tr>";
    } else {
        echo "<tr><td colspan='5' style='text-align:center;'>No fee records found for this student.</td></tr>";
    }
    ?>
</table>
<?php endif; ?>

</body>
</html>