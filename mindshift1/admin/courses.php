<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Course.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = new Database();
$course = new Course();
$current_user = $auth->getCurrentUser();

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $result = $course->create($_POST['title'], $_POST['course_code'], $_POST['description'], $_POST['lecturer_id'], isset($_POST['is_active']) ? 1 : 0);
        if ($result['success']) { $message = 'Course created successfully'; $action = 'list'; } else { $error = $result['message']; }
    } elseif ($action === 'edit') {
        $result = $course->update($_POST['course_id'], $_POST['title'], $_POST['course_code'], $_POST['description'], $_POST['lecturer_id'], isset($_POST['is_active']) ? 1 : 0);
        if ($result['success']) { $message = 'Course updated successfully'; $action = 'list'; } else { $error = $result['message']; }
    } elseif ($action === 'delete') {
        $result = $course->delete($_POST['course_id']);
        if ($result['success']) { $message = 'Course deactivated successfully'; $action = 'list'; } else { $error = $result['message']; }
    }
}

$courses = $db->fetchAll("
    SELECT c.*, u.first_name, u.last_name, u.email,
           COUNT(DISTINCT e.student_id) as enrolled_students,
           COUNT(DISTINCT r.id) as resources_count
    FROM courses c
    LEFT JOIN users u ON c.lecturer_id = u.id
    LEFT JOIN enrollments e ON c.id = e.course_id AND e.is_active = 1
    LEFT JOIN resources r ON c.id = r.course_id AND r.is_active = 1
    GROUP BY c.id ORDER BY c.created_at DESC
");

$lecturers = $db->fetchAll("SELECT id, first_name, last_name, email FROM users WHERE role = 'lecturer' AND is_active = 1 ORDER BY first_name");

$edit_course = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_course = $db->fetch("SELECT * FROM courses WHERE id = ?", [$_GET['id']]);
    if (!$edit_course) { $error = 'Course not found'; $action = 'list'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Management - Mind Shift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>Mind Shift</span></a>
        </div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i><span>Users</span></a></div>
            <div class="nav-item"><a href="lecturers.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>Lecturers</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link active"><i class="fas fa-book"></i><span>Courses</span></a></div>
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
            <h1><i class="fas fa-book"></i>Courses Management</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Quick Actions</h5>
                <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Course</a>
            </div>
        </div>

        <?php if ($action === 'list'): ?>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-book me-2"></i>All Courses</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Course</th><th>Lecturer</th><th>Students</th><th>Resources</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($courses as $c): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['title']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($c['course_code']); ?></small></td>
                                <td><?php echo $c['first_name'] ? htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) : '<span class="text-muted">Unassigned</span>'; ?></td>
                                <td><span class="badge bg-primary"><?php echo $c['enrolled_students']; ?></span></td>
                                <td><span class="badge bg-info"><?php echo $c['resources_count']; ?></span></td>
                                <td><span class="badge bg-<?php echo $c['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $c['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Deactivate this course?')">
                                            <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> me-2"></i><?php echo $action === 'add' ? 'Add New Course' : 'Edit Course'; ?></h5></div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <?php if ($action === 'edit'): ?><input type="hidden" name="course_id" value="<?php echo $edit_course['id']; ?>"><?php endif; ?>
                    <div class="col-md-6">
                        <label class="form-label">Course Title *</label>
                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($edit_course['title'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course Code *</label>
                        <input type="text" class="form-control" name="course_code" value="<?php echo htmlspecialchars($edit_course['course_code'] ?? ''); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($edit_course['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lecturer</label>
                        <select class="form-select" name="lecturer_id">
                            <option value="">Select Lecturer</option>
                            <?php foreach ($lecturers as $l): ?>
                            <option value="<?php echo $l['id']; ?>" <?php echo ($edit_course['lecturer_id'] ?? '') == $l['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($l['first_name'] . ' ' . $l['last_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_active" <?php echo ($edit_course['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Active Course</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-2"></i><?php echo $action === 'add' ? 'Create' : 'Update'; ?> Course</button>
                        <a href="?action=list" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-script.js"></script>
</body>
</html>
