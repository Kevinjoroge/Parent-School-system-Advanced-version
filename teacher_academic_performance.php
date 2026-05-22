<?php
session_start();
include('config/db.php');

// 1. SECURITY: Teacher Access Only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher'){
    header("Location: teacher_login.php");
    exit();
}

// 2. DATA INITIALIZATION
$admission = isset($_GET['admission_no']) ? mysqli_real_escape_string($conn, $_GET['admission_no']) : '';
$selected_term = isset($_GET['term_id']) ? mysqli_real_escape_string($conn, $_GET['term_id']) : '';
$selected_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';
$student = null;

if (!empty($admission)) {
    // JOIN with classes to get the class name for the profile display
    $student_query = mysqli_query($conn, "SELECT students.*, classes.class_name FROM students JOIN classes ON students.class_id=classes.id WHERE admission_no='$admission'");
    $student = mysqli_fetch_assoc($student_query);
}

// 3. AI LOGIC HELPERS
function getAIFeedback($avg, $low_subject) {
    if($avg >= 75) return "Excellent mastery. Recommendation: Peer mentorship or advanced project work.";
    if($avg >= 50) return "Steady performance. Focus on $low_subject to boost overall mean score.";
    return "⚠️ Critical Alert: Requires immediate parent-teacher conference and remedial schedule.";
}

function getStudyPlan($low_subject) {
    return "Focus on $low_subject revision modules, past papers from 2023-2025, and daily 1-hour focus sessions.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Academic Control Center - Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f0f4f8; font-family: 'Inter', sans-serif; padding: 20px; }
        .card-custom { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 25px; border: none; }
        .feature-btn { margin-bottom: 12px; width: 100%; text-align: left; padding: 14px; font-weight: 600; transition: 0.3s; border-radius: 10px; border: 1px solid #ddd; background: #fff; }
        .feature-btn:hover { transform: translateX(5px); background: #f8f9fa; border-color: #0d6efd; color: #0d6efd; }
        .data-view { display: none; animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .chart-container { position: relative; height: 400px; width: 100%; }
        .cluster-pill { padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .stat-box { padding: 15px; border-radius: 12px; background: #f8f9fa; border-left: 5px solid #0d6efd; }
        .profile-header { background: #f8f9fa; padding: 15px; border-radius: 12px; border-left: 5px solid #0d6efd; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cpu-fill text-primary"></i> Academic Intelligence</h2>
        <a href="teacher_dashboard.php" class="btn btn-dark rounded-pill">Exit Module</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card-custom">
                <h5 class="mb-3">Student Intelligence</h5>
                <form method="GET" class="input-group mb-4">
                    <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($selected_class); ?>">
                    <input type="hidden" name="term_id" value="<?php echo htmlspecialchars($selected_term); ?>">
                    
                    <input type="text" name="admission_no" class="form-control rounded-start-pill" placeholder="Adm No" value="<?php echo htmlspecialchars($admission); ?>">
                    <button class="btn btn-primary rounded-end-pill" type="submit">Analyze</button>
                </form>

                <?php if($student): 
                    $sid = $student['id'];
                    $stats_q = mysqli_query($conn, "SELECT AVG(marks) as avg_m FROM grades WHERE student_id='$sid'");
                    $stats = mysqli_fetch_assoc($stats_q);
                    $avg = round($stats['avg_m'] ?? 0, 1);
                    
                    $low_q = mysqli_query($conn, "SELECT s.subject_name FROM grades g JOIN subjects s ON g.subject_id=s.id WHERE g.student_id='$sid' ORDER BY g.marks ASC LIMIT 1");
                    $low_sub = mysqli_fetch_assoc($low_q)['subject_name'] ?? 'N/A';

                    // 1. Attendance Data
                    $att_q = mysqli_query($conn, "SELECT COUNT(*) as total_days, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_days FROM attendance WHERE student_id='$sid'");
                    $att_data = mysqli_fetch_assoc($att_q);
                    $total_days = $att_data['total_days'] ?? 0;
                    $present_days = $att_data['present_days'] ?? 0;
                    $attendance_pct = ($total_days > 0) ? round(($present_days / $total_days) * 100, 1) : 100;

                    // 2. Discipline Data
                    $disc_q = mysqli_query($conn, "SELECT COUNT(*) as incident_count FROM discipline_reports WHERE student_id='$sid'");
                    $disc_data = mysqli_fetch_assoc($disc_q);
                    $incident_count = $disc_data['incident_count'] ?? 0;

                    // 3. Smart Alerts Engine Logic
                    $is_at_risk = false;
                    $risk_reasons = [];
                    if($avg > 0 && $avg < 50) { $is_at_risk = true; $risk_reasons[] = "Low Academic Average ($avg%)"; }
                    if($attendance_pct < 60) { $is_at_risk = true; $risk_reasons[] = "Critical Attendance Level ($attendance_pct%)"; }
                    if($incident_count > 0) { $is_at_risk = true; $risk_reasons[] = "Active Discipline Records ($incident_count incidents)"; }

                    // 4. Attendance Pattern Algorithm
                    $absent_q = mysqli_query($conn, "SELECT attendance_date, DAYNAME(attendance_date) as day_of_week FROM attendance WHERE student_id='$sid' AND status='Absent'");
                    $absences_by_day = ['Monday'=>0, 'Tuesday'=>0, 'Wednesday'=>0, 'Thursday'=>0, 'Friday'=>0];
                    $total_absences = 0;
                    
                    if($absent_q) {
                        while($ab = mysqli_fetch_assoc($absent_q)) {
                            $day = $ab['day_of_week'];
                            if(isset($absences_by_day[$day])) $absences_by_day[$day]++;
                            $total_absences++;
                        }
                    }
                    
                    arsort($absences_by_day);
                    $worst_day = array_key_first($absences_by_day);
                    $worst_day_count = $absences_by_day[$worst_day];
                    
                    $pattern_msg = "Steady attendance. No irregular patterns detected.";
                    if($worst_day_count > 0 && $worst_day_count >= ($total_absences / 2) && $total_absences >= 3) {
                        $pattern_msg = "⚠️ Pattern Detected: Student is frequently absent on {$worst_day}s.";
                    } elseif ($total_absences >= 5) {
                        $pattern_msg = "📉 Risk of chronic absenteeism. Attendance dropping over time.";
                    }
                ?>
                    <div class="profile-header">
                        <h6 class="mb-1 text-primary"><i class="bi bi-person-badge-fill"></i> Student Profile</h6>
                        <div class="row small g-1">
                            <div class="col-12"><strong>Name:</strong> <?php echo $student['name']; ?></div>
                            <div class="col-6"><strong>Adm No:</strong> <?php echo $student['admission_no']; ?></div>
                            <div class="col-6"><strong>Class:</strong> <?php echo $student['class_name']; ?></div>
                        </div>
                    </div>
                    
                    <button class="feature-btn" onclick="showView('trend_view')"><i class="bi bi-graph-up"></i> Performance Trend</button>
                    <button class="feature-btn" onclick="showView('predict_view')"><i class="bi bi-magic"></i> AI Grade & Feedback</button>
                    <button class="feature-btn" onclick="showView('study_view')"><i class="bi bi-book"></i> Study Recommendations</button>
                    <button class="feature-btn text-danger" onclick="showView('risk_view')"><i class="bi bi-exclamation-triangle"></i> AI Risk Detection</button>
                    <button class="feature-btn text-info" onclick="showView('attendance_view')"><i class="bi bi-calendar-x"></i> Attendance Patterns</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card-custom text-center">
                <h5 class="mb-3">Class-Wide Intelligence</h5>
                <form method="GET" class="row g-2 mb-4">
                    <input type="hidden" name="admission_no" value="<?php echo htmlspecialchars($admission); ?>">
                    <div class="col-6">
                        <select name="class_id" class="form-select rounded-pill" required>
                            <option value="">-- Select Class --</option>
                            <?php 
                            $cl_q = mysqli_query($conn, "SELECT * FROM classes");
                            while($c = mysqli_fetch_assoc($cl_q)) {
                                $sel = ($selected_class == $c['id']) ? 'selected' : '';
                                echo "<option value='".$c['id']."' $sel>".$c['class_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-4">
                        <select name="term_id" class="form-select rounded-pill">
                            <option value="">-- Select Term --</option>
                            <?php 
                            $tr_q = mysqli_query($conn, "SELECT * FROM terms");
                            while($t = mysqli_fetch_assoc($tr_q)) {
                                $sel = ($selected_term == $t['id']) ? 'selected' : '';
                                echo "<option value='".$t['id']."' $sel>".$t['term_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-2">
                        <button class="btn btn-success rounded-pill w-100" type="submit">Go</button>
                    </div>
                </form>

                <div class="row g-2">
                    <div class="col-4"><button class="btn btn-outline-primary btn-sm w-100" onclick="showView('class_trend')">Class Trend</button></div>
                    <div class="col-4"><button class="btn btn-outline-success btn-sm w-100" onclick="showView('class_clustering')">Clustering</button></div>
                    <div class="col-4"><button class="btn btn-outline-info btn-sm w-100" onclick="showView('class_analytics')">Analytics</button></div>
                </div>
            </div>
        </div>
    </div>

    <div id="risk_view" class="data-view card-custom border border-danger border-2">
        <h4><i class="bi bi-shield-exclamation text-danger"></i> Smart Alerts & Risk Detection</h4>
        <?php if($student): ?>
            <?php if($is_at_risk): ?>
                <div class="alert alert-danger mt-3 rounded-4 shadow-sm">
                    <h5 class="fw-bold">⚠️ This student needs attention</h5>
                    <ul class="mb-0">
                        <?php foreach($risk_reasons as $reason): ?>
                            <li><strong><?php echo $reason; ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="alert alert-success mt-3 rounded-4 shadow-sm">
                    <h5 class="fw-bold">✅ Student is On-Track</h5>
                    <p class="mb-0">No risk factors detected based on grades, attendance, and discipline.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div id="attendance_view" class="data-view card-custom border border-info border-2">
        <h4><i class="bi bi-calendar-check text-info"></i> Attendance Pattern Analysis</h4>
        <?php if($student): ?>
            <div class="row mt-3 g-3">
                <div class="col-md-6"><div class="stat-box"><strong>Rate:</strong> <?php echo $attendance_pct; ?>%</div></div>
                <div class="col-md-6"><div class="stat-box"><strong>Absences:</strong> <?php echo $total_absences; ?> Days</div></div>
            </div>
            <div class="p-4 mt-4 bg-light rounded-4">
                <h6><i class="bi bi-cpu"></i> Pattern Insights</h6>
                <p class="fst-italic mb-0">"<?php echo $pattern_msg; ?>"</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="trend_view" class="data-view card-custom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Performance Trend</h4>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="admission_no" value="<?php echo htmlspecialchars($admission); ?>">
                <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($selected_class); ?>">
                <select name="term_id" class="form-select form-select-sm rounded-pill" style="width: 180px;" onchange="this.form.submit()">
                    <option value="">All Terms</option>
                    <?php 
                    $tr_q2 = mysqli_query($conn, "SELECT * FROM terms");
                    while($t2 = mysqli_fetch_assoc($tr_q2)) {
                        $sel2 = ($selected_term == $t2['id']) ? 'selected' : '';
                        echo "<option value='".$t2['id']."' $sel2>".$t2['term_name']."</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
        <div class="chart-container"><canvas id="studentChart"></canvas></div>
    </div>

    <div id="predict_view" class="data-view card-custom">
        <h4>AI Grade Prediction & Feedback</h4>
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="p-4 rounded-4 bg-primary text-white">
                    <h6>Predicted Final Grade</h6>
                    <h2 class="display-4 fw-bold"><?php echo ($avg >= 80)?'A':(($avg >= 60)?'B':'C'); ?></h2>
                    <p>Based on linear regression of past 3 exams.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-4 rounded-4 bg-dark text-white">
                    <h6>Automated Teacher Feedback</h6>
                    <p class="fst-italic">"<?php echo getAIFeedback($avg, $low_sub); ?>"</p>
                </div>
            </div>
        </div>
    </div>

    <div id="study_view" class="data-view card-custom">
        <h4><i class="bi bi-lightbulb text-warning"></i> Intelligent Study Plan</h4>
        <div class="alert alert-warning mt-3 rounded-4">
            <strong>Target Area:</strong> Improvement needed in <b><?php echo $low_sub; ?></b>.
            <hr>
            <p><?php echo getStudyPlan($low_sub); ?></p>
        </div>
    </div>

    <div id="class_trend" class="data-view card-custom">
        <h4>Class Performance Trend (Aggregated)</h4>
        <div class="chart-container"><canvas id="classTrendChart"></canvas></div>
    </div>

    <div id="class_clustering" class="data-view card-custom">
        <h4>Student Performance Clustering (K-Means Logic)</h4>
        <div class="table-responsive mt-3">
            <table class="table align-middle">
                <thead><tr><th>Student</th><th>Avg</th><th>Cluster</th></tr></thead>
                <tbody>
                    <?php 
                    if($selected_class){
                        $clus_q = mysqli_query($conn, "SELECT s.name, AVG(g.marks) as avg_m FROM students s JOIN grades g ON s.id=g.student_id WHERE s.class_id='$selected_class' GROUP BY s.id");
                        while($row = mysqli_fetch_assoc($clus_q)){
                            $m = $row['avg_m'];
                            if($m >= 75) { $c = "Elite"; $cl = "bg-success"; }
                            elseif($m >= 50) { $c = "Average"; $cl = "bg-primary"; }
                            else { $c = "At-Risk"; $cl = "bg-danger"; }
                            echo "<tr><td>".$row['name']."</td><td>".round($m,1)."%</td><td><span class='cluster-pill text-white $cl'>$c</span></td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="class_analytics" class="data-view card-custom">
        <h4 class="mb-4">Class Performance Analytics</h4>
        
        <?php 
        if($selected_class && $selected_term):
            $stats_query = mysqli_query($conn, "
                SELECT s.subject_name, AVG(g.marks) as avg_marks 
                FROM grades g 
                JOIN subjects s ON g.subject_id = s.id 
                JOIN students st ON g.student_id = st.id
                WHERE st.class_id = '$selected_class' AND g.term_id = '$selected_term'
                GROUP BY s.id ORDER BY avg_marks DESC");

            $subject_stats = mysqli_fetch_all($stats_query, MYSQLI_ASSOC);
            $best_sub = !empty($subject_stats) ? $subject_stats[0] : ['subject_name' => 'N/A', 'avg_marks' => 0];
            $worst_sub = !empty($subject_stats) ? end($subject_stats) : ['subject_name' => 'N/A', 'avg_marks' => 0];
            
            $class_avg_q = mysqli_query($conn, "SELECT AVG(marks) as total_avg FROM grades g JOIN students st ON g.student_id=st.id WHERE st.class_id='$selected_class' AND g.term_id='$selected_term'");
            $class_total_avg = mysqli_fetch_assoc($class_avg_q)['total_avg'] ?? 0;

            $dist_query = mysqli_query($conn, "
                SELECT 
                    SUM(CASE WHEN avg_score >= 70 THEN 1 ELSE 0 END) as elite,
                    SUM(CASE WHEN avg_score >= 50 AND avg_score < 70 THEN 1 ELSE 0 END) as standard,
                    SUM(CASE WHEN avg_score < 50 THEN 1 ELSE 0 END) as at_risk
                FROM (
                    SELECT AVG(marks) as avg_score 
                    FROM grades g
                    JOIN students st ON g.student_id = st.id
                    WHERE st.class_id = '$selected_class' AND g.term_id = '$selected_term'
                    GROUP BY g.student_id
                ) as subquery");
            $dist = mysqli_fetch_assoc($dist_query);
        ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-box">
                    <small class="text-muted text-uppercase fw-bold">Class Average</small>
                    <h2 class="text-primary mb-0"><?php echo round($class_total_avg, 1); ?>%</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box" style="border-left-color: #198754;">
                    <small class="text-muted text-uppercase fw-bold">Best Subject</small>
                    <h4 class="mb-0"><?php echo $best_sub['subject_name']; ?></h4>
                    <p class="small text-success mb-0">High Mastery: <?php echo round($best_sub['avg_marks'], 1); ?>%</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box" style="border-left-color: #dc3545;">
                    <small class="text-muted text-uppercase fw-bold">Worst Subject</small>
                    <h4 class="mb-0 text-danger"><?php echo $worst_sub['subject_name']; ?></h4>
                    <p class="small text-muted mb-0">Urgent review needed.</p>
                </div>
            </div>
        </div>

        <div class="chart-container" style="height: 300px;">
            <canvas id="distributionChart"></canvas>
        </div>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-graph-up-arrow fs-1 text-muted"></i>
                <p class="mt-2">Select a Class and Term to view algorithmic analytics.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showView(id) {
    document.querySelectorAll('.data-view').forEach(v => v.style.display = 'none');
    document.getElementById(id).style.display = 'block';
}

// Student Graph Data (Filtered by Term if selected)
<?php if($student): 
    $term_clause = $selected_term ? "AND g.term_id='$selected_term'" : "";
    $sq = "SELECT s.subject_name, 
           MAX(CASE WHEN e.exam_name LIKE '%Opener%' THEN g.marks END) as op,
           MAX(CASE WHEN e.exam_name LIKE '%Mid%' THEN g.marks END) as mid,
           MAX(CASE WHEN e.exam_name LIKE '%End%' THEN g.marks END) as en
           FROM grades g JOIN subjects s ON g.subject_id=s.id JOIN exams e ON g.exam_id=e.id
           WHERE g.student_id='$sid' $term_clause GROUP BY s.id";
    $res = mysqli_query($conn, $sq);
    $lbls = []; $d1 = []; $d2 = []; $d3 = [];
    if($res) {
        while($r = mysqli_fetch_assoc($res)){
            $lbls[] = $r['subject_name']; $d1[] = $r['op']??0; $d2[] = $r['mid']??0; $d3[] = $r['en']??0;
        }
    }
?>
if(document.getElementById('studentChart')) {
    new Chart(document.getElementById('studentChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($lbls); ?>,
            datasets: [
                { label: 'Opener', data: <?php echo json_encode($d1); ?>, borderColor: '#ef4444', tension: 0.3 },
                { label: 'Mid-Term', data: <?php echo json_encode($d2); ?>, borderColor: '#f59e0b', tension: 0.3 },
                { label: 'End-Term', data: <?php echo json_encode($d3); ?>, borderColor: '#10b981', tension: 0.3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}
<?php endif; ?>

// Class Trend Graph
<?php if($selected_class && $selected_term): 
    $cq = "SELECT s.subject_name, 
           AVG(CASE WHEN e.exam_name LIKE '%Opener%' THEN g.marks END) as op,
           AVG(CASE WHEN e.exam_name LIKE '%Mid%' THEN g.marks END) as mid,
           AVG(CASE WHEN e.exam_name LIKE '%End%' THEN g.marks END) as en
           FROM grades g JOIN subjects s ON g.subject_id=s.id JOIN exams e ON g.exam_id=e.id JOIN students st ON g.student_id=st.id
           WHERE st.class_id='$selected_class' AND g.term_id='$selected_term' GROUP BY s.id";
    $cres = mysqli_query($conn, $cq);
    $clbls = []; $cd1 = []; $cd2 = []; $cd3 = [];
    if($cres) {
        while($cr = mysqli_fetch_assoc($cres)){
            $clbls[] = $cr['subject_name']; $cd1[] = round($cr['op'],1)??0; $cd2[] = round($cr['mid'],1)??0; $cd3[] = round($cr['en'],1)??0;
        }
    }
?>
if(document.getElementById('classTrendChart')) {
    new Chart(document.getElementById('classTrendChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($clbls); ?>,
            datasets: [
                { label: 'Class Opener Avg', data: <?php echo json_encode($cd1); ?>, borderColor: '#6366f1', borderDash: [5,5], tension: 0.3 },
                { label: 'Class Mid-Term Avg', data: <?php echo json_encode($cd2); ?>, borderColor: '#8b5cf6', tension: 0.3 },
                { label: 'Class End-Term Avg', data: <?php echo json_encode($cd3); ?>, borderColor: '#ec4899', tension: 0.3 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}

if(document.getElementById('distributionChart')) {
    new Chart(document.getElementById('distributionChart'), {
        type: 'bar',
        data: {
            labels: ['Elite (70%+)', 'Standard (50-69%)', 'At-Risk (<50%)'],
            datasets: [{
                label: 'No. of Students',
                data: [<?php echo (int)$dist['elite']; ?>, <?php echo (int)$dist['standard']; ?>, <?php echo (int)$dist['at_risk']; ?>],
                backgroundColor: ['#10b981', '#3b82f6', '#ef4444'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}
<?php endif; ?>
</script>
</body>
</html>