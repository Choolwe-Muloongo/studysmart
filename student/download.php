<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../includes/subscription_check.php';
require_once '../includes/offline_catalog.php';
$auth=new Auth();$auth->requireRole('student');$db=new Database();$current_user=$auth->getCurrentUser();requireSubscription();ensureOfflineCatalogTable($db);

$tab=$_GET['tab'] ?? 'video';
if(!in_array($tab,['video','document','music'],true)) $tab='video';
$page=max(1,(int)($_GET['page']??1)); $per_page=9; $offset=($page-1)*$per_page;
$search=trim($_GET['search']??''); $course_filter=(int)($_GET['course']??0); $sort=$_GET['sort']??'recent'; if(!in_array($sort,['recent','name'],true)) $sort='recent';

function downloadUrl(array $changes=[]): string { $p=$_GET; foreach($changes as $k=>$v){ if($v===null||$v==='') unset($p[$k]); else $p[$k]=$v;} return 'download.php'.(!empty($p)?('?'.http_build_query($p)):''); }

$courses=$db->fetchAll("SELECT DISTINCT c.id,c.title FROM courses c JOIN enrollments e ON c.id=e.course_id WHERE e.student_id=? AND e.is_active=1 ORDER BY c.title",[$current_user['id']]);
$where=["oc.user_id=?","oc.resource_type=?","oc.status='downloaded'"];$params=[$current_user['id'],$tab];
if($search!==''){ $where[]='(oc.title LIKE ? OR c.title LIKE ?)'; $q="%{$search}%"; $params[]=$q; $params[]=$q; }
if($course_filter>0){ $where[]='oc.course_id=?'; $params[]=$course_filter; }
$w=implode(' AND ',$where);
$order=$sort==='name' ? 'oc.title ASC' : 'oc.downloaded_at DESC';
$total_count=(int)$db->fetch("SELECT COUNT(*) AS count FROM student_offline_catalog oc LEFT JOIN courses c ON oc.course_id=c.id WHERE {$w}",$params)['count']; $total_pages=max(1,(int)ceil($total_count/$per_page));
$items=$db->fetchAll("SELECT oc.*, c.title AS course_title, r.external_url FROM student_offline_catalog oc LEFT JOIN courses c ON oc.course_id=c.id LEFT JOIN resources r ON r.id=oc.resource_id WHERE {$w} ORDER BY {$order} LIMIT {$per_page} OFFSET {$offset}",$params);
?>
<!DOCTYPE html><html><head><title>Downloads</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/student-style.css"></head><body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content"><div class="top-nav"><h1><i class="fas fa-download"></i>Downloads</h1></div>
<ul class="nav nav-tabs mb-3"><li class="nav-item"><a class="nav-link <?php echo $tab==='video'?'active':''; ?>" href="<?php echo htmlspecialchars(downloadUrl(['tab'=>'video','page'=>null])); ?>">Videos</a></li><li class="nav-item"><a class="nav-link <?php echo $tab==='document'?'active':''; ?>" href="<?php echo htmlspecialchars(downloadUrl(['tab'=>'document','page'=>null])); ?>">Documents</a></li><li class="nav-item"><a class="nav-link <?php echo $tab==='music'?'active':''; ?>" href="<?php echo htmlspecialchars(downloadUrl(['tab'=>'music','page'=>null])); ?>">Music</a></li></ul><div class="alert alert-info mb-3">Offline items are stored inside the StudySmart app for offline viewing and are not exported as standalone files.</div>
<div class="card"><div class="card-header"><form method="GET" class="d-flex gap-2 flex-wrap"><input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>"><input name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-sm" placeholder="Search" style="max-width:220px"><select name="course" class="form-select form-select-sm" style="max-width:220px"><option value="0">All Courses</option><?php foreach($courses as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $course_filter===(int)$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['title']); ?></option><?php endforeach; ?></select><select name="sort" class="form-select form-select-sm" style="max-width:160px"><option value="recent" <?php echo $sort==='recent'?'selected':''; ?>>Recent</option><option value="name" <?php echo $sort==='name'?'selected':''; ?>>Name</option></select><button class="btn btn-sm btn-primary">Apply</button></form></div>
<div class="card-body"><h6>Available Offline</h6><div id="offlineNotice" class="alert alert-warning d-none mt-2"></div><div class="row g-3"><?php foreach($items as $it): $online=offlineOnlineUrl((int)$it['resource_id'],$it['resource_type']); ?><div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body"><h6><?php echo htmlspecialchars($it['title']); ?></h6><small class="text-muted"><?php echo htmlspecialchars($it['course_title'] ?? 'No course'); ?></small><br><small><?php echo date('M j, Y g:i A', strtotime($it['downloaded_at'])); ?></small><div class="mt-2"><button class="btn btn-sm btn-outline-primary offline-open" data-local-key="<?php echo htmlspecialchars($it['cache_key']); ?>" data-online-url="<?php echo htmlspecialchars($online); ?>" data-requires-network="<?php echo (int)$it['requires_network']; ?>">Open</button><?php if((int)$it['requires_network']===1): ?><div class="text-danger small mt-2">Go online to view this item.</div><?php endif; ?></div></div></div></div><?php endforeach; ?></div></div></div></div>
<script>
document.querySelectorAll('.offline-open').forEach(btn=>{btn.addEventListener('click', async ()=>{const notice=document.getElementById('offlineNotice'); notice.classList.add('d-none'); const localKey=btn.dataset.localKey; const canonicalLocalKey=localKey?new URL(localKey,window.location.origin).toString():''; const online=btn.dataset.onlineUrl; const requires=btn.dataset.requiresNetwork==='1'; if(requires && !navigator.onLine){ notice.textContent='Go online to view this item.'; notice.classList.remove('d-none'); return; } try { if('caches' in window && canonicalLocalKey){ const hit=await caches.match(canonicalLocalKey); if(hit){ window.location.href=canonicalLocalKey; return; } } } catch(e){} window.location.href=online;});});
</script>
</body></html>
