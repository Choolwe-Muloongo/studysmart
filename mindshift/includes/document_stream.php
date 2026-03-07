<?php
/**
 * Secure Document Streaming Endpoint
 * Prevents direct downloads by streaming documents
 */
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

// Get document ID from request
$doc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$doc_id) {
    http_response_code(400);
    die('Invalid document ID');
}

// Verify user has access to this document
$document = $db->fetch("
    SELECT r.*, c.title as course_title, c.lecturer_id
    FROM resources r 
    JOIN courses c ON r.course_id = c.id 
    LEFT JOIN enrollments e ON c.id = e.course_id AND e.student_id = ? AND e.is_active = 1
    WHERE r.id = ? 
    AND r.resource_type != 'video' 
    AND r.is_active = 1
    AND (
        (e.student_id = ? AND e.is_active = 1) 
        OR c.lecturer_id = ?
    )
", [$current_user['id'], $doc_id, $current_user['id'], $current_user['id']]);

if (!$document) {
    http_response_code(403);
    die('Access denied');
}

// Get file path
$file_path = null;
if (!empty($document['file_path'])) {
    $db_path = $document['file_path'];
    if (strpos($db_path, 'uploads/') === 0) {
        $file_path = __DIR__ . '/../' . $db_path;
    } elseif (strpos($db_path, '/') === 0) {
        $file_path = $db_path;
    } else {
        $file_path = __DIR__ . '/../uploads/' . $db_path;
    }
}

if (!$file_path || !file_exists($file_path)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    die('Document file not found');
}

// Get file info
$file_size = filesize($file_path);
$file_name = basename($file_path);
$file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Determine MIME type
$mime_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'txt' => 'text/plain',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif'
];
$mime_type = $mime_types[$file_ext] ?? mime_content_type($file_path) ?? 'application/octet-stream';

// Set headers - prevent download, force inline viewing
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . $file_size);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

// CRITICAL: Use inline to display in browser, not attachment
header('Content-Disposition: inline; filename="' . $file_name . '"');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: Range');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Stream file
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
exit;
?>

