<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

/* SAVE DISCIPLINE REPORT */
if(isset($_POST['submit_report'])){

    $class_id = $_POST['class_id'];
    $student_id = $_POST['student_id'];
    $incident_date = $_POST['incident_date'];
    $incident_type = $_POST['incident_type'];
    $description = $_POST['description'];
    $action_taken = $_POST['action_taken'];

    mysqli_query($conn,"
        INSERT INTO discipline_reports
        (student_id,class_id,incident_date,incident_type,description,action_taken)
        VALUES
        ('$student_id','$class_id','$incident_date','$incident_type','$description','$action_taken')
    ");

    echo "<p style='color:green;'>Discipline Report Submitted Successfully!</p>";
}
?>

<h2>Discipline Reporting Form</h2>
<hr>

<form method="POST">

<label>Select Class:</label><br>
<select name="class_id" required onchange="this.form.submit()">
<option value="">Select Class</option>
<?php
$classes = mysqli_query($conn,"SELECT * FROM classes");
while($c=mysqli_fetch_assoc($classes)){
echo "<option value='".$c['id']."'>".$c['class_name']."</option>";
}
?>
</select>

</form>

<?php
/* LOAD STUDENTS AFTER CLASS SELECTED */
if(isset($_POST['class_id'])){

$class_id = $_POST['class_id'];

$students = mysqli_query($conn,"SELECT * FROM students WHERE class_id='$class_id'");
?>

<form method="POST">

<input type="hidden" name="class_id" value="<?php echo $class_id; ?>">

<br><label>Select Student:</label><br>
<select name="student_id" required>
<option value="">Select Student</option>
<?php
while($st=mysqli_fetch_assoc($students)){
echo "<option value='".$st['id']."'>".$st['name']." (".$st['admission_no'].")</option>";
}
?>
</select>

<br><br>

<label>Date & Time:</label><br>
<input type="datetime-local" name="incident_date" required>

<br><br>

<label>Type of Incident:</label><br>
<select name="incident_type" required>
<option value="">Select Type</option>
<option>Late Coming</option>
<option>Absenteeism</option>
<option>Fighting</option>
<option>Disrespect</option>
<option>Incomplete Homework</option>
<option>Other</option>
</select>

<br><br>

<label>Description:</label><br>
<textarea name="description" rows="4" cols="40" required></textarea>

<br><br>

<label>Action Taken:</label><br>
<textarea name="action_taken" rows="3" cols="40" required></textarea>

<br><br>

<button name="submit_report">Submit Report</button>

</form>

<?php } ?>

<br><hr>
<a href="teacher_dashboard.php">⬅ Back to Dashboard</a>