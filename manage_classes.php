<?php
include('config/db.php');

/* ADD CLASS */
if(isset($_POST['add_class'])){
    $class_name = $_POST['class_name'];

    mysqli_query($conn,"INSERT INTO classes (class_name)
    VALUES ('$class_name')");

    echo "<script>alert('Class Added Successfully'); window.location='manage_classes.php';</script>";
}

/* DELETE CLASS */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM classes WHERE id='$id'");
    echo "<script>alert('Class Deleted'); window.location='manage_classes.php';</script>";
}

/* UPDATE CLASS */
if(isset($_POST['update_class'])){
    $id = $_POST['id'];
    $class_name = $_POST['class_name'];

    mysqli_query($conn,"UPDATE classes 
        SET class_name='$class_name' 
        WHERE id='$id'");

    echo "<script>alert('Class Updated Successfully'); window.location='manage_classes.php';</script>";
}
?>

<h2>Manage Classes</h2>
<hr>

<h3>Add Class</h3>

<form method="POST">
    Class Name:<br>
    <input type="text" name="class_name" required><br><br>
    <button type="submit" name="add_class">Add Class</button>
</form>

<hr>

<h3>All Classes</h3>

<table border="1" cellpadding="8">
<tr>
<th>Class Name</th>
<th>Action</th>
</tr>

<?php
$result = mysqli_query($conn,"SELECT * FROM classes");

while($row = mysqli_fetch_assoc($result)){
?>

<tr>
<td><?php echo $row['class_name']; ?></td>
<td>
    <a href="?delete=<?php echo $row['id']; ?>">Delete</a>
</td>
</tr>

<?php } ?>

</table>