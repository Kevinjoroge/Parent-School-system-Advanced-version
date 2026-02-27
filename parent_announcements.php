<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$announcements = mysqli_query($conn,"
    SELECT * FROM announcements
    WHERE audience='parents' OR audience='both'
    ORDER BY created_at DESC
");
?>

<h2>School Announcements</h2>
<hr>

<?php
if(mysqli_num_rows($announcements) > 0){
    while($a = mysqli_fetch_assoc($announcements)){
        echo "<div style='border:1px solid #ccc;padding:15px;margin-bottom:15px;background:#f9f9f9;'>";
        echo "<h3>".$a['title']."</h3>";
        echo "<small>Posted on: ".$a['created_at']."</small><br><br>";
        echo "<p>".$a['message']."</p>";

        if(!empty($a['event_date'])){
            echo "<p><strong>Event Date:</strong> ".$a['event_date']."</p>";
        }

        echo "</div>";
    }
} else {
    echo "<p>No announcements available.</p>";
}
?>

<br>
<a href="parent_dashboard.php"><button>Back to Dashboard</button></a>