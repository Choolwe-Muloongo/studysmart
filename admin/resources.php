<?php
session_start();
require_once '../config/database.php';
require_once '../includes/brand_logo.php';
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
        $resource_type = $_POST['resource_type'] ?? 'document';
        
        if ($title && $course_id && isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/documents/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['resource_file']['name']);
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $file_path)) {
                $result = $db->execute("INSERT INTO resources (course_id, lecturer_id, title, description, resource_type, file_path, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())", 
                    [$course_id, $current_user['id'], $title, $description, $resource_type, 'documents/' . $file_name]);
                
                if ($result) {
                    $message = 'Document uploaded successfully';
                    $action = 'list';
                } else {
                    $error = 'Failed to save document';
                }
            } else {
                $error = 'Failed to upload file';
            }
        } else {
            $error = 'Please fill in all required fields and select a file';
        }
    } elseif ($action === 'delete' && isset($_POST['resource_id'])) {
        $db->execute("UPDATE resources SET is_active = 0 WHERE id = ?", [$_POST['resource_id']]);
        $message = 'Document removed';
        $action = 'list';
    }
}

// View document
$view_resource = null;
if (isset($_GET['view'])) {
    $view_resource = $db->fetch("SELECT r.*, c.title as course_title FROM resources r LEFT JOIN courses c ON r.course_id = c.id WHERE r.id = ?", [$_GET['view']]);
}

$resources = $db->fetchAll("SELECT r.*, c.title as course_title, u.first_name, u.last_name FROM resources r LEFT JOIN courses c ON r.course_id = c.id LEFT JOIN users u ON r.lecturer_id = u.id WHERE r.resource_type != 'video' ORDER BY r.created_at DESC");
$courses = $db->fetchAll("SELECT id, title FROM courses WHERE is_active = 1 ORDER BY title");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <style>
        .document-viewer { background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .document-viewer iframe { width: 100%; height: 75vh; border: none; }
        .viewer-header { background: linear-gradient(135deg, #FF8C00 0%, #FF4500 100%); color: white; padding: 1rem 1.5rem; }
    </style>
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
            <div class="nav-item"><a href="resources.php" class="nav-link active"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
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
            <h1><i class="fas fa-file-alt"></i>Resources</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <?php if ($view_resource): ?>
        <div class="document-viewer mb-4">
            <div class="viewer-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($view_resource['title']); ?></h5>
                    <small><?php echo htmlspecialchars($view_resource['course_title'] ?? 'No course'); ?></small>
                </div>
                <a href="resources.php" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-2"></i>Back</a>
            </div>
            <?php
            $file_ext = strtolower(pathinfo($view_resource['file_path'] ?? '', PATHINFO_EXTENSION));
            $file_url = APP_URL . '/uploads/' . $view_resource['file_path'];
            
            if ($file_ext === 'pdf'):
            ?>
            <iframe src="<?php echo $file_url; ?>#toolbar=0"></iframe>
            <?php elseif (in_array($file_ext, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'])): ?>
            <iframe src="https://docs.google.com/gview?url=<?php echo urlencode($file_url); ?>&embedded=true"></iframe>
            <?php else: ?>
            <div class="p-5 text-center">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h5>Preview not available</h5>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($action === 'add'): ?>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Document</h5></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Document Title *</label>
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
                        <label class="form-label">Document Type</label>
                        <select class="form-select" name="resource_type">
                            <option value="document">Document</option>
                            <option value="pdf">PDF</option>
                            <option value="presentation">Presentation</option>
                            <option value="notes">Notes</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload File *</label>
                        <input type="file" class="form-control" name="resource_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt" required>
                        <small class="text-muted">PDF, DOC, DOCX, PPT, PPTX, TXT (Max 50MB)</small>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-upload me-2"></i>Upload</button>
                        <a href="resources.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Documents</h5>
                <a href="?action=add" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Upload Document</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>All Documents</h5></div>
            <div class="card-body">
                <?php if (empty($resources)): ?>
                <p class="text-muted text-center py-4">No documents uploaded yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Title</th><th>Course</th><th>Type</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($resources as $r): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['course_title'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($r['resource_type'] ?? 'document'); ?></span></td>
                                <td><span class="badge bg-<?php echo $r['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $r['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($r['created_at'])); ?></td>
                                <td>
                                    <a href="?view=<?php echo $r['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this document?')">
                                        <input type="hidden" name="resource_id" value="<?php echo $r['id']; ?>">
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
