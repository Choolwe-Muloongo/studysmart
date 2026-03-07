<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('student');

$db = new Database();
$current_user = $auth->getCurrentUser();

// Get selected resource for viewing
$view_resource = null;
if (isset($_GET['view'])) {
    $view_resource = $db->fetch("SELECT r.*, c.title as course_title FROM resources r JOIN courses c ON r.course_id = c.id JOIN enrollments e ON c.id = e.course_id WHERE r.id = ? AND e.student_id = ? AND r.is_active = 1 AND e.is_active = 1", [$_GET['view'], $current_user['id']]);
}

$resources = $db->fetchAll("SELECT r.*, c.title as course_title FROM resources r JOIN courses c ON r.course_id = c.id JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = ? AND r.is_active = 1 AND e.is_active = 1 AND r.resource_type != 'video' ORDER BY r.created_at DESC", [$current_user['id']]);
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
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%); }
        .sidebar-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .nav-link:hover { background: rgba(102, 126, 234, 0.15); color: #667eea; }
        .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .user-avatar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .top-nav h1 i { color: #667eea; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); }
        
        .resource-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .resource-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .resource-icon.pdf { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .resource-icon.doc { background: linear-gradient(135deg, #3498db, #2980b9); }
        .resource-icon.ppt { background: linear-gradient(135deg, #e67e22, #d35400); }
        .resource-icon.txt { background: linear-gradient(135deg, #95a5a6, #7f8c8d); }
        .resource-icon.default { background: linear-gradient(135deg, #667eea, #764ba2); }
        
        .document-viewer {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .document-viewer iframe {
            width: 100%;
            height: 80vh;
            border: none;
        }
        .viewer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
        }
    </style>
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>StudySmart</span></a></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link active"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="calendar.php" class="nav-link"><i class="fas fa-calendar"></i><span>Calendar</span></a></div>
            <div class="nav-item"><a href="grades.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Grades</span></a></div>
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

        <?php if ($view_resource): ?>
        <!-- Document Viewer -->
        <div class="document-viewer mb-4">
            <div class="viewer-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($view_resource['title']); ?></h5>
                    <small><?php echo htmlspecialchars($view_resource['course_title']); ?></small>
                </div>
                <a href="resources.php" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-2"></i>Back to Resources</a>
            </div>
            <?php
            $file_path = $view_resource['file_path'];
            $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
            $file_url = APP_URL . '/uploads/' . basename($file_path);
            
            if ($file_ext === 'pdf'):
            ?>
            <iframe src="<?php echo $file_url; ?>#toolbar=0" title="Document Viewer"></iframe>
            <?php elseif (in_array($file_ext, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'])): ?>
            <iframe src="https://docs.google.com/gview?url=<?php echo urlencode($file_url); ?>&embedded=true" title="Document Viewer"></iframe>
            <?php else: ?>
            <div class="p-5 text-center">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h5>Preview not available</h5>
                <p class="text-muted">This file type cannot be previewed online.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Available Documents</h5></div>
            <div class="card-body">
                <?php if (empty($resources)): ?>
                <p class="text-muted text-center py-4">No documents available.</p>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($resources as $r): 
                        $ext = strtolower(pathinfo($r['file_path'] ?? '', PATHINFO_EXTENSION));
                        $icon_class = 'default';
                        if ($ext === 'pdf') $icon_class = 'pdf';
                        elseif (in_array($ext, ['doc', 'docx'])) $icon_class = 'doc';
                        elseif (in_array($ext, ['ppt', 'pptx'])) $icon_class = 'ppt';
                        elseif ($ext === 'txt') $icon_class = 'txt';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="?view=<?php echo $r['id']; ?>" class="text-decoration-none">
                            <div class="resource-card card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="resource-icon <?php echo $icon_class; ?>">
                                        <i class="fas fa-file-<?php echo $ext === 'pdf' ? 'pdf' : ($icon_class === 'doc' ? 'word' : ($icon_class === 'ppt' ? 'powerpoint' : 'alt')); ?>"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($r['title']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($r['course_title']); ?></small>
                                        <br><small class="text-muted"><?php echo date('M j, Y', strtotime($r['created_at'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        </a>
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
