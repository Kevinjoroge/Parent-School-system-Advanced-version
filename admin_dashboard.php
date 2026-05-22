<?php
session_start();

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit();
}
include('config/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Command Center | SIS</title>
    <style>
        :root {
            --admin-dark: #1a1c23;
            --admin-sidebar: #242a38;
            --accent-blue: #3498db;
            --accent-purple: #9b59b6;
            --accent-green: #2ecc71;
            --text-gray: #a0aec0;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation Style */
        .sidebar {
            width: 260px;
            background: var(--admin-dark);
            color: white;
            padding: 30px 20px;
            box-sizing: border-box;
        }

        .sidebar h2 {
            font-size: 18px;
            letter-spacing: 2px;
            margin-bottom: 40px;
            color: var(--accent-blue);
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 40px;
            background: #f7fafc;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .welcome-text h1 { margin: 0; font-size: 24px; color: #2d3748; }
        .welcome-text p { margin: 5px 0 0; color: var(--text-gray); }

        /* Dashboard Grid */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .card {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-top: 4px solid var(--accent-blue);
        }

        .card h3 {
            margin-top: 0;
            font-size: 16px;
            text-transform: uppercase;
            color: #4a5568;
            letter-spacing: 1px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 10px;
        }

        /* Menu Buttons */
        .menu-link {
            display: block;
            background: #edf2f7;
            color: #2d3748;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }

        .menu-link:hover {
            background: var(--accent-blue);
            color: white;
            transform: translateX(5px);
        }

        /* Specific Card Colors */
        .setup-card { border-top-color: var(--accent-purple); }
        .report-card { border-top-color: var(--accent-green); }

        .btn-logout {
            display: inline-block;
            background: #e53e3e;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>SIS ADMIN</h2>
    <p style="font-size: 12px; color: var(--text-gray); text-align: center;">System Version 2026.1</p>
    <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">
    <a href="logout.php" class="btn-logout" style="width: 100%; text-align: center; box-sizing: border-box;">Sign Out</a>
</div>

<div class="main-content">
    <header>
        <div class="welcome-text">
            <h1>Administrative Dashboard</h1>
            <p>Welcome back, System Administrator</p>
        </div>
    </header>

    <div class="grid-container">
        
        <div class="card">
            <h3>👥 Manage Users</h3>
            <a href="manage_students.php" class="menu-link">Manage Students</a>
            <a href="manage_teachers.php" class="menu-link">Manage Teachers</a>
        </div>

        <div class="card setup-card">
            <h3>⚙️ System Setup</h3>
            <a href="manage_classes.php" class="menu-link">Manage Classes</a>
            <a href="manage_subjects.php" class="menu-link">Manage Subjects</a>
            <a href="manage_terms.php" class="menu-link">Manage Terms</a>
            <a href="manage_exams.php" class="menu-link">Manage Exam Categories</a>
        </div>

        <div class="card report-card">
            <h3>📊 Reports & Notices</h3>
            <a href="admin_reports.php" class="menu-link">Generate Reports</a>
            <a href="admin_announcements.php" class="menu-link">Post Announcements</a>
            <a href="admin_view_logs.php" class="menu-link">View System Logs</a>
            <a href="admin_post_fees.php" class="menu-link">Post Fees Structure</a>
            <a href="admin_academic_performance.php" class="menu-link">Academic Performance Analysis</a>
        </div>

    </div>
</div>

</body>
</html>