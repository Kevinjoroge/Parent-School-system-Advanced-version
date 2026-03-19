<?php
include('config/db.php');

/* ADD EXAM - LOGIC PRESERVED */
if(isset($_POST['add_exam'])){
    $exam_name = $_POST['exam_name'];

    mysqli_query($conn,"INSERT INTO exams (exam_name)
    VALUES ('$exam_name')");

    echo "<script>alert('Exam Category Added Successfully'); window.location='manage_exams.php';</script>";
}

/* DELETE EXAM - LOGIC PRESERVED */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM exams WHERE id='$id'");
    echo "<script>alert('Exam Deleted'); window.location='manage_exams.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Management | Admin</title>
    <style>
        :root {
            --admin-dark: #1a1c23;
            --accent-red: #c0392b;
            --danger: #e74c3c;
            --white: #ffffff;
            --bg-body: #f7fafc;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-body);
            color: #2d3748;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h2 { color: var(--admin-dark); margin: 0; text-transform: uppercase; letter-spacing: 1px; }

        /* Card Styling */
        .card {
            background: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border-top: 4px solid var(--accent-red);
        }

        .form-inline {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }

        .form-group { flex: 1; }

        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }

        input {
            width: 100%;
            padding: 11px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 15px;
        }

        button[name="add_exam"] {
            background: var(--accent-red);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button[name="add_exam"]:hover { background: #a93226; }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        th {
            background: var(--admin-dark);
            color: white;
            text-align: left;
            padding: 15px;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
            font-size: 16px;
        }

        tr:hover { background: #fdf2f2; }

        .btn-delete {
            color: var(--danger);
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            border: 1px solid var(--danger);
            padding: 5px 12px;
            border-radius: 4px;
            transition: 0.3s;
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
        }

        .btn-back {
            background: #718096;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
        }

        .exam-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2>Exam Categories</h2>
        <a href="admin_dashboard.php" class="btn-back">Dashboard</a>
    </div>

    <div class="card">
        <h3 style="margin-top:0; font-size:16px; margin-bottom:20px; color: var(--accent-red);">Configure New Assessment Type</h3>
        <form method="POST">
            <div class="form-inline">
                <div class="form-group">
                    <label>Exam Name</label>
                    <input type="text" name="exam_name" placeholder="e.g. End of Term One" required>
                </div>
                <button type="submit" name="add_exam">Add Category</button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Category Name</th>
                <th style="text-align: right;">Operations</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($conn,"SELECT * FROM exams ORDER BY id DESC");

            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    ?>
                    <tr>
                        <td><span class="exam-badge"><?php echo $row['exam_name']; ?></span></td>
                        <td style="text-align: right;">
                            <a href="?delete=<?php echo $row['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('CRITICAL: Deleting this exam category will permanently remove all associated student grades. Continue?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php 
                }
            } else {
                echo "<tr><td colspan='2' style='text-align:center; color:#999; padding:40px;'>No exam categories defined.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>