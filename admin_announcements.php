<?php
session_start();
include('config/db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: admin_login.php");
    exit();
}

$status_msg = "";

if(isset($_POST['post'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $audience = $_POST['audience'];
    $event_date = $_POST['event_date'] ?: NULL;

    $query = "INSERT INTO announcements(title,message,audience,event_date) 
              VALUES('$title','$message','$audience', " . ($event_date ? "'$event_date'" : "NULL") . ")";
    
    if(mysqli_query($conn, $query)){
        $status_msg = "Announcement dispatched successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Communication Hub | Admin</title>
    <style>
        :root {
            --admin-dark: #1a1c23;
            --accent-blue: #3498db;
            --white: #ffffff;
            --bg-body: #f3f4f6;
            --success: #27ae60;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-body);
            color: #2d3748;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .hub-container {
            width: 100%;
            max-width: 600px;
            background: var(--white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-top: 5px solid var(--accent-blue);
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        header h2 {
            margin: 0;
            color: var(--admin-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 22px;
        }

        header p {
            color: #718096;
            font-size: 14px;
            margin-top: 5px;
        }

        .status-banner {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid var(--success);
        }

        /* Form Styling */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #4a5568;
            text-transform: uppercase;
        }

        input[type="text"], 
        input[type="date"], 
        select, 
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 15px;
            margin-bottom: 20px;
            transition: border-color 0.2s;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent-blue);
            background: #fafafa;
        }

        textarea { resize: vertical; min-height: 120px; }

        .btn-post {
            width: 100%;
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .btn-post:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-post:active { transform: translateY(0); }

        .footer-links {
            margin-top: 25px;
            text-align: center;
            border-top: 1px solid #edf2f7;
            padding-top: 20px;
        }

        .back-link {
            color: #718096;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .back-link:hover { color: var(--admin-dark); }
    </style>
</head>
<body>

<div class="hub-container">
    <header>
        <h2>Broadcast Center</h2>
        <p>Send notices to Teachers, Parents, or both.</p>
    </header>

    <?php if($status_msg): ?>
        <div class="status-banner">
            <?php echo $status_msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Announcement Title</label>
        <input type="text" name="title" placeholder="e.g., Annual Sports Day 2026" required>

        <label>Target Audience</label>
        <select name="audience" required>
            <option value="teachers">Teachers Only</option>
            <option value="parents">Parents Only</option>
            <option value="both">All Stakeholders (Both)</option>
        </select>

        <label>Message Details</label>
        <textarea name="message" placeholder="Type your announcement here..." required></textarea>

        <label>Event Date (Leave blank if not an event)</label>
        <input type="date" name="event_date">

        <button type="submit" name="post" class="btn-post">Dispatch Announcement</button>
    </form>

    <div class="footer-links">
        <a href="admin_dashboard.php" class="back-link"><button>Dashboard</button></a>
    </div>
</div>

</body>
</html>