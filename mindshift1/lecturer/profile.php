<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('lecturer');

$db = new Database();
$current_user = $auth->getCurrentUser();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $result = $db->execute("UPDATE users SET first_name = ?, last_name = ?, phone = ?, whatsapp_number = ? WHERE id = ?", 
            [$_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['whatsapp_number'], $current_user['id']]);
        if ($result) { $message = 'Profile updated'; $current_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$current_user['id']]); }
        else { $error = 'Update failed'; }
    } elseif (isset($_POST['change_password'])) {
        if (!password_verify($_POST['current_password'], $current_user['password'])) { $error = 'Current password incorrect'; }
        elseif ($_POST['new_password'] !== $_POST['confirm_password']) { $error = 'Passwords do not match'; }
        else {
            $db->execute("UPDATE users SET password = ? WHERE id = ?", [password_hash($_POST['new_password'], PASSWORD_DEFAULT), $current_user['id']]);
            $message = 'Password changed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Mind Shift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <style>
        .sidebar { background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); }
        .sidebar-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .card-header { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .btn-primary { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .nav-link:hover { background: rgba(52, 152, 219, 0.15); color: #3498db; }
        .nav-link.active { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .user-avatar { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .top-nav h1 i { color: #3498db; }
    </style>
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>Mind Shift</span></a></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>My Courses</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link active"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-user"></i>My Profile</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <div class="user-avatar mx-auto mb-3" style="width:100px;height:100px;font-size:2.5rem;"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                        <h4><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($current_user['email']); ?></p>
                        <span class="badge bg-warning">Lecturer</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5></div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-6"><label class="form-label">First Name</label><input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($current_user['first_name']); ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($current_user['last_name']); ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>"></div>
                            <div class="col-md-6"><label class="form-label">WhatsApp</label><input type="tel" class="form-control" name="whatsapp_number" value="<?php echo htmlspecialchars($current_user['whatsapp_number'] ?? ''); ?>"></div>
                            <div class="col-12"><button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update</button></div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5></div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-4"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current_password" required></div>
                            <div class="col-md-4"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" required></div>
                            <div class="col-md-4"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="confirm_password" required></div>
                            <div class="col-12"><button type="submit" name="change_password" class="btn btn-warning"><i class="fas fa-key me-2"></i>Change Password</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/assets/js/admin-script.js"></script>
</body>
</html>
