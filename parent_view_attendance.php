<?php
session_start();
include('config/db.php');

/* SECURITY CHECK */
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

if(!isset($_SESSION['admission_no'])){
    header("Location: parent_login.php");
    exit();
}

$admission_no = $_SESSION['admission_no'];

/* FETCH STUDENT DETAILS */
$student_query = mysqli_query($conn,
    "SELECT students.*, classes.class_name
     FROM students
     JOIN classes ON students.class_id = classes.id
     WHERE students.admission_no='$admission_no'"
);

$student = mysqli_fetch_assoc($student_query);

if(!$student){
    echo "Student not found.";
    exit();
}

$student_id = $student['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Records | SIS</title>
    <style>
        :root {
            --primary-blue: #0a2a66;
            --present-green: #27ae60;
            --absent-red: #e74c3c;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background: rgba(10, 42, 102, 0.85); /* Formal deep blue tint */
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .report-container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.98);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary-blue);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        h2, h3 {
            margin: 0;
            color: var(--primary-blue);
        }

        /* Info Card Section */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .info-item p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .info-item strong {
            color: var(--primary-blue);
        }

        /* Professional Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
        }

        th {
            background-color: var(--primary-blue);
            color: white;
            text-align: left;
            padding: 15px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            color: #333;
            font-size: 15px;
        }

        tr:hover {
            background-color: #f1f4f9;
        }

        /* Status Pills */
        .status-pill {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-present { background: #d4edda; color: var(--present-green); }
        .status-absent { background: #f8d7da; color: var(--absent-red); }

        /* Buttons */
        .btn-back {
            text-decoration: none;
            background: var(--primary-blue);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-back:hover {
            background: #1c448e;
            transform: translateY(-2px);
        }

        @media print {
            .btn-back, .overlay { background: white !important; padding: 0; }
            .report-container { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="overlay">
    <div class="report-container">
        
        <div class="header-section">
            <h2>Attendance Records</h2>
            <a href="parent_dashboard.php"><button class="btn-back">Dashboard</button></a>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <p><strong>Student Name</strong></p>
                <p><?php echo $student['name']; ?></p>
            </div>
            <div class="info-item">
                <p><strong>Admission No</strong></p>
                <p><?php echo $student['admission_no']; ?></p>
            </div>
            <div class="info-item">
                <p><strong>Current Class</strong></p>
                <p><?php echo $student['class_name']; ?></p>
            </div>
        </div>

        <h3>Attendance History</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $attendance = mysqli_query($conn,"
                    SELECT attendance.*, classes.class_name
                    FROM attendance
                    JOIN classes ON attendance.class_id = classes.id
                    WHERE attendance.student_id='$student_id'
                    ORDER BY attendance.attendance_date DESC
                ");

                if(mysqli_num_rows($attendance) > 0){
                    while($row = mysqli_fetch_assoc($attendance)){
                        // Check status for color coding
                        $status = $row['status'];
                        $pill_class = (strtolower($status) == 'present') ? 'status-present' : 'status-absent';

                        echo "<tr>";
                        echo "<td><strong>" . date('M d, Y', strtotime($row['attendance_date'])) . "</strong></td>";
                        echo "<td>" . $row['class_name'] . "</td>";
                        echo "<td><span class='status-pill $pill_class'>" . $status . "</span></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align:center; padding:30px; color:#999;'>No attendance records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>