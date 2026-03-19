<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Reports | SIS Admin</title>
    <style>
        :root {
            --admin-dark: #1a1c23;
            --report-blue: #3498db;
            --report-green: #2ecc71;
            --white: #ffffff;
            --bg-body: #f7fafc;
            --text-main: #2d3748;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }

        h2 { margin: 0; color: var(--admin-dark); text-transform: uppercase; letter-spacing: 1px; }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }

        /* Card Styling */
        .report-card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .report-card:hover { transform: translateY(-3px); }

        .card-student { border-top: 5px solid var(--report-blue); }
        .card-class { border-top: 5px solid var(--report-green); }

        h3 {
            margin-top: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-student h3 { color: var(--report-blue); }
        .card-class h3 { color: var(--report-green); }

        p.desc {
            font-size: 13px;
            color: #718096;
            margin-bottom: 20px;
        }

        /* Form Elements */
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="text"], select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 15px;
            margin-bottom: 20px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #cbd5e0;
            background: #fcfcfc;
        }

        .btn-generate {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-student { background: var(--report-blue); }
        .btn-student:hover { background: #2980b9; }

        .btn-class { background: var(--report-green); }
        .btn-class:hover { background: #27ae60; }

        .btn-back {
            background: #718096;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-section">
        <h2>📊 Generate Reports</h2>
        <a href="admin_dashboard.php" class="btn-back">Dashboard</a>
    </div>

    <div class="report-grid">
        
        <div class="report-card card-student">
            <h3>👤 Individual Student Report</h3>
            <p class="desc">Generate a comprehensive transcript and performance breakdown for a single student.</p>
            
            <form method="GET" action="admin_student_report.php">
                <label>Admission Number</label>
                <input type="text" name="admission_no" placeholder="Enter Unique ID (e.g. ADM/001)" required>
                
                <button type="submit" class="btn-generate btn-student">Generate Transcript</button>
            </form>
        </div>

        <div class="report-card card-class">
            <h3>🏫 Class Performance Report</h3>
            <p class="desc">View merit lists, class averages, and subject rankings for an entire grade level.</p>
            
            <form method="GET" action="admin_class_report.php">
                <label>Target Class</label>
                <select name="class_id" required>
                    <option value="">-- Choose Class --</option>
                    <?php
                    $class = mysqli_query($conn,"SELECT * FROM classes");
                    while($c = mysqli_fetch_assoc($class)){
                        echo "<option value='".$c['id']."'>".$c['class_name']."</option>";
                    }
                    ?>
                </select>
                
                <button type="submit" class="btn-generate btn-class">Generate Merit List</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>