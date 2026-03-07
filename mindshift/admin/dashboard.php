<?php
session_start();
require_once '../config/database.php';

// Simple session check
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$current_user_id = $_SESSION['user_id'];

// Get current user info
$current_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$current_user_id]);

// Get platform-wide analytics
$total_students = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND is_active = 1")['count'];
$total_lecturers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'lecturer' AND is_active = 1")['count'];
$total_courses = $db->fetch("SELECT COUNT(*) as count FROM courses WHERE is_active = 1")['count'];
$total_resources = $db->fetch("SELECT COUNT(*) as count FROM resources WHERE is_active = 1")['count'];
$total_sessions = $db->fetch("SELECT COUNT(*) as count FROM sessions WHERE is_active = 1")['count'];
$total_enrollments = $db->fetch("SELECT COUNT(*) as count FROM enrollments WHERE is_active = 1")['count'];

// Get recent activities
$recent_enrollments = $db->fetchAll("
    SELECT e.*, u.first_name, u.last_name, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN courses c ON e.course_id = c.id
    WHERE e.is_active = 1
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");

$recent_resources = $db->fetchAll("
    SELECT r.*, c.title as course_title, u.first_name, u.last_name
    FROM resources r
    JOIN courses c ON r.course_id = c.id
    JOIN users u ON r.lecturer_id = u.id
    WHERE r.is_active = 1
    ORDER BY r.created_at DESC
    LIMIT 5
");

// Get monthly enrollment statistics
$monthly_enrollments = $db->fetchAll("
    SELECT DATE_FORMAT(enrolled_at, '%Y-%m') as month, COUNT(*) as enrollments
    FROM enrollments
    WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(enrolled_at, '%Y-%m')
    ORDER BY month
");

// Get top courses by enrollment
$top_courses = $db->fetchAll("
    SELECT c.title, c.course_code, COUNT(e.id) as enrollment_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id AND e.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY enrollment_count DESC
    LIMIT 5
");

// Get system alerts
$system_alerts = [];
$inactive_users = $db->fetch("SELECT COUNT(*) as count FROM users WHERE is_active = 0")['count'];
if ($inactive_users > 0) {
    $system_alerts[] = "{$inactive_users} inactive user(s) found";
}

$expired_sessions = $db->fetch("SELECT COUNT(*) as count FROM user_sessions WHERE expires_at < NOW()")['count'];
if ($expired_sessions > 0) {
    $system_alerts[] = "{$expired_sessions} expired session(s) need cleanup";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mind Shift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/unified-style.css">
    <style>
        /* Admin Dashboard Specific Styles */
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
        }
        
        .stat-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #FF69B4, #FF1493);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(255, 105, 180, 0.2);
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #FF69B4, #FF1493);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-card p {
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .stat-card i {
            color: #FF69B4;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .main-content {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #FF69B4 0%, #FF1493 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            border: none;
            font-weight: 700;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 700;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .activity-item {
            padding: 1rem;
            border-radius: 12px;
            background: #f8f9fa;
            margin-bottom: 0.75rem;
            border-left: 4px solid #FF69B4;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background: #fff;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .activity-item:last-child {
            margin-bottom: 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FF69B4 0%, #FF1493 100%);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 105, 180, 0.4);
            background: linear-gradient(135deg, #FF1493 0%, #FF69B4 100%);
        }
        
        .top-nav {
            background: white;
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .top-nav h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1a1a1a;
            margin: 0;
        }
        
        .top-nav h1 i {
            color: #FF69B4;
            margin-right: 0.75rem;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #FF69B4 0%, #FF1493 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: none;
            border-left: 4px solid #ffc107;
            border-radius: 15px;
        }
        
        /* Sidebar styles */
        .sidebar {
            background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%);
        }
        
        .sidebar-header {
            background: linear-gradient(135deg, #FF69B4 0%, #FF1493 100%);
            padding: 1.5rem;
        }
        
        .sidebar-brand {
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .logo-image {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.875rem 1.25rem;
            border-radius: 10px;
            margin: 0.25rem 0.75rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
        }
        
        .nav-link:hover {
            background: rgba(255, 105, 180, 0.15);
            color: #FF69B4;
            transform: translateX(5px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #FF69B4 0%, #FF1493 100%);
            color: white;
        }
        
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
            background: #FF69B4;
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 10px;
            cursor: pointer;
        }
        
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <div class="logo-container d-flex align-items-center gap-2">
                    <img src="../WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png" alt="Mind Shift Logo" class="logo-image">
                    <span>Mind Shift</span>
                </div>
            </a>
        </div>
        <div class="sidebar-nav py-3">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="users.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="lecturers.php" class="nav-link">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Lecturers</span>
            </a>
            <a href="students.php" class="nav-link">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
            <a href="courses.php" class="nav-link">
                <i class="fas fa-book"></i>
                <span>Courses</span>
            </a>
            <a href="resources.php" class="nav-link">
                <i class="fas fa-file-alt"></i>
                <span>Resources</span>
            </a>
            <a href="videos.php" class="nav-link">
                <i class="fas fa-video"></i>
                <span>Videos</span>
            </a>
            <a href="sessions.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i>
                <span>Sessions</span>
            </a>
            <a href="notifications.php" class="nav-link">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
            <a href="analytics.php" class="nav-link">
                <i class="fas fa-chart-pie"></i>
                <span>Analytics</span>
            </a>
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                Admin Dashboard
            </h1>
            <div class="user-info d-flex align-items-center gap-3">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?>
                </div>
                <span class="d-none d-md-inline fw-semibold"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-2">
                <div class="stat-card">
                    <h3><?php echo $total_students; ?></h3>
                    <p class="mb-0"><i class="fas fa-user-graduate me-2"></i>Students</p>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="stat-card">
                    <h3><?php echo $total_lecturers; ?></h3>
                    <p class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Lecturers</p>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="stat-card">
                    <h3><?php echo $total_courses; ?></h3>
                    <p class="mb-0"><i class="fas fa-book me-2"></i>Courses</p>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="stat-card">
                    <h3><?php echo $total_resources; ?></h3>
                    <p class="mb-0"><i class="fas fa-file-alt me-2"></i>Resources</p>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="stat-card">
                    <h3><?php echo $total_sessions; ?></h3>
                    <p class="mb-0"><i class="fas fa-calendar me-2"></i>Sessions</p>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="stat-card">
                    <h3><?php echo $total_enrollments; ?></h3>
                    <p class="mb-0"><i class="fas fa-users me-2"></i>Enrollments</p>
                </div>
            </div>
        </div>

        <!-- System Alerts -->
        <?php if (!empty($system_alerts)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>System Alerts:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($system_alerts as $alert): ?>
                        <li><?php echo htmlspecialchars($alert); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Monthly Enrollments</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="enrollmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie me-2"></i>Top Courses by Enrollment</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="courseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions and Recent Activities -->
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="users.php" class="btn btn-primary">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                            <a href="lecturers.php" class="btn btn-primary">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Manage Lecturers
                            </a>
                            <a href="courses.php" class="btn btn-primary">
                                <i class="fas fa-book me-2"></i>Manage Courses
                            </a>
                            <a href="resources.php" class="btn btn-primary">
                                <i class="fas fa-file-alt me-2"></i>Manage Resources
                            </a>
                            <a href="sessions.php" class="btn btn-primary">
                                <i class="fas fa-calendar me-2"></i>Manage Sessions
                            </a>
                            <a href="analytics.php" class="btn btn-primary">
                                <i class="fas fa-chart-pie me-2"></i>Detailed Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="fas fa-clock me-2"></i>Recent Enrollments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_enrollments)): ?>
                            <?php foreach ($recent_enrollments as $enrollment): ?>
                            <div class="activity-item">
                                <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                                enrolled in <strong><?php echo htmlspecialchars($enrollment['course_title']); ?></strong>
                                <br><small class="text-muted"><?php echo date('M j, Y', strtotime($enrollment['enrolled_at'])); ?></small>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">No recent enrollments</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5><i class="fas fa-file-upload me-2"></i>Recent Resources</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_resources)): ?>
                            <?php foreach ($recent_resources as $resource): ?>
                            <div class="activity-item">
                                <strong><?php echo htmlspecialchars($resource['title']); ?></strong>
                                <br><small class="text-muted">by <?php echo htmlspecialchars($resource['first_name'] . ' ' . $resource['last_name']); ?> • <?php echo date('M j, Y', strtotime($resource['created_at'])); ?></small>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">No recent resources</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle function
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
        
        // Monthly Enrollments Chart
        const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
        new Chart(enrollmentCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_enrollments, 'month')); ?>,
                datasets: [{
                    label: 'Enrollments',
                    data: <?php echo json_encode(array_column($monthly_enrollments, 'enrollments')); ?>,
                    borderColor: '#ff8c00',
                    backgroundColor: 'rgba(255, 105, 180, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ff8c00',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false }
                },
                scales: { 
                    y: { 
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Top Courses Chart
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        new Chart(courseCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($top_courses, 'title')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($top_courses, 'enrollment_count')); ?>,
                    backgroundColor: ['#ff8c00', '#ff4500', '#ff6347', '#ff7f50', '#ffa500'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                cutout: '60%'
            }
        });
    </script>
</body>
</html>
