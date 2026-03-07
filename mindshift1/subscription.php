<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Subscription.php';

$auth = new Auth();
$auth->requireLogin();

$subscription = new Subscription();
$db = new Database();
$current_user = $auth->getCurrentUser();

// Get user's subscriptions
$user_subscriptions = $subscription->getUserSubscriptions($current_user['id']);

// Get available plans
$plans = $subscription->getSubscriptionPlans();

// Get courses
$courses = $db->fetchAll("SELECT * FROM courses WHERE is_active = 1 ORDER BY title");

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'subscribe') {
        $subscription_id = (int)$_POST['subscription_id'];
        $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : null;
        $promo_code = trim($_POST['promo_code'] ?? '');
        
        $result = $subscription->createSubscription($current_user['id'], $subscription_id, $course_id, $promo_code ?: null);
        
        if ($result['success']) {
            $success_message = 'Subscription activated successfully!';
            header('Location: subscription.php?success=1');
            exit();
        } else {
            $error_message = $result['message'];
        }
    } elseif ($action === 'add_course') {
        $user_subscription_id = (int)$_POST['user_subscription_id'];
        $course_id = (int)$_POST['course_id'];
        
        $result = $subscription->addCourseToSubscription($user_subscription_id, $course_id);
        
        if ($result['success']) {
            $success_message = 'Course added successfully!';
            header('Location: subscription.php?success=1');
            exit();
        } else {
            $error_message = $result['message'];
        }
    }
}

if (isset($_GET['success'])) {
    $success_message = 'Operation completed successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subscriptions - Mind Shift</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ff8c00 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .subscription-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
        }

        .subscription-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .subscription-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .subscription-card.active {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.05);
        }

        .subscription-card.expired {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.05);
        }

        .btn-subscribe {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 12px 25px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-subscribe:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .plan-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .plan-card:hover {
            border-color: #ff8c00;
            transform: translateY(-2px);
        }

        .price {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="subscription-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold"><i class="fas fa-crown me-2"></i>My Subscriptions</h1>
            <a href="student/dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
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

        <div class="row">
            <div class="col-lg-8">
                <h3 class="mb-4">Current Subscriptions</h3>
                <?php if (empty($user_subscriptions)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>You don't have any active subscriptions. Subscribe below to access courses.
                    </div>
                <?php else: ?>
                    <?php foreach ($user_subscriptions as $sub): ?>
                        <div class="subscription-card <?php echo $sub['status'] === 'active' && strtotime($sub['end_date']) > time() ? 'active' : 'expired'; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold"><?php echo ucfirst(str_replace('_', ' ', $sub['subscription_type'])); ?></h5>
                                    <?php if ($sub['course_title']): ?>
                                        <p class="mb-1"><strong>Course:</strong> <?php echo htmlspecialchars($sub['course_title']); ?></p>
                                    <?php endif; ?>
                                    <p class="mb-1"><strong>Status:</strong> 
                                        <span class="badge bg-<?php echo $sub['status'] === 'active' && strtotime($sub['end_date']) > time() ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($sub['status']); ?>
                                        </span>
                                    </p>
                                    <p class="mb-1"><strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($sub['start_date'])); ?></p>
                                    <p class="mb-1"><strong>End Date:</strong> <?php echo date('M d, Y', strtotime($sub['end_date'])); ?></p>
                                    <p class="mb-0"><strong>Amount Paid:</strong> K<?php echo number_format($sub['amount_paid'], 2); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($sub['subscription_type'] === 'single_course' && $sub['status'] === 'active' && strtotime($sub['end_date']) > time()): ?>
                                <hr>
                                <h6 class="mb-3">Add More Courses</h6>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="action" value="add_course">
                                    <input type="hidden" name="user_subscription_id" value="<?php echo $sub['id']; ?>">
                                    <select name="course_id" class="form-control" required>
                                        <option value="">-- Select a course --</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-subscribe">
                                        <i class="fas fa-plus me-2"></i>Add Course
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <h3 class="mb-4">Subscribe Now</h3>
                <?php if (empty($plans)): ?>
                    <div class="alert alert-warning">
                        No subscription plans available at the moment.
                    </div>
                <?php else: ?>
                    <?php foreach ($plans as $plan): ?>
                        <div class="plan-card">
                            <h5 class="fw-bold"><?php echo ucfirst(str_replace('_', ' ', $plan['subscription_type'])); ?></h5>
                            <div class="price mb-3">K<?php echo number_format($plan['price'], 2); ?></div>
                            <p class="text-muted mb-3"><?php echo $plan['period_days']; ?> Days</p>
                            <?php if ($plan['description']): ?>
                                <p class="small mb-3"><?php echo htmlspecialchars($plan['description']); ?></p>
                            <?php endif; ?>

                            <!-- Inline subscribe form (no modal, avoids overlay issues) -->
                            <form method="POST" class="mt-2">
                                <input type="hidden" name="action" value="subscribe">
                                <input type="hidden" name="subscription_id" value="<?php echo $plan['id']; ?>">

                                <?php if ($plan['subscription_type'] === 'single_course'): ?>
                                    <div class="mb-2">
                                        <label class="form-label mb-1 small">Select Course *</label>
                                        <select name="course_id" class="form-control form-control-sm" required>
                                            <option value="">-- Select a course --</option>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-2">
                                    <label class="form-label mb-1 small">Promo Code (Optional)</label>
                                    <input type="text" name="promo_code" class="form-control form-control-sm" placeholder="Enter promo code">
                                </div>

                                <div class="alert alert-info py-2 px-3 mb-2">
                                    <small>
                                        <strong>Price:</strong> K<?php echo number_format($plan['price'], 2); ?><br>
                                        <strong>Duration:</strong> <?php echo $plan['period_days']; ?> days
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-subscribe w-100">
                                    Confirm Subscription
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

