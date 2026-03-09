<?php
/**
 * Secure Video Streaming Endpoint
 * Prevents direct downloads by streaming video in chunks
 */
// Suppress any output before headers
ob_start();
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';
ob_end_clean();

$auth = new Auth();
$db = new Database();

// Check if user is authenticated
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    die('Unauthorized');
}

$current_user = $auth->getCurrentUser();

// Get video ID from request
$video_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$video_id) {
    http_response_code(400);
    die('Invalid video ID');
}

// Verify user has access to this video
// Check if student is enrolled OR lecturer owns the course
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
    http_response_code(403);
    die('Access denied');
}

// Get file path
$file_path = null;
if (!empty($video['file_path'])) {
    // Handle both relative and absolute paths
    $db_path = $video['file_path'];
    // If path already includes 'uploads/', use it as is, otherwise prepend it
    if (strpos($db_path, 'uploads/') === 0) {
        $file_path = __DIR__ . '/../' . $db_path;
    } elseif (strpos($db_path, '/') === 0) {
        // Absolute path
        $file_path = $db_path;
    } else {
        // Relative path like 'videos/filename.mp4'
        $file_path = __DIR__ . '/../uploads/' . $db_path;
    }
} elseif (!empty($video['video_url'])) {
    // External URL - redirect
    header('Location: ' . $video['video_url']);
    exit;
}

// Debug: Log the path being checked
error_log("Video Stream: Checking file path: " . ($file_path ?? 'null'));
error_log("Video Stream: File exists: " . (file_exists($file_path ?? '') ? 'YES' : 'NO'));

if (!$file_path || !file_exists($file_path)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    // Show helpful error message
    $error_msg = "Video file not found.\n";
    $error_msg .= "Database path: " . ($video['file_path'] ?? 'null') . "\n";
    $error_msg .= "Resolved path: " . ($file_path ?? 'null') . "\n";
    $error_msg .= "File exists: " . (file_exists($file_path ?? '') ? 'YES' : 'NO') . "\n";
    $error_msg .= "Uploads directory: " . __DIR__ . '/../uploads/' . "\n";
    $error_msg .= "Uploads exists: " . (is_dir(__DIR__ . '/../uploads/') ? 'YES' : 'NO') . "\n";
    if (is_dir(__DIR__ . '/../uploads/')) {
        $files = scandir(__DIR__ . '/../uploads/videos/');
        $error_msg .= "Files in videos folder: " . implode(', ', array_slice($files, 2)) . "\n";
    }
    die($error_msg);
}

// Get file info
$file_size = filesize($file_path);
$file_name = basename($file_path);

// Determine MIME type based on file extension
$file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
$mime_types = [
    'mp4' => 'video/mp4',
    'webm' => 'video/webm',
    'ogg' => 'video/ogg',
    'avi' => 'video/x-msvideo',
    'mov' => 'video/quicktime',
    'wmv' => 'video/x-ms-wmv',
    'flv' => 'video/x-flv'
];
$mime_type = $mime_types[$file_ext] ?? 'video/mp4';

// Verify file is readable
if (!is_readable($file_path)) {
    http_response_code(403);
    header('Content-Type: text/plain');
    die('Video file is not readable. Check file permissions.');
}

error_log("Video Stream: Streaming file - Path: $file_path, Size: $file_size, MIME: $mime_type");

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
    header('Access-Control-Allow-Headers: Range');
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit;
}

// Handle range requests for video seeking
$range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;

if ($range) {
    // Parse range header
    if (strpos($range, '=') !== false) {
        list($size_unit, $range_orig) = explode('=', $range, 2);
        if (trim($size_unit) == 'bytes') {
            $ranges = explode(',', $range_orig);
            $range = trim($ranges[0]); // Use first range
        } else {
            $range = '';
        }
    }
} else {
    $range = '';
}

// Set headers for video streaming
header('Content-Type: ' . $mime_type);
header('Accept-Ranges: bytes');
header('Content-Length: ' . $file_size);
header('X-Content-Type-Options: nosniff');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file_path)) . ' GMT');
header('ETag: "' . md5($file_path . '|' . $file_size . '|' . filemtime($file_path)) . '"');
header('X-Offline-Eligible: 1');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');

// Disable download - use inline to play in browser
header('Content-Disposition: inline; filename="' . $file_name . '"');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: Range');

if ($range) {
    // Handle range request
    list($a, $range) = explode('-', $range);
    $a = (int)trim($a);
    $range = trim($range);
    
    if (empty($range)) {
        $range = $file_size - 1;
    } else {
        $range = (int)$range;
    }
    
    // Validate range
    if ($a < 0) $a = 0;
    if ($range >= $file_size) $range = $file_size - 1;
    if ($a > $range) {
        http_response_code(416);
        header('Content-Range: bytes */' . $file_size);
        exit;
    }
    
    $new_length = $range - $a + 1;
    
    header('HTTP/1.1 206 Partial Content');
    header('Content-Length: ' . $new_length);
    header('Content-Range: bytes ' . $a . '-' . $range . '/' . $file_size);
    
    $fp = @fopen($file_path, 'rb');
    if (!$fp) {
        http_response_code(500);
        die('Error opening file');
    }
    
    fseek($fp, $a);
    
    $chunk_size = 8192;
    $bytes_to_read = $new_length;
    
    while ($bytes_to_read > 0 && !feof($fp)) {
        $read_size = min($chunk_size, $bytes_to_read);
        $data = fread($fp, $read_size);
        if ($data === false) break;
        echo $data;
        $bytes_to_read -= $read_size;
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
    
    fclose($fp);
} else {
    // Stream entire file
    $fp = @fopen($file_path, 'rb');
    if (!$fp) {
        http_response_code(500);
        die('Error opening file');
    }
    
    $chunk_size = 8192;
    
    while (!feof($fp)) {
        $data = fread($fp, $chunk_size);
        if ($data === false) break;
        echo $data;
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
    
    fclose($fp);
}

exit;
?>

