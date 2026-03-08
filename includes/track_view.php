<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$db = new Database();

header('Content-Type: application/json');
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$current_user = $auth->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['id']) || empty($input['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$resource_id = (int)$input['id'];
$type = $input['type'];

$allowed_types = ['video', 'document'];
if (!in_array($type, $allowed_types, true)) {
    echo json_encode(['success' => false, 'message' => 'Unsupported type']);
    exit;
}

$type_condition = $type === 'video' ? "r.resource_type = 'video'" : "r.resource_type != 'video'";

// Verify resource exists and user has access
$resource = $db->fetch("SELECT r.*, c.lecturer_id FROM resources r JOIN courses c ON r.course_id = c.id JOIN enrollments e ON c.id = e.course_id WHERE r.id = ? AND {$type_condition} AND r.is_active = 1 AND e.student_id = ? AND e.is_active = 1", [$resource_id, $current_user['id']]);
if (!$resource) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Resource not found or access denied']);
    exit;
}

// Insert access log and increment view count
try {
    $db->execute("INSERT INTO resource_access (resource_id, student_id, access_type) VALUES (?, ?, 'view')", [$resource_id, $current_user['id']]);
    $db->execute("UPDATE resources SET views_count = views_count + 1 WHERE id = ?", [$resource_id]);
    $new = $db->fetch("SELECT views_count FROM resources WHERE id = ?", [$resource_id]);
    echo json_encode(['success' => true, 'views' => (int)$new['views_count']]);
} catch (Exception $e) {
    error_log('track_view error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
