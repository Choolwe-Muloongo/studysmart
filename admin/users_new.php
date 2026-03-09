<?php
session_start();
require_once '../config/database.php';
require_once '../includes/brand_logo.php';
require_once '../classes/Auth.php';
require_once '../classes/Notification.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = new Database();
$notification = new Notification();

$current_user = $auth->getCurrentUser();

// Handle actions
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $role = $_POST['role'] ?? 'student';
        $phone = $_POST['phone'] ?? '';
        $whatsapp_number = $_POST['whatsapp_number'] ?? '';
        
        $auth_obj = new Auth();
        $result = $auth_obj->register($username, $email, $password, $first_name, $last_name, $role, $phone, $whatsapp_number);
        
        if ($result['success']) {
            $message = 'User created successfully';
            $action = 'list';
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'edit') {
        $user_id = $_POST['user_id'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $role = $_POST['role'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $whatsapp_number = $_POST['whatsapp_number'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $result = $db->execute("
            UPDATE users 
            SET first_name = ?, last_name = ?, role = ?, phone = ?, whatsapp_number = ?, is_active = ?
            WHERE id = ?
        ", [$first_name, $last_name, $role, $phone, $whatsapp_number, $is_active, $user_id]);
        
        if ($result) {
            $message = 'User updated successfully';
            $action = 'list';
        } else {
            $error = 'Failed to update user';
        }
    } elseif ($action === 'delete') {
        $user_id = $_POST['user_id'] ?? '';
        
        // Soft delete - set is_active to 0
        $result = $db->execute("UPDATE users SET is_active = 0 WHERE id = ?", [$user_id]);
        
        if ($result) {
            $message = 'User deactivated successfully';
            $action = 'list';
        } else {
            $error = 'Failed to deactivate user';
        }
    }
}

// Get users for listing
$users = $db->fetchAll("
    SELECT u.*, 
           COUNT(DISTINCT e.course_id) as enrolled_courses,
           COUNT(DISTINCT s.id) as sessions_count
    FROM users u
    LEFT JOIN enrollments e ON u.id = e.student_id AND e.is_active = 1
    LEFT JOIN sessions s ON u.id = s.lecturer_id AND e.is_active = 1
    WHERE u.id != ?
    GROUP BY u.id
    ORDER BY u.created_at DESC
", [$current_user['id']]);

// Get user for editing
$edit_user = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_GET['id']]);
    if (!$edit_user) {
        $error = 'User not found';
        $action = 'list';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <!-- Particle Background -->
    <div class="particles"></div>
    
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <?php render_brand_logo(['href' => "dashboard.php", 'class' => "sidebar-brand", 'size' => "md", 'logo_path' => "../WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png", 'alt' => "StudySmart logo"]); ?>
        </div>
        <div class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="users.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="lecturers.php" class="nav-link">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Lecturers</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="students.php" class="nav-link">
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="courses.php" class="nav-link">
                    <i class="fas fa-book"></i>
                    <span>Courses</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="resources.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Resources</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="sessions.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Sessions</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="notifications.php" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="analytics.php" class="nav-link">
                    <i class="fas fa-chart-pie"></i>
                    <span>Analytics</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <h1>
                <i class="fas fa-users"></i>
                User Management
            </h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?>
                </div>
                <span>Welcome, <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Quick Actions</h5>
                            <a href="?action=add" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Add New User
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($action === 'list'): ?>
            <!-- Users List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th data-sort="name">Name</th>
                                    <th data-sort="username">Username/Email</th>
                                    <th data-sort="role">Role</th>
                                    <th data-sort="status">Status</th>
                                    <th data-sort="courses">Courses</th>
                                    <th data-sort="created">Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>">
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3" style="width: 35px; height: 35px; font-size: 0.875rem;">
                                                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                                    <?php if ($user['phone']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            </div>
                                        </td>
                                        <td data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'lecturer' ? 'warning' : 'info'); ?>">
                                                <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                            </span>
                                        </td>
                                        <td data-status="<?php echo $user['is_active']; ?>">
                                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td data-courses="<?php echo $user['enrolled_courses']; ?>">
                                            <?php echo $user['enrolled_courses']; ?> courses
                                        </td>
                                        <td data-created="<?php echo strtotime($user['created_at']); ?>">
                                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" data-tooltip="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($user['is_active']): ?>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to deactivate this user?')">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" data-tooltip="Deactivate User">
                                                            <i class="fas fa-user-times"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="action" value="activate" class="btn btn-sm btn-success" data-tooltip="Activate User">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit User Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-<?php echo $action === 'add' ? 'user-plus' : 'user-edit'; ?> me-2"></i>
                        <?php echo $action === 'add' ? 'Add New User' : 'Edit User'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($edit_user['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($edit_user['last_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>" required>
                        </div>
                        <?php if ($action === 'add'): ?>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="student" <?php echo ($edit_user['role'] ?? '') === 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="lecturer" <?php echo ($edit_user['role'] ?? '') === 'lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                                <option value="admin" <?php echo ($edit_user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                            <input type="tel" class="form-control" id="whatsapp_number" name="whatsapp_number" 
                                   value="<?php echo htmlspecialchars($edit_user['whatsapp_number'] ?? ''); ?>">
                        </div>
                        <?php if ($action === 'edit'): ?>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           <?php echo ($edit_user['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active User
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-2"></i>
                                <?php echo $action === 'add' ? 'Create User' : 'Update User'; ?>
                            </button>
                            <a href="?action=list" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-script.js"></script>
</body>
</html>
