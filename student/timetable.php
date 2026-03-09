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
requireSubscription();

$db->execute("CREATE TABLE IF NOT EXISTS study_timetables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    subject VARCHAR(180) NULL,
    day_of_week VARCHAR(20) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$success=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    if ($action==='add') {
        $title=trim($_POST['title']??'');
        $subject=trim($_POST['subject']??'');
        $day=trim($_POST['day_of_week']??'');
        $start=trim($_POST['start_time']??'');
        $end=trim($_POST['end_time']??'');
        $note=trim($_POST['note']??'');
        if ($title===''||$day===''||$start===''||$end==='') $error='Fill required fields.';
        else {
            $db->execute("INSERT INTO study_timetables (student_id,title,subject,day_of_week,start_time,end_time,note) VALUES (?,?,?,?,?,?,?)",[$current_user['id'],$title,$subject,$day,$start,$end,$note]);
            $success='Timetable item added.';
        }
    } elseif ($action==='delete') {
        $id=(int)($_POST['id']??0);
        $db->execute("DELETE FROM study_timetables WHERE id=? AND student_id=?",[$id,$current_user['id']]);
        $success='Timetable item removed.';
    }
}

$items=$db->fetchAll("SELECT * FROM study_timetables WHERE student_id=? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time",[$current_user['id']]);
$days=['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
?>
<!doctype html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Study Timetable - StudySmart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"><link rel="stylesheet" href="assets/css/student-style.css">
<style>.sidebar{background:linear-gradient(180deg,#1e3c72 0%,#2a5298 100%)}.sidebar-header,.card-header,.nav-link.active,.user-avatar{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}.nav-link:hover{background:rgba(102,126,234,.15);color:#667eea}</style></head><body>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content"><div class="top-nav"><h1><i class="fas fa-table"></i>Study Timetable</h1><div class="user-info"><div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'],0,1)); ?></div><span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'].' '.$current_user['last_name']); ?></span><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div>
<?php if($success):?><div class="alert alert-success"><?php echo htmlspecialchars($success);?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?php echo htmlspecialchars($error);?></div><?php endif;?>
<div class="row g-4"><div class="col-lg-4"><div class="card"><div class="card-header"><h5 class="mb-0">Add Study Slot</h5></div><div class="card-body"><form method="POST"><input type="hidden" name="action" value="add"><div class="mb-2"><label class="form-label">Title*</label><input class="form-control" name="title" required></div><div class="mb-2"><label class="form-label">Subject</label><input class="form-control" name="subject"></div><div class="mb-2"><label class="form-label">Day*</label><select class="form-select" name="day_of_week" required><?php foreach($days as $d):?><option><?php echo $d;?></option><?php endforeach;?></select></div><div class="row g-2"><div class="col"><label class="form-label">Start*</label><input type="time" class="form-control" name="start_time" required></div><div class="col"><label class="form-label">End*</label><input type="time" class="form-control" name="end_time" required></div></div><div class="mb-2 mt-2"><label class="form-label">Note</label><input class="form-control" name="note"></div><button class="btn btn-primary w-100">Save</button></form></div></div></div>
<div class="col-lg-8"><div class="card"><div class="card-header"><h5 class="mb-0">My Weekly Plan</h5></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Day</th><th>Time</th><th>Title</th><th>Subject</th><th></th></tr></thead><tbody><?php if(empty($items)):?><tr><td colspan="5" class="text-center text-muted">No timetable entries yet.</td></tr><?php else: foreach($items as $it):?><tr><td><?php echo htmlspecialchars($it['day_of_week']);?></td><td><?php echo substr($it['start_time'],0,5).' - '.substr($it['end_time'],0,5);?></td><td><?php echo htmlspecialchars($it['title']);?><br><small class="text-muted"><?php echo htmlspecialchars($it['note']);?></small></td><td><?php echo htmlspecialchars($it['subject']);?></td><td><form method="POST" onsubmit="return confirm('Remove this item?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$it['id'];?>"><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr><?php endforeach; endif;?></tbody></table></div></div></div></div>
</div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script><script src="../admin/assets/js/admin-script.js"></script>
    <script src="assets/js/global-music-player.js"></script></body></html>
