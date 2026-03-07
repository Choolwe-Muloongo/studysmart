<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = new Database();
$current_user = $auth->getCurrentUser();

$message = '';
$error = '';

// Get current settings
$settings = [];
$settings_rows = $db->fetchAll("SELECT * FROM system_settings");
foreach ($settings_rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = substr($key, 8);
            $existing = $db->fetch("SELECT id FROM system_settings WHERE setting_key = ?", [$setting_key]);
            if ($existing) {
                $db->execute("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?", [$value, $setting_key]);
            } else {
                $db->execute("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)", [$setting_key, $value]);
            }
        }
    }
    $message = 'Settings saved successfully';
    
    // Refresh settings
    $settings_rows = $db->fetchAll("SELECT * FROM system_settings");
    $settings = [];
    foreach ($settings_rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header"><a href="dashboard.php" class="sidebar-brand"><i class="fas fa-graduation-cap"></i><span>StudySmart</span></a></div>
        <div class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
            <div class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i><span>Users</span></a></div>
            <div class="nav-item"><a href="lecturers.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>Lecturers</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>Courses</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i><span>Notifications</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="settings.php" class="nav-link active"><i class="fas fa-cog"></i><span>Settings</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-cog"></i>Settings</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

        <form method="POST">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-globe me-2"></i>General Settings</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Site Name</label>
                                <input type="text" class="form-control" name="setting_site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'StudySmart'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Site Description</label>
                                <textarea class="form-control" name="setting_site_description" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" class="form-control" name="setting_contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Platform Settings</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Max File Upload Size (MB)</label>
                                <input type="number" class="form-control" name="setting_max_upload_size" value="<?php echo htmlspecialchars($settings['max_upload_size'] ?? '50'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Session Duration (minutes)</label>
                                <input type="number" class="form-control" name="setting_session_duration" value="<?php echo htmlspecialchars($settings['session_duration'] ?? '60'); ?>">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="setting_allow_registration" value="1" <?php echo ($settings['allow_registration'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Allow New Registrations</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Settings</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-script.js"></script>
</body>
</html>
