<?php
include('config/db.php');

/* ADD SUBJECT */
if(isset($_POST['add_subject'])){
    $subject_name = $_POST['subject_name'];

    mysqli_query($conn,"INSERT INTO subjects (subject_name)
    VALUES ('$subject_name')");

    echo "<script>alert('Subject Added Successfully'); window.location='manage_subjects.php';</script>";
}

/* DELETE SUBJECT */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM subjects WHERE id='$id'");
    echo "<script>alert('Subject Deleted'); window.location='manage_subjects.php';</script>";
}
?>

<h2>Manage Subjects</h2>
<hr>

<h3>Add Subject</h3>

<form method="POST">
    Subject Name:<br>
    <input type="text" name="subject_name" required><br><br>
    <button type="submit" name="add_subject">Add Subject</button>
</form>

<hr>

<h3>All Subjects</h3>

<table border="1" cellpadding="8">
<tr>
<th>Subject Name</th>
<th>Action</th>
</tr>

<?php
$result = mysqli_query($conn,"SELECT * FROM subjects");

while($row = mysqli_fetch_assoc($result)){
?>

<tr>
<td><?php echo $row['subject_name']; ?></td>
<td>
    <a href="?delete=<?php echo $row['id']; ?>">Delete</a>
</td>
</tr>

<?php } ?>

</table>