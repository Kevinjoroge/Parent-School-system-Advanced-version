<?php
include('config/db.php');

/* ADD TEACHER */
if(isset($_POST['add_teacher'])){
    $name = $_POST['name'];
    $unique_code = $_POST['unique_code'];
    $contact = $_POST['contact'];

    mysqli_query($conn,"INSERT INTO teachers (name, unique_code, contact)
    VALUES ('$name','$unique_code','$contact')");

    echo "<script>alert('Teacher Added Successfully'); window.location='manage_teachers.php';</script>";
}

/* DELETE TEACHER */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM teachers WHERE id='$id'");
    echo "<script>alert('Teacher Deleted'); window.location='manage_teachers.php';</script>";
}

/* EDIT TEACHER */
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
?>

<h2>Manage Teachers</h2>
<hr>

<h3>Add Teacher</h3>

<form method="POST">
    Teacher Name:<br>
    <input type="text" name="name" required><br><br>

    Unique Code:<br>
    <input type="text" name="unique_code" required><br><br>

    Contact:<br>
    <input type="text" name="contact" required><br><br>

    <button type="submit" name="add_teacher">Add Teacher</button>
</form>

<hr>

<h3>All Teachers</h3>

<table border="1" cellpadding="8">
<tr>
<th>Name</th>
<th>Unique Code</th>
<th>Contact</th>
<th>Action</th>
</tr>

<?php
$result = mysqli_query($conn,"SELECT * FROM teachers");

while($row = mysqli_fetch_assoc($result)){
?>

<tr>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['unique_code']; ?></td>
<td><?php echo $row['contact']; ?></td>
<td>
    <a href="?delete=<?php echo $row['id']; ?>">Delete</a>
</td>
</tr>

<?php } ?>

</table>