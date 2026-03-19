<?php
include('config/db.php');

/* =========================================
   SAVE ATTENDANCE - LOGIC PRESERVED
   ========================================= */
if(isset($_POST['submit_attendance'])){
    $class_id = $_POST['class_id'];
    $date = $_POST['attendance_date'];

    foreach($_POST['status'] as $student_id => $status){
        mysqli_query($conn,"INSERT INTO attendance 
        (student_id, class_id, attendance_date, status)
        VALUES ('$student_id','$class_id','$date','$status')");
    }
    echo "<script>alert('Attendance Recorded Successfully');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Attendance | SIS</title>
    <style>
        :root {
            --teacher-primary: #0f3460;
            --accent: #e94560;
            --white: #ffffff;
            --present: #27ae60;
            --absent: #e74c3c;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background: rgba(26, 42, 102, 0.85);
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
        }

        .container {
            background: var(--white);
            width: 100%;
            max-width: 900px;
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

        .filter-section {
            background: #f4f7f6;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            border: 1px solid #ddd;
        }

        label { font-weight: bold; color: #555; display: block; margin-bottom: 5px; }

        select, input[type="date"] {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: var(--teacher-primary);
            color: white;
            text-align: left;
            padding: 15px;
            font-size: 14px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 15px;
        }

        tr:nth-child(even) { background: #fafafa; }

        /* Customizing Radio Buttons for Attendance */
        .radio-cell { text-align: center; }
        
        .present-label { color: var(--present); font-weight: bold; }
        .absent-label { color: var(--absent); font-weight: bold; }

        .btn-save {
            background: var(--teacher-primary);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-save:hover { background: var(--accent); }

        .btn-back {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-weight: bold;
        }

        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
    </style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <h2>Record Daily Attendance</h2>

        <form method="POST">
            <div class="filter-section">
                <div>
                    <label>Select Class:</label>
                    <select name="class_id" required onchange="this.form.submit()">
                        <option value="">-- Choose Class --</option>
                        <?php
                        $classes = mysqli_query($conn,"SELECT * FROM classes");
                        while($row = mysqli_fetch_assoc($classes)){
                            $selected = (isset($_POST['class_id']) && $_POST['class_id']==$row['id']) ? "selected" : "";
                            echo "<option value='".$row['id']."' $selected>".$row['class_name']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label>Attendance Date:</label>
                    <input type="date" name="attendance_date" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <?php
            if(isset($_POST['class_id'])){
                $class_id = $_POST['class_id'];
                $students = mysqli_query($conn,"SELECT * FROM students WHERE class_id='$class_id'");
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Admission No</th>
                        <th style="text-align: center;">Present</th>
                        <th style="text-align: center;">Absent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($stu = mysqli_fetch_assoc($students)){ ?>
                    <tr>
                        <td><strong><?php echo $stu['name']; ?></strong></td>
                        <td><?php echo $stu['admission_no']; ?></td>
                        <td class="radio-cell">
                            <input type="radio" name="status[<?php echo $stu['id']; ?>]" value="Present" required>
                        </td>
                        <td class="radio-cell">
                            <input type="radio" name="status[<?php echo $stu['id']; ?>]" value="Absent">
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <button type="submit" name="submit_attendance" class="btn-save">Save Attendance Records</button>

            <?php } else { ?>
                <p style="text-align: center; color: #999; padding: 40px; border: 2px dashed #ddd; border-radius: 8px;">
                    Please select a class to load the student registry.
                </p>
            <?php } ?>
        </form>

        <hr>
        <a href="teacher_dashboard.php" class="btn-back"><button>Dashboard</button></a>
    </div>
</div>

</body>
</html>