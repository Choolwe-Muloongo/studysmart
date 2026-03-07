<?php
// Subscription access control helper
require_once __DIR__ . '/../classes/Subscription.php';

function checkSubscriptionAccess($user_id, $course_id = null, $redirect = true) {
    $subscription = new Subscription();
    
    if (!$subscription->hasActiveSubscription($user_id, $course_id)) {
        if ($redirect) {
            $_SESSION['subscription_required'] = true;
            $_SESSION['redirect_after_subscription'] = $_SERVER['REQUEST_URI'];
            header('Location: ../subscription.php');
            exit();
        }
        return false;
    }
    
    return true;
}

function requireSubscription($course_id = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
    
    checkSubscriptionAccess($_SESSION['user_id'], $course_id, true);
}

?>

