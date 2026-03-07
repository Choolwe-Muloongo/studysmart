<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/Subscription.php';

$auth = new Auth();
$auth->requireRole('admin');

$db = new Database();
$subscription = new Subscription();
$current_user = $auth->getCurrentUser();

$error_message = '';
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_plan') {
        $subscription_type = $_POST['subscription_type'];
        $price = (float)$_POST['price'];
        $period_days = (int)$_POST['period_days'];
        $description = trim($_POST['description'] ?? '');
        
        $sql = "INSERT INTO subscriptions (subscription_type, price, period_days, description) VALUES (?, ?, ?, ?)";
        $result = $db->execute($sql, [$subscription_type, $price, $period_days, $description]);
        
        if ($result) {
            $success_message = 'Subscription plan created successfully!';
        } else {
            $error_message = 'Failed to create subscription plan.';
        }
    } elseif ($action === 'update_plan') {
        $id = (int)$_POST['id'];
        $price = (float)$_POST['price'];
        $period_days = (int)$_POST['period_days'];
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sql = "UPDATE subscriptions SET price = ?, period_days = ?, description = ?, is_active = ? WHERE id = ?";
        $result = $db->execute($sql, [$price, $period_days, $description, $is_active, $id]);
        
        if ($result) {
            $success_message = 'Subscription plan updated successfully!';
        } else {
            $error_message = 'Failed to update subscription plan.';
        }
    } elseif ($action === 'create_promo') {
        $code = strtoupper(trim($_POST['code']));
        $discount_type = $_POST['discount_type'];
        $discount_value = (float)$_POST['discount_value'];
        $max_uses = !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : null;
        $valid_from = $_POST['valid_from'];
        $valid_until = $_POST['valid_until'];
        
        $sql = "INSERT INTO promo_codes (code, discount_type, discount_value, max_uses, valid_from, valid_until) VALUES (?, ?, ?, ?, ?, ?)";
        $result = $db->execute($sql, [$code, $discount_type, $discount_value, $max_uses, $valid_from, $valid_until]);
        
        if ($result) {
            $success_message = 'Promo code created successfully!';
        } else {
            $error_message = 'Failed to create promo code. Code may already exist.';
        }
    } elseif ($action === 'update_promo') {
        $id = (int)$_POST['id'];
        $discount_type = $_POST['discount_type'];
        $discount_value = (float)$_POST['discount_value'];
        $max_uses = !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : null;
        $valid_from = $_POST['valid_from'];
        $valid_until = $_POST['valid_until'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sql = "UPDATE promo_codes SET discount_type = ?, discount_value = ?, max_uses = ?, valid_from = ?, valid_until = ?, is_active = ? WHERE id = ?";
        $result = $db->execute($sql, [$discount_type, $discount_value, $max_uses, $valid_from, $valid_until, $is_active, $id]);
        
        if ($result) {
            $success_message = 'Promo code updated successfully!';
        } else {
            $error_message = 'Failed to update promo code.';
        }
    }
}

// Get all subscription plans
$plans = $db->fetchAll("SELECT * FROM subscriptions ORDER BY subscription_type, price");

// Get all promo codes
$promo_codes = $db->fetchAll("SELECT * FROM promo_codes ORDER BY created_at DESC");

