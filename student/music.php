<?php
session_start();
require_once '../config/database.php';
require_once '../includes/brand_logo.php';
require_once '../classes/Auth.php';
require_once '../includes/subscription_check.php';
require_once '../includes/offline_catalog.php';
$auth = new Auth(); $auth->requireRole('student'); $db = new Database(); $current_user = $auth->getCurrentUser(); requireSubscription(); ensureOfflineCatalogTable($db);
function musicUrl(array $changes=[]): string { $p=$_GET; foreach($changes as $k=>$v){ if($v===null||$v==='') unset($p[$k]); else $p[$k]=$v;} return 'music.php'.(!empty($p)?('?'.http_build_query($p)):''); }
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['offline_action'],$_POST['resource_id'])){ $rid=(int)$_POST['resource_id']; $r=$db->fetch("SELECT r.id,r.title,r.course_id,r.file_size,r.file_path,r.external_url FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE r.id=? AND e.student_id=? AND e.is_active=1 AND r.is_active=1",[$rid,$current_user['id']]); if($r){ if($_POST['offline_action']==='download') offlineUpsertDownload($db,(int)$current_user['id'],$r,'music'); if($_POST['offline_action']==='remove') offlineRemoveDownload($db,(int)$current_user['id'],$rid,'music'); } header('Location: '.musicUrl()); exit; }
$page=max(1,(int)($_GET['page']??1)); $per_page=8; $offset=($page-1)*$per_page; $course_filter=(int)($_GET['course']??0); $search=trim($_GET['search']??'');
$where=["e.student_id=?","e.is_active=1","r.is_active=1","LOWER(COALESCE(r.file_path,'')) REGEXP '\\.(mp3|wav|ogg|m4a|aac)$'"];$params=[$current_user['id']]; if($course_filter>0){$where[]='r.course_id=?';$params[]=$course_filter;} if($search!==''){ $where[]='(r.title LIKE ? OR r.description LIKE ? OR c.title LIKE ?)'; $q="%{$search}%"; array_push($params,$q,$q,$q);} $w=implode(' AND ',$where);
$total_count=(int)$db->fetch("SELECT COUNT(*) AS count FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE {$w}",$params)['count']; $total_pages=max(1,(int)ceil($total_count/$per_page));
$tracks=$db->fetchAll("SELECT r.*,c.title AS course_title FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE {$w} ORDER BY r.created_at DESC LIMIT {$per_page} OFFSET {$offset}",$params); $courses=$db->fetchAll("SELECT DISTINCT c.id,c.title FROM courses c JOIN enrollments e ON c.id=e.course_id WHERE e.student_id=? AND e.is_active=1 ORDER BY c.title",[$current_user['id']]);
$offline=offlineStatusMap($db,(int)$current_user['id'],array_map(fn($t)=>(int)$t['id'],$tracks),'music');
?>
<!DOCTYPE html><html><head><title>Music</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"><link rel="stylesheet" href="../admin/assets/css/admin-style.css"></head><body>
<nav class="sidebar" id="sidebar"><div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>StudySmart</span></a></div><div class="sidebar-nav"><div class="nav-item"><a href="music.php" class="nav-link active"><i class="fas fa-music"></i><span>Music</span></a></div><div class="nav-item"><a href="download.php" class="nav-link"><i class="fas fa-download"></i><span>Downloads</span></a></div></div></nav>
<div class="main-content"><div class="top-nav"><h1>Music</h1><div class="user-info"><a href="download.php" class="btn btn-sm btn-outline-primary me-2">Downloads</a></div></div>
<div class="card"><div class="card-header"><form method="GET" class="d-flex gap-2"><input name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-sm" placeholder="Search"><select name="course" class="form-select form-select-sm"><option value="0">All Courses</option><?php foreach($courses as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $course_filter===(int)$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['title']); ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary">Filter</button></form></div><div class="card-body"><div class="row g-3"><?php foreach($tracks as $t): $isDown=(($offline[(int)$t['id']]['status']??'')==='downloaded'); ?><div class="col-lg-6"><div class="card"><div class="card-body"><h6><?php echo htmlspecialchars($t['title']); ?></h6><audio controls class="w-100"><source src="../includes/document_stream.php?id=<?php echo (int)$t['id']; ?>"></audio><form method="POST" class="mt-2 d-inline"><input type="hidden" name="resource_id" value="<?php echo (int)$t['id']; ?>"><input type="hidden" name="offline_action" value="<?php echo $isDown?'remove':'download'; ?>"><button class="btn btn-sm <?php echo $isDown?'btn-outline-danger':'btn-outline-success'; ?>"><?php echo $isDown?'Remove download':'Download for offline'; ?></button></form></div></div></div><?php endforeach; ?></div></div></div></div><?php require_once __DIR__ . '/includes/sw_registration.php'; ?>
</body></html>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Music - StudySmart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="../admin/assets/css/admin-style.css">
<style>.sidebar{background:linear-gradient(180deg,#1e3c72 0%,#2a5298 100%)}.sidebar-header,.card-header,.btn-primary,.nav-link.active,.user-avatar{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}.nav-link:hover{background:rgba(102,126,234,.15);color:#667eea}.track-card{border:none;border-radius:14px;box-shadow:0 4px 14px rgba(0,0,0,.1)}</style>
</head><body>
<nav class="sidebar" id="sidebar"><div class="sidebar-header"><?php render_brand_logo(['href' => "dashboard.php", 'class' => "sidebar-brand", 'size' => "md", 'logo_path' => "../WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png", 'alt' => "StudySmart logo"]); ?></div><div class="sidebar-nav">
<div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
<div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
<div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
<div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
<div class="nav-item"><a href="music.php" class="nav-link active"><i class="fas fa-music"></i><span>Music</span></a></div>
<div class="nav-item"><a href="timetable.php" class="nav-link"><i class="fas fa-table"></i><span>Timetable</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
<div class="nav-item"><a href="calendar.php" class="nav-link"><i class="fas fa-calendar"></i><span>Calendar</span></a></div>
<div class="nav-item"><a href="grades.php" class="nav-link"><i class="fas fa-chart-line"></i><span>Grades</span></a></div>
</div></nav>
<button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="main-content"><div class="top-nav"><h1><i class="fas fa-music"></i>Music</h1><div class="user-info"><div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'],0,1)); ?></div><span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'].' '.$current_user['last_name']); ?></span><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div>
<div class="card"><div class="card-header"><div class="d-flex justify-content-between align-items-center flex-wrap gap-3"><h5 class="mb-0"><i class="fas fa-headphones me-2"></i>Audio Resources</h5>
<form method="GET" class="d-flex gap-2 flex-wrap"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search tracks..." value="<?php echo htmlspecialchars($search); ?>" style="max-width:220px;">
<select name="course" class="form-select form-select-sm" style="max-width:220px;"><option value="0">All Courses</option><?php foreach($courses as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $course_filter===(int)$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['title']); ?></option><?php endforeach; ?></select>
<button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i></button><?php if($search!==''||$course_filter>0): ?><a href="music.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a><?php endif; ?></form><div class="form-check form-switch ms-auto">
<input class="form-check-input" type="checkbox" role="switch" id="overlayToggle">
<label class="form-check-label text-white" for="overlayToggle">Enable mini player overlay outside Music page</label>
</div></div></div>
<div class="card-body"><?php if(empty($tracks)): ?><p class="text-center text-muted py-4">No music/audio resources found.</p><?php else: ?><div class="row g-3"><?php foreach($tracks as $t): ?><div class="col-lg-6"><div class="track-card card"><div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="mb-1"><?php echo htmlspecialchars($t['title']); ?></h6><small class="text-muted"><?php echo htmlspecialchars($t['course_title']); ?> • <?php echo number_format((int)($t['views_count']??0)); ?> listens</small></div><i class="fas fa-music text-primary"></i></div><div class="mt-3 d-flex flex-wrap gap-2"><button type="button" class="btn btn-primary btn-sm play-global-track" data-track='<?php echo htmlspecialchars(json_encode(["id"=>(int)$t["id"],"title"=>$t["title"],"url"=>"../includes/document_stream.php?id=".(int)$t["id"],"course_title"=>$t["course_title"]]), ENT_QUOTES, "UTF-8"); ?>'><i class="fas fa-play me-1"></i>Play in global player</button><button type="button" class="btn btn-outline-primary btn-sm save-offline" data-offline-save="true" data-url="../includes/document_stream.php?id=<?php echo (int)$t['id']; ?>"><i class="fas fa-cloud-download-alt me-1"></i>Save Offline</button></div></div></div></div><?php endforeach; ?></div>
<?php if($total_pages>1): ?><nav class="mt-4"><ul class="pagination justify-content-center"><li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="<?php echo htmlspecialchars(musicUrl(['page'=>$page-1])); ?>">Previous</a></li><?php for($i=max(1,$page-2);$i<=min($total_pages,$page+2);$i++): ?><li class="page-item <?php echo $i===$page?'active':''; ?>"><a class="page-link" href="<?php echo htmlspecialchars(musicUrl(['page'=>$i])); ?>"><?php echo $i; ?></a></li><?php endfor; ?><li class="page-item <?php echo $page>=$total_pages?'disabled':''; ?>"><a class="page-link" href="<?php echo htmlspecialchars(musicUrl(['page'=>$page+1])); ?>">Next</a></li></ul></nav><?php endif; ?>
<?php endif; ?></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script><script src="../admin/assets/js/admin-script.js"></script>
<script src="assets/js/global-music-player.js"></script>
<script>
(function(){
  const overlayKey = 'studysmart_music_overlay_enabled';
  const tracks = <?php echo json_encode(array_map(function($t){ return ["id"=>(int)$t["id"],"title"=>$t["title"],"url"=>"../includes/document_stream.php?id=".(int)$t["id"],"course_title"=>$t["course_title"]]; }, $tracks)); ?>;
  if (window.StudySmartMusicPlayer && tracks.length) {
    window.StudySmartMusicPlayer.enqueueMany(tracks);
  }
  document.querySelectorAll('.play-global-track').forEach((btn) => {
    btn.addEventListener('click', () => {
      try {
        const track = JSON.parse(btn.getAttribute('data-track'));
        if (window.StudySmartMusicPlayer) {
          window.StudySmartMusicPlayer.enqueue(track, true);
        }
      } catch (_) {}
    });
  });
  const overlayToggle = document.getElementById('overlayToggle');
  if (overlayToggle) {
    const enabled = localStorage.getItem(overlayKey);
    overlayToggle.checked = enabled === null ? true : enabled === '1';
    overlayToggle.addEventListener('change', () => {
      localStorage.setItem(overlayKey, overlayToggle.checked ? '1' : '0');
      if (window.StudySmartMusicPlayer) {
        window.StudySmartMusicPlayer.setOverlayEnabled(overlayToggle.checked);
      }
    });
  }
})();
</script>
<?php require_once __DIR__ . '/includes/sw_registration.php'; ?>
</body></html>
