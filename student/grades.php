<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('student');

$db = new Database();
$current_user = $auth->getCurrentUser();

$courses = $db->fetchAll("SELECT c.*, e.enrolled_at FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = ? AND e.is_active = 1", [$current_user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%); }
        .sidebar-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .nav-link:hover { background: rgba(102, 126, 234, 0.15); color: #667eea; }
        .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .user-avatar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .top-nav h1 i { color: #667eea; }
    </style>
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>StudySmart</span></a></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="music.php" class="nav-link"><i class="fas fa-music"></i><span>Music</span></a></div>
            <div class="nav-item"><a href="timetable.php" class="nav-link"><i class="fas fa-table"></i><span>Timetable</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="calendar.php" class="nav-link"><i class="fas fa-calendar"></i><span>Calendar</span></a></div>
            <div class="nav-item"><a href="grades.php" class="nav-link active"><i class="fas fa-chart-line"></i><span>Grades</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-chart-line"></i>Grades</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>My Grades</h5></div>
            <div class="card-body">
                <?php if (empty($courses)): ?>
                <p class="text-muted text-center py-4">No courses enrolled.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Course</th><th>Code</th><th>Enrolled</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($courses as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['course_code']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($c['enrolled_at'])); ?></td>
                                <td><span class="badge bg-info">In Progress</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mt-3"><i class="fas fa-info-circle me-1"></i>Grades will be available when your lecturers submit them.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/assets/js/admin-script.js"></script>
</body>
</html>