// Get subscription statistics
$total_subscriptions = $db->fetch("SELECT COUNT(*) as count FROM user_subscriptions")['count'];
$active_subscriptions = $db->fetch("SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'active' AND end_date > NOW()")['count'];
$total_revenue = $db->fetch("SELECT SUM(amount_paid) as total FROM user_subscriptions")['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Management - StudySmart</title>
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
            <div class="nav-item"><a href="courses.php" class="nav-link"><i class="fas fa-book"></i><span>Courses</span></a></div>
            <div class="nav-item"><a href="lecturers.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>Lecturers</span></a></div>
            <div class="nav-item"><a href="students.php" class="nav-link"><i class="fas fa-user-graduate"></i><span>Students</span></a></div>
            <div class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
            <div class="nav-item"><a href="videos.php" class="nav-link"><i class="fas fa-video"></i><span>Videos</span></a></div>
            <div class="nav-item"><a href="sessions.php" class="nav-link"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
            <div class="nav-item"><a href="notifications.php" class="nav-link"><i class="fas fa-bell"></i><span>Notifications</span></a></div>
            <div class="nav-item"><a href="subscriptions.php" class="nav-link active"><i class="fas fa-crown"></i><span>Subscriptions</span></a></div>
            <div class="nav-item"><a href="analytics.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Analytics</span></a></div>
            <div class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></div>
            <div class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user"></i><span>Profile</span></a></div>
        </div>
    </nav>

    <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

    <div class="main-content">
        <div class="top-nav">
            <h1><i class="fas fa-crown"></i>Subscription Management</h1>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?></div>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo $total_subscriptions; ?></h3>
                    <p><i class="fas fa-crown me-2"></i>Total Subscriptions</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo $active_subscriptions; ?></h3>
                    <p><i class="fas fa-check-circle me-2"></i>Active Subscriptions</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3>K<?php echo number_format($total_revenue, 2); ?></h3>
                    <p><i class="fas fa-dollar-sign me-2"></i>Total Revenue</p>
                </div>
            </div>
        </div>

        <!-- Subscription Plans -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Subscription Plans</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                    <i class="fas fa-plus me-2"></i>Create Plan
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Period (Days)</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($plans)): ?>
                                <tr><td colspan="6" class="text-center text-muted">No subscription plans found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($plans as $plan): ?>
                                    <tr>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $plan['subscription_type'])); ?></td>
                                        <td>K<?php echo number_format($plan['price'], 2); ?></td>
                                        <td><?php echo $plan['period_days']; ?></td>
                                        <td><?php echo htmlspecialchars($plan['description'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $plan['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPlanModal<?php echo $plan['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Plan Modal -->
                                    <div class="modal fade" id="editPlanModal<?php echo $plan['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Subscription Plan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_plan">
                                                        <input type="hidden" name="id" value="<?php echo $plan['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Type</label>
                                                            <input type="text" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $plan['subscription_type'])); ?>" disabled>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Price (K)</label>
                                                            <input type="number" step="0.01" class="form-control" name="price" value="<?php echo $plan['price']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Period (Days)</label>
                                                            <input type="number" class="form-control" name="period_days" value="<?php echo $plan['period_days']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($plan['description'] ?? ''); ?></textarea>
                                                        </div>
                                                        <div class="mb-3 form-check">
                                                            <input type="checkbox" class="form-check-input" name="is_active" id="active<?php echo $plan['id']; ?>" <?php echo $plan['is_active'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="active<?php echo $plan['id']; ?>">Active</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update Plan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Promo Codes -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Promo Codes</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPromoModal">
                    <i class="fas fa-plus me-2"></i>Create Promo Code
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Discount Type</th>
                                <th>Discount Value</th>
                                <th>Max Uses</th>
                                <th>Used</th>
                                <th>Valid From</th>
                                <th>Valid Until</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($promo_codes)): ?>
                                <tr><td colspan="9" class="text-center text-muted">No promo codes found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($promo_codes as $promo): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($promo['code']); ?></strong></td>
                                        <td><?php echo ucfirst($promo['discount_type']); ?></td>
                                        <td><?php echo $promo['discount_type'] === 'percentage' ? $promo['discount_value'] . '%' : 'K' . number_format($promo['discount_value'], 2); ?></td>
                                        <td><?php echo $promo['max_uses'] ?? 'Unlimited'; ?></td>
                                        <td><?php echo $promo['used_count']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($promo['valid_from'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($promo['valid_until'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $promo['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $promo['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPromoModal<?php echo $promo['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Promo Modal -->
                                    <div class="modal fade" id="editPromoModal<?php echo $promo['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Promo Code</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_promo">
                                                        <input type="hidden" name="id" value="<?php echo $promo['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Code</label>
                                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($promo['code']); ?>" disabled>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Discount Type</label>
                                                            <select class="form-control" name="discount_type" required>
                                                                <option value="percentage" <?php echo $promo['discount_type'] === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                                                                <option value="fixed" <?php echo $promo['discount_type'] === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Discount Value</label>
                                                            <input type="number" step="0.01" class="form-control" name="discount_value" value="<?php echo $promo['discount_value']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Max Uses (leave empty for unlimited)</label>
                                                            <input type="number" class="form-control" name="max_uses" value="<?php echo $promo['max_uses']; ?>">
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Valid From</label>
                                                                <input type="datetime-local" class="form-control" name="valid_from" value="<?php echo date('Y-m-d\TH:i', strtotime($promo['valid_from'])); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Valid Until</label>
                                                                <input type="datetime-local" class="form-control" name="valid_until" value="<?php echo date('Y-m-d\TH:i', strtotime($promo['valid_until'])); ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 form-check">
                                                            <input type="checkbox" class="form-check-input" name="is_active" id="promo_active<?php echo $promo['id']; ?>" <?php echo $promo['is_active'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="promo_active<?php echo $promo['id']; ?>">Active</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update Promo Code</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Plan Modal -->
    <div class="modal fade" id="createPlanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_plan">
                        <div class="mb-3">
                            <label class="form-label">Subscription Type</label>
                            <select class="form-control" name="subscription_type" required>
                                <option value="single_course">Single Course</option>
                                <option value="multiple_courses">Multiple Courses</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price (K)</label>
                            <input type="number" step="0.01" class="form-control" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Period (Days)</label>
                            <input type="number" class="form-control" name="period_days" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Promo Modal -->
    <div class="modal fade" id="createPromoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Promo Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_promo">
                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control" name="code" required style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Type</label>
                            <select class="form-control" name="discount_type" required>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" step="0.01" class="form-control" name="discount_value" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Uses (leave empty for unlimited)</label>
                            <input type="number" class="form-control" name="max_uses">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid From</label>
                                <input type="datetime-local" class="form-control" name="valid_from" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid Until</label>
                                <input type="datetime-local" class="form-control" name="valid_until" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Promo Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin-script.js"></script>
</body>
</html>

