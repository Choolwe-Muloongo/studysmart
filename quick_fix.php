<?php
require_once 'config/database.php';

$db = new Database();

echo "🔧 Quick fix for missing session_type column...\n";

try {
    // Check if session_type column exists
    $result = $db->fetch("SHOW COLUMNS FROM sessions LIKE 'session_type'");
    
    if (!$result) {
        echo "Adding session_type column to sessions table...\n";
        $db->execute("ALTER TABLE sessions ADD COLUMN session_type VARCHAR(50) DEFAULT 'tutoring'");
        echo "✅ session_type column added successfully!\n";
    } else {
        echo "✅ session_type column already exists\n";
    }
    
    // Also check and add other missing columns
    $columns_to_check = [
        'max_students' => 'ALTER TABLE sessions ADD COLUMN max_students INT DEFAULT 10',
        'status' => 'ALTER TABLE sessions ADD COLUMN status ENUM("scheduled", "ongoing", "completed", "cancelled") DEFAULT "scheduled"',
        'location' => 'ALTER TABLE sessions ADD COLUMN location VARCHAR(255) DEFAULT "Online"'
    ];
    
    foreach ($columns_to_check as $column => $sql) {
        $result = $db->fetch("SHOW COLUMNS FROM sessions LIKE '$column'");
        if (!$result) {
            echo "Adding $column column...\n";
            $db->execute($sql);
            echo "✅ $column column added!\n";
        } else {
            echo "✅ $column column already exists\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Database structure should now be complete!\n";
?>
