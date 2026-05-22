<?php
session_start();
include('config/db.php');

// 1. SECURITY: Parent Access Only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'parent' || !isset($_SESSION['admission_no'])){
    header("Location: parent_login.php");
    exit();
}

$admission = $_SESSION['admission_no'];
$selected_term = isset($_GET['term_id']) ? mysqli_real_escape_string($conn, $_GET['term_id']) : '';

// 2. FETCH STUDENT DATA
$student_query = mysqli_query($conn, "
    SELECT students.*, classes.class_name 
    FROM students 
    JOIN classes ON students.class_id = classes.id 
    WHERE admission_no = '$admission'
");
$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    echo "Student record not found.";
    exit();
}

$sid = $student['id'];

// 3. AI LOGIC HELPERS
function getAIFeedback($avg, $low_subject) {
    if($avg >= 75) return "Excellent mastery. Recommendation: Peer mentorship or advanced project work.";
    if($avg >= 50) return "Steady performance. Focus on $low_subject to boost overall mean score.";
    return "⚠️ Critical Alert: Requires immediate parent-teacher conference and remedial schedule.";
}

function getStudyPlan($low_subject) {
    return "Focus on $low_subject revision modules, past papers from 2023-2025, and daily 1-hour focus sessions.";
}

// FETCH STATS FOR AI
$stats_q = mysqli_query($conn, "SELECT AVG(marks) as avg_m FROM grades WHERE student_id='$sid'");
$stats = mysqli_fetch_assoc($stats_q);
$avg = round($stats['avg_m'] ?? 0, 1);

$low_q = mysqli_query($conn, "SELECT s.subject_name FROM grades g JOIN subjects s ON g.subject_id=s.id WHERE g.student_id='$sid' ORDER BY g.marks ASC LIMIT 1");
$low_sub = mysqli_fetch_assoc($low_q)['subject_name'] ?? 'N/A';

// 4. NEW: AI RISK DETECTION LOGIC
$fail_q = mysqli_query($conn, "SELECT COUNT(*) as fail_count FROM grades WHERE student_id='$sid' AND marks < 40");
$fail_count = mysqli_fetch_assoc($fail_q)['fail_count'] ?? 0;

$risk_level = "Low";
$risk_color = "success";
$risk_message = "Student is performing well and shows no signs of academic risk. Consistent tracking is recommended.";

if ($avg < 40 || $fail_count >= 3) {
    $risk_level = "High";
    $risk_color = "danger";
    $risk_message = "Critical: High probability of academic failure detected. Immediate intervention and remedial sessions are required.";
} elseif ($avg < 60 || $fail_count >= 1) {
    $risk_level = "Moderate";
    $risk_color = "warning";
    $risk_message = "Warning: Some areas require attention to prevent further decline. Focus on subjects scoring below average.";
}

// 5. NEW: ATTENDANCE PATTERN LOGIC
// Assuming an 'attendance' table exists with columns: student_id, status ('Present', 'Absent')
$att_q = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days
    FROM attendance WHERE student_id='$sid'
