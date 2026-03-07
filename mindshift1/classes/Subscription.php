<?php
require_once __DIR__ . '/../config/database.php';

class Subscription {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Check if user has active subscription
    public function hasActiveSubscription($user_id, $course_id = null) {
        // If course is not specified, ANY active subscription counts
        if ($course_id === null) {
            $sql = "SELECT 1 
                    FROM user_subscriptions 
                    WHERE user_id = ? 
                    AND status = 'active' 
                    AND end_date > NOW()
                    LIMIT 1";
            return (bool)$this->db->fetch($sql, [$user_id]);
        }

        // Course-specific check
        $sql = "SELECT us.id 
                FROM user_subscriptions us
                JOIN subscriptions s ON us.subscription_id = s.id
                WHERE us.user_id = ? 
                AND us.status = 'active'
                AND us.end_date > NOW()
                AND (
                    (s.subscription_type = 'single_course' AND us.course_id = ?) OR
                    (s.subscription_type = 'multiple_courses' AND (
                        us.course_id = ? OR 
                        EXISTS (SELECT 1 FROM subscription_courses sc WHERE sc.user_subscription_id = us.id AND sc.course_id = ?)
                    ))
                )
                LIMIT 1";
        
        $subscription = $this->db->fetch($sql, [$user_id, $course_id, $course_id, $course_id]);
        return $subscription !== false;
    }
    
    // Get user's active subscriptions
    public function getUserSubscriptions($user_id) {
        return $this->db->fetchAll("SELECT us.*, s.subscription_type, s.description, c.title as course_title
                                    FROM user_subscriptions us
                                    JOIN subscriptions s ON us.subscription_id = s.id
                                    LEFT JOIN courses c ON us.course_id = c.id
                                    WHERE us.user_id = ?
                                    ORDER BY us.created_at DESC", [$user_id]);
    }
    
    // Get active subscription for a specific course
    public function getActiveSubscriptionForCourse($user_id, $course_id) {
        return $this->db->fetch("SELECT us.*, s.subscription_type 
                                 FROM user_subscriptions us
                                 JOIN subscriptions s ON us.subscription_id = s.id
                                 WHERE us.user_id = ? 
                                 AND us.status = 'active'
                                 AND us.end_date > NOW()
                                 AND (
                                     (s.subscription_type = 'single_course' AND us.course_id = ?) OR
                                     (s.subscription_type = 'multiple_courses' AND (
                                         us.course_id = ? OR 
                                         EXISTS (SELECT 1 FROM subscription_courses sc WHERE sc.user_subscription_id = us.id AND sc.course_id = ?)
                                     ))
                                 )
                                 LIMIT 1", [$user_id, $course_id, $course_id, $course_id]);
    }
    
    // Helper: ensure enrollment exists for a course
    private function ensureEnrollment($user_id, $course_id) {
        if (!$course_id) {
            return;
        }

        $existing = $this->db->fetch(
            "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?",
            [$user_id, $course_id]
        );

        if (!$existing) {
            $this->db->execute(
                "INSERT INTO enrollments (student_id, course_id, is_active) VALUES (?, ?, 1)",
                [$user_id, $course_id]
            );
        }
    }

    // Create a subscription
    public function createSubscription($user_id, $subscription_id, $course_id = null, $promo_code = null) {
        $subscription = $this->db->fetch("SELECT * FROM subscriptions WHERE id = ? AND is_active = 1", [$subscription_id]);
        if (!$subscription) {
            return ['success' => false, 'message' => 'Invalid subscription plan'];
        }

        // Prevent duplicate active single-course subscription for the same course
        if ($subscription['subscription_type'] === 'single_course' && $course_id) {
            $existing = $this->db->fetch(
                "SELECT us.id 
                 FROM user_subscriptions us
                 JOIN subscriptions s ON us.subscription_id = s.id
                 WHERE us.user_id = ? 
                 AND us.status = 'active'
                 AND us.end_date > NOW()
                 AND s.subscription_type = 'single_course'
                 AND us.course_id = ?",
                [$user_id, $course_id]
            );

            if ($existing) {
                return ['success' => false, 'message' => 'You already have an active subscription for this course'];
            }
        }
        
        // Calculate price with promo code
        $price = $subscription['price'];
        $promo_code_id = null;
        
        if ($promo_code) {
            $promo = $this->validatePromoCode($promo_code);
            if ($promo['valid']) {
                $promo_code_id = $promo['id'];
                if ($promo['discount_type'] === 'percentage') {
                    $price = $price * (1 - $promo['discount_value'] / 100);
                } else {
                    $price = max(0, $price - $promo['discount_value']);
                }
            }
        }
        
        // Calculate dates
        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime("+{$subscription['period_days']} days"));
        
        // Insert subscription
        $sql = "INSERT INTO user_subscriptions (user_id, subscription_id, course_id, start_date, end_date, promo_code_id, amount_paid) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $result = $this->db->execute($sql, [$user_id, $subscription_id, $course_id, $start_date, $end_date, $promo_code_id, $price]);
        
        if ($result) {
            $user_subscription_id = $this->db->lastInsertId();
            
            // Update promo code usage
            if ($promo_code_id) {
                $this->db->execute("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?", [$promo_code_id]);
            }

            // Ensure enrollment for this course (if specified)
            if ($course_id) {
                $this->ensureEnrollment($user_id, $course_id);
            }
            
            return ['success' => true, 'message' => 'Subscription created successfully', 'subscription_id' => $user_subscription_id];
        }
        
        return ['success' => false, 'message' => 'Failed to create subscription'];
    }
    
    // Add course to existing subscription
    public function addCourseToSubscription($user_subscription_id, $course_id) {
        // Check if subscription is active
        $subscription = $this->db->fetch("SELECT us.*, s.subscription_type 
                                          FROM user_subscriptions us
                                          JOIN subscriptions s ON us.subscription_id = s.id
                                          WHERE us.id = ? AND us.status = 'active' AND us.end_date > NOW()", [$user_subscription_id]);
        
        if (!$subscription) {
            return ['success' => false, 'message' => 'Subscription not found or expired'];
        }
        
        // Check if course already added
        $existing = $this->db->fetch("SELECT * FROM subscription_courses WHERE user_subscription_id = ? AND course_id = ?", 
                                     [$user_subscription_id, $course_id]);
        if ($existing) {
            return ['success' => false, 'message' => 'Course already added to subscription'];
        }
        
        // Add course (for both single_course and multiple_courses subscriptions)
        $result = $this->db->execute(
            "INSERT INTO subscription_courses (user_subscription_id, course_id) VALUES (?, ?)",
            [$user_subscription_id, $course_id]
        );
        
        if ($result) {
            // Also make sure the student is enrolled in this course
            $this->ensureEnrollment($subscription['user_id'], $course_id);
            return ['success' => true, 'message' => 'Course added successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to add course'];
    }
    
    // Validate promo code
    public function validatePromoCode($code) {
        $promo = $this->db->fetch("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1", [$code]);
        
        if (!$promo) {
            return ['valid' => false, 'message' => 'Invalid promo code'];
        }
        
        $now = date('Y-m-d H:i:s');
        if ($now < $promo['valid_from'] || $now > $promo['valid_until']) {
            return ['valid' => false, 'message' => 'Promo code has expired'];
        }
        
        if ($promo['max_uses'] && $promo['used_count'] >= $promo['max_uses']) {
            return ['valid' => false, 'message' => 'Promo code has reached maximum uses'];
        }
        
        return [
            'valid' => true,
            'id' => $promo['id'],
            'discount_type' => $promo['discount_type'],
            'discount_value' => $promo['discount_value']
        ];
    }
    
    // Get all subscription plans
    public function getSubscriptionPlans() {
        return $this->db->fetchAll("SELECT * FROM subscriptions WHERE is_active = 1 ORDER BY subscription_type, price");
    }
    
    // Get subscription plan by ID
    public function getSubscriptionPlan($id) {
        return $this->db->fetch("SELECT * FROM subscriptions WHERE id = ? AND is_active = 1", [$id]);
    }
    
    // Update subscription status (expire old ones)
    public function updateExpiredSubscriptions() {
        return $this->db->execute("UPDATE user_subscriptions SET status = 'expired' WHERE status = 'active' AND end_date < NOW()");
    }
}

?>

