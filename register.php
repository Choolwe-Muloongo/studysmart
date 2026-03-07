<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';
require_once 'classes/Subscription.php';

$auth = new Auth();
$subscription = new Subscription();
$db = new Database();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: student/dashboard.php');
    exit();
}

$error_message = '';
$success_message = '';
$selected_plan_id = isset($_GET['plan']) ? (int)$_GET['plan'] : null;
$plans = $subscription->getSubscriptionPlans();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subscription_id = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : null;
    $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : null;
    $promo_code = trim($_POST['promo_code'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($subscription_id && !$subscription->getSubscriptionPlan($subscription_id)) {
        $error_message = 'Invalid subscription plan selected.';
    } else {
        try {
            // Register user
            $result = $auth->register($username, $email, $password, $first_name, $last_name, 'student', $phone, $phone);
            
            if ($result['success']) {
                $user_id = $result['user_id'];
                
                // Create subscription if selected
                if ($subscription_id) {
                    $sub_result = $subscription->createSubscription($user_id, $subscription_id, $course_id, $promo_code ?: null);
                    
                    if (!$sub_result['success']) {
                        $error_message = $sub_result['message'];
                    } else {
                        $success_message = 'Registration successful! You can now login.';
                    }
                } else {
                    $success_message = 'Registration successful! Please subscribe to access courses.';
                }
                
                if ($success_message) {
                    header('Location: login.php?registered=1');
                    exit();
                }
            } else {
                $error_message = $result['message'];
            }
        } catch (Exception $e) {
            $error_message = 'An error occurred during registration. Please try again.';
        }
    }
}

// Get courses for single course subscription
$courses = $db->fetchAll("SELECT * FROM courses WHERE is_active = 1 ORDER BY title");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - StudySmart</title>
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

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            max-width: 800px;
            margin: 0 auto;
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .register-header h1 {
            font-weight: 800;
            margin-bottom: 10px;
        }

        .register-body {
            padding: 40px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.3rem rgba(102, 126, 234, 0.25);
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .subscription-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }

        .plan-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .plan-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .plan-card.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .alert {
            border-radius: 15px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1><i class="fas fa-user-plus me-2"></i>Create Account</h1>
            <p>Join StudySmart and start your learning journey</p>
        </div>
        
        <div class="register-body">
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <?php if (!empty($plans)): ?>
                <div class="subscription-section">
                    <h5 class="mb-3"><i class="fas fa-crown me-2"></i>Choose Subscription Plan</h5>
                    <?php foreach ($plans as $plan): ?>
                        <div class="plan-card" onclick="selectPlan(<?php echo $plan['id']; ?>, '<?php echo $plan['subscription_type']; ?>')">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="subscription_id" 
                                       id="plan_<?php echo $plan['id']; ?>" value="<?php echo $plan['id']; ?>"
                                       <?php echo ($selected_plan_id == $plan['id']) ? 'checked' : ''; ?>>
                                <label class="form-check-label w-100" for="plan_<?php echo $plan['id']; ?>">
                                    <strong><?php echo ucfirst(str_replace('_', ' ', $plan['subscription_type'])); ?></strong>
                                    <span class="float-end">K<?php echo number_format($plan['price'], 2); ?> / <?php echo $plan['period_days']; ?> days</span>
                                    <?php if ($plan['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($plan['description']); ?></small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                        
                        <?php if ($plan['subscription_type'] === 'single_course'): ?>
                            <div id="course_select_<?php echo $plan['id']; ?>" class="mb-3" style="display: none;">
                                <label for="course_id_<?php echo $plan['id']; ?>" class="form-label">Select Course</label>
                                <select class="form-control" id="course_id_<?php echo $plan['id']; ?>" name="course_id">
                                    <option value="">-- Select a course --</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <div class="mb-3 mt-3">
                        <label for="promo_code" class="form-label">Promo Code (Optional)</label>
                        <input type="text" class="form-control" id="promo_code" name="promo_code" 
                               placeholder="Enter promo code" value="<?php echo htmlspecialchars($_POST['promo_code'] ?? ''); ?>">
                    </div>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-register mt-4">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>
            
            <div class="text-center mt-4">
                <p>Already have an account? <a href="login.php" style="color: #667eea; text-decoration: none;">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPlan(planId, type) {
            document.getElementById('plan_' + planId).checked = true;
            
            // Show/hide course selection
            document.querySelectorAll('[id^="course_select_"]').forEach(el => {
                el.style.display = 'none';
            });
            
            if (type === 'single_course') {
                const courseSelect = document.getElementById('course_select_' + planId);
                if (courseSelect) {
                    courseSelect.style.display = 'block';
                }
            }
            
            // Update selected state
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }
        
        // Initialize on page load
        document.querySelectorAll('input[name="subscription_id"]').forEach(radio => {
            if (radio.checked) {
                const planId = radio.value;
                const type = radio.closest('.plan-card').querySelector('strong').textContent.toLowerCase().replace(/\s+/g, '_');
                selectPlan(planId, type);
            }
        });
    </script>
</body>
</html>

