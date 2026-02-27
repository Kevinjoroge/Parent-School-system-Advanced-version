<?php
include('config/db.php');

if(isset($_POST['add_student'])){
    $name = $_POST['name'];
    $adm = $_POST['adm'];
    $parent_contact = $_POST['parent_contact'];
    $class_id = $_POST['class_id'];

    mysqli_query($conn,"INSERT INTO students (name, admission_no, parent_contact, class_id)
    VALUES ('$name','$adm','$parent_contact','$class_id')");

    echo "<script>alert('Student Added Successfully'); window.location='manage_students.php';</script>";
}

if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM students WHERE id='$id'");
    echo "<script>alert('Student Deleted'); window.location='manage_students.php';</script>";
}
?>

<h2>Manage Students</h2>
<hr>

<h3>Add Student</h3>

<form method="POST">
    Student Name:<br>
    <input type="text" name="name" required><br><br>

    Admission Number:<br>
    <input type="text" name="adm" required><br><br>

    Parent Contact:<br>
    <input type="text" name="parent_contact" required><br><br>

    Class:<br>
    <select name="class_id" required>
        <option value="">Select Class</option>
        <?php
        $classes = mysqli_query($conn,"SELECT * FROM classes");
        while($row = mysqli_fetch_assoc($classes)){
            echo "<option value='".$row['id']."'>".$row['class_name']."</option>";
        }
        ?>
    </select><br><br>

    <button type="submit" name="add_student">Add Student</button>
</form>

<hr>

<h3>All Students</h3>

<table border="1" cellpadding="8">
<tr>
<th>Name</th>
<th>Admission</th>
<th>Parent Contact</th>
<th>Class</th>
<th>Action</th>
</tr>

<?php
$result = mysqli_query($conn,"
SELECT students.*, classes.class_name 
FROM students 
JOIN classes ON students.class_id = classes.id
");

while($row = mysqli_fetch_assoc($result)){
    echo "<tr>
    <td>".$row['name']."</td>
    <td>".$row['admission_no']."</td>
    <td>".$row['parent_contact']."</td>
    <td>".$row['class_name']."</td>
    <td><a href='?delete=".$row['id']."'>Delete</a></td>
    </tr>";
}
?>

</table>