<?php
session_start();
require_once '../config/database.php';
require_once '../includes/brand_logo.php';
require_once '../classes/Auth.php';
require_once '../includes/subscription_check.php';
require_once '../includes/offline_catalog.php';
$auth=new Auth();$auth->requireRole('student');$db=new Database();$current_user=$auth->getCurrentUser();requireSubscription();ensureOfflineCatalogTable($db);
function resourcesUrl(array $changes=[]): string { $p=$_GET; foreach($changes as $k=>$v){ if($v===null||$v==='') unset($p[$k]); else $p[$k]=$v;} return 'resources.php'.(!empty($p)?('?'.http_build_query($p)):''); }
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['offline_action'],$_POST['resource_id'])){ $rid=(int)$_POST['resource_id']; $r=$db->fetch("SELECT r.id,r.title,r.course_id,r.file_size,r.file_path,r.external_url FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE r.id=? AND e.student_id=? AND e.is_active=1 AND r.is_active=1",[$rid,$current_user['id']]); if($r){ if($_POST['offline_action']==='download') offlineUpsertDownload($db,(int)$current_user['id'],$r,'document'); if($_POST['offline_action']==='remove') offlineRemoveDownload($db,(int)$current_user['id'],$rid,'document'); } header('Location: '.resourcesUrl(['view'=>null])); exit; }
$view_resource=null; if(isset($_GET['view'])) $view_resource=$db->fetch("SELECT r.*,c.title AS course_title FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE r.id=? AND e.student_id=? AND e.is_active=1 AND r.is_active=1",[(int)$_GET['view'],$current_user['id']]);
$page=max(1,(int)($_GET['page']??1));$per_page=12;$offset=($page-1)*$per_page;$course_filter=(int)($_GET['course']??0);$search=trim($_GET['search']??'');
$where=["e.student_id=?","e.is_active=1","r.is_active=1","r.resource_type!='video'","LOWER(COALESCE(r.file_path,'')) NOT REGEXP '\\.(mp3|wav|ogg|m4a|aac)$'"];$params=[$current_user['id']]; if($course_filter>0){$where[]='r.course_id=?';$params[]=$course_filter;} if($search!==''){ $where[]='(r.title LIKE ? OR r.description LIKE ? OR c.title LIKE ?)'; $q="%{$search}%";array_push($params,$q,$q,$q);} $w=implode(' AND ',$where);
$total_count=(int)$db->fetch("SELECT COUNT(*) AS count FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE {$w}",$params)['count'];$total_pages=max(1,(int)ceil($total_count/$per_page));
$resources=$db->fetchAll("SELECT r.*,c.title AS course_title FROM resources r JOIN courses c ON r.course_id=c.id JOIN enrollments e ON c.id=e.course_id WHERE {$w} ORDER BY r.created_at DESC LIMIT {$per_page} OFFSET {$offset}",$params);$courses=$db->fetchAll("SELECT DISTINCT c.id,c.title FROM courses c JOIN enrollments e ON c.id=e.course_id WHERE e.student_id=? AND e.is_active=1 ORDER BY c.title",[$current_user['id']]);
$offline=offlineStatusMap($db,(int)$current_user['id'],array_map(fn($r)=>(int)$r['id'],$resources),'document');
?>
<!DOCTYPE html><html><head><title>Resources</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"><link rel="stylesheet" href="../admin/assets/css/admin-style.css"></head><body>
<nav class="sidebar" id="sidebar"><div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>StudySmart</span></a></div><div class="sidebar-nav"><div class="nav-item"><a href="resources.php" class="nav-link active"><i class="fas fa-file-alt"></i><span>Resources</span></a></div><div class="nav-item"><a href="download.php" class="nav-link"><i class="fas fa-download"></i><span>Downloads</span></a></div></div></nav>
<div class="main-content"><div class="top-nav"><h1>Resources</h1><div class="user-info"><a href="download.php" class="btn btn-sm btn-outline-primary">Downloads</a></div></div>
<?php if($view_resource){ require_once '../includes/document_viewer.php'; } ?>
<div class="card"><div class="card-header"><form method="GET" class="d-flex gap-2"><input name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-sm" placeholder="Search"><select name="course" class="form-select form-select-sm"><option value="0">All Courses</option><?php foreach($courses as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $course_filter===(int)$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['title']); ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary">Filter</button></form></div><div class="card-body"><div class="row g-3"><?php foreach($resources as $r): $isDown=(($offline[(int)$r['id']]['status']??'')==='downloaded'); ?><div class="col-md-6 col-lg-4"><div class="card"><div class="card-body"><h6><?php echo htmlspecialchars($r['title']); ?></h6><small class="text-muted"><?php echo htmlspecialchars($r['course_title']); ?></small><div class="mt-2 d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars(resourcesUrl(['view'=>$r['id']])); ?>">Open</a><form method="POST"><input type="hidden" name="resource_id" value="<?php echo (int)$r['id']; ?>"><input type="hidden" name="offline_action" value="<?php echo $isDown?'remove':'download'; ?>"><button class="btn btn-sm <?php echo $isDown?'btn-outline-danger':'btn-outline-success'; ?>"><?php echo $isDown?'Remove download':'Download for offline'; ?></button></form></div></div></div></div><?php endforeach; ?></div></div></div></div></body></html>
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
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; color: white; }
        .btn-success:hover { background: linear-gradient(135deg, #20c997 0%, #28a745 100%); color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4); }
        
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
        <div class="sidebar-header"><?php render_brand_logo(['href' => "dashboard.php", 'class' => "sidebar-brand", 'size' => "md", 'logo_path' => "../WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png", 'alt' => "StudySmart logo"]); ?></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link active"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
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
            <h1><i class="fas fa-file-alt"></i>Resources</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if ($view_resource): ?>
        <!-- Unified Document Viewer -->
        <?php require_once '../includes/document_viewer.php'; ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Available Documents</h5>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <form method="GET" class="d-flex gap-2">
                            <?php if (isset($_GET['view'])): ?>
                                <input type="hidden" name="view" value="<?php echo $_GET['view']; ?>">
                            <?php endif; ?>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" style="max-width: 200px;">
                            <select name="course" class="form-select form-select-sm" style="max-width: 200px;">
                                <option value="0">All Courses</option>
                                <?php foreach ($enrolled_courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo $course_filter == $course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="file_type" class="form-select form-select-sm" style="max-width: 160px;">
                                <option value="">All Types</option>
                                <?php foreach (['pdf','doc','docx','ppt','pptx','txt','jpg','jpeg','png','gif'] as $ft): ?>
                                    <option value="<?php echo $ft; ?>" <?php echo $file_type_filter === $ft ? 'selected' : ''; ?>><?php echo strtoupper($ft); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                            <?php if ($course_filter > 0 || !empty($search) || !empty($file_type_filter)): ?>
                                <a href="resources.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($resources)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                    <h5 class="mb-2">No documents found</h5>
                    <p class="text-muted mb-4">You don't have access to any resources yet. Enroll in a course to get started!</p>
                    <a href="../subscription.php" class="btn btn-success">
                        <i class="fas fa-plus-circle me-2"></i>Enroll in a Course
                    </a>
                </div>
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
                        <a href="?view=<?php echo $r['id']; ?><?php echo $course_filter > 0 ? '&course=' . $course_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($file_type_filter) ? '&file_type=' . urlencode($file_type_filter) : ''; ?>" class="text-decoration-none">
                            <div class="resource-card card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <div class="resource-icon <?php echo $icon_class; ?>">
                                        <i class="fas fa-file-<?php echo $ext === 'pdf' ? 'pdf' : ($icon_class === 'doc' ? 'word' : ($icon_class === 'ppt' ? 'powerpoint' : 'alt')); ?>"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($r['title']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($r['course_title']); ?></small>
                                        <br><small class="text-muted"><?php echo number_format((int)($r['views_count'] ?? 0)); ?> views</small>
                                        <br><small class="text-muted"><?php echo date('M j, Y', strtotime($r['created_at'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $course_filter > 0 ? '&course=' . $course_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($file_type_filter) ? '&file_type=' . urlencode($file_type_filter) : ''; ?>">Previous</a>
                        </li>
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $course_filter > 0 ? '&course=' . $course_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($file_type_filter) ? '&file_type=' . urlencode($file_type_filter) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $course_filter > 0 ? '&course=' . $course_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($file_type_filter) ? '&file_type=' . urlencode($file_type_filter) : ''; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <p class="text-center text-muted mt-2">Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_count); ?> of <?php echo $total_count; ?> resources</p>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/assets/js/admin-script.js"></script>
    <script src="assets/js/global-music-player.js"></script>
</body>
</html>
