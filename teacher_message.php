<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$status_msg = "";
$status_type = "";

/* SEND MESSAGE - LOGIC PRESERVED */
if(isset($_POST['send'])){
    $student_id = $_POST['student_id'];
    $message = mysqli_real_escape_string($conn,$_POST['message']);

    /* 🔎 GET PARENT FROM USERS TABLE */
    $parent_query = mysqli_query($conn,"
        SELECT id FROM users
        WHERE role='parent'
        AND student_id='$student_id'
    ");

    $parent = mysqli_fetch_assoc($parent_query);

    if($parent){
        $parent_id = $parent['id'];
        mysqli_query($conn,"
            INSERT INTO messages(student_id,sender_role,sender_id,receiver_role,receiver_id,message)
            VALUES('$student_id','teacher','$teacher_id','parent','$parent_id','$message')
        ");
        $status_msg = "Message sent successfully to the parent.";
        $status_type = "success";
    } else {
        $status_msg = "Error: No parent account found for this student.";
        $status_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compose Message | SIS Faculty</title>
    <style>
        :root {
            --teacher-primary: #0f3460;
            --accent-blue: #16213e;
            --white: #ffffff;
            --success: #27ae60;
            --error: #e74c3c;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background: rgba(15, 52, 96, 0.85);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .message-card {
            background: var(--white);
            width: 100%;
            max-width: 600px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        h2 { 
            color: var(--teacher-primary); 
            margin-top: 0;
            border-bottom: 3px solid var(--teacher-primary);
            padding-bottom: 10px;
            text-transform: uppercase;
            font-size: 20px;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .alert-success { background: #d4edda; color: var(--success); border: 1px solid var(--success); }
        .alert-error { background: #f8d7da; color: var(--error); border: 1px solid var(--error); }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            font-size: 14px;
        }

        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 20px;
            font-family: inherit;
            box-sizing: border-box;
        }

        textarea { height: 120px; resize: vertical; }

        .btn-send {
            background: var(--teacher-primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-send:hover { background: var(--accent-blue); }

        .footer-nav {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-secondary {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            background: #f8f9fa;
            color: #444;
            text-decoration: none;
            text-align: center;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-secondary:hover { background: #eee; }

        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
    </style>
</head>
<body>

<div class="overlay">
    <div class="message-card">
        <h2>Message Parent</h2>

        <?php if($status_msg): ?>
            <div class="alert alert-<?php echo $status_type; ?>"><?php echo $status_msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>1. Select Class:</label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="">-- Choose Class --</option>
                <?php
                $class_query = mysqli_query($conn,"SELECT * FROM classes");
                while($class = mysqli_fetch_assoc($class_query)){
                    $selected = (isset($_POST['class_id']) && $_POST['class_id']==$class['id']) ? "selected" : "";
                    echo "<option value='".$class['id']."' $selected>".$class['class_name']."</option>";
                }
                ?>
            </select>

            <?php if(isset($_POST['class_id']) && $_POST['class_id']!=""): ?>
                <?php $class_id = $_POST['class_id']; ?>
                <label>2. Select Student (Parent will be notified):</label>
                <select name='student_id' required>
                    <option value="">-- Choose Student --</option>
                    <?php
                    $student_query = mysqli_query($conn,"SELECT * FROM students WHERE class_id='$class_id'");
                    while($student = mysqli_fetch_assoc($student_query)){
                        echo "<option value='".$student['id']."'>".$student['name']."</option>";
                    }
                    ?>
                </select>
            <?php endif; ?>

            <label>3. Message Body:</label>
            <textarea name="message" required placeholder="Type your message to the parent here..."></textarea>

            <button type="submit" name="send" class="btn-send">SEND</button>
        </form>

        <hr>

        <div class="footer-nav">
            <a href="teacher_inbox.php" class="btn-secondary">View Inbox</a>
            <a href="teacher_dashboard.php" class="btn-secondary">Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>