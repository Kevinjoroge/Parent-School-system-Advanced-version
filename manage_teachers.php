<?php
include('config/db.php');

/* ADD TEACHER - LOGIC PRESERVED */
if(isset($_POST['add_teacher'])){
    $name = $_POST['name'];
    $unique_code = $_POST['unique_code'];
    $contact = $_POST['contact'];

    mysqli_query($conn,"INSERT INTO teachers (name, unique_code, contact)
    VALUES ('$name','$unique_code','$contact')");

    echo "<script>alert('Teacher Added Successfully'); window.location='manage_teachers.php';</script>";
}

/* DELETE TEACHER - LOGIC PRESERVED */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM teachers WHERE id='$id'");
    echo "<script>alert('Teacher Deleted'); window.location='manage_teachers.php';</script>";
}

/* UPDATE TEACHER - LOGIC PRESERVED */
if(isset($_POST['update_teacher'])){
    $id = $_POST['id'];
    $name = $_POST['name'];
    $unique_code = $_POST['unique_code'];
    $contact = $_POST['contact'];

    mysqli_query($conn,"UPDATE teachers 
        SET name='$name', unique_code='$unique_code', contact='$contact' 
        WHERE id='$id'");

    echo "<script>alert('Teacher Updated Successfully'); window.location='manage_teachers.php';</script>";
}

/* FETCH TEACHER DATA FOR EDITING */
$edit_data = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM teachers WHERE id='$id'");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Management | Admin Portal</title>
    <style>
        :root {
            --admin-primary: #1a1c23;
            --accent-blue: #3498db;
            --danger: #e74c3c;
            --success: #27ae60;
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
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h2 { color: var(--admin-primary); margin: 0; text-transform: uppercase; letter-spacing: 1px; }

        .card {
            background: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 40px;
            border-top: 4px solid var(--accent-blue);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn-submit {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 11px 25px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
        }

        .btn-update { background: var(--success); }
        .btn-submit:hover { opacity: 0.9; }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #718096;
            text-decoration: none;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        th {
            background: var(--admin-primary);
            color: white;
            text-align: left;
            padding: 15px;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
            font-size: 15px;
        }

        tr:hover { background: #f8fafc; }

        .action-links a {
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: 0.3s;
        }

        .edit-link { color: var(--accent-blue); border: 1px solid var(--accent-blue); margin-right: 5px; }
        .edit-link:hover { background: var(--accent-blue); color: white; }

        .delete-link { color: var(--danger); border: 1px solid var(--danger); }
        .delete-link:hover { background: var(--danger); color: white; }

        .btn-back {
            background: #718096;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
        }

        .badge {
            background: #edf2f7;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
            color: var(--admin-primary);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2>Manage Teachers</h2>
        <a href="admin_dashboard.php" class="btn-back">Dashboard</a>
    </div>

    <div class="card">
        <h3 style="margin-top:0; font-size:16px; margin-bottom:20px; color: var(--accent-blue);">
            <?php echo $edit_data ? 'Update Faculty Member' : 'Register New Faculty Member'; ?>
        </h3>
        <form method="POST">
            <?php if($edit_data): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo $edit_data['name'] ?? ''; ?>" placeholder="e.g. Mr. David Smith" required>
                </div>
                <div>
                    <label>Unique Staff Code</label>
                    <input type="text" name="unique_code" value="<?php echo $edit_data['unique_code'] ?? ''; ?>" placeholder="e.g. TCH/001" required>
                </div>
                <div>
                    <label>Contact Details</label>
                    <input type="text" name="contact" value="<?php echo $edit_data['contact'] ?? ''; ?>" placeholder="e.g. Email or Phone" required>
                </div>
                <div>
                    <?php if($edit_data): ?>
                        <button type="submit" name="update_teacher" class="btn-submit btn-update">Update Teacher</button>
                        <a href="manage_teachers.php" class="btn-cancel">Cancel Edit</a>
                    <?php else: ?>
                        <button type="submit" name="add_teacher" class="btn-submit">Add Teacher</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Teacher Name</th>
                <th>Unique Code</th>
                <th>Contact Information</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($conn,"SELECT * FROM teachers ORDER BY name ASC");

            if(mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    ?>
                    <tr>
                        <td><strong><?php echo $row['name']; ?></strong></td>
                        <td><span class="badge"><?php echo $row['unique_code']; ?></span></td>
                        <td><?php echo $row['contact']; ?></td>
                        <td style="text-align: center;" class="action-links">
                            <a href="?edit=<?php echo $row['id']; ?>" class="edit-link">Edit</a>
                            <a href="?delete=<?php echo $row['id']; ?>" 
                               class="delete-link" 
                               onclick="return confirm('WARNING: Are you sure you want to delete this teacher?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php 
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; color:#999; padding:40px;'>No teachers registered yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>