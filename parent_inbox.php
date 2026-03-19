<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent'){
    header("Location: parent_login.php");
    exit();
}

$parent_id = $_SESSION['user_id']; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Inbox | School System</title>
    <style>
        :root {
            --primary-blue: #2c3e50;
            --bg-gray: #f4f7f6;
            --border-color: #e0e0e0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-gray);
            margin: 0;
            padding: 20px;
        }

        .inbox-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .inbox-header {
            background: var(--primary-blue);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inbox-header h2 { margin: 0; font-size: 20px; letter-spacing: 0.5px; }

        .btn-back {
            text-decoration: none;
            color: white;
            font-size: 13px;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 6px 12px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .btn-back:hover { background: rgba(255,255,255,0.1); }

        .message-thread {
            display: flex;
            gap: 15px;
            text-decoration: none;
            color: inherit;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
            align-items: flex-start;
        }

        .message-thread:hover {
            background-color: #fcfcfc;
        }

        .teacher-photo-thumb {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #eee;
            flex-shrink: 0;
        }

        .thread-content {
            flex-grow: 1;
            min-width: 0;
        }

        .thread-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .teacher-name {
            font-weight: 700;
            color: var(--primary-blue);
            font-size: 16px;
        }

        .student-tag {
            font-size: 11px;
            background: #ecf0f1;
            padding: 2px 8px;
            border-radius: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .message-preview {
            font-size: 14px;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 10px;
        }

        .date-stamp {
            font-size: 12px;
            color: #95a5a6;
            display: block;
        }

        .view-btn {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .empty-state {
            padding: 50px;
            text-align: center;
            color: #7f8c8d;
        }
    </style>
</head>
<body>

<div class="inbox-container">
    <div class="inbox-header">
        <h2>Messages</h2>
        <a href="parent_dashboard.php" class="btn-back">Dashboard</a>
    </div>

    <?php
    /**
     * UPDATED LOGIC:
     * We group by teacher_id/sender_id but look for the MAX(id) where the parent is involved.
     * This keeps the thread in the inbox even if the parent was the last one to reply.
     */
    $query = mysqli_query($conn,"
        SELECT 
            m1.*, 
            teachers.name AS teacher_real_name, 
            teachers.profile_photo, 
            students.name AS student_name
        FROM messages m1
        JOIN teachers ON (m1.sender_id = teachers.id OR m1.receiver_id = teachers.id)
        JOIN students ON m1.student_id = students.id
        WHERE m1.id IN (
            SELECT MAX(id) 
            FROM messages 
            WHERE (receiver_id = '$parent_id' AND receiver_role = 'parent')
               OR (sender_id = '$parent_id' AND sender_role = 'parent')
            GROUP BY CASE 
                WHEN sender_role = 'parent' THEN receiver_id 
                ELSE sender_id 
            END
        )
        ORDER BY m1.created_at DESC
    ");

    if(mysqli_num_rows($query) > 0){
        while($row = mysqli_fetch_assoc($query)){
            $t_photo = !empty($row['profile_photo']) ? "uploads/profile_pics/" . $row['profile_photo'] : "assets/default-avatar.png";
            
            // Determine the teacher ID for the link (if parent sent last, it's the receiver_id)
            $chat_teacher_id = ($row['sender_role'] == 'parent') ? $row['receiver_id'] : $row['sender_id'];
            ?>
            <div class="message-thread">
                <img src="<?php echo $t_photo; ?>" alt="Teacher" class="teacher-photo-thumb">

                <div class="thread-content">
                    <div class="thread-meta">
                        <span class="teacher-name">Tr. <?php echo htmlspecialchars($row['teacher_real_name']); ?></span>
                        <span class="student-tag">Ref: <?php echo htmlspecialchars($row['student_name']); ?></span>
                    </div>
                    
                    <div class="message-preview">
                        <?php 
                            if($row['sender_id'] == $parent_id && $row['sender_role'] == 'parent') {
                                echo "<strong>You: </strong>";
                            }
                            echo htmlspecialchars($row['message']); 
                        ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                        <span class="date-stamp"><?php echo date('M d, Y | h:i A', strtotime($row['created_at'])); ?></span>
                        <a href="parent_conversation.php?teacher_id=<?php echo $chat_teacher_id; ?>&student_id=<?php echo $row['student_id']; ?>">
                            <button class="view-btn">View Chat</button>
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<div class='empty-state'>
                <p>No messages found in your inbox.</p>
              </div>";
    }
    ?>
</div>

</body>
</html>