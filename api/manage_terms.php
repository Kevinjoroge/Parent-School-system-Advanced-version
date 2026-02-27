<?php
include('config/db.php');

/* ADD TERM */
if(isset($_POST['add_term'])){
    $term_name = $_POST['term_name'];

    mysqli_query($conn,"INSERT INTO terms (term_name)
    VALUES ('$term_name')");

    echo "<script>alert('Term Added Successfully'); window.location='manage_terms.php';</script>";
}

/* DELETE TERM */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM terms WHERE id='$id'");
    echo "<script>alert('Term Deleted'); window.location='manage_terms.php';</script>";
}
?>

<h2>Manage Terms</h2>
<hr>

<h3>Add Term</h3>

<form method="POST">
    Term Name:<br>
    <input type="text" name="term_name" required><br><br>
    <button type="submit" name="add_term">Add Term</button>
</form>

<hr>

<h3>All Terms</h3>

<table border="1" cellpadding="8">
<tr>
<th>Term Name</th>
<th>Action</th>
</tr>

<?php
$result = mysqli_query($conn,"SELECT * FROM terms");

while($row = mysqli_fetch_assoc($result)){
?>

<tr>
<td><?php echo $row['term_name']; ?></td>
<td>
    <a href="?delete=<?php echo $row['id']; ?>">Delete</a>
</td>
</tr>

<?php } ?>

</table>