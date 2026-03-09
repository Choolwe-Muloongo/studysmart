<?php
session_start();
require_once '../config/database.php';
require_once '../includes/brand_logo.php';
require_once '../classes/Auth.php';
require_once '../includes/subscription_check.php';

$auth = new Auth();
$auth->requireRole('student');

$db = new Database();
$current_user = $auth->getCurrentUser();

// Dashboard is accessible, but show subscription warning if no active subscription
require_once '../classes/Subscription.php';
$subscription = new Subscription();
$has_subscription = $subscription->hasActiveSubscription($current_user['id']);

// Get student stats
$enrolled_courses = $db->fetchAll("SELECT c.*, u.first_name, u.last_name FROM courses c JOIN enrollments e ON c.id = e.course_id LEFT JOIN users u ON c.lecturer_id = u.id WHERE e.student_id = ? AND e.is_active = 1 AND c.is_active = 1", [$current_user['id']]);
$upcoming_sessions = $db->fetchAll("SELECT s.*, c.title as course_title FROM sessions s JOIN courses c ON s.course_id = c.id JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = ? AND s.session_date >= NOW() AND s.is_active = 1 ORDER BY s.session_date LIMIT 5", [$current_user['id']]);
$recent_resources = $db->fetchAll("SELECT r.*, c.title as course_title FROM resources r JOIN courses c ON r.course_id = c.id JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = ? AND r.is_active = 1 ORDER BY r.created_at DESC LIMIT 5", [$current_user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%); }
        .sidebar-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card::before { background: linear-gradient(90deg, #667eea, #764ba2); }
        .stat-card h3 { background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; background-clip: text; }
        .stat-card i { color: #667eea; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-primary:hover { background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); }
        .nav-link:hover { background: rgba(102, 126, 234, 0.15); color: #667eea; }
        .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .activity-item { border-left-color: #667eea; }
        .user-avatar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .top-nav h1 i { color: #667eea; }
    </style>
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><?php render_brand_logo(['href' => "dashboard.php", 'class' => "sidebar-brand", 'size' => "md", 'logo_path' => "../WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png", 'alt' => "StudySmart logo"]); ?></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="music.php" class="nav-link"><i class="fas fa-music"></i><span>Music</span></a></div>
            <div class="nav-item"><a href="timetable.php" class="nav-link"><i class="fas fa-table"></i><span>Timetable</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="calendar.php" class="nav-link"><i class="fas fa-calendar"></i><span>Calendar</span></a></div>
            <div class="nav-item"><a href="grades.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Grades</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-tachometer-alt"></i>Student Dashboard</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        
        <?php if (!empty($new_videos)): ?>
        <div class="modal fade" id="newVideoModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header"><h5 class="modal-title"><i class="fas fa-bell text-primary me-2"></i>New Videos Available</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <ul class="list-group">
                  <?php foreach($new_videos as $nv): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div><strong><?php echo htmlspecialchars($nv['title']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($nv['course_title']); ?></small></div>
                    <a href="watch_video.php?id=<?php echo (int)$nv['id']; ?>" class="btn btn-sm btn-outline-primary">Watch</a>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

<?php if (!$has_subscription): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>No Active Subscription:</strong> You need an active subscription to access courses, resources, and videos. 
            <a href="../subscription.php" class="alert-link">Subscribe now</a> to get started!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="stat-card"><h3><?php echo count($enrolled_courses); ?></h3><p><i class="fas fa-book me-2"></i>Enrolled Courses</p></div></div>
            <div class="col-md-4"><div class="stat-card"><h3><?php echo count($upcoming_sessions); ?></h3><p><i class="fas fa-calendar me-2"></i>Upcoming Sessions</p></div></div>
            <div class="col-md-4"><div class="stat-card"><h3><?php echo count($recent_resources); ?></h3><p><i class="fas fa-file-alt me-2"></i>New Resources</p></div></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-book me-2"></i>My Courses</h5></div>
                    <div class="card-body">
                        <?php if (empty($enrolled_courses)): ?>
                        <p class="text-muted text-center py-4">No courses enrolled yet.</p>
                        <?php else: ?>
                        <?php foreach ($enrolled_courses as $c): ?>
                        <div class="activity-item">
                            <strong><?php echo htmlspecialchars($c['title']); ?></strong>
                            <br><small class="text-muted">By <?php echo htmlspecialchars(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')); ?></small>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Upcoming Sessions</h5></div>
                    <div class="card-body">
                        <?php if (empty($upcoming_sessions)): ?>
                        <p class="text-muted text-center py-4">No upcoming sessions.</p>
                        <?php else: ?>
                        <?php foreach ($upcoming_sessions as $s): ?>
                        <div class="activity-item">
                            <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                            <br><small class="text-muted"><?php echo date('M j, Y H:i', strtotime($s['session_date'])); ?></small>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Recent Resources</h5></div>
                    <div class="card-body">
                        <?php if (empty($recent_resources)): ?>
                        <p class="text-muted text-center py-4">No resources available.</p>
                        <?php else: ?>
                        <?php foreach ($recent_resources as $r): ?>
                        <div class="activity-item">
                            <strong><?php echo htmlspecialchars($r['title']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($r['course_title']); ?></small>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/assets/js/admin-script.js"></script>
    <script src="assets/js/global-music-player.js"></script>
<script>
(function(){
  const modalEl=document.getElementById("newVideoModal");
  if(modalEl){
    const key="studysmart_last_video_popup";
    const last=localStorage.getItem(key);
    const today=new Date().toDateString();
    if(last!==today){
      const m=new bootstrap.Modal(modalEl); m.show();
      localStorage.setItem(key,today);
    }
    if("Notification" in window){
      Notification.requestPermission().then((perm)=>{
        if(perm==="granted"){
          const first=modalEl.querySelector(".list-group-item strong");
          if(first) new Notification("StudySmart: New video uploaded",{body:first.textContent});
        }
      }).catch(()=>{});
    }
  }
})();
</script>
</body>
</html>
