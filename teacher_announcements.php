<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

$announcements = mysqli_query($conn,"
    SELECT * FROM announcements
    WHERE audience='teachers' OR audience='both'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Announcements | SIS</title>
    <style>
        :root {
            --teacher-primary: #0f3460;
            --accent-glow: #e94560;
            --white: #ffffff;
            --text-muted: #666;
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
            box-sizing: border-box;
            display: flex;
            justify-content: center;
        }

        .container {
            background: var(--white);
            width: 100%;
            max-width: 850px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        h2 { 
            color: var(--teacher-primary); 
            text-transform: uppercase;
            border-bottom: 3px solid var(--teacher-primary);
            padding-bottom: 10px;
            margin-top: 0;
            letter-spacing: 1px;
        }

        /* Announcement Card Styling */
        .announcement-card {
            border: 1px solid #eee;
            border-left: 5px solid var(--accent-glow);
            padding: 25px;
            margin-bottom: 25px;
            background: #fdfdfd;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .announcement-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .announcement-card h3 {
            margin: 0 0 10px 0;
            color: var(--teacher-primary);
            font-size: 22px;
        }

        .post-date {
            font-size: 13px;
            color: var(--text-muted);
            display: block;
            margin-bottom: 15px;
            font-style: italic;
        }

        .message-content {
            line-height: 1.7;
            color: #333;
            font-size: 16px;
        }

        /* Event Highlight */
        .event-box {
            display: inline-flex;
            align-items: center;
            margin-top: 15px;
            padding: 8px 15px;
            background: #fff5f6;
            color: var(--accent-glow);
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #ffe0e3;
        }

        .btn-back {
            background: #555;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-back:hover { background: #333; }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #999;
            font-size: 18px;
        }

        hr { border: 0; border-top: 1px solid #eee; margin: 30px 0; }
    </style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <h2>Faculty Notice Board</h2>
        
        <div class="notice-board">
            <?php
            if(mysqli_num_rows($announcements) > 0){
                while($a = mysqli_fetch_assoc($announcements)){
                    echo "<div class='announcement-card'>";
                    echo "<h3>".$a['title']."</h3>";
                    
                    // Formatted Date for better readability
                    $posted_on = date('F j, Y | g:i A', strtotime($a['created_at']));
                    echo "<span class='post-date'>Posted on: $posted_on</span>";
                    
                    echo "<div class='message-content'>".nl2br(htmlspecialchars($a['message']))."</div>";

                    if(!empty($a['event_date'])){
                        $event_f = date('D, d M Y', strtotime($a['event_date']));
                        echo "<div class='event-box'>📅 Scheduled Event: $event_f</div>";
                    }

                    echo "</div>";
                }
            } else {
                echo "<div class='empty-state'>No faculty announcements at this time.</div>";
            }
            ?>
        </div>

        <hr>
        <a href="teacher_dashboard.php" class="btn-back">Dashboard</a>
    </div>
</div>

</body>
</html>