<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../includes/subscription_check.php';

$auth = new Auth();
$auth->requireRole('student');

$db = new Database();
$current_user = $auth->getCurrentUser();

// Check subscription access (general access check)
requireSubscription();

$enrolled = $db->fetchAll("SELECT c.*, u.first_name, u.last_name, COUNT(r.id) as resources FROM courses c JOIN enrollments e ON c.id = e.course_id LEFT JOIN users u ON c.lecturer_id = u.id LEFT JOIN resources r ON c.id = r.course_id AND r.is_active = 1 WHERE e.student_id = ? AND e.is_active = 1 AND c.is_active = 1 GROUP BY c.id", [$current_user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - StudySmart</title>
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
            <div class="nav-item"><a href="courses.php" class="nav-link active"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="calendar.php" class="nav-link"><i class="fas fa-calendar"></i><span>Calendar</span></a></div>
            <div class="nav-item"><a href="grades.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Grades</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-book"></i>My Courses</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Enrolled Courses</h5>
                    <a href="../subscription.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus-circle me-2"></i>Enroll in Another Course
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($enrolled)): ?>
                <p class="text-muted text-center py-4">Not enrolled in any courses yet.</p>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($enrolled as $c): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($c['title']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars($c['course_code']); ?></p>
                                <p class="small"><i class="fas fa-chalkboard-teacher me-2"></i><?php echo htmlspecialchars(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')); ?></p>
                                <span class="badge bg-info"><?php echo $c['resources']; ?> resources</span>

                                <?php
                                // Fetch a few recent resources/videos for this course to show on My Courses
                                $course_resources = $db->fetchAll("SELECT id, title, resource_type, created_at FROM resources WHERE course_id = ? AND is_active = 1 ORDER BY created_at DESC LIMIT 4", [$c['id']]);
                                if (!empty($course_resources)):
                                ?>
                                <hr>
                                <h6 class="mb-2">Latest Materials</h6>
                                <ul class="list-unstyled small">
                                    <?php foreach ($course_resources as $cr): ?>
                                        <li>
                                            <?php if ($cr['resource_type'] === 'video'): ?>
                                                <i class="fas fa-video me-2 text-muted"></i>
                                                <a href="../student/watch_video.php?id=<?php echo $cr['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($cr['title']); ?></a>
                                            <?php else: ?>
                                                <i class="fas fa-file-alt me-2 text-muted"></i>
                                                <a href="../student/resources.php?view=<?php echo $cr['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($cr['title']); ?></a>
                                            <?php endif; ?>
                                            <br><small class="text-muted"><?php echo date('M j', strtotime($cr['created_at'])); ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="../student/resources.php?course=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary me-2">View All Resources</a>
                                <a href="../student/videos.php?course=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-secondary">View All Videos</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/assets/js/admin-script.js"></script>
</body>
</html>
