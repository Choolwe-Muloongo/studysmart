<?php
// Test file to verify database connection and class loading
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing StudySmart Platform</h2>";

// Test 1: Check if config file can be loaded
echo "<h3>Test 1: Loading config/database.php</h3>";
try {
    require_once 'config/database.php';
    echo "✅ Config file loaded successfully<br>";
    echo "APP_NAME: " . APP_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Error loading config: " . $e->getMessage() . "<br>";
}

// Test 2: Check if database connection works
echo "<h3>Test 2: Database Connection</h3>";
try {
    $db = new Database();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 3: Check if Auth class can be loaded
echo "<h3>Test 3: Loading Auth class</h3>";
try {
    require_once 'classes/Auth.php';
    echo "✅ Auth class loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading Auth class: " . $e->getMessage() . "<br>";
}

// Test 4: Check if other classes can be loaded
echo "<h3>Test 4: Loading other classes</h3>";
try {
    require_once 'classes/Course.php';
    require_once 'classes/Resource.php';
    require_once 'classes/Notification.php';
    echo "✅ All classes loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading classes: " . $e->getMessage() . "<br>";
}

// Test 5: Check if database tables exist
echo "<h3>Test 5: Database Tables</h3>";
try {
    $db = new Database();
    $tables = ['users', 'courses', 'enrollments', 'resources', 'sessions', 'notifications'];
    
    foreach ($tables as $table) {
        $result = $db->fetch("SHOW TABLES LIKE ?", [$table]);
        if ($result) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' does not exist<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking tables: " . $e->getMessage() . "<br>";
}

echo "<h3>Test Complete!</h3>";
echo "<p>If all tests passed, you can now access the platform.</p>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?> 