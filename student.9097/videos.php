<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('student');

$db = new Database();
$current_user = $auth->getCurrentUser();

// Get selected video for viewing
$view_video = null;
if (isset($_GET['watch'])) {
    $view_video = $db->fetch("SELECT r.*, c.title as course_title FROM resources r JOIN courses c ON r.course_id = c.id JOIN enrollments e ON c.id = e.course_id WHERE r.id = ? AND e.student_id = ? AND r.resource_type = 'video' AND r.is_active = 1 AND e.is_active = 1", [$_GET['watch'], $current_user['id']]);
}

$videos = $db->fetchAll("SELECT r.*, c.title as course_title FROM resources r JOIN courses c ON r.course_id = c.id JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = ? AND r.resource_type = 'video' AND r.is_active = 1 AND e.is_active = 1 ORDER BY r.created_at DESC", [$current_user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos - StudySmart</title>
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
        
        .video-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            overflow: hidden;
        }
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .video-thumbnail {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        .video-player {
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .video-player video {
            width: 100%;
            max-height: 70vh;
        }
        .player-header {
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
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link active"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="calendar.php" class="nav-link"><i class="fas fa-calendar"></i><span>Calendar</span></a></div>
            <div class="nav-item"><a href="grades.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Grades</span></a></div>
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

        <?php if ($view_video): ?>
        <!-- Video Player -->
        <div class="video-player mb-4">
            <div class="player-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($view_video['title']); ?></h5>
                    <small><?php echo htmlspecialchars($view_video['course_title']); ?></small>
                </div>
                <a href="videos.php" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-2"></i>Back to Videos</a>
            </div>
            <?php
            $video_url = '';
            if (!empty($view_video['video_url'])) {
                // YouTube or external URL
                $video_url = $view_video['video_url'];
                if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                    // Convert to embed URL
                    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches);
                    $youtube_id = $matches[1] ?? '';
                    if ($youtube_id):
            ?>
            <div class="ratio ratio-16x9">
                <iframe src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>" allowfullscreen></iframe>
            </div>
            <?php 
                    endif;
                } else {
            ?>
            <video controls>
                <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <?php
                }
            } elseif (!empty($view_video['file_path'])) {
                $file_url = APP_URL . '/uploads/' . basename($view_video['file_path']);
            ?>
            <video controls>
                <source src="<?php echo $file_url; ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <?php } ?>
            <?php if (!empty($view_video['description'])): ?>
            <div class="p-4 bg-white">
                <h6>Description</h6>
                <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($view_video['description'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-video me-2"></i>Available Videos</h5></div>
            <div class="card-body">
                <?php if (empty($videos)): ?>
                <p class="text-muted text-center py-4">No videos available.</p>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($videos as $v): ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="?watch=<?php echo $v['id']; ?>" class="text-decoration-none">
                            <div class="video-card card h-100">
                                <div class="video-thumbnail">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                                <div class="card-body">
                                    <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($v['title']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($v['course_title']); ?></small>
                                    <br><small class="text-muted"><?php echo date('M j, Y', strtotime($v['created_at'])); ?></small>
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
