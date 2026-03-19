<?php
session_start();
include('config/db.php');

if(isset($_POST['post_fees'])){
    $class_id = $_POST['class_id'];
    $cat_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $term_id = $_POST['term_id'];

    // 1. Store in structure for reference
    mysqli_query($conn, "INSERT INTO fee_structure (class_id, term_id, fee_category_id, amount) VALUES ('$class_id', '$term_id', '$cat_id', '$amount')");

    // 2. The Automation: Apply this charge to every student in that class
    $students = mysqli_query($conn, "SELECT id FROM students WHERE class_id = '$class_id'");
    
    while($s = mysqli_fetch_assoc($students)){
        $sid = $s['id'];
        $ref = "INV-T".$term_id."-C".$class_id; // Unique Invoice Reference
        
        mysqli_query($conn, "INSERT INTO fee_payments (student_id, category_id, amount_paid, transaction_type, reference_no, date_paid) 
                             VALUES ('$sid', '$cat_id', '$amount', 'charge', '$ref', CURDATE())");
    }
    echo "<script>alert('Fee applied successfully to all students in the class!');</script>";
}
?>

<div class="admin-container">
    <h2>Post New Fee Structure</h2>
    <form method="POST">
        <label>Class:</label>
        <select name="class_id" required>
            <?php 
            $classes = mysqli_query($conn, "SELECT * FROM classes");
            while($c = mysqli_fetch_assoc($classes)) echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
            ?>
        </select>

        <label>Fee Type:</label>
        <select name="category_id" required>
            <?php 
            $cats = mysqli_query($conn, "SELECT * FROM fee_categories");
            while($ct = mysqli_fetch_assoc($cats)) echo "<option value='{$ct['id']}'>{$ct['category_name']}</option>";
            ?>
        </select>

        <input type="number" name="amount" placeholder="Amount (e.g., 20000)" required>
        <input type="hidden" name="term_id" value="1"> <button type="submit" name="post_fees" class="btn">Post Fees to All Parents</button>
    </form>
</div>
<hr>
<a href="admin_dashboard.php"><button>Back</button></a>