<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

$status_msg = "";
/* SAVE DISCIPLINE REPORT - LOGIC UNTOUCHED */
if(isset($_POST['submit_report'])){
    $class_id = $_POST['class_id'];
    $student_id = $_POST['student_id'];
    $incident_date = $_POST['incident_date'];
    $incident_type = $_POST['incident_type'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $action_taken = mysqli_real_escape_string($conn, $_POST['action_taken']);

    mysqli_query($conn,"
        INSERT INTO discipline_reports
        (student_id,class_id,incident_date,incident_type,description,action_taken)
        VALUES
        ('$student_id','$class_id','$incident_date','$incident_type','$description','$action_taken')
    ");

    $status_msg = "Discipline Report Submitted Successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discipline Reporting | SIS</title>
    <style>
        :root {
            --teacher-primary: #0f3460;
            --accent-red: #e94560;
            --white: #ffffff;
            --gray-bg: #f4f7f6;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background: rgba(15, 52, 96, 0.85);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            background: var(--white);
            width: 100%;
            max-width: 700px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        h2 { 
            color: var(--teacher-primary); 
            text-transform: uppercase; 
            border-bottom: 3px solid var(--teacher-primary);
            padding-bottom: 10px;
            margin-top: 0;
        }

        .report-form { margin-top: 25px; }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
            font-size: 14px;
        }

        select, input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: inherit;
            font-size: 15px;
            box-sizing: border-box;
            margin-bottom: 20px;
        }

        textarea { resize: vertical; }

        .btn-submit {
            background: var(--accent-red);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: 0.3s;
        }

        .btn-submit:hover { background: #c62842; }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #c3e6cb;
        }

        .btn-back {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #666;
            font-weight: bold;
        }

        .step-container {
            background: var(--gray-bg);
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid var(--teacher-primary);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <h2>Discipline Reporting</h2>

        <?php if($status_msg): ?>
            <div class="alert-success"><?php echo $status_msg; ?></div>
        <?php endif; ?>

        <div class="step-container">
            <form method="POST">
                <label>Select Class for Incident:</label>
                <select name="class_id" required onchange="this.form.submit()">
                    <option value="">-- Choose Class --</option>
                    <?php
                    $classes = mysqli_query($conn,"SELECT * FROM classes");
                    while($c=mysqli_fetch_assoc($classes)){
                        $selected = (isset($_POST['class_id']) && $_POST['class_id'] == $c['id']) ? "selected" : "";
                        echo "<option value='".$c['id']."' $selected>".$c['class_name']."</option>";
                    }
                    ?>
                </select>
            </form>
        </div>

        <?php
        if(isset($_POST['class_id'])){
            $class_id = $_POST['class_id'];
            $students = mysqli_query($conn,"SELECT * FROM students WHERE class_id='$class_id'");
        ?>

        <form method="POST" class="report-form">
            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">

            <label>Student Involved:</label>
            <select name="student_id" required>
                <option value="">-- Select Student --</option>
                <?php
                while($st=mysqli_fetch_assoc($students)){
                    echo "<option value='".$st['id']."'>".$st['name']." (".$st['admission_no'].")</option>";
                }
                ?>
            </select>

            <label>Date & Time of Incident:</label>
            <input type="datetime-local" name="incident_date" required>

            <label>Nature of Incident:</label>
            <select name="incident_type" required>
                <option value="">-- Select Incident Type --</option>
                <option>Late Coming</option>
                <option>Absenteeism</option>
                <option>Fighting/Bullying</option>
                <option>Disrespect/Defiance</option>
                <option>Incomplete Homework</option>
                <option>Dress Code Violation</option>
                <option>Other</option>
            </select>

            <label>Detailed Description:</label>
            <textarea name="description" rows="4" required placeholder="Describe exactly what happened..."></textarea>

            <label>Action Taken / Recommendation:</label>
            <textarea name="action_taken" rows="3" required placeholder="What measures were taken by the teacher?"></textarea>

            <button type="submit" name="submit_report" class="btn-submit">Submit Official Report</button>
        </form>

        <?php } else { ?>
            <p style="text-align:center; color:#999; margin-top:30px;">Select a class above to begin the reporting process.</p>
        <?php } ?>

        <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
        <a href="teacher_dashboard.php" class="btn-back"><button>Dashboard</button></a>
    </div>
</div>

</body>
</html>