");
$att_data = mysqli_fetch_assoc($att_q);
$present = $att_data['present_days'] ?? 0;
$absent = $att_data['absent_days'] ?? 0;
$total_days = $present + $absent;
$attendance_rate = $total_days > 0 ? round(($present / $total_days) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Intelligence | Parent Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f0f4f8; font-family: 'Inter', sans-serif; padding: 20px; }
        .card-custom { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 25px; border: none; }
        .feature-btn { margin-bottom: 12px; width: 100%; text-align: left; padding: 14px; font-weight: 600; transition: 0.3s; border-radius: 10px; border: 1px solid #ddd; background: #fff; }
        .feature-btn:hover, .feature-btn.active { transform: translateX(5px); background: #f8f9fa; border-color: #0d6efd; color: #0d6efd; }
        .data-view { display: none; animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .chart-container { position: relative; height: 400px; width: 100%; }
        .profile-header { background: #f8f9fa; padding: 15px; border-radius: 12px; border-left: 5px solid #0d6efd; margin-bottom: 20px; }
        .attendance-chart-wrapper { position: relative; width: 200px; height: 200px; margin: auto; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cpu-fill text-primary"></i> Student Performance Analytics</h2>
        <a href="parent_dashboard.php" class="btn btn-dark rounded-pill">Back to Dashboard</a>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card-custom">
                <div class="profile-header">
                    <h6 class="mb-1 text-primary"><i class="bi bi-person-badge-fill"></i> Student Details</h6>
                    <div class="row small g-1">
                        <div class="col-12"><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></div>
                        <div class="col-12"><strong>Adm No:</strong> <?php echo htmlspecialchars($student['admission_no']); ?></div>
                        <div class="col-12"><strong>Class:</strong> <?php echo htmlspecialchars($student['class_name']); ?></div>
                    </div>
                </div>
                
                <button class="feature-btn" onclick="showView('trend_view', this)"><i class="bi bi-graph-up"></i> Performance Trend</button>
                <button class="feature-btn" onclick="showView('predict_view', this)"><i class="bi bi-magic"></i> AI Grade & Feedback</button>
                <button class="feature-btn" onclick="showView('study_view', this)"><i class="bi bi-book"></i> Study Recommendations</button>
                
                <button class="feature-btn" onclick="showView('risk_view', this)"><i class="bi bi-shield-exclamation"></i> AI Risk Detection</button>
                <button class="feature-btn" onclick="showView('attendance_view', this)"><i class="bi bi-calendar-check"></i> Attendance Pattern</button>
            </div>
        </div>

        <div class="col-md-8">
            
            <div id="trend_view" class="data-view card-custom" style="display: block;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Mastery Trend</h4>
                    <form method="GET" class="d-flex gap-2">
                        <select name="term_id" class="form-select form-select-sm rounded-pill" style="width: 150px;" onchange="this.form.submit()">
                            <option value="">Full History</option>
                            <?php 
                            $tr_q = mysqli_query($conn, "SELECT * FROM terms");
                            while($t = mysqli_fetch_assoc($tr_q)) {
                                $sel = ($selected_term == $t['id']) ? 'selected' : '';
                                echo "<option value='".$t['id']."' $sel>".$t['term_name']."</option>";
                            }
                            ?>
                        </select>
                    </form>
                </div>
                <div class="chart-container"><canvas id="studentChart"></canvas></div>
            </div>

            <div id="predict_view" class="data-view card-custom">
                <h4>AI Grade Prediction</h4>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-primary text-white text-center">
                            <h6>Predicted Grade</h6>
                            <h2 class="display-4 fw-bold"><?php echo ($avg >= 80)?'A':(($avg >= 60)?'B':(($avg >= 40)?'C':'D')); ?></h2>
                            <p class="small mb-0">Based on historical data analytics</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-dark text-white h-100">
                            <h6>System Feedback</h6>
                            <p class="fst-italic mb-0 mt-3">"<?php echo getAIFeedback($avg, $low_sub); ?>"</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="study_view" class="data-view card-custom">
                <h4><i class="bi bi-lightbulb text-warning"></i> Study Recommendations</h4>
                <div class="alert alert-warning mt-3 rounded-4">
                    <strong>Critical Focus:</strong> Improvement needed in <b><?php echo htmlspecialchars($low_sub); ?></b>.
                    <hr>
                    <p class="mb-0"><?php echo getStudyPlan(htmlspecialchars($low_sub)); ?></p>
                </div>
            </div>

            <div id="risk_view" class="data-view card-custom">
                <h4><i class="bi bi-shield-exclamation text-<?php echo $risk_color; ?>"></i> AI Risk Detection</h4>
                <div class="alert alert-<?php echo $risk_color; ?> mt-3 rounded-4 shadow-sm">
                    <h5 class="alert-heading fw-bold">Risk Level: <?php echo $risk_level; ?></h5>
                    <hr>
                    <p class="mb-3"><?php echo $risk_message; ?></p>
                    <div class="bg-white rounded p-3 text-dark border">
                        <h6 class="fw-bold mb-2">Risk Factors Identified:</h6>
                        <ul class="mb-0">
                            <li><strong>Overall Mean Score:</strong> <?php echo $avg; ?>%</li>
                            <li><strong>Subjects Below 40%:</strong> <?php echo $fail_count; ?> subject(s)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div id="attendance_view" class="data-view card-custom">
                <h4><i class="bi bi-calendar3 text-info"></i> Attendance Pattern</h4>
                <?php if($total_days > 0): ?>
                    <div class="row align-items-center mt-4">
                        <div class="col-md-6 text-center">
                            <div class="attendance-chart-wrapper">
                                <canvas id="attendanceChart"></canvas>
                                <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">
                                    <h4 class="mb-0 fw-bold"><?php echo $attendance_rate; ?>%</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <h6 class="text-muted mb-2">Attendance Health</h6>
                            <div class="progress mb-4" style="height: 20px; border-radius: 10px;">
                                <div class="progress-bar <?php echo $attendance_rate >= 80 ? 'bg-success' : ($attendance_rate >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo $attendance_rate; ?>%;" 
                                     aria-valuenow="<?php echo $attendance_rate; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <ul class="list-group list-group-flush rounded border">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total Days Present 
                                    <span class="badge bg-success rounded-pill px-3 py-2"><?php echo $present; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total Days Absent 
                                    <span class="badge bg-danger rounded-pill px-3 py-2"><?php echo $absent; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-light fw-bold">
                                    Total Logged Days
                                    <span><?php echo $total_days; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mt-3 rounded-4 text-center">
                        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
                        No attendance records available for this student yet.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
function showView(id, btnElement) {
    // Hide all views
    document.querySelectorAll('.data-view').forEach(v => v.style.display = 'none');
    // Show requested view
    document.getElementById(id).style.display = 'block';
    
    // Manage active button state (optional visual upgrade)
    document.querySelectorAll('.feature-btn').forEach(b => b.classList.remove('active', 'border-primary'));
    if(btnElement) {
        btnElement.classList.add('active', 'border-primary');
    }
}

// Student Mastery Graph Data
<?php 
    $term_clause = $selected_term ? "AND g.term_id='$selected_term'" : "";
    $sq = "SELECT s.subject_name, 
           MAX(CASE WHEN e.exam_name LIKE '%Opener%' THEN g.marks END) as op,
           MAX(CASE WHEN e.exam_name LIKE '%Mid%' THEN g.marks END) as mid,
           MAX(CASE WHEN e.exam_name LIKE '%End%' THEN g.marks END) as en
           FROM grades g JOIN subjects s ON g.subject_id=s.id JOIN exams e ON g.exam_id=e.id
           WHERE g.student_id='$sid' $term_clause GROUP BY s.id";
    $res = mysqli_query($conn, $sq);
    $lbls = []; $d1 = []; $d2 = []; $d3 = [];
    while($r = mysqli_fetch_assoc($res)){
        $lbls[] = $r['subject_name']; 
        $d1[] = $r['op'] ?? 0; 
        $d2[] = $r['mid'] ?? 0; 
        $d3[] = $r['en'] ?? 0;
    }
?>
new Chart(document.getElementById('studentChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($lbls); ?>,
        datasets: [
            { label: 'Opener Exam', data: <?php echo json_encode($d1); ?>, borderColor: '#ef4444', tension: 0.3 },
            { label: 'Mid-Term', data: <?php echo json_encode($d2); ?>, borderColor: '#f59e0b', tension: 0.3 },
            { label: 'End-Term', data: <?php echo json_encode($d3); ?>, borderColor: '#10b981', tension: 0.3 }
        ]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Attendance Pattern Doughnut Chart
<?php if($total_days > 0): ?>
const attCtx = document.getElementById('attendanceChart');
if (attCtx) {
    new Chart(attCtx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{
                data: [<?php echo $present; ?>, <?php echo $absent; ?>],
                backgroundColor: ['#10b981', '#ef4444'], // Green for present, Red for absent
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%', // Creates the thin ring look
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.raw + ' days';
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>
</script>
</body>
</html>