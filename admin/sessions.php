<?php
session_start();
require_once '../config/database.php';
require_once '../includes/brand_logo.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = new Database();
$current_user = $auth->getCurrentUser();

$sessions = $db->fetchAll("
    SELECT s.*, c.title as course_title, u.first_name, u.last_name
    FROM sessions s
    LEFT JOIN courses c ON s.course_id = c.id
    LEFT JOIN users u ON s.lecturer_id = u.id
    ORDER BY s.session_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            <div class="nav-item"><a href="sessions.php" class="nav-link active"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i><span>Notifications</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-calendar-alt"></i>Sessions</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo count($sessions); ?></h3>
                    <p><i class="fas fa-calendar-alt me-2"></i>Total Sessions</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo count(array_filter($sessions, fn($s) => strtotime($s['session_date']) >= time())); ?></h3>
                    <p><i class="fas fa-clock me-2"></i>Upcoming</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo count(array_filter($sessions, fn($s) => $s['is_active'])); ?></h3>
                    <p><i class="fas fa-check-circle me-2"></i>Active</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>All Sessions</h5></div>
            <div class="card-body">
                <?php if (empty($sessions)): ?>
                <p class="text-muted text-center py-4">No sessions scheduled yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Title</th><th>Course</th><th>Lecturer</th><th>Date</th><th>Duration</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($sessions as $s): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($s['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($s['course_title'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($s['session_date'])); ?></td>
                                <td><?php echo $s['duration_minutes']; ?> mins</td>
                                <td><span class="badge bg-<?php echo $s['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-script.js"></script>
</body>
</html>
