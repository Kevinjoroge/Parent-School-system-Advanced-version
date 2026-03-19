<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

if(!isset($_GET['student_id'])){
    die("<div style='padding:20px; font-family:sans-serif;'>No student selected. <a href='teacher_inbox.php'>Go Back</a></div>");
}

$student_id = mysqli_real_escape_string($conn, $_GET['student_id']);

/* UPDATED: Fetch Student Name AND Photo for Header */
$student_name = "Student";
$student_photo = "assets/default-avatar.png"; 
$s_query = mysqli_query($conn, "SELECT name, profile_photo FROM students WHERE id = '$student_id'");
if($s_row = mysqli_fetch_assoc($s_query)) {
    $student_name = $s_row['name'];
    if(!empty($s_row['profile_photo'])){
        $student_photo = "uploads/profile_pics/" . $s_row['profile_photo'];
    }
}

/* Get parent user id */
$parent_query = mysqli_query($conn,"
    SELECT id FROM users 
    WHERE role='parent' AND student_id='$student_id'
");
$parent = mysqli_fetch_assoc($parent_query);
$parent_id = $parent['id'] ?? null;

/* Send Message */
if(isset($_POST['send']) && $parent_id && !empty(trim($_POST['message']))){
    $message = mysqli_real_escape_string($conn,$_POST['message']);
    mysqli_query($conn,"
        INSERT INTO messages(student_id,sender_role,sender_id,receiver_role,receiver_id,message)
        VALUES('$student_id','teacher','$teacher_id','parent','$parent_id','$message')
    ");
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

/* Fetch conversation */
$chat = mysqli_query($conn,"
    SELECT * FROM messages 
    WHERE student_id='$student_id' 
    AND (
        (sender_id='$teacher_id' AND receiver_id='$parent_id' AND sender_role='teacher') OR 
        (sender_id='$parent_id' AND receiver_id='$teacher_id' AND sender_role='parent')
    )
    ORDER BY created_at ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat regarding <?php echo htmlspecialchars($student_name); ?></title>
    <style>
        :root {
            --primary-navy: #1a1a2e;
            --sent-bubble: #dcf8c6;
            --received-bubble: #ffffff;
            --bg-chat: #e5ddd5;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .chat-container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            background: var(--bg-chat);
            display: flex;
            flex-direction: column;
            height: 100vh;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chat-header {
            background: var(--primary-navy);
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-grow: 1;
        }

        .header-photo {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .chat-header h3 { margin: 0; font-size: 16px; }
        .chat-header span { font-size: 11px; opacity: 0.8; display: block; }
        
        .btn-back-style {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
        }

        .chat-window {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .msg-row { display: flex; width: 100%; }
        .sent-row { justify-content: flex-end; }
        .received-row { justify-content: flex-start; }

        .message {
            max-width: 75%;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.4;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            position: relative;
        }

        .sent {
            background-color: var(--sent-bubble);
            border-top-right-radius: 2px;
        }

        .received {
            background-color: var(--received-bubble);
            border-top-left-radius: 2px;
        }

        .msg-info {
            display: block;
            font-size: 10px;
            color: #7f8c8d;
            margin-top: 4px;
            text-align: right;
        }

        .chat-input-area {
            background: #f0f0f0;
            padding: 10px 20px;
            border-top: 1px solid #ddd;
        }

        .input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        textarea {
            flex: 1;
            border: 1px solid #ccc;
            border-radius: 20px;
            padding: 10px 15px;
            resize: none;
            font-family: inherit;
            height: 22px;
            outline: none;
        }

        button[name="send"] {
            background: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-window::-webkit-scrollbar { width: 6px; }
        .chat-window::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 10px; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="teacher_inbox.php" class="btn-back-style">Back</a>
        <div class="header-info">
            <img src="<?php echo $student_photo; ?>" class="header-photo" alt="Student">
            <div>
                <h3><?php echo htmlspecialchars($student_name); ?></h3>
                <span>Parent Conversation</span>
            </div>
        </div>
    </div>

    <div class="chat-window" id="chatWindow">
        <?php
        if(mysqli_num_rows($chat) > 0){
            while($row = mysqli_fetch_assoc($chat)){
                $is_teacher = ($row['sender_role'] == 'teacher');
                ?>
                <div class="msg-row <?php echo $is_teacher ? 'sent-row' : 'received-row'; ?>">
                    <div class="message <?php echo $is_teacher ? 'sent' : 'received'; ?>">
                        <?php echo htmlspecialchars($row['message']); ?>
                        <span class="msg-info"><?php echo date('H:i', strtotime($row['created_at'])); ?></span>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p style='text-align:center; color:#7f8c8d; margin-top:50px;'>No messages yet. Send a message to the parent below.</p>";
        }
        ?>
    </div>

    <div class="chat-input-area">
        <?php if($parent_id): ?>
        <form method="POST">
            <div class="input-wrapper">
                <textarea name="message" placeholder="Type a message..." required></textarea>
                <button type="submit" name="send">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
                </button>
            </div>
        </form>
        <?php else: ?>
            <p style="text-align:center; color:#e74c3c; font-size:13px; font-weight:600;">Parent has not registered for this student yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    var chatWindow = document.getElementById("chatWindow");
    chatWindow.scrollTop = chatWindow.scrollHeight;
</script>

</body>
</html>