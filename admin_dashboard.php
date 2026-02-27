<?php
session_start();

if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit();
}
?>
<?php
include('config/db.php');
?>

<h2>ADMIN DASHBOARD</h2>
<hr>

<h3>Manage Users</h3>

<a href="manage_students.php"><button>Manage Students</button></a>
<br><br>
<a href="manage_teachers.php"><button>Manage Teachers</button></a>


<hr>

<h3>System Setup</h3>

<a href="manage_classes.php"><button>Manage Classes</button></a>
<br><br>
<a href="manage_subjects.php"><button>Manage Subjects</button></a>
<br><br>
<a href="manage_terms.php"><button>Manage Terms</button></a>
<br><br>
<a href="manage_exams.php"><button>Manage Exam Categories</button></a>


<hr>

<h3>Reports & Announcements</h3>

<a href="reports.php"><button>Generate Reports</button></a>
<br><br>
<a href="admin_announcements.php"><button>Post Announcements</button></a>
<br><br>
<a href="logs.php"><button>View System Logs</button></a>
