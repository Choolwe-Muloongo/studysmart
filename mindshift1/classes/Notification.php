<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($user_id, $title, $message, $type = 'info') {
        $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
        $result = $this->db->execute($sql, [$user_id, $title, $message, $type]);
        
        if ($result) {
            // Here you could add real-time notification via WebSocket or Server-Sent Events
            $this->sendRealTimeNotification($user_id, $title, $message, $type);
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    public function createBulk($user_ids, $title, $message, $type = 'info') {
        $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES ";
        $values = [];
        $params = [];
        
        foreach ($user_ids as $user_id) {
            $values[] = "(?, ?, ?, ?)";
            $params = array_merge($params, [$user_id, $title, $message, $type]);
        }
        
        $sql .= implode(', ', $values);
        return $this->db->execute($sql, $params);
    }
    
    public function getUserNotifications($user_id, $limit = 50, $unread_only = false) {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$user_id];
        
        if ($unread_only) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUnreadCount($user_id) {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$user_id]);
        return $result['count'] ?? 0;
    }
    
    public function markAsRead($notification_id, $user_id) {
        return $this->db->execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$notification_id, $user_id]);
    }
    
    public function markAllAsRead($user_id) {
        return $this->db->execute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$user_id]);
    }
    
    public function delete($notification_id, $user_id) {
        return $this->db->execute("DELETE FROM notifications WHERE id = ? AND user_id = ?", [$notification_id, $user_id]);
    }
    
    // Course-specific notifications
    public function notifyNewResource($course_id, $resource_title, $lecturer_name) {
        $students = $this->db->fetchAll("
            SELECT u.id, u.first_name, u.last_name, u.email 
            FROM users u 
            JOIN enrollments e ON u.id = e.student_id 
            WHERE e.course_id = ? AND e.is_active = 1 AND u.is_active = 1
        ", [$course_id]);
        
        $title = "New Resource Available";
        $message = "A new resource '{$resource_title}' has been uploaded by {$lecturer_name}";
        
        foreach ($students as $student) {
            $this->create($student['id'], $title, $message, 'info');
            $this->sendEmail($student['email'], $title, $message, $student['first_name']);
        }
    }
    
    public function notifyNewSession($course_id, $session_title, $session_date, $lecturer_name) {
        $students = $this->db->fetchAll("
            SELECT u.id, u.first_name, u.last_name, u.email 
            FROM users u 
            JOIN enrollments e ON u.id = e.student_id 
            WHERE e.course_id = ? AND e.is_active = 1 AND u.is_active = 1
        ", [$course_id]);
        
        $title = "New Class Session Scheduled";
        $message = "'{$session_title}' has been scheduled for " . date('M d, Y at H:i', strtotime($session_date)) . " by {$lecturer_name}";
        
        foreach ($students as $student) {
            $this->create($student['id'], $title, $message, 'info');
            $this->sendEmail($student['email'], $title, $message, $student['first_name']);
        }
    }
    
    public function notifyEnrollment($student_id, $course_title, $lecturer_name) {
        $title = "Course Enrollment Confirmed";
        $message = "You have been successfully enrolled in '{$course_title}' by {$lecturer_name}";
        
        $this->create($student_id, $title, $message, 'success');
        
        $student = $this->db->fetch("SELECT email, first_name FROM users WHERE id = ?", [$student_id]);
        if ($student) {
            $this->sendEmail($student['email'], $title, $message, $student['first_name']);
        }
    }
    
    public function notifyLecturerAssignment($lecturer_id, $course_title, $admin_name) {
        $title = "Course Assignment";
        $message = "You have been assigned to teach '{$course_title}' by {$admin_name}";
        
        $this->create($lecturer_id, $title, $message, 'info');
        
        $lecturer = $this->db->fetch("SELECT email, first_name FROM users WHERE id = ?", [$lecturer_id]);
        if ($lecturer) {
            $this->sendEmail($lecturer['email'], $title, $message, $lecturer['first_name']);
        }
    }
    
    private function sendRealTimeNotification($user_id, $title, $message, $type) {
        // This is where you would implement WebSocket or Server-Sent Events
        // For now, we'll just store it for JavaScript polling
        $notification_data = [
            'user_id' => $user_id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'timestamp' => time()
        ];
        
        // Save to temporary file for JavaScript polling (simple implementation)
        $file = "temp/notifications_{$user_id}.json";
        $existing = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $existing[] = $notification_data;
        
        // Keep only last 10 notifications in temp file
        $existing = array_slice($existing, -10);
        
        if (!is_dir('temp')) {
            mkdir('temp', 0755, true);
        }
        
        file_put_contents($file, json_encode($existing));
    }
    
    private function sendEmail($to, $subject, $message, $first_name = '') {
        // Simple email implementation
        $headers = [
            'From: ' . APP_NAME . ' <noreply@mindshift.com>',
            'Reply-To: support@mindshift.com',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $body = "
        <html>
        <head>
            <title>{$subject}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { padding: 10px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . APP_NAME . "</h1>
                </div>
                <div class='content'>
                    " . ($first_name ? "<p>Dear {$first_name},</p>" : "") . "
                    <p>{$message}</p>
                    <p>Please log in to your account for more details.</p>
                    <p><a href='" . APP_URL . "' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Visit Platform</a></p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
                    <p>Contact: support@fierotechnologies.com | +260 974 286 888</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // In production, use a proper email service like PHPMailer or SendGrid
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
    
    public function getRecentNotifications($user_id) {
        $file = "temp/notifications_{$user_id}.json";
        if (file_exists($file)) {
            $notifications = json_decode(file_get_contents($file), true);
            // Clear the file after reading
            unlink($file);
            return $notifications;
        }
        return [];
    }
}
?> 