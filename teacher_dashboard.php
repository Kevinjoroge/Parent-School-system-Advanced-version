<?php
session_start();
include('config/db.php');

/* SECURITY CHECK */
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

/* ===========================
    HANDLE PHOTO UPLOAD
   =========================== */
if(isset($_POST['upload_photo'])) {
    $target_dir = "uploads/profile_pics/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $new_filename = "teacher_" . $teacher_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($file_extension, $allowed)) {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            mysqli_query($conn, "UPDATE teachers SET profile_photo='$new_filename' WHERE id='$teacher_id'");
            header("Location: teacher_dashboard.php"); 
            exit();
        }
    }
}

/* FETCH TEACHER DETAILS (Using 'contact' as seen in your screenshot) */
$t_query = mysqli_query($conn, "SELECT * FROM teachers WHERE id='$teacher_id'");
$teacher = mysqli_fetch_assoc($t_query);

$photo_path = !empty($teacher['profile_photo']) ? "uploads/profile_pics/" . $teacher['profile_photo'] : "assets/default-avatar.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | SIS</title>
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --teacher-blue: #0f3460;
            --accent-glow: #e94560;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 280px;
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3);
        }

        /* PROFILE SECTION */
        .profile-box {
            text-align: center;
            margin-bottom: 20px;
        }

        .avatar-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .avatar-wrapper img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid var(--accent-glow);
            object-fit: cover;
            transition: 0.3s;
        }

        .avatar-wrapper:hover img { opacity: 0.6; }

        .avatar-wrapper .upload-icon {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-size: 10px; display: none; font-weight: bold;
        }

        .avatar-wrapper:hover .upload-icon { display: block; }

        .teacher-name {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            font-size: 16px;
        }

        .sidebar h2 {
            font-size: 14px; text-align: center; letter-spacing: 2px;
            margin: 20px 0; border-bottom: 2px solid var(--accent-glow);
            padding-bottom: 10px; opacity: 0.8;
        }

        .user-tag {
            background: rgba(255,255,255,0.05);
            padding: 15px; border-radius: 8px; margin-bottom: 30px;
        }

        .user-tag small { color: var(--accent-glow); text-transform: uppercase; font-weight: bold; font-size: 10px; display: block; margin-bottom: 5px;}
        .user-tag p { margin: 4px 0; font-size: 13px; color: #ccc; }
        .user-tag p strong { color: white; }

        /* MAIN CONTENT TILES */
        .main-content { flex: 1; padding: 50px; background: rgba(244, 247, 249, 0.85); overflow-y: auto; }
        .main-content h3 { color: var(--primary-dark); font-size: 28px; margin-bottom: 30px; font-weight: 300; }
        .modules-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
        .tile {
            background: var(--white); height: 140px; border-radius: 12px;
            display: flex; justify-content: center; align-items: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08); transition: 0.3s;
            border-bottom: 4px solid var(--teacher-blue);
        }
        .tile:hover { transform: translateY(-8px); background: var(--teacher-blue); border-bottom: 4px solid var(--accent-glow); }
        .tile span { font-size: 16px; font-weight: 600; color: var(--primary-dark); text-align: center; }
        .tile:hover span { color: var(--white); }

        .logout-btn {
            margin-top: auto; display: block; padding: 12px;
            background: var(--accent-glow); color: white; text-align: center;
            text-decoration: none; border-radius: 6px; font-weight: bold;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="profile-box">
        <form action="" method="POST" enctype="multipart/form-data" id="photoForm">
            <label class="avatar-wrapper">
                <img src="<?php echo $photo_path; ?>" alt="Teacher Photo">
                <span class="upload-icon">CLICK TO<br>UPDATE</span>
                <input type="file" name="photo" style="display:none" onchange="document.getElementById('photoForm').submit()">
                <input type="hidden" name="upload_photo" value="1">
            </label>
        </form>
        <span class="teacher-name"><?php echo htmlspecialchars($teacher['name']); ?></span>
    </div>

    <h2>FACULTY PORTAL</h2>
    
    <div class="user-tag">
        <small>Staff Information</small>
        <p>ID Code: <strong><?php echo htmlspecialchars($teacher['unique_code']); ?></strong></p>
        <p>Contact: <strong><?php echo htmlspecialchars($teacher['contact']); ?></strong></p>
    </div>

    <a href="logout.php" class="logout-btn">Log Out Securely</a>
</div>

<div class="main-content">
    <h3>Classroom Management</h3>
    <div class="modules-grid">
        <a href="teacher_attendance.php" style="text-decoration:none;"><div class="tile"><span>Record Attendance</span></div></a>
        <a href="teacher_grades.php" style="text-decoration:none;"><div class="tile"><span>Update Scores</span></div></a>
        <a href="teacher_discipline.php" style="text-decoration:none;"><div class="tile"><span>Discipline Cases</span></div></a>
        <a href="teacher_announcements.php" style="text-decoration:none;"><div class="tile"><span>School Events</span></div></a>
        <a href="teacher_message.php" style="text-decoration:none;"><div class="tile"><span>Messaging</span></div></a>
        <a href="teacher_class_announcements.php" style="text-decoration:none;"><div class="tile"><span>Class Announcements</span></div></a>
    </div>
</div>

</body>
</html>