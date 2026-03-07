<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = new Database();
$current_user = $auth->getCurrentUser();

$message = '';
$error = '';

$lecturers = $db->fetchAll("
    SELECT u.*, COUNT(DISTINCT c.id) as courses_count, COUNT(DISTINCT e.student_id) as total_students
    FROM users u
    LEFT JOIN courses c ON u.id = c.lecturer_id AND c.is_active = 1
    LEFT JOIN enrollments e ON c.id = e.course_id AND e.is_active = 1
    WHERE u.role = 'lecturer'
    GROUP BY u.id ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturers - Mind Shift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>Mind Shift</span></a></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i><span>Users</span></a></div>
            <div class="nav-item"><a href="lecturers.php" class="nav-link active"><i class="fas fa-chalkboard-teacher"></i><span>Lecturers</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i><span>Notifications</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-chalkboard-teacher"></i>Lecturers</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo count($lecturers); ?></h3>
                    <p><i class="fas fa-chalkboard-teacher me-2"></i>Total Lecturers</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo array_sum(array_column($lecturers, 'courses_count')); ?></h3>
                    <p><i class="fas fa-book me-2"></i>Total Courses</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo array_sum(array_column($lecturers, 'total_students')); ?></h3>
                    <p><i class="fas fa-user-graduate me-2"></i>Total Students</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>All Lecturers</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Courses</th><th>Students</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($lecturers as $l): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3" style="width:35px;height:35px;font-size:0.875rem;"><?php echo strtoupper(substr($l['first_name'], 0, 1)); ?></div>
                                        <strong><?php echo htmlspecialchars($l['first_name'] . ' ' . $l['last_name']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($l['email']); ?></td>
                                <td><?php echo htmlspecialchars($l['phone'] ?? '-'); ?></td>
                                <td><span class="badge bg-primary"><?php echo $l['courses_count']; ?></span></td>
                                <td><span class="badge bg-info"><?php echo $l['total_students']; ?></span></td>
                                <td><span class="badge bg-<?php echo $l['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $l['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td><a href="users.php?action=edit&id=<?php echo $l['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-script.js"></script>
</body>
</html>
