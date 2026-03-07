<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('lecturer');

$db = new Database();
$current_user = $auth->getCurrentUser();

$total_courses = $db->fetch("SELECT COUNT(*) as count FROM courses WHERE lecturer_id = ? AND is_active = 1", [$current_user['id']])['count'];
$total_students = $db->fetch("SELECT COUNT(DISTINCT e.student_id) as count FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.lecturer_id = ? AND e.is_active = 1", [$current_user['id']])['count'];
$total_resources = $db->fetch("SELECT COUNT(*) as count FROM resources WHERE lecturer_id = ? AND is_active = 1", [$current_user['id']])['count'];
$total_sessions = $db->fetch("SELECT COUNT(*) as count FROM sessions WHERE lecturer_id = ?", [$current_user['id']])['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Mind Shift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%); }
        .sidebar-header { background: linear-gradient(135deg, #DC143C 0%, #C41E3A 100%); }
        .stat-card::before { background: linear-gradient(90deg, #DC143C, #C41E3A); }
        .stat-card h3 { background: linear-gradient(135deg, #DC143C, #C41E3A); -webkit-background-clip: text; background-clip: text; }
        .stat-card i { color: #DC143C; }
        .card-header { background: linear-gradient(135deg, #DC143C 0%, #C41E3A 100%); }
        .nav-link:hover { background: rgba(220, 20, 60, 0.15); color: #DC143C; }
        .nav-link.active { background: linear-gradient(135deg, #DC143C 0%, #C41E3A 100%); }
        .user-avatar { background: linear-gradient(135deg, #DC143C 0%, #C41E3A 100%); }
        .top-nav h1 i { color: #DC143C; }
    </style>
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>Mind Shift</span></a></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link active"><i class="fas fa-chart-pie"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-chart-pie"></i>My Analytics</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $total_courses; ?></h3><p><i class="fas fa-book me-2"></i>Courses</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $total_students; ?></h3><p><i class="fas fa-user-graduate me-2"></i>Students</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $total_resources; ?></h3><p><i class="fas fa-file-alt me-2"></i>Resources</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $total_sessions; ?></h3><p><i class="fas fa-calendar me-2"></i>Sessions</p></div></div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Overview</h5></div>
            <div class="card-body">
                <p>Your teaching statistics are displayed above. You have <strong><?php echo $total_courses; ?></strong> active courses with <strong><?php echo $total_students; ?></strong> enrolled students.</p>
                <p>You have uploaded <strong><?php echo $total_resources; ?></strong> resources and scheduled <strong><?php echo $total_sessions; ?></strong> sessions.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/assets/js/admin-script.js"></script>
</body>
</html>
