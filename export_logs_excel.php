<?php
include('config/db.php');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=system_logs.xls");

echo "ID\tRole\tUser ID\tActivity\tDate\n";

$query=mysqli_query($conn,"SELECT * FROM system_logs ORDER BY log_time DESC");

while($row=mysqli_fetch_assoc($query)){

echo $row['id']."\t".
$row['user_role']."\t".
$row['user_id']."\t".
$row['activity']."\t".
$row['log_time']."\n";

}
?>