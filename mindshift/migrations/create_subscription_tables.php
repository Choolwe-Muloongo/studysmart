<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();

try {
    // Create subscriptions table
    $db->execute("CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subscription_type ENUM('single_course', 'multiple_courses') NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        period_days INT NOT NULL,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Created subscriptions table\n";
    
    // Create promo_codes table (needed before user_subscriptions)
    $db->execute("CREATE TABLE IF NOT EXISTS promo_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        discount_type ENUM('percentage', 'fixed') NOT NULL,
        discount_value DECIMAL(10,2) NOT NULL,
        max_uses INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        valid_from DATETIME NOT NULL,
        valid_until DATETIME NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Created promo_codes table\n";
    
    // Create user_subscriptions table
    $db->execute("CREATE TABLE IF NOT EXISTS user_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subscription_id INT NOT NULL,
        course_id INT NULL,
        status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        promo_code_id INT NULL,
        amount_paid DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (subscription_id) REFERENCES subscriptions(id),
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
        FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id) ON DELETE SET NULL
    )");
    echo "Created user_subscriptions table\n";
    
    // Create subscription_courses table (for multiple courses in single subscription)
    $db->execute("CREATE TABLE IF NOT EXISTS subscription_courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_subscription_id INT NOT NULL,
        course_id INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_subscription_id) REFERENCES user_subscriptions(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        UNIQUE KEY unique_subscription_course (user_subscription_id, course_id)
    )");
    echo "Created subscription_courses table\n";
    
    echo "\nAll subscription tables created successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

