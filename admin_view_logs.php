<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}

$role = $_GET['role'] ?? "";
$search = $_GET['search'] ?? "";

// Logic Preserved
$query = "
SELECT system_logs.*, teachers.unique_code, students.admission_no
FROM system_logs
LEFT JOIN teachers ON system_logs.user_id = teachers.id
LEFT JOIN students ON system_logs.user_id = students.id
WHERE 1
";

if($role!=""){
    $query .= " AND system_logs.user_role='$role'";
}

if($search!=""){
    $query .= " AND (
        teachers.unique_code LIKE '%$search%' OR
        students.admission_no LIKE '%$search%' OR
        system_logs.activity LIKE '%$search%'
    )";
}

$query .= " ORDER BY system_logs.log_time DESC";
$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Logs | Security Console</title>
    <style>
        :root {
            --log-dark: #1a1c23;
            --admin-blue: #3498db;
            --teacher-green: #2ecc71;
            --parent-orange: #f39c12;
            --white: #ffffff;
            --bg-body: #f8fafc;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-body);
            color: #2d3748;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h2 { margin: 0; color: var(--log-dark); text-transform: uppercase; letter-spacing: 1px; }

        /* Filter Section */
        .filter-card {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group { display: flex; flex-direction: column; gap: 8px; }
        .filter-group label { font-size: 12px; font-weight: bold; color: #718096; text-transform: uppercase; }

        select, input[type="text"] {
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 14px;
            min-width: 200px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-search { background: var(--log-dark); color: white; }
        .btn-export { background: #edf2f7; color: #4a5568; border: 1px solid #cbd5e0; }
        .btn-export:hover { background: #e2e8f0; }
        .btn-back { background: #718096; color: white; }

        /* Table Styling */
        .log-table-container {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        table { width: 100%; border-collapse: collapse; }
        
        th {
            background: var(--log-dark);
            color: #a0aec0;
            text-align: left;
            padding: 15px;
            font-size: 13px;
            text-transform: uppercase;
        }

        td { padding: 15px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
        
        tr:hover { background: #f7fafc; }

        /* Role Badges */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-admin { background: #ebf8ff; color: var(--admin-blue); }
        .badge-teacher { background: #f0fff4; color: var(--teacher-green); }
        .badge-parent { background: #fffaf0; color: var(--parent-orange); }

        .time-text { color: #718096; font-size: 12px; font-family: monospace; }
        .activity-text { font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2>System Activity Logs</h2>
        <a href="admin_dashboard.php" class="btn btn-back">Dashboard</a>
    </div>

    <form method="GET" class="filter-card">
        <div class="filter-group">
            <label>Filter Role</label>
            <select name="role">
                <option value="">All Roles</option>
                <option value="admin" <?php if($role=="admin") echo "selected"; ?>>Admin</option>
                <option value="teacher" <?php if($role=="teacher") echo "selected"; ?>>Teacher</option>
                <option value="parent" <?php if($role=="parent") echo "selected"; ?>>Parent</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Keyword Search</label>
            <input type="text" name="search" placeholder="Code / ID / Activity..." value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <button type="submit" class="btn btn-search">Apply Filter</button>
        <a href="export_logs_excel.php" class="btn btn-export">Excel</a>
    </form>

    <div class="log-table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th style="width: 120px;">Role</th>
                    <th style="width: 150px;">User Identifier</th>
                    <th>Activity Performed</th>
                    <th style="width: 200px;">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($row=mysqli_fetch_assoc($result)){
                    $user="";
                    $badge_class = "badge-" . $row['user_role'];

                    if($row['user_role']=="teacher"){
                        $user = "Code: " . $row['unique_code'];
                    } elseif($row['user_role']=="parent"){
                        $user = "Adm: " . $row['admission_no'];
                    } else {
                        $user = "System Admin";
                    }
                ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($row['user_role']); ?></span></td>
                    <td><strong><?php echo $user; ?></strong></td>
                    <td class="activity-text"><?php echo htmlspecialchars($row['activity']); ?></td>
                    <td class="time-text"><?php echo date("Y-m-d H:i:s", strtotime($row['log_time'])); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>