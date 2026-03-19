<?php
// CRITICAL: No spaces or empty lines above this <?php tag.
ob_start(); 
session_start();
include('config/db.php');

// Using your requested path
require_once 'dompdf/autoload.inc.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    die("Unauthorized Access");
}

// 2. Get variables from the URL
$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$term_id  = isset($_GET['term_id']) ? $_GET['term_id'] : '';
$exam_id  = isset($_GET['exam_id']) ? $_GET['exam_id'] : '';

if(empty($class_id) || empty($term_id) || empty($exam_id)){
    die("Error: Missing report parameters.");
}

// 3. Fetch Meta Data for the Header
$class_q = mysqli_query($conn, "SELECT class_name FROM classes WHERE id='$class_id'");
$class_data = mysqli_fetch_assoc($class_q);

$term_q = mysqli_query($conn, "SELECT term_name FROM terms WHERE id='$term_id'");
$term_data = mysqli_fetch_assoc($term_q);

$exam_q = mysqli_query($conn, "SELECT exam_name FROM exams WHERE id='$exam_id'");
$exam_data = mysqli_fetch_assoc($exam_q);

// 4. Fetch Students and Performance
$query = mysqli_query($conn,"
    SELECT students.id, students.name, students.admission_no, AVG(grades.points) AS mean_points
    FROM students
    JOIN grades ON students.id=grades.student_id
    WHERE students.class_id='$class_id' 
    AND grades.term_id='$term_id' 
    AND grades.exam_id='$exam_id'
    GROUP BY students.id
    ORDER BY mean_points DESC
");

if (!function_exists('gradeFromPoints')) {
    function gradeFromPoints($p){
        if($p >= 11.5) return "A"; elseif($p >= 10.5) return "A-";
        elseif($p >= 9.5) return "B+"; elseif($p >= 8.5) return "B";
        elseif($p >= 7.5) return "B-"; elseif($p >= 6.5) return "C+";
        elseif($p >= 5.5) return "C"; elseif($p >= 4.5) return "C-";
        elseif($p >= 3.5) return "D+"; elseif($p >= 2.5) return "D";
        elseif($p >= 1.5) return "D-"; else return "E";
    }
}

// 5. Build HTML Content
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #444; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin-bottom:5px;">OFFICIAL CLASS REPORT</h1>
        <h2 style="margin-top:0;"><?php echo strtoupper($class_data['class_name'] ?? 'Class Report'); ?></h2>
        <p><?php echo $term_data['term_name'] ?? ''; ?> | <?php echo $exam_data['exam_name'] ?? ''; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Student Name</th>
                <th>Adm No</th>
                <th>Mean Points</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $pos = 1; $rank = 1; $prev_points = null;
            while($row = mysqli_fetch_assoc($query)){
                $m_points = round($row['mean_points'], 2);
                if($prev_points !== null && $m_points < $prev_points){
                    $rank = $pos;
                }
                echo "<tr>
                        <td>$rank</td>
                        <td>".htmlspecialchars($row['name'])."</td>
                        <td>".htmlspecialchars($row['admission_no'])."</td>
                        <td>".$m_points."</td>
                        <td>".gradeFromPoints($m_points)."</td>
                      </tr>";
                $prev_points = $m_points;
                $pos++;
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Report Generated on <?php echo date('d-m-Y H:i'); ?></p>
    </div>
</body>
</html>
<?php
// 6. Final PDF Processing
$html = ob_get_clean();

// Clear any accidental output before PDF start
if (ob_get_length()) ob_end_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Attachment => 0 opens it in the browser tab, avoiding IDM/Expired trial issues
$dompdf->stream("Class_Report.pdf", ["Attachment" => 0]);
exit();
?>