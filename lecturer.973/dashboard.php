<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('lecturer');

$db = new Database();
$current_user = $auth->getCurrentUser();

// Get lecturer stats
$courses = $db->fetchAll("SELECT c.*, COUNT(e.id) as enrolled FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id AND e.is_active = 1 WHERE c.lecturer_id = ? AND c.is_active = 1 GROUP BY c.id", [$current_user['id']]);
$total_students = $db->fetch("SELECT COUNT(DISTINCT e.student_id) as count FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.lecturer_id = ? AND e.is_active = 1", [$current_user['id']])['count'];
$total_resources = $db->fetch("SELECT COUNT(*) as count FROM resources WHERE lecturer_id = ? AND is_active = 1", [$current_user['id']])['count'];
$upcoming_sessions = $db->fetchAll("SELECT s.*, c.title as course_title FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.lecturer_id = ? AND s.session_date >= NOW() ORDER BY s.session_date LIMIT 5", [$current_user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); }
        .sidebar-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .stat-card::before { background: linear-gradient(90deg, #3498db, #2980b9); }
        .stat-card h3 { background: linear-gradient(135deg, #3498db, #2980b9); -webkit-background-clip: text; background-clip: text; }
        .stat-card i { color: #3498db; }
        .card-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .btn-primary { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .btn-primary:hover { background: linear-gradient(135deg, #2980b9 0%, #3498db 100%); box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4); }
        .nav-link:hover { background: rgba(52, 152, 219, 0.15); color: #3498db; }
        .nav-link.active { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .activity-item { border-left-color: #3498db; }
        .user-avatar { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .top-nav h1 i { color: #3498db; }
    </style>
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>StudySmart</span></a></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-tachometer-alt"></i>Lecturer Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="stat-card"><h3><?php echo count($courses); ?></h3><p><i class="fas fa-book me-2"></i>My Courses</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $total_students; ?></h3><p><i class="fas fa-user-graduate me-2"></i>Total Students</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo $total_resources; ?></h3><p><i class="fas fa-file-alt me-2"></i>Resources</p></div></div>
            <div class="col-md-3"><div class="stat-card"><h3><?php echo count($upcoming_sessions); ?></h3><p><i class="fas fa-calendar me-2"></i>Upcoming Sessions</p></div></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-book me-2"></i>My Courses</h5></div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                        <p class="text-muted text-center py-4">No courses assigned yet.</p>
                        <?php else: ?>
                        <?php foreach ($courses as $c): ?>
                        <div class="activity-item">
                            <strong><?php echo htmlspecialchars($c['title']); ?></strong>
                            <br><small class="text-muted"><?php echo $c['enrolled']; ?> students enrolled</small>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Upcoming Sessions</h5></div>
                    <div class="card-body">
                        <?php if (empty($upcoming_sessions)): ?>
                        <p class="text-muted text-center py-4">No upcoming sessions.</p>
                        <?php else: ?>
                        <?php foreach ($upcoming_sessions as $s): ?>
                        <div class="activity-item">
                            <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($s['course_title']); ?> • <?php echo date('M j, Y H:i', strtotime($s['session_date'])); ?></small>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/assets/js/admin-script.js"></script>
</body>
</html>
