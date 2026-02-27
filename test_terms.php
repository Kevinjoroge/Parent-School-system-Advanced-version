<?php
include('config/db.php');

$result = mysqli_query($conn,"SELECT * FROM terms");

if(!$result){
    die("Query failed: " . mysqli_error($conn));
}

echo "Number of terms: " . mysqli_num_rows($result) . "<br><br>";

while($row = mysqli_fetch_assoc($result)){
    echo $row['id'] . " - " . $row['term_name'] . "<br>";
}
?>