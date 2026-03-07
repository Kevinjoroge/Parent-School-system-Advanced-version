<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: admin_login.php");
    exit();
}

$role = $_GET['role'] ?? "";
$search = $_GET['search'] ?? "";

$query = "
SELECT system_logs.*, teachers.unique_code, students.admission_no
FROM system_logs
LEFT JOIN teachers ON system_logs.user_id = teachers.id
LEFT JOIN students ON system_logs.user_id = students.id
WHERE 1
";

if($role!=""){
    $query .= " AND system_logs.user_role='$role'";
}

if($search!=""){
    $query .= " AND (
        teachers.unique_code LIKE '%$search%' OR
        students.admission_no LIKE '%$search%' OR
        system_logs.activity LIKE '%$search%'
    )";
}

$query .= " ORDER BY system_logs.log_time DESC";

$result = mysqli_query($conn,$query);
?>

<h2>System Logs</h2>

<form method="GET">

Filter Role:
<select name="role">
<option value="">All</option>
<option value="admin" <?php if($role=="admin") echo "selected"; ?>>Admin</option>
<option value="teacher" <?php if($role=="teacher") echo "selected"; ?>>Teacher</option>
<option value="parent" <?php if($role=="parent") echo "selected"; ?>>Parent</option>
</select>

Search:
<input type="text" name="search" placeholder="Admission No / Teacher Code / Activity" value="<?php echo $search; ?>">

<button type="submit">Search</button>

<a href="export_logs_excel.php">
<button type="button">Download Excel</button>
</a>

<a href="export_logs_pdf.php">
<button type="button">Download PDF</button>
</a>

</form>

<br>

<table border="1" cellpadding="10">

<tr>
<th>ID</th>
<th>Role</th>
<th>User</th>
<th>Activity</th>
<th>Date & Time</th>
</tr>

<?php
while($row=mysqli_fetch_assoc($result)){

$user="";

if($row['user_role']=="teacher"){
$user=$row['unique_code'];
}
elseif($row['user_role']=="parent"){
$user=$row['admission_no'];
}
else{
$user="Admin";
}

?>

<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo ucfirst($row['user_role']); ?></td>
<td><?php echo $user; ?></td>
<td><?php echo $row['activity']; ?></td>
<td><?php echo $row['log_time']; ?></td>
</tr>

<?php } ?>

</table>

<hr>
<br>
<a href="admin_dashboard.php"><button>Back</button></a>