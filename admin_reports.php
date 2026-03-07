<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<h2>📊 Generate Reports</h2>

<hr>

<h3>Student Report</h3>

<form method="GET" action="admin_student_report.php">

<label>Admission Number</label><br>
<input type="text" name="admission_no" placeholder="Enter Admission Number" required>

<br><br>

<button type="submit" class="btn btn-primary">Generate Student Report</button>

</form>

<hr>

<h3>Class Report</h3>

<form method="GET" action="admin_class_report.php">

<label>Select Class</label><br>

<select name="class_id" required>

<option value="">Select Class</option>

<?php
$class = mysqli_query($conn,"SELECT * FROM classes");

while($c = mysqli_fetch_assoc($class)){
    echo "<option value='".$c['id']."'>".$c['class_name']."</option>";
}
?>

</select>

<br><br>

<button type="submit" class="btn btn-primary">Generate Class Report</button>

</form>

<hr>

<br>

<a href="admin_dashboard.php"><button>Back</button></a>

</body>
</html>