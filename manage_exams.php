<?php
include('config/db.php');

/* ADD EXAM */
if(isset($_POST['add_exam'])){
    $exam_name = $_POST['exam_name'];

    mysqli_query($conn,"INSERT INTO exams (exam_name)
    VALUES ('$exam_name')");

    echo "<script>alert('Exam Category Added Successfully'); window.location='manage_exams.php';</script>";
}

/* DELETE EXAM */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM exams WHERE id='$id'");
    echo "<script>alert('Exam Deleted'); window.location='manage_exams.php';</script>";
}
?>

<h2>Manage Exam Categories</h2>
<hr>

<h3>Add Exam Category</h3>

<form method="POST">
    Exam Name:<br>
    <input type="text" name="exam_name" required><br><br>
    <button type="submit" name="add_exam">Add Exam Category</button>
</form>

<hr>

<h3>All Exam Categories</h3>

<table border="1" cellpadding="8">
<tr>
<th>Exam Name</th>
<th>Action</th>
</tr>

<?php
$result = mysqli_query($conn,"SELECT * FROM exams");

while($row = mysqli_fetch_assoc($result)){
?>

<tr>
<td><?php echo $row['exam_name']; ?></td>
<td>
    <a href="?delete=<?php echo $row['id']; ?>">Delete</a>
</td>
</tr>

<?php } ?>

</table>