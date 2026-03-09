<?php
session_start();
require_once '../config/database.php';
require_once '../includes/brand_logo.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = new Database();
$current_user = $auth->getCurrentUser();

// Get analytics data
$total_users = $db->fetch("SELECT COUNT(*) as count FROM users")['count'];
$total_students = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'student'")['count'];
$total_lecturers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'lecturer'")['count'];
$total_courses = $db->fetch("SELECT COUNT(*) as count FROM courses")['count'];
$total_enrollments = $db->fetch("SELECT COUNT(*) as count FROM enrollments")['count'];
$total_resources = $db->fetch("SELECT COUNT(*) as count FROM resources")['count'];

$monthly_enrollments = $db->fetchAll("
    SELECT DATE_FORMAT(enrolled_at, '%Y-%m') as month, COUNT(*) as count
    FROM enrollments
    WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
    ORDER BY month
");

$course_enrollments = $db->fetchAll("
    SELECT c.title, COUNT(e.id) as count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    GROUP BY c.id ORDER BY count DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><?php render_brand_logo(['href' => "dashboard.php", 'class' => "sidebar-brand", 'size' => "md", 'logo_path' => "../WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png", 'alt' => "StudySmart logo"]); ?></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i><span>Users</span></a></div>
            <div class="nav-item"><a href="lecturers.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>Lecturers</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i><span>Notifications</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link active"><i class="fas fa-chart-pie"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-chart-pie"></i>Analytics</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-2"><div class="stat-card"><h3><?php echo $total_users; ?></h3><p><i class="fas fa-users me-2"></i>Total Users</p></div></div>
            <div class="col-6 col-lg-2"><div class="stat-card"><h3><?php echo $total_students; ?></h3><p><i class="fas fa-user-graduate me-2"></i>Students</p></div></div>
            <div class="col-6 col-lg-2"><div class="stat-card"><h3><?php echo $total_lecturers; ?></h3><p><i class="fas fa-chalkboard-teacher me-2"></i>Lecturers</p></div></div>
            <div class="col-6 col-lg-2"><div class="stat-card"><h3><?php echo $total_courses; ?></h3><p><i class="fas fa-book me-2"></i>Courses</p></div></div>
            <div class="col-6 col-lg-2"><div class="stat-card"><h3><?php echo $total_enrollments; ?></h3><p><i class="fas fa-clipboard-list me-2"></i>Enrollments</p></div></div>
            <div class="col-6 col-lg-2"><div class="stat-card"><h3><?php echo $total_resources; ?></h3><p><i class="fas fa-file-alt me-2"></i>Resources</p></div></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Enrollments</h5></div>
                    <div class="card-body"><div class="chart-container"><canvas id="enrollmentChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Top Courses</h5></div>
                    <div class="card-body"><div class="chart-container"><canvas id="courseChart"></canvas></div></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-script.js"></script>
    <script>
        new Chart(document.getElementById('enrollmentChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_enrollments, 'month')); ?>,
                datasets: [{
                    label: 'Enrollments',
                    data: <?php echo json_encode(array_column($monthly_enrollments, 'count')); ?>,
                    borderColor: '#ff8c00',
                    backgroundColor: 'rgba(255,140,0,0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('courseChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($course_enrollments, 'title')); ?>,
                datasets: [{
                    label: 'Enrollments',
                    data: <?php echo json_encode(array_column($course_enrollments, 'count')); ?>,
                    backgroundColor: ['#ff8c00','#ff4500','#ff6347','#ff7f50','#ffa500','#ffb347','#ffc87c','#ffd700','#ffdf00','#ffe135']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
