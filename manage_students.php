<?php
include('config/db.php');

// 1. ADD STUDENT LOGIC
if(isset($_POST['add_student'])){
    $name = $_POST['name'];
    $adm = $_POST['adm'];
    $parent_contact = $_POST['parent_contact'];
    $class_id = $_POST['class_id'];

    mysqli_query($conn,"INSERT INTO students (name, admission_no, parent_contact, class_id) 
    VALUES ('$name','$adm','$parent_contact','$class_id')");

    echo "<script>alert('Student Added Successfully'); window.location='manage_students.php';</script>";
}

// 2. UPDATE STUDENT LOGIC
if(isset($_POST['update_student'])){
    $id = $_POST['student_id'];
    $name = $_POST['name'];
    $adm = $_POST['adm'];
    $parent_contact = $_POST['parent_contact'];
    $class_id = $_POST['class_id'];

    mysqli_query($conn,"UPDATE students SET name='$name', admission_no='$adm', parent_contact='$parent_contact', class_id='$class_id' WHERE id='$id'");

    echo "<script>alert('Student Updated Successfully'); window.location='manage_students.php';</script>";
}

// 3. DELETE STUDENT LOGIC
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM students WHERE id='$id'");
    echo "<script>alert('Student Deleted'); window.location='manage_students.php';</script>";
}

// 4. FETCH DATA FOR EDITING
$edit_data = null;
if(isset($_GET['edit'])){
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM students WHERE id='$id'");
    $edit_data = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students | Admin Portal</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        h3 { margin-top: 25px; color: #34495e; }
        
        /* Form Styling */
        form { background: #f9f9f9; padding: 20px; border-radius: 5px; border: 1px solid #eee; }
        label { font-weight: bold; display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        /* Button Styling */
        button { cursor: pointer; padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; transition: 0.3s; }
        button[name="add_student"], button[name="update_student"] { background-color: #27ae60; color: white; }
        button[name="add_student"]:hover, button[name="update_student"]:hover { background-color: #219150; }
        .btn-cancel { background-color: #95a5a6; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-size: 14px; }
        .btn-back { background-color: #34495e; color: white; margin-top: 20px; }
        
        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #3498db; color: white; text-align: left; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        tr:hover { background-color: #f1f1f1; }
        
        /* Action Links */
        .action-links a { text-decoration: none; font-weight: bold; }
        .edit-link { color: #3498db; }
        .delete-link { color: #e74c3c; margin-left: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Student Management System</h2>

    <div class="form-section">
        <h3><?php echo $edit_data ? 'Update Student Record' : 'Register New Student'; ?></h3>
        <form method="POST">
            <?php if($edit_data): ?>
                <input type="hidden" name="student_id" value="<?php echo $edit_data['id']; ?>">
            <?php endif; ?>

            <label>Student Full Name</label>
            <input type="text" name="name" value="<?php echo $edit_data['name'] ?? ''; ?>" placeholder="e.g. John Doe" required>

            <label>Admission Number</label>
            <input type="text" name="adm" value="<?php echo $edit_data['admission_no'] ?? ''; ?>" placeholder="e.g. ADM-001" required>

            <label>Parent/Guardian Contact</label>
            <input type="text" name="parent_contact" value="<?php echo $edit_data['parent_contact'] ?? ''; ?>" placeholder="e.g. +254..." required>

            <label>Assigned Class</label>
            <select name="class_id" required>
                <option value="">-- Select Class --</option>
                <?php
                $classes = mysqli_query($conn,"SELECT * FROM classes");
                while($row = mysqli_fetch_assoc($classes)){
                    $selected = ($edit_data && $edit_data['class_id'] == $row['id']) ? 'selected' : '';
                    echo "<option value='".$row['id']."' $selected>".$row['class_name']."</option>";
                }
                ?>
            </select>

            <div style="margin-top: 10px;">
                <?php if($edit_data): ?>
                    <button type="submit" name="update_student">Save Changes</button>
                    <a href="manage_students.php" class="btn-cancel">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_student">Register Student</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="list-section">
        <h3>Registered Students List</h3>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Admission</th>
                    <th>Parent Contact</th>
                    <th>Class</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn,"
                    SELECT students.*, classes.class_name 
                    FROM students 
                    JOIN classes ON students.class_id = classes.id
                    ORDER BY students.id DESC
                ");

                while($row = mysqli_fetch_assoc($result)){
                    echo "<tr>
                    <td>".$row['name']."</td>
                    <td>".$row['admission_no']."</td>
                    <td>".$row['parent_contact']."</td>
                    <td>".$row['class_name']."</td>
                    <td class='action-links'>
                        <a href='?edit=".$row['id']."' class='edit-link'>Edit</a>
                        <a href='?delete=".$row['id']."' class='delete-link' onclick=\"return confirm('Confirm deletion of this record?')\">Delete</a>
                    </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <a href="admin_dashboard.php"><button class="btn-back">Return to Dashboard</button></a>
</div>

</body>
</html>