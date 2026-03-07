<?php
require_once __DIR__ . '/../config/database.php';

class Resource {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function upload($course_id, $lecturer_id, $title, $description, $file_data, $external_url = null) {
        // Validate course access
        if (!$this->canAccessCourse($lecturer_id, $course_id)) {
            return ['success' => false, 'message' => 'Access denied to this course'];
        }
        
        $file_path = null;
        $file_size = null;
        $type = 'link';
        
        if ($file_data && $file_data['error'] === UPLOAD_ERR_OK) {
            // Handle file upload
            $upload_result = $this->handleFileUpload($file_data);
            if (!$upload_result['success']) {
                return $upload_result;
            }
            
            $file_path = $upload_result['file_path'];
            $file_size = $upload_result['file_size'];
            $type = $upload_result['type'];
        }
        
        $sql = "INSERT INTO resources (course_id, lecturer_id, title, description, type, file_path, file_size, external_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->db->execute($sql, [$course_id, $lecturer_id, $title, $description, $type, $file_path, $file_size, $external_url]);
        
        if ($result) {
            $resource_id = $this->db->lastInsertId();
            
            // Send notifications to enrolled students
            $course = $this->db->fetch("SELECT title FROM courses WHERE id = ?", [$course_id]);
            $lecturer = $this->db->fetch("SELECT first_name, last_name FROM users WHERE id = ?", [$lecturer_id]);
            $lecturer_name = $lecturer['first_name'] . ' ' . $lecturer['last_name'];
            
            $notification = new Notification();
            $notification->notifyNewResource($course_id, $title, $lecturer_name);
            
            return ['success' => true, 'message' => 'Resource uploaded successfully', 'resource_id' => $resource_id];
        } else {
            return ['success' => false, 'message' => 'Failed to save resource'];
        }
    }
    
    public function update($resource_id, $lecturer_id, $title, $description, $external_url = null) {
        // Check if lecturer owns this resource
        $resource = $this->db->fetch("SELECT * FROM resources WHERE id = ? AND lecturer_id = ?", [$resource_id, $lecturer_id]);
        if (!$resource) {
            return ['success' => false, 'message' => 'Resource not found or access denied'];
        }
        
        $sql = "UPDATE resources SET title = ?, description = ?, external_url = ? WHERE id = ?";
        $result = $this->db->execute($sql, [$title, $description, $external_url, $resource_id]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Resource updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update resource'];
        }
    }
    
    public function delete($resource_id, $lecturer_id) {
        // Check if lecturer owns this resource
        $resource = $this->db->fetch("SELECT * FROM resources WHERE id = ? AND lecturer_id = ?", [$resource_id, $lecturer_id]);
        if (!$resource) {
            return ['success' => false, 'message' => 'Resource not found or access denied'];
        }
        
        // Delete file if exists
        if ($resource['file_path'] && file_exists($resource['file_path'])) {
            unlink($resource['file_path']);
        }
        
        $sql = "UPDATE resources SET is_active = 0 WHERE id = ?";
        $result = $this->db->execute($sql, [$resource_id]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Resource deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete resource'];
        }
    }
    
    public function getCourseResources($course_id, $user_id = null, $user_role = null) {
        $sql = "SELECT r.*, u.first_name, u.last_name 
                FROM resources r 
                JOIN users u ON r.lecturer_id = u.id 
                WHERE r.course_id = ? AND r.is_active = 1";
        
        // Students can only see resources if they're enrolled
        if ($user_role === 'student') {
            $enrolled = $this->db->fetch("SELECT id FROM enrollments WHERE course_id = ? AND student_id = ? AND is_active = 1", [$course_id, $user_id]);
            if (!$enrolled) {
                return [];
            }
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($sql, [$course_id]);
    }
    
    public function getResource($resource_id, $user_id, $user_role) {
        $resource = $this->db->fetch("SELECT r.*, c.title as course_title, u.first_name, u.last_name 
                                      FROM resources r 
                                      JOIN courses c ON r.course_id = c.id 
                                      JOIN users u ON r.lecturer_id = u.id 
                                      WHERE r.id = ? AND r.is_active = 1", [$resource_id]);
        
        if (!$resource) {
            return null;
        }
        
        // Check access permissions
        if ($user_role === 'student') {
            $enrolled = $this->db->fetch("SELECT id FROM enrollments WHERE course_id = ? AND student_id = ? AND is_active = 1", [$resource['course_id'], $user_id]);
            if (!$enrolled) {
                return null;
            }
        } elseif ($user_role === 'lecturer' && $resource['lecturer_id'] != $user_id) {
            return null;
        }
        
        return $resource;
    }
    
    public function downloadResource($resource_id, $user_id, $user_role) {
        $resource = $this->getResource($resource_id, $user_id, $user_role);
        
        if (!$resource || !$resource['file_path'] || !file_exists($resource['file_path'])) {
            return ['success' => false, 'message' => 'File not found'];
        }
        
        // Track download
        if ($user_role === 'student') {
            $this->trackAccess($resource_id, $user_id, 'download');
        }
        
        // Update download count
        $this->db->execute("UPDATE resources SET downloads_count = downloads_count + 1 WHERE id = ?", [$resource_id]);
        
        return [
            'success' => true,
            'file_path' => $resource['file_path'],
            'filename' => basename($resource['file_path']),
            'title' => $resource['title']
        ];
    }
    
    public function viewResource($resource_id, $user_id, $user_role) {
        $resource = $this->getResource($resource_id, $user_id, $user_role);
        
        if (!$resource) {
            return ['success' => false, 'message' => 'Resource not found or access denied'];
        }
        
        // Track view
        if ($user_role === 'student') {
            $this->trackAccess($resource_id, $user_id, 'view');
        }
        
        // Update view count
        $this->db->execute("UPDATE resources SET views_count = views_count + 1 WHERE id = ?", [$resource_id]);
        
        return ['success' => true, 'resource' => $resource];
    }
    
    public function getResourceStats($resource_id, $lecturer_id) {
        // Verify lecturer owns this resource
        $resource = $this->db->fetch("SELECT * FROM resources WHERE id = ? AND lecturer_id = ?", [$resource_id, $lecturer_id]);
        if (!$resource) {
            return null;
        }
        
        $stats = [];
        
        // View/Download counts
        $stats['views'] = $resource['views_count'];
        $stats['downloads'] = $resource['downloads_count'];
        
        // Student access details
        $stats['student_access'] = $this->db->fetchAll("
            SELECT u.first_name, u.last_name, ra.access_type, ra.accessed_at,
                   COUNT(*) as access_count
            FROM resource_access ra
            JOIN users u ON ra.student_id = u.id
            WHERE ra.resource_id = ?
            GROUP BY ra.student_id, ra.access_type
            ORDER BY ra.accessed_at DESC
        ", [$resource_id]);
        
        return $stats;
    }
    
    public function getLecturerResources($lecturer_id) {
        $sql = "SELECT r.*, c.title as course_title, c.course_code,
                       (SELECT COUNT(DISTINCT student_id) FROM resource_access ra WHERE ra.resource_id = r.id) as unique_views
                FROM resources r
                JOIN courses c ON r.course_id = c.id
                WHERE r.lecturer_id = ? AND r.is_active = 1
                ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($sql, [$lecturer_id]);
    }
    
    private function handleFileUpload($file_data) {
        $upload_dir = UPLOAD_PATH;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validate file size
        if ($file_data['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size exceeds maximum limit of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
        }
        
        // Validate file type
        $file_extension = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, ALLOWED_FILE_TYPES)) {
            return ['success' => false, 'message' => 'File type not allowed. Allowed types: ' . implode(', ', ALLOWED_FILE_TYPES)];
        }
        
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file_data['tmp_name'], $file_path)) {
            // Determine resource type
            $type = 'document';
            if (in_array($file_extension, ['mp4', 'avi', 'mov', 'wmv', 'flv'])) {
                $type = 'video';
            } elseif (in_array($file_extension, ['pdf'])) {
                $type = 'pdf';
            }
            
            return [
                'success' => true,
                'file_path' => $file_path,
                'file_size' => $file_data['size'],
                'type' => $type
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file'];
        }
    }
    
    private function trackAccess($resource_id, $student_id, $access_type) {
        $sql = "INSERT INTO resource_access (resource_id, student_id, access_type) VALUES (?, ?, ?)";
        $this->db->execute($sql, [$resource_id, $student_id, $access_type]);
    }
    
    private function canAccessCourse($lecturer_id, $course_id) {
        $course = $this->db->fetch("SELECT lecturer_id FROM courses WHERE id = ? AND is_active = 1", [$course_id]);
        return $course && $course['lecturer_id'] == $lecturer_id;
    }
}
?> 