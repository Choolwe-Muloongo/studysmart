<?php
session_start();
require_once '../config/database.php';
require_once '../includes/brand_logo.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->requireRole('admin');
$db = new Database();
$current_user = $auth->getCurrentUser();

if (!defined('REVENUE_PRODUCT_KEY')) define('REVENUE_PRODUCT_KEY', 'study_smart');
if (!defined('PLATFORM_FEE_PERCENT')) define('PLATFORM_FEE_PERCENT', 0.02);
if (!defined('WITHDRAW_LENCO_FEE_PERCENT')) define('WITHDRAW_LENCO_FEE_PERCENT', 0.015);
if (!defined('WITHDRAW_LENCO_FEE_FIXED')) define('WITHDRAW_LENCO_FEE_FIXED', 1.00);

function calc_withdraw_lenco_fee(float $amount): float { return round(max(0.30, ($amount * WITHDRAW_LENCO_FEE_PERCENT) + WITHDRAW_LENCO_FEE_FIXED), 2); }
function calc_platform_fee(float $amount): float { return round(max(0.20, ($amount * PLATFORM_FEE_PERCENT)), 2); }

$db->execute("CREATE TABLE IF NOT EXISTS admin_withdrawals (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$owner_credits = (float)($db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM revenue_ledger WHERE product_key = ? AND status = 'successful'", [REVENUE_PRODUCT_KEY])['total'] ?? 0);
$reserved_withdrawals = (float)($db->fetch("SELECT COALESCE(SUM(amount_requested),0) AS total FROM admin_withdrawals WHERE status IN ('pending','processing','completed')", [])['total'] ?? 0);
$available_balance = max(0, $owner_credits - $reserved_withdrawals);

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_withdraw') {
    $amount = round((float)($_POST['amount'] ?? 0), 2);
    if ($amount <= 0) {
        $error = 'Enter a valid withdrawal amount.';
    } elseif ($amount > $available_balance) {
        $error = 'Insufficient withdrawable owner balance.';
    } else {
        $gateway_fee = calc_withdraw_lenco_fee($amount);
        $platform_fee = calc_platform_fee($amount);
        $net_payout = round(max(0, $amount - $gateway_fee - $platform_fee), 2);

        if ($net_payout <= 0) {
            $error = 'Net payout is too low after charges.';
        } else {
            $reference = 'SS_WD_' . time() . '_' . $current_user['id'];
            $db->execute("INSERT INTO admin_withdrawals (admin_user_id, amount_requested, withdrawal_fee, platform_fee, net_payout, status, reference, note) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)", [
                $current_user['id'], $amount, $gateway_fee, $platform_fee, $net_payout, $reference, 'Queued for Lenco payout processing'
            ]);
            $success = "Withdrawal request submitted. Ref: {$reference}";
            $reserved_withdrawals += $amount;
            $available_balance = max(0, $owner_credits - $reserved_withdrawals);
        }
    }
}

$withdrawals = $db->fetchAll("SELECT w.*, u.first_name, u.last_name FROM admin_withdrawals w LEFT JOIN users u ON u.id = w.admin_user_id ORDER BY w.created_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Withdrawals - StudySmart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin-style.css">
<style>.sidebar{background:linear-gradient(180deg,#1e3c72 0%,#2a5298 100%)}.sidebar-header,.card-header,.nav-link.active{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}.nav-link:hover{background:rgba(102,126,234,.15);color:#667eea}</style>
</head><body>
<nav class="sidebar" id="sidebar"><div class="sidebar-header"><?php render_brand_logo(['href' => "dashboard.php", 'class' => "sidebar-brand", 'size' => "md", 'logo_path' => "../WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png", 'alt' => "StudySmart logo"]); ?></div>
<div class="sidebar-nav">
<div class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
<div class="nav-item"><a href="subscriptions.php" class="nav-link"><i class="fas fa-crown"></i><span>Subscriptions</span></a></div>
<div class="nav-item"><a href="withdrawals.php" class="nav-link active"><i class="fas fa-money-bill-wave"></i><span>Withdrawals</span></a></div>
</div></nav>
<button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="main-content"><div class="top-nav"><h1><i class="fas fa-money-bill-wave"></i>Withdrawals</h1>
<div class="user-info"><div class="user-avatar"><?php echo strtoupper(substr($current_user['first_name'],0,1)); ?></div><span><?php echo htmlspecialchars($current_user['first_name'].' '.$current_user['last_name']); ?></span><a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div>

<?php if($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="row g-4">
<div class="col-lg-4"><div class="card"><div class="card-header"><h5 class="mb-0">Owner Balance</h5></div><div class="card-body">
<p><strong>Total owner credits:</strong> K<?php echo number_format($owner_credits,2); ?></p>
<p><strong>Reserved/withdrawn:</strong> K<?php echo number_format($reserved_withdrawals,2); ?></p>
<p class="mb-3"><strong>Available:</strong> K<?php echo number_format($available_balance,2); ?></p>
<form method="POST"><input type="hidden" name="action" value="request_withdraw">
<label class="form-label">Withdrawal amount (K)</label>
<input type="number" step="0.01" min="1" max="<?php echo htmlspecialchars((string)$available_balance); ?>" name="amount" class="form-control" required>
<small class="text-muted d-block mt-2">Charges apply: Lenco withdrawal fee + your 2% platform fee.</small>
<button class="btn btn-primary w-100 mt-3" type="submit">Request Withdrawal</button>
</form></div></div></div>
<div class="col-lg-8"><div class="card"><div class="card-header"><h5 class="mb-0">Recent Withdrawal Requests</h5></div><div class="card-body table-responsive">
<table class="table table-striped align-middle"><thead><tr><th>Ref</th><th>Amount</th><th>Fees</th><th>Net Payout</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php if(empty($withdrawals)): ?><tr><td colspan="6" class="text-center text-muted">No withdrawals yet.</td></tr><?php else: foreach($withdrawals as $w): ?>
<tr><td><?php echo htmlspecialchars($w['reference'] ?? '-'); ?></td><td>K<?php echo number_format((float)$w['amount_requested'],2); ?></td><td>K<?php echo number_format((float)$w['withdrawal_fee'] + (float)$w['platform_fee'],2); ?></td><td>K<?php echo number_format((float)$w['net_payout'],2); ?></td><td><span class="badge bg-<?php echo $w['status']==='completed'?'success':($w['status']==='failed'?'danger':'warning'); ?>"><?php echo htmlspecialchars(ucfirst($w['status'])); ?></span></td><td><?php echo date('M j, Y H:i', strtotime($w['created_at'])); ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script><script src="assets/js/admin-script.js"></script>
</body></html>
