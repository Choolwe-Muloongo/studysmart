<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Subscription.php';

// ============================
// Lenco Mobile Money Configuration (Study Smart)
define('LENCO_SECRET_KEY', '01196d7abf5ae617b9f9dbd187198f6fa1b95485e8229a396c82d297a689d447');
define('LENCO_PUBLIC_KEY', 'pub-0244f2fd9ff633776ae46a5a34853cad92aec60232743a05');
define('LENCO_BASE_URL', 'https://api.lenco.co/access/v2');
define('REVENUE_PRODUCT_KEY', 'study_smart');
define('PLATFORM_FEE_PERCENT', 0.02);   // Your gain: fixed 2%
define('LENCO_FEE_PERCENT', 0.015);      // Lenco variable fee estimate
define('LENCO_FEE_FIXED', 0.75);         // Lenco fixed fee estimate (K)
define('WITHDRAW_LNCO_FEE_PERCENT', 0.015);
define('WITHDRAW_LNCO_FEE_FIXED', 1.00);
define('MIN_FEE', 0.30);

// Basic sanitization helper
function ss_sanitize($value): string {
    return trim(filter_var((string)$value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

// This project uses the Database class from config/database.php; avoid direct PDO extraction.


function ss_calc_platform_fee(float $amount): float {
    return round(max(MIN_FEE, $amount * PLATFORM_FEE_PERCENT), 2);
}

function ss_calc_lenco_fee(float $amount): float {
    return round(max(MIN_FEE, ($amount * LENCO_FEE_PERCENT) + LENCO_FEE_FIXED), 2);
}

function ss_calc_txn_fee(float $amount): float {
    return round(ss_calc_platform_fee($amount) + ss_calc_lenco_fee($amount), 2);
}

function ss_calc_withdraw_lenco_fee(float $amount): float {
    return round(max(MIN_FEE, ($amount * WITHDRAW_LNCO_FEE_PERCENT) + WITHDRAW_LNCO_FEE_FIXED), 2);
}

// Ensure revenue & payment tracking tables exist (so withdrawals can be isolated to Study Smart only)
function ss_ensure_tables($db): void {
    // Stores each Study Smart payment attempt + subscription metadata tied to it
    $db->execute("
        CREATE TABLE IF NOT EXISTS study_smart_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            subscription_plan_id INT NOT NULL,
            course_id INT NULL,
            promo_code VARCHAR(100) NULL,
            amount DECIMAL(12,2) NOT NULL,
            reference VARCHAR(100) NOT NULL UNIQUE,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            processed TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");


    // Backfill/add fee columns for fee-aware settlement (safe on existing installs)
    try { $db->execute("ALTER TABLE study_smart_payments ADD COLUMN base_amount DECIMAL(12,2) NULL AFTER amount"); } catch (Throwable $e) {}
    try { $db->execute("ALTER TABLE study_smart_payments ADD COLUMN transaction_fee DECIMAL(12,2) NULL AFTER base_amount"); } catch (Throwable $e) {}
    try { $db->execute("ALTER TABLE study_smart_payments ADD COLUMN owner_amount DECIMAL(12,2) NULL AFTER transaction_fee"); } catch (Throwable $e) {}

    // Admin withdrawals (owner funds only)
    $db->execute("
        CREATE TABLE IF NOT EXISTS admin_withdrawals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_user_id INT NOT NULL,
            amount_requested DECIMAL(12,2) NOT NULL,
            withdrawal_fee DECIMAL(12,2) NOT NULL,
            platform_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
            net_payout DECIMAL(12,2) NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            reference VARCHAR(100) NULL UNIQUE,
            note VARCHAR(255) NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Isolated revenue ledger for Study Smart only (for withdrawals/balance)
    $db->execute("
        CREATE TABLE IF NOT EXISTS revenue_ledger (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_key VARCHAR(50) NOT NULL,
            user_id INT NULL,
            amount DECIMAL(12,2) NOT NULL,
            reference VARCHAR(100) NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'successful',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_product_ref (product_key, reference)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

$auth = new Auth();
$auth->requireLogin();

$subscription = new Subscription();
$db = new Database();
$current_user = $auth->getCurrentUser();

// ============================
// Payment flow endpoints (AJAX)
// - POST init_mobile_money=1  -> starts Lenco mobile money collection
// - GET verify_reference=REF  -> verifies payment, logs Study Smart revenue, then activates subscription

ss_ensure_tables($db);

// ============================
// Load Subscription Plans (from `subscriptions` table)
// IMPORTANT: Some installations don't expose PDO via the Database wrapper reliably.
// So we prefer the app's Database helper ($db->fetchAll) and only fall back to PDO if needed.
$plans = [];
try {
    if (is_object($db) && method_exists($db, 'fetchAll')) {
        // Treat NULL is_active as active to avoid "No active subscription plans found"
        $plans = $db->fetchAll("SELECT id, subscription_type, price, period_days, description FROM subscriptions WHERE IFNULL(is_active, 1) = 1 ORDER BY price ASC");
    }
} catch (Throwable $e) {
    // If plans can't be loaded, keep empty; UI will show a message.
    $plans = [];
}

// Verify payment reference
if (isset($_GET['verify_reference'])) {
    header('Content-Type: application/json');
    $reference = ss_sanitize($_GET['verify_reference']);
    if ($reference === '') {
        echo json_encode(['success' => false, 'message' => 'Invalid reference']);
        exit;
    }

    try {
        $ch = curl_init(LENCO_BASE_URL . '/collections/status/' . $reference);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . LENCO_SECRET_KEY,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['data']['status'])) {
            $status = $result['data']['status'];

            if ($status === 'successful') {
                // Process only once
                                $payRow = $db->fetch("SELECT * FROM study_smart_payments WHERE reference = ? LIMIT 1", [$reference]);

                if (!$payRow) {
                    echo json_encode(['success' => false, 'message' => 'Payment record not found. Please contact support.']);
                    exit;
                }

                if ((int)$payRow['processed'] === 1) {
                    echo json_encode(['success' => true, 'message' => 'Already processed']);
                    exit;
                }

                // Activate subscription using saved metadata
                $subResult = $subscription->createSubscription(
                    (int)$payRow['user_id'],
                    (int)$payRow['subscription_plan_id'],
                    $payRow['course_id'] !== null ? (int)$payRow['course_id'] : null,
                    $payRow['promo_code'] ?: null
                );

                if (!$subResult['success']) {
                    echo json_encode(['success' => false, 'message' => $subResult['message'] ?? 'Subscription activation failed']);
                    exit;
                }

                // Mark processed & status
                $db->execute("UPDATE study_smart_payments SET status='successful', processed=1 WHERE reference = ?", [$reference]);
// Log Study Smart revenue (separate ledger for withdrawals)
                $ownerAmount = isset($payRow['owner_amount']) ? (float)$payRow['owner_amount'] : (float)$payRow['amount'];
                $db->execute("INSERT IGNORE INTO revenue_ledger (product_key, user_id, amount, reference, status, created_at) VALUES (?, ?, ?, ?, 'successful', NOW())", [REVENUE_PRODUCT_KEY, (int)$payRow['user_id'], $ownerAmount, $reference]);
echo json_encode(['success' => true, 'message' => 'Payment verified and subscription activated']);
                exit;
            }

            echo json_encode(['success' => false, 'status' => $status, 'message' => 'Payment not completed yet']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
        exit;
    } catch (Throwable $e) {
        error_log("Study Smart verify error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Verification error. Please try again.']);
        exit;
    }
}

// Initiate mobile money payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['init_mobile_money'])) {
    header('Content-Type: application/json');

    $subscription_plan_id = (int)($_POST['subscription_plan_id'] ?? $_POST['subscription_id'] ?? $_POST['plan_id'] ?? 0);
    $course_id = isset($_POST['course_id']) && $_POST['course_id'] !== '' ? (int)$_POST['course_id'] : null;
    $promo_code = ss_sanitize($_POST['promo_code'] ?? '');
    $phone = ss_sanitize($_POST['phone'] ?? '');
    $operator = ss_sanitize($_POST['operator'] ?? '');

    // Amount comes from the plan (never trust client-side amount)
    $plansIndex = [];
    foreach ($plans as $p) {
        $plansIndex[(int)$p['id']] = $p;
    }

    if ($subscription_plan_id <= 0 || !isset($plansIndex[$subscription_plan_id])) {
        if (empty($plansIndex)) {
            echo json_encode(['success' => false, 'message' => 'No active subscription plans found']);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Invalid subscription plan']);
        exit;
    }

    $plan = $plansIndex[$subscription_plan_id];
    $base_amount = (float)$plan['price'];
    $platform_fee = ss_calc_platform_fee($base_amount);
    $lenco_fee = ss_calc_lenco_fee($base_amount);
    $transaction_fee = round($platform_fee + $lenco_fee, 2);
    $owner_amount = $base_amount;
    $amount = round($base_amount + $transaction_fee, 2);

    if ($base_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        exit;
    }

    if ($phone === '' || $operator === '') {
        echo json_encode(['success' => false, 'message' => 'Phone and operator required']);
        exit;
    }

    try {
        $reference = 'SS_' . (int)$current_user['id'] . '_' . time();

        // Save pending payment with subscription metadata
        $db->execute("INSERT INTO study_smart_payments (user_id, subscription_plan_id, course_id, promo_code, amount, base_amount, transaction_fee, owner_amount, platform_fee, lenco_fee, reference, status, processed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0, NOW(), NOW())", [$current_user['id'], $subscription_plan_id, $course_id, $promo_code, $amount, $base_amount, $transaction_fee, $owner_amount, $platform_fee, $lenco_fee, $reference]);
$payment_data = [
            'amount' => $amount,
            'reference' => $reference,
            'phone' => $phone,
            'operator' => $operator,
            'country' => 'zm',
            'bearer' => 'merchant'
        ];

        $ch = curl_init(LENCO_BASE_URL . '/collections/mobile-money');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payment_data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . LENCO_SECRET_KEY,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if (in_array($httpCode, [200, 201, 202], true) && isset($result['data']['reference'])) {
            echo json_encode([
                'success' => true,
                'reference' => $result['data']['reference'],
                'status' => $result['data']['status'] ?? 'pending',
                'message' => 'Payment initiated. Check your phone to authorize.',
                'base_amount' => $base_amount,
                'platform_fee' => $platform_fee,
                'lenco_fee' => $lenco_fee,
                'transaction_fee' => $transaction_fee,
                'total_amount' => $amount
            ]);
            exit;
        }

        // If initiation fails, keep record but mark as failed for audits
        $db->execute("UPDATE study_smart_payments SET status='failed' WHERE reference = ?", [$reference]);

        error_log("Study Smart init failed: " . $response);
        echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Payment initiation failed']);
        exit;
    } catch (Throwable $e) {
        error_log("Study Smart init error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Payment failed. Please try again.']);
        exit;
    }
}


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
        // Subscription is activated only after payment verification (see AJAX endpoints above)
        $error_message = 'Please complete payment to activate your subscription.';
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
    <title>My Subscriptions - StudySmart</title>
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

        <div id="paymentStatusBox" class="alert" style="display:none;"></div>

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
                    <?php if (empty($plans)): ?>
  <div class="alert alert-warning" style="margin: 16px 0;">No active subscription plans are available. Please add/activate plans in the admin panel.</div>
<?php endif; ?>
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

                                <div class="mb-2">
                                    <label class="form-label mb-1 small">Mobile Money Operator *</label>
                                    <select name="operator" class="form-control form-control-sm" required>
                                        <option value="">Select operator</option>
                                        <option value="mtn">MTN Mobile Money</option>
                                        <option value="airtel">Airtel Money</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label mb-1 small">Mobile Money Number *</label>
                                    <input type="tel" name="phone" class="form-control form-control-sm" placeholder="e.g. 097XXXXXXX" required>

                                </div>

                                <div class="alert alert-info py-2 px-3 mb-2">
                                    <small>
                                        <strong>Price:</strong> K<?php echo number_format($plan['price'], 2); ?><br>
                                        <?php $est_platform = ss_calc_platform_fee((float)$plan['price']); $est_lenco = ss_calc_lenco_fee((float)$plan['price']); $est_fee = $est_platform + $est_lenco; ?>
                                        <strong>Your Platform Fee (2%):</strong> K<?php echo number_format($est_platform, 2); ?><br>
                                        <strong>Lenco Fee:</strong> K<?php echo number_format($est_lenco, 2); ?><br>
                                        <strong>Total Transaction Fees:</strong> K<?php echo number_format($est_fee, 2); ?><br>
                                        <strong>Total Charge:</strong> K<?php echo number_format(((float)$plan['price'] + $est_fee), 2); ?><br>
                                        <strong>Duration:</strong> <?php echo $plan['period_days']; ?> days
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-subscribe w-100">
                                    Pay & Activate Subscription
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function () {
  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

  async function postForm(url, data) {
    const resp = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(data) });
    return await resp.json();
  }

  async function getJson(url) {
    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
    return await resp.json();
  }

  function showToast(msg, type) {
    // Simple inline alert area
    const box = qs('#paymentStatusBox');
    if (!box) return alert(msg);
    box.className = 'alert ' + (type === 'success' ? 'alert-success' : (type === 'warn' ? 'alert-warning' : 'alert-danger'));
    box.style.display = 'block';
    box.innerText = msg;
  }

  async function pollVerify(reference) {
    const maxTries = 20; // ~60s
    for (let i = 0; i < maxTries; i++) {
      await new Promise(r => setTimeout(r, 3000));
      const res = await getJson('subscription.php?verify_reference=' + encodeURIComponent(reference));
      if (res && res.success) {
        showToast('Payment verified ✅ Subscription activated!', 'success');
        setTimeout(() => window.location.href = 'subscription.php?success=1', 1200);
        return;
      }
      if (res && res.status && res.status !== 'pending') {
        // If status is failed/cancelled/etc, stop polling
        showToast(res.message || ('Payment status: ' + res.status), 'error');
        return;
      }
      showToast('Waiting for payment confirmation... (check your phone)', 'warn');
    }
    showToast('Still waiting for payment confirmation. If you already approved, refresh this page in a minute.', 'warn');
  }

  qsa('form.mt-2').forEach(form => {
    form.addEventListener('submit', async function (e) {
      const action = qs('input[name="action"]', form)?.value;
      if (action !== 'subscribe') return; // only intercept subscribe forms
      e.preventDefault();

      const planId = qs('input[name="subscription_id"]', form)?.value || '';
      const courseId = qs('select[name="course_id"]', form)?.value || '';
      const promo = qs('input[name="promo_code"]', form)?.value || '';
      const phone = qs('input[name="phone"]', form)?.value || '';
      const operator = qs('select[name="operator"]', form)?.value || '';

      if (!operator || !phone) {
        showToast('Please select operator and enter your mobile money number.', 'error');
        return;
      }

      showToast('Starting payment... (you will receive a prompt on your phone)', 'warn');

      const init = await postForm(window.location.pathname.split('/').pop() || 'subscription.php', {
        init_mobile_money: '1',
        subscription_plan_id: planId,
        course_id: courseId,
        promo_code: promo,
        phone: phone,
        operator: operator
      });

      if (!init || !init.success) {
        showToast(init?.message || 'Payment initiation failed. Please try again.', 'error');
        return;
      }

      showToast('Payment initiated. Please approve on your phone...', 'warn');
      pollVerify(init.reference);
    });
  });
})();
</script>

</body>
</html>

