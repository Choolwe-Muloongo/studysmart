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
$action = $_GET['action'] ?? 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $course_id = $_POST['course_id'] ?? '';
        $video_url = $_POST['video_url'] ?? '';
        
        if ($title && $course_id) {
            $file_path = null;
            
            // Handle file upload if no URL provided
            if (empty($video_url) && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/videos/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['video_file']['name']);
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $file_path)) {
                    $file_path = 'videos/' . $file_name;
                } else {
                    $error = 'Failed to upload video file';
                }
            }
            
            if (!$error) {
                $result = $db->execute("INSERT INTO resources (course_id, lecturer_id, title, description, resource_type, file_path, video_url, is_active, created_at) VALUES (?, ?, ?, ?, 'video', ?, ?, 1, NOW())", 
                    [$course_id, $current_user['id'], $title, $description, $file_path, $video_url ?: null]);
                
                if ($result) {
                    $message = 'Video added successfully';
                    $action = 'list';
                } else {
                    $error = 'Failed to add video';
                }
            }
        } else {
            $error = 'Please fill in required fields';
        }
    } elseif ($action === 'delete' && isset($_POST['video_id'])) {
        $db->execute("UPDATE resources SET is_active = 0 WHERE id = ? AND resource_type = 'video'", [$_POST['video_id']]);
        $message = 'Video removed';
        $action = 'list';
    }
}

$videos = $db->fetchAll("SELECT r.*, c.title as course_title, u.first_name, u.last_name FROM resources r LEFT JOIN courses c ON r.course_id = c.id LEFT JOIN users u ON r.lecturer_id = u.id WHERE r.resource_type = 'video' ORDER BY r.created_at DESC");
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE is_active = 1 ORDER BY title");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos - Mind Shift</title>
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
            <div class="nav-item"><a href="lecturers.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>Lecturers</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link active"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i><span>Notifications</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-video"></i>Videos</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <?php if ($action === 'add'): ?>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Video</h5></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Video Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course *</label>
                        <select class="form-select" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">YouTube/Video URL</label>
                        <input type="url" class="form-control" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                        <small class="text-muted">Paste a YouTube URL or direct video link</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Or Upload Video File</label>
                        <input type="file" class="form-control" name="video_file" accept="video/*">
                        <small class="text-muted">Max 100MB (MP4, AVI, MOV)</small>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-2"></i>Add Video</button>
                        <a href="videos.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Videos</h5>
                <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Video</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-video me-2"></i>All Videos</h5></div>
            <div class="card-body">
                <?php if (empty($videos)): ?>
                <p class="text-muted text-center py-4">No videos uploaded yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Title</th><th>Course</th><th>Type</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($videos as $v): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($v['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($v['course_title'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (!empty($v['video_url'])): ?>
                                    <span class="badge bg-danger"><i class="fab fa-youtube me-1"></i>URL</span>
                                    <?php else: ?>
                                    <span class="badge bg-info"><i class="fas fa-file-video me-1"></i>File</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-<?php echo $v['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $v['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($v['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this video?')">
                                        <input type="hidden" name="video_id" value="<?php echo $v['id']; ?>">
                                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-script.js"></script>
</body>
</html>
