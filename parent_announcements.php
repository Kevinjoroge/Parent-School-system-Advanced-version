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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcements | SIS</title>
    <style>
        :root {
            --primary-blue: #0a2a66;
            --accent-gold: #f39c12;
            --white: #ffffff;
            --text-muted: #666;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
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
        }

        .container {
            background: white;
            width: 100%;
            max-width: 800px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        h2 { 
            color: var(--primary-blue); 
            text-transform: uppercase;
            border-bottom: 3px solid var(--primary-blue);
            padding-bottom: 10px;
            margin-top: 0;
        }

        /* Announcement Card Styling */
        .announcement-card {
            border: 1px solid #eee;
            border-left: 5px solid var(--accent-gold);
            padding: 20px;
            margin-bottom: 20px;
            background: #fdfdfd;
            border-radius: 4px;
            transition: transform 0.2s;
        }

        .announcement-card:hover {
            transform: scale(1.01);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .announcement-card h3 {
            margin: 0 0 10px 0;
            color: var(--primary-blue);
            font-size: 20px;
        }

        .post-date {
            font-size: 12px;
            color: var(--text-muted);
            display: block;
            margin-bottom: 15px;
        }

        .message-content {
            line-height: 1.6;
            color: #444;
            font-size: 15px;
        }

        .event-tag {
            display: inline-block;
            margin-top: 15px;
            padding: 5px 12px;
            background: #fff3cd;
            color: #856404;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-back {
            background: #666;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-back:hover { background: #444; }

        .empty-msg {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        hr { border: 0; border-top: 1px solid #eee; margin: 25px 0; }
    </style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <h2>Announcements & Events</h2>
        
        <div class="notice-board">
            <?php
            if(mysqli_num_rows($announcements) > 0){
                while($a = mysqli_fetch_assoc($announcements)){
                    echo "<div class='announcement-card'>";
                    echo "<h3>".$a['title']."</h3>";
                    echo "<span class='post-date'>Posted on: " . date('F j, Y, g:i a', strtotime($a['created_at'])) . "</span>";
                    echo "<div class='message-content'>".$a['message']."</div>";

                    if(!empty($a['event_date'])){
                        echo "<div class='event-tag'>🗓 Event Date: " . date('d M Y', strtotime($a['event_date'])) . "</div>";
                    }

                    echo "</div>";
                }
            } else {
                echo "<p class='empty-msg'>No announcements available at this time.</p>";
            }
            ?>
        </div>

        <hr>
        <a href="parent_dashboard.php" class="btn-back">Dashboard</a>
    </div>
</div>

</body>
</html>