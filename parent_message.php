<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$admission_no = $_SESSION['admission_no'];

$student_query = mysqli_query($conn,"SELECT id FROM students WHERE admission_no='$admission_no'");
$student = mysqli_fetch_assoc($student_query);
$student_id = $student['id'];

$msg_status = "";
if(isset($_POST['send'])){

    $teacher_id = $_POST['teacher_id'];
    $message = mysqli_real_escape_string($conn,$_POST['message']);

    mysqli_query($conn,"
        INSERT INTO messages
        (student_id,sender_role,sender_id,receiver_role,receiver_id,message)
        VALUES
        ('$student_id','parent','$student_id','teacher','$teacher_id','$message')
    ");

    $msg_status = "Message sent successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Message | SIS</title>
    <style>
        :root {
            --primary-blue: #0a2a66;
            --success-green: #27ae60;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: url("assets/backgrounds/school1.JPG") no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background: rgba(10, 42, 102, 0.8);
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
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
            color: var(--primary-blue); 
            margin-top: 0;
            border-bottom: 3px solid var(--primary-blue);
            padding-bottom: 10px;
            text-transform: uppercase;
            font-size: 22px;
        }

        /* Form styling */
        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: inherit;
            font-size: 15px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        button {
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            border-radius: 6px;
            padding: 12px 25px;
            border: none;
        }

        button[name="send"] {
            background: var(--primary-blue);
            color: white;
            width: 100%;
            font-size: 16px;
        }

        button[name="send"]:hover { background: #1c448e; }

        /* Inbox and Back buttons */
        .secondary-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-inbox {
            background: var(--success-green);
            color: white;
            flex: 1;
        }

        .btn-back {
            background: #666;
            color: white;
            flex: 1;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
    </style>
</head>
<body>

<div class="overlay">
    <div class="message-card">
        <h2>Compose Message</h2>

        <?php if($msg_status): ?>
            <div class="alert-success"><?php echo $msg_status; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label style="display:block; margin-bottom: 8px; font-weight: bold; color: #555;">To Teacher:</label>
            <select name="teacher_id" required>
                <option value="">-- Select Recipient --</option>
                <?php
                $teachers = mysqli_query($conn,"SELECT * FROM teachers");
                while($t=mysqli_fetch_assoc($teachers)){
                    echo "<option value='".$t['id']."'>".$t['name']."</option>";
                }
                ?>
            </select>

            <br><br>

            <label style="display:block; margin-bottom: 8px; font-weight: bold; color: #555;">Message Content:</label>
            <textarea name="message" rows="5" required placeholder="Describe your inquiry or concern here..."></textarea>
            
            <br><br>
            <button name="send">SEND</button>
        </form>

        <hr>

        <div class="secondary-actions">
            <a href="parent_inbox.php" style="flex:1;"><button class="btn-inbox">View Inbox</button></a>
            <a href="parent_dashboard.php" style="flex:1;"><button class="btn-back">Dashboard</button></a>
        </div>
    </div>
</div>

</body>
</html>