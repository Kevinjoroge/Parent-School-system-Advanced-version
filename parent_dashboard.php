<?php
session_start();
include('config/db.php');

/* SECURITY CHECK */
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

if(!isset($_SESSION['admission_no'])){
    header("Location: parent_login.php");
    exit();
}

$admission_no = $_SESSION['admission_no'];

/* ===========================
    HANDLE PHOTO UPLOAD
   =========================== */
if(isset($_POST['upload_photo'])) {
    $target_dir = "uploads/profile_pics/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    // Use admission number for unique identification
    $new_filename = "student_" . $admission_no . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($file_extension, $allowed)) {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            mysqli_query($conn, "UPDATE students SET profile_photo='$new_filename' WHERE admission_no='$admission_no'");
            header("Location: parent_dashboard.php"); 
            exit();
        }
    }
}

/* FETCH STUDENT DETAILS */
$student_query = mysqli_query($conn,
    "SELECT students.*, classes.class_name
     FROM students
     JOIN classes ON students.class_id = classes.id
     WHERE students.admission_no='$admission_no'"
);

$student = mysqli_fetch_assoc($student_query);

if(!$student){
    echo "Student record not found.";
    exit();
}

$photo_path = !empty($student['profile_photo']) ? "uploads/profile_pics/" . $student['profile_photo'] : "assets/default-avatar.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard | SIS</title>
    <style>
        :root {
            --primary-blue: rgba(10, 42, 102, 0.9);
            --dark-overlay: rgba(0, 0, 0, 0.05);
            --white: #ffffff;
            --accent-light: #ecf0f1;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 300px;
            background: var(--primary-blue);
            backdrop-filter: blur(10px);
            color: white;
            padding: 30px 20px;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3);
            z-index: 2;
            display: flex;
            flex-direction: column;
        }

        /* PROFILE SECTION */
        .profile-box {
            text-align: center;
            margin-bottom: 25px;
        }

        .avatar-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .avatar-wrapper img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 3px solid var(--white);
            object-fit: cover;
            transition: 0.3s;
        }

        .avatar-wrapper:hover img { opacity: 0.7; }

        .avatar-wrapper .upload-hint {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-size: 11px;
            display: none;
            font-weight: bold;
            text-transform: uppercase;
        }

        .avatar-wrapper:hover .upload-hint { display: block; }

        .sidebar h2 {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 15px 0 25px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 15px;
            text-align: center;
        }

        .student-info-box {
            background: rgba(255,255,255,0.15);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #fff;
        }

        .student-info-box h3 {
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: var(--accent-light);
        }

        .student-info-box p { margin: 5px 0; font-size: 14px; }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            padding: 40px;
            background: rgba(244, 247, 249, 0.93);
            overflow-y: auto;
        }

        .main-content h3 {
            color: #0a2a66;
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .modules-grid a { text-decoration: none; }

        .modules-grid button {
            width: 100%; height: 100px;
            background: var(--white); border: none;
            border-radius: 8px; color: #0a2a66;
            font-size: 15px; font-weight: 600;
            cursor: pointer; transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border-bottom: 3px solid #eee;
        }

        .modules-grid button:hover {
            background: #0a2a66; color: white;
            border-bottom: 3px solid #000;
            transform: translateY(-3px);
        }

        .logout-container {
            margin-top: auto;
            padding-top: 20px;
        }

        .logout-btn {
            width: 100%;
            background: #c0392b;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: 0.3s;
        }

        .logout-btn:hover { background: #a93226; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="profile-box">
        <form action="" method="POST" enctype="multipart/form-data" id="photoForm">
            <label class="avatar-wrapper">
                <img src="<?php echo $photo_path; ?>" alt="Student Profile">
                <span class="upload-hint">Update<br>Photo</span>
                <input type="file" name="photo" style="display:none" onchange="document.getElementById('photoForm').submit()">
                <input type="hidden" name="upload_photo" value="1">
            </label>
        </form>
    </div>

    <h2>Parent Portal</h2>
    
    <div class="student-info-box">
        <h3>Current Student</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
        <p><strong>Adm:</strong> <?php echo htmlspecialchars($student['admission_no']); ?></p>
        <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class_name']); ?></p>
    </div>

    <div class="logout-container">
        <a href="logout.php" class="logout-btn">Secure Logout</a>
    </div>
</div>

<div class="main-content">
    <h3>Parental Control Panel</h3>

    <div class="modules-grid">
        <a href="parent_view_attendance.php"><button>Attendance</button></a>
        <a href="parent_view_grades.php"><button>Academic Results</button></a>
        <a href="parent_class_announcements.php"><button>Class Announcements</button></a>
        <a href="parent_view_discipline.php"><button>Discipline</button></a>
        <a href="parent_announcements.php"><button>School Events</button></a>
        <a href="parent_fee_statement.php"><button>Financial Status</button></a>
        <a href="parent_message.php"><button>Messaging</button></a>
    </div>
</div>

</body>
</html>