<?php
/**
 * Test script to verify video file paths and permissions
 * Access this directly in browser: /includes/test_video.php?id=X
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$db = new Database();

if (!$auth->isLoggedIn()) {
    die('Please log in first');
}

$current_user = $auth->getCurrentUser();
$video_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$video_id) {
    die('Please provide video ID: ?id=X');
}

// Get video from database
$video = $db->fetch("
    SELECT r.*, c.title as course_title, c.lecturer_id
    FROM resources r 
    JOIN courses c ON r.course_id = c.id 
    LEFT JOIN enrollments e ON c.id = e.course_id AND e.student_id = ? AND e.is_active = 1
    WHERE r.id = ? 
    AND r.resource_type = 'video' 
    AND r.is_active = 1
    AND (
        (e.student_id = ? AND e.is_active = 1) 
        OR c.lecturer_id = ?
    )
", [$current_user['id'], $video_id, $current_user['id'], $current_user['id']]);

if (!$video) {
    die('Video not found or access denied');
}

echo "<h2>Video Debug Information</h2>";
echo "<pre>";
echo "Video ID: " . $video['id'] . "\n";
echo "Title: " . $video['title'] . "\n";
echo "Database file_path: " . ($video['file_path'] ?? 'NULL') . "\n";
echo "Database video_url: " . ($video['video_url'] ?? 'NULL') . "\n";
echo "\n";

// Check file path resolution
$db_path = $video['file_path'] ?? '';
$resolved_path = null;

if (!empty($db_path)) {
    if (strpos($db_path, 'uploads/') === 0) {
        $resolved_path = __DIR__ . '/../' . $db_path;
    } elseif (strpos($db_path, '/') === 0) {
        $resolved_path = $db_path;
    } else {
        $resolved_path = __DIR__ . '/../uploads/' . $db_path;
    }
    
    echo "Resolved file path: " . $resolved_path . "\n";
    echo "File exists: " . (file_exists($resolved_path) ? 'YES' : 'NO') . "\n";
    echo "File is readable: " . (is_readable($resolved_path) ? 'YES' : 'NO') . "\n";
    echo "File size: " . (file_exists($resolved_path) ? filesize($resolved_path) . ' bytes' : 'N/A') . "\n";
    echo "File permissions: " . (file_exists($resolved_path) ? substr(sprintf('%o', fileperms($resolved_path)), -4) : 'N/A') . "\n";
} else {
    echo "No file_path in database\n";
}

echo "\n";
echo "Uploads directory: " . __DIR__ . '/../uploads/' . "\n";
echo "Uploads exists: " . (is_dir(__DIR__ . '/../uploads/') ? 'YES' : 'NO') . "\n";
echo "Uploads readable: " . (is_readable(__DIR__ . '/../uploads/') ? 'YES' : 'NO') . "\n";

if (is_dir(__DIR__ . '/../uploads/videos/')) {
    echo "\nFiles in uploads/videos/:\n";
    $files = scandir(__DIR__ . '/../uploads/videos/');
    foreach (array_slice($files, 2) as $file) {
        $full_path = __DIR__ . '/../uploads/videos/' . $file;
        echo "  - $file (" . filesize($full_path) . " bytes)\n";
    }
}

echo "\n";
echo "Streaming URL: " . APP_URL . "/includes/video_stream.php?id=" . $video_id . "\n";
echo "</pre>";

// Try to test the streaming endpoint
echo "<h3>Test Streaming</h3>";
echo "<p><a href='video_stream.php?id=" . $video_id . "' target='_blank'>Click here to test video_stream.php</a></p>";
?>

