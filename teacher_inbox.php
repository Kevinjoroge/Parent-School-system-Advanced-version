<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id']; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Inbox | School System</title>
    <style>
        :root {
            --primary-navy: #1a1a2e;
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
            background: var(--primary-navy);
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

        .student-photo-thumb {
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

        .student-name {
            font-weight: 700;
            color: var(--primary-navy);
            font-size: 16px;
        }

        .adm-tag {
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
            background-color: var(--primary-navy);
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
        <h2>Teacher Message Center</h2>
        <a href="teacher_dashboard.php" class="btn-back">Dashboard</a>
    </div>

    <?php
    /**
     * UPDATED LOGIC:
     * We find the MAX(id) for each student_id where the teacher is involved 
     * (either as sender or receiver). This prevents the thread from 
     * disappearing after the teacher replies.
     */
    $query = mysqli_query($conn,"
        SELECT 
            m1.*, 
            students.name AS student_real_name, 
            students.admission_no,
            students.profile_photo
        FROM messages m1
        JOIN students ON m1.student_id = students.id
        WHERE m1.id IN (
            SELECT MAX(id) 
            FROM messages 
            WHERE (receiver_id = '$teacher_id' AND receiver_role = 'teacher')
               OR (sender_id = '$teacher_id' AND sender_role = 'teacher')
            GROUP BY student_id
        )
        ORDER BY m1.created_at DESC
    ");

    if(mysqli_num_rows($query) > 0){
        while($row = mysqli_fetch_assoc($query)){
            $s_photo = !empty($row['profile_photo']) ? "uploads/profile_pics/" . $row['profile_photo'] : "assets/default-avatar.png";
            ?>
            <div class="message-thread">
                <img src="<?php echo $s_photo; ?>" alt="Student" class="student-photo-thumb">

                <div class="thread-content">
                    <div class="thread-meta">
                        <span class="student-name"><?php echo htmlspecialchars($row['student_real_name']); ?></span>
                        <span class="adm-tag">Adm: <?php echo htmlspecialchars($row['admission_no']); ?></span>
                    </div>
                    
                    <div class="message-preview">
                        <?php 
                            // Add a "You:" prefix if the teacher sent the last message
                            if($row['sender_id'] == $teacher_id && $row['sender_role'] == 'teacher') {
                                echo "<strong>You: </strong>";
                            }
                            echo htmlspecialchars($row['message']); 
                        ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                        <span class="date-stamp"><?php echo date('M d, Y | h:i A', strtotime($row['created_at'])); ?></span>
                        <a href="teacher_conversation.php?student_id=<?php echo $row['student_id']; ?>">
                            <button class="view-btn">View Chat</button>
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<div class='empty-state'>
                <p>No messages from parents yet.</p>
              </div>";
    }
    ?>
</div>

</body>
</html>