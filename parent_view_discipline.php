<?php
session_start();
include('config/db.php');

/* =========================
   KEEPING YOUR EXACT LOGIC
   ========================= */
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$admission_no = $_SESSION['admission_no'];

$student = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM students WHERE admission_no='$admission_no'"
));

$student_id = $student['id'];

$reports = mysqli_query($conn,"
    SELECT discipline_reports.*, classes.class_name
    FROM discipline_reports
    JOIN classes ON discipline_reports.class_id = classes.id
    WHERE discipline_reports.student_id='$student_id'
    ORDER BY incident_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discipline Reports | SIS</title>
    <style>
        :root {
            --primary-blue: #0a2a66;
            --white: #ffffff;
            --gray: #f4f7f6;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
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
            background: var(--white);
            width: 100%;
            max-width: 1000px;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        h2 { 
            color: var(--primary-blue); 
            margin-top: 0;
            border-bottom: 3px solid var(--primary-blue);
            padding-bottom: 10px;
            text-transform: uppercase;
            font-size: 22px;
        }

        /* Formal Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        th {
            background-color: var(--primary-blue);
            color: white;
            text-align: left;
            padding: 15px;
            font-size: 13px;
            text-transform: uppercase;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #333;
        }

        tr:hover { background-color: #f9f9f9; }

        /* Status Colors */
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        /* Logic for these classes is in the while loop below */
        .resolved { background: #d4edda; color: #155724; }
        .pending { background: #fff3cd; color: #856404; }
        .serious { background: #f8d7da; color: #721c24; }

        .btn-back {
            background: #666;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-back:hover { background: #444; }

        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
    </style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <h2>Discipline Reports</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Incident Type</th>
                    <th>Description</th>
                    <th>Action Taken</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(mysqli_num_rows($reports) > 0){
                    while($row = mysqli_fetch_assoc($reports)){
                        // Simple logic for status styling
                        $status = strtolower($row['status']);
                        $status_class = "status-badge";
                        if($status == 'resolved') $status_class .= " resolved";
                        elseif($status == 'pending') $status_class .= " pending";
                        else $status_class .= " serious";

                        echo "<tr>";
                        echo "<td>" . date('d M Y', strtotime($row['incident_date'])) . "</td>";
                        echo "<td>" . $row['class_name'] . "</td>";
                        echo "<td><strong>" . $row['incident_type'] . "</strong></td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>" . $row['action_taken'] . "</td>";
                        echo "<td><span class='$status_class'>" . $row['status'] . "</span></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#999;'>No discipline reports found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <hr>
        <a href='parent_dashboard.php' class="btn-back">Dashboard</a>
    </div>
</div>

</body>
</html>