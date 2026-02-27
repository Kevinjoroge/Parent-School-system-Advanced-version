<?php
include('config/db.php');

/* SAVE ATTENDANCE */
if(isset($_POST['submit_attendance'])){

    $class_id = $_POST['class_id'];
    $date = $_POST['attendance_date'];

    foreach($_POST['status'] as $student_id => $status){

        mysqli_query($conn,"INSERT INTO attendance 
        (student_id, class_id, attendance_date, status)
        VALUES ('$student_id','$class_id','$date','$status')");
    }

    echo "<script>alert('Attendance Recorded Successfully');</script>";
}
?>

<h2>Record Attendance</h2>
<hr>

<form method="POST">

Class:
<select name="class_id" required onchange="this.form.submit()">
<option value="">Select Class</option>

<?php
$classes = mysqli_query($conn,"SELECT * FROM classes");
while($row = mysqli_fetch_assoc($classes)){
    $selected = (isset($_POST['class_id']) && $_POST['class_id']==$row['id']) ? "selected" : "";
    echo "<option value='".$row['id']."' $selected>".$row['class_name']."</option>";
}
?>

</select>

<br><br>

Date:
<input type="date" name="attendance_date" required>

<br><br>

<?php
if(isset($_POST['class_id'])){

$class_id = $_POST['class_id'];

$students = mysqli_query($conn,"SELECT * FROM students WHERE class_id='$class_id'");
?>

<table border="1" cellpadding="8">
<tr>
<th>Name</th>
<th>Admission No</th>
<th>Present</th>
<th>Absent</th>
</tr>

<?php while($stu = mysqli_fetch_assoc($students)){ ?>

<tr>
<td><?php echo $stu['name']; ?></td>
<td><?php echo $stu['admission_no']; ?></td>
<td><input type="radio" name="status[<?php echo $stu['id']; ?>]" value="Present" required></td>
<td><input type="radio" name="status[<?php echo $stu['id']; ?>]" value="Absent"></td>
</tr>

<?php } ?>

</table>

<br>
<button type="submit" name="submit_attendance">Save Attendance</button>

<?php } ?>

</form>