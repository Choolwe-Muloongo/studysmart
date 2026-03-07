<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('lecturer');

$db = new Database();
$current_user = $auth->getCurrentUser();

$students = $db->fetchAll("SELECT DISTINCT u.*, c.title as course_title FROM users u JOIN enrollments e ON u.id = e.student_id JOIN courses c ON e.course_id = c.id WHERE c.lecturer_id = ? AND e.is_active = 1 ORDER BY u.first_name", [$current_user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Mind Shift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); }
        .sidebar-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .card-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .nav-link:hover { background: rgba(52, 152, 219, 0.15); color: #3498db; }
        .nav-link.active { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .user-avatar { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .top-nav h1 i { color: #3498db; }
    </style>
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>Mind Shift</span></a></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link active"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
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
            <h1><i class="fas fa-user-graduate"></i>My Students</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Students in My Courses</h5></div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                <p class="text-muted text-center py-4">No students enrolled yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Email</th><th>Course</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                            <tr>
                                <td><div class="d-flex align-items-center"><div class="user-avatar me-2" style="width:30px;height:30px;font-size:0.75rem;"><?php echo strtoupper(substr($s['first_name'], 0, 1)); ?></div><strong><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></strong></div></td>
                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                <td><?php echo htmlspecialchars($s['course_title']); ?></td>
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
    <script src="../admin/assets/js/admin-script.js"></script>
</body>
</html>
