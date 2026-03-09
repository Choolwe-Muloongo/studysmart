<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../includes/subscription_check.php';
require_once '../includes/offline_catalog.php';

$auth = new Auth();
$auth->requireRole('student');
$db = new Database();
$current_user = $auth->getCurrentUser();
requireSubscription();
ensureOfflineCatalogTable($db);

function videosUrl(array $changes = []): string {
    $params = $_GET;
    foreach ($changes as $k => $v) {
        if ($v === null || $v === '') unset($params[$k]); else $params[$k] = $v;
    }
    return 'videos.php' . (!empty($params) ? ('?' . http_build_query($params)) : '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offline_action'], $_POST['resource_id'])) {
    $rid = (int)$_POST['resource_id'];
    $resource = $db->fetch("SELECT id,title,course_id,file_size,file_path,external_url FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE r.id=? AND e.student_id=? AND e.is_active=1 AND r.is_active=1 AND r.resource_type='video'", [$rid, $current_user['id']]);
    if ($resource) {
        if ($_POST['offline_action'] === 'download') offlineUpsertDownload($db, (int)$current_user['id'], $resource, 'video');
        if ($_POST['offline_action'] === 'remove') offlineRemoveDownload($db, (int)$current_user['id'], $rid, 'video');
    }
    header('Location: ' . videosUrl(['watch' => null]));
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 9; $offset = ($page - 1) * $per_page;
$course_filter = (int)($_GET['course'] ?? 0);
$search = trim($_GET['search'] ?? '');
$view_video = null;
if (isset($_GET['watch'])) {
    $view_video = $db->fetch("SELECT r.*, c.title AS course_title FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE r.id=? AND e.student_id=? AND e.is_active=1 AND r.is_active=1 AND r.resource_type='video'", [(int)$_GET['watch'], $current_user['id']]);
}
$where = ["e.student_id=?", "e.is_active=1", "r.is_active=1", "r.resource_type='video'"];
$params = [$current_user['id']];
if ($course_filter > 0) { $where[] = 'r.course_id=?'; $params[] = $course_filter; }
if ($search !== '') { $where[] = '(r.title LIKE ? OR r.description LIKE ? OR c.title LIKE ?)'; $q="%{$search}%"; $params[]=$q; $params[]=$q; $params[]=$q; }
$whereSql = implode(' AND ', $where);
$total_count = (int)$db->fetch("SELECT COUNT(*) AS count FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE {$whereSql}", $params)['count'];
$total_pages = max(1, (int)ceil($total_count / $per_page));
if ($page > $total_pages) { $page = $total_pages; $offset = ($page - 1) * $per_page; }
$videos = $db->fetchAll("SELECT r.*, c.title AS course_title FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE {$whereSql} ORDER BY r.created_at DESC LIMIT {$per_page} OFFSET {$offset}", $params);
$enrolled_courses = $db->fetchAll("SELECT DISTINCT c.id,c.title FROM courses c JOIN enrollments e ON c.id=e.course_id WHERE e.student_id=? AND e.is_active=1 ORDER BY c.title", [$current_user['id']]);
$offline = offlineStatusMap($db, (int)$current_user['id'], array_map(fn($v)=>(int)$v['id'], $videos), 'video');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Videos - StudySmart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"><link rel="stylesheet" href="../admin/assets/css/admin-style.css"></head>
<body>
<nav class="sidebar" id="sidebar"><div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>StudySmart</span></a></div><div class="sidebar-nav">
<div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div><div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div><div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div><div class="nav-item"><a href="videos.php" class="nav-link active"><i class="fas fa-video"></i><span>Videos</span></a></div><div class="nav-item"><a href="music.php" class="nav-link"><i class="fas fa-music"></i><span>Music</span></a></div><div class="nav-item"><a href="download.php" class="nav-link"><i class="fas fa-download"></i><span>Downloads</span></a></div></div></nav>
<div class="main-content"><div class="top-nav"><h1><i class="fas fa-video"></i>Videos</h1><div class="user-info"><a href="download.php" class="btn btn-sm btn-outline-primary me-2">Downloads</a><div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'],0,1)); ?></div><span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'].' '.$current_user['last_name']); ?></span></div></div>
<?php if ($view_video) { require_once '../includes/custom_video_player.php'; } ?>
<div class="card"><div class="card-header"><form method="GET" class="d-flex gap-2 flex-wrap"><input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-sm" placeholder="Search videos..." style="max-width:220px"><select name="course" class="form-select form-select-sm" style="max-width:220px"><option value="0">All Courses</option><?php foreach($enrolled_courses as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $course_filter===(int)$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['title']); ?></option><?php endforeach; ?></select><button class="btn btn-primary btn-sm">Filter</button></form></div>
<div class="card-body"><div class="row g-3"><?php foreach($videos as $v): $isDown=(($offline[(int)$v['id']]['status'] ?? '')==='downloaded'); ?><div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><h6><?php echo htmlspecialchars($v['title']); ?></h6><small class="text-muted"><?php echo htmlspecialchars($v['course_title']); ?></small><div class="mt-3 d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars(videosUrl(['watch'=>$v['id'],'page'=>null])); ?>">Watch</a><form method="POST"><?php if($isDown): ?><input type="hidden" name="offline_action" value="remove"><?php else: ?><input type="hidden" name="offline_action" value="download"><?php endif; ?><input type="hidden" name="resource_id" value="<?php echo (int)$v['id']; ?>"><button class="btn btn-sm <?php echo $isDown?'btn-outline-danger':'btn-outline-success'; ?>"><?php echo $isDown?'Remove download':'Download for offline'; ?></button></form></div></div></div></div><?php endforeach; ?></div></div></div></div>
<script src="../admin/assets/js/admin-script.js"></script></body></html>
