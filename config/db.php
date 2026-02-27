<?php
$conn = mysqli_connect("localhost","root","","school_system");

if(!$conn){
    die("Connection Failed");
}
?>



<?php
getenv()
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: 3306; // Default to 3306 if not set

$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    // Pro tip: In production, don't show the full error to users
    error_log("Connection Failed: " . mysqli_connect_error());
    die("Database connection error. Please try again later.");
}
?>