<?php
// 1. MUST BE FIRST: Start the session and connect to DB
session_start();
include('config/db.php');

// 2. SECURITY: Check if parent is actually logged in
if (!isset($_SESSION['parent_id']) && !isset($_SESSION['student_id'])) {
    die("Error: Please log in to view the fee statement.");
}

// 3. GET ID: Use the student_id from the session
$student_id = $_SESSION['student_id']; 

// 4. THE QUERY
$query = "SELECT fp.*, fc.category_name 
          FROM fee_payments fp 
          JOIN fee_categories fc ON fp.category_id = fc.id 
          WHERE fp.student_id = '$student_id' 
          ORDER BY fp.date_paid ASC";

$res = mysqli_query($conn, $query);

if (!$res) {
    die("Database Query Failed: " . mysqli_error($conn));
}

$total_billed = 0; 
$total_paid = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        .statement-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 40px;
            background: #fff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        /* Navigation Styles */
        .nav-bar {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }
        .btn-dashboard {
            text-decoration: none;
            background-color: #2c3e50;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-dashboard:hover {
            background-color: #34495e;
        }
        
        .school-header {
            text-align: center;
            border-bottom: 3px double #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }
        .school-header h1 { margin: 0; color: #2c3e50; text-transform: uppercase; font-size: 24px; }

        .doc-title {
            text-align: center;
            text-decoration: underline;
            margin-bottom: 25px;
            font-size: 18px;
            font-weight: bold;
        }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .statement-table th {
            background-color: #2c3e50;
            color: white;
            text-align: left;
            padding: 12px;
            font-size: 14px;
        }
        .statement-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 14px;
        }
        .statement-table tr:nth-child(even) { background-color: #f9f9f9; }

        .summary-wrapper {
            float: right;
            width: 320px;
        }
        .summary-card {
            border: 1px solid #2c3e50;
            border-radius: 4px;
            overflow: hidden;
        }
        .summary-line {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .summary-line.total {
            background: #2c3e50;
            color: white;
            font-size: 18px;
            border-bottom: none;
        }
        .footer-note {
            margin-top: 50px;
            font-size: 12px;
            color: #95a5a6;
            clear: both;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .text-red { color: #e74c3c; font-weight: bold; }
        .text-green { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>

<div class="statement-container">
    <div class="nav-bar">
        <a href="parent_dashboard.php" class="btn-dashboard">Dashboard</a>
    </div>

    <div class="school-header">
        <h1>Official School Management System</h1>
    </div>

    <div class="doc-title">STUDENT FINANCIAL STATEMENT</div>

    <table class="statement-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th style="text-align: right;">Debit (Owed)</th>
                <th style="text-align: right;">Credit (Paid)</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($res)): 
                $is_charge = ($row['transaction_type'] == 'charge');
                if($is_charge) $total_billed += $row['amount_paid']; else $total_paid += $row['amount_paid'];
            ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($row['date_paid'])); ?></td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td style="text-align: right;" class="<?php echo $is_charge ? 'text-red' : ''; ?>">
                    <?php echo $is_charge ? number_format($row['amount_paid'], 2) : '-'; ?>
                </td>
                <td style="text-align: right;" class="<?php echo !$is_charge ? 'text-green' : ''; ?>">
                    <?php echo !$is_charge ? number_format($row['amount_paid'], 2) : '-'; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="summary-wrapper">
        <div class="summary-card">
            <div class="summary-line">
                <span>Total Invoiced:</span>
                <span><?php echo number_format($total_billed, 2); ?></span>
            </div>
            <div class="summary-line">
                <span>Total Received:</span>
                <span><?php echo number_format($total_paid, 2); ?></span>
            </div>
            <div class="summary-line total">
                <span>Balance Due:</span>
                <span><?php echo number_format($total_billed - $total_paid, 2); ?></span>
            </div>
        </div>
    </div>

    <div class="footer-note">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>&copy; <?php echo date('Y'); ?> School Management System. All Rights Reserved.</p>
    </div>
</div>

</body>
</html>