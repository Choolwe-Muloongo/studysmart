<?php
require_once __DIR__ . '/../config/database.php';

class Session {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($course_id, $lecturer_id, $title, $description, $session_date, $duration_minutes = 60, $meeting_link = '', $meeting_platform = 'zoom', $max_students = 10, $session_type = 'tutoring', $location = 'Online') {
        try {
            $result = $this->db->execute("
                INSERT INTO sessions (course_id, lecturer_id, title, description, session_date, duration_minutes, meeting_link, meeting_platform, max_students, session_type, location, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ", [$course_id, $lecturer_id, $title, $description, $session_date, $duration_minutes, $meeting_link, $meeting_platform, $max_students, $session_type, $location]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Session created successfully', 'session_id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create session'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function update($session_id, $course_id, $lecturer_id, $title, $description, $session_date, $duration_minutes = 60, $meeting_link = '', $meeting_platform = 'zoom', $max_students = 10, $session_type = 'tutoring', $location = 'Online', $is_active = 1) {
        try {
            $result = $this->db->execute("
                UPDATE sessions 
                SET course_id = ?, lecturer_id = ?, title = ?, description = ?, session_date = ?, duration_minutes = ?, meeting_link = ?, meeting_platform = ?, max_students = ?, session_type = ?, location = ?, is_active = ?
                WHERE id = ?
            ", [$course_id, $lecturer_id, $title, $description, $session_date, $duration_minutes, $meeting_link, $meeting_platform, $max_students, $session_type, $location, $is_active, $session_id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Session updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update session'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function delete($session_id) {
        try {
            $result = $this->db->execute("UPDATE sessions SET is_active = 0 WHERE id = ?", [$session_id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Session deactivated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to deactivate session'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function getById($session_id) {
        return $this->db->fetch("SELECT s.*, c.title as course_title, c.course_code, u.first_name, u.last_name 
                                 FROM sessions s 
                                 LEFT JOIN courses c ON s.course_id = c.id 
                                 LEFT JOIN users u ON s.lecturer_id = u.id 
                                 WHERE s.id = ?", [$session_id]);
    }
    
    public function getAll($filters = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (isset($filters['course_id'])) {
            $where .= " AND s.course_id = ?";
            $params[] = $filters['course_id'];
        }
        
        if (isset($filters['lecturer_id'])) {
            $where .= " AND s.lecturer_id = ?";
            $params[] = $filters['lecturer_id'];
        }
        
        if (isset($filters['is_active'])) {
            $where .= " AND s.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql = "SELECT s.*, c.title as course_title, c.course_code, u.first_name, u.last_name 
                FROM sessions s 
                LEFT JOIN courses c ON s.course_id = c.id 
                LEFT JOIN users u ON s.lecturer_id = u.id 
                $where ORDER BY s.session_date DESC";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUpcoming($limit = 10, $lecturer_id = null) {
        $sql = "SELECT s.*, c.title as course_title, c.course_code, u.first_name, u.last_name 
                FROM sessions s 
                LEFT JOIN courses c ON s.course_id = c.id 
                LEFT JOIN users u ON s.lecturer_id = u.id 
                WHERE s.session_date > NOW() AND s.is_active = 1";
        $params = [];
        
        if ($lecturer_id) {
            $sql .= " AND s.lecturer_id = ?";
            $params[] = $lecturer_id;
        }
        
        $sql .= " ORDER BY s.session_date ASC LIMIT " . intval($limit);
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getPast($limit = 10, $lecturer_id = null) {
        $sql = "SELECT s.*, c.title as course_title, c.course_code, u.first_name, u.last_name 
                FROM sessions s 
                LEFT JOIN courses c ON s.course_id = c.id 
                LEFT JOIN users u ON s.lecturer_id = u.id 
                WHERE s.session_date < NOW() AND s.is_active = 1";
        $params = [];
        
        if ($lecturer_id) {
            $sql .= " AND s.lecturer_id = ?";
            $params[] = $lecturer_id;
        }
        
        $sql .= " ORDER BY s.session_date DESC LIMIT " . intval($limit);
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getByLecturer($lecturer_id) {
        return $this->db->fetchAll("
            SELECT s.*, c.title as course_title, c.course_code 
            FROM sessions s 
            LEFT JOIN courses c ON s.course_id = c.id 
            WHERE s.lecturer_id = ? AND s.is_active = 1 
            ORDER BY s.session_date DESC
        ", [$lecturer_id]);
    }
    
    public function getByCourse($course_id) {
        return $this->db->fetchAll("
            SELECT s.*, u.first_name, u.last_name 
            FROM sessions s 
            LEFT JOIN users u ON s.lecturer_id = u.id 
            WHERE s.course_id = ? AND s.is_active = 1 
            ORDER BY s.session_date DESC
        ", [$course_id]);
    }
    
    public function getStudentSessions($student_id) {
        return $this->db->fetchAll("
            SELECT s.*, c.title as course_title, c.course_code, u.first_name, u.last_name 
            FROM sessions s 
            JOIN courses c ON s.course_id = c.id 
            JOIN enrollments e ON c.id = e.course_id 
            LEFT JOIN users u ON s.lecturer_id = u.id 
            WHERE e.student_id = ? AND e.is_active = 1 AND s.is_active = 1 
            ORDER BY s.session_date DESC
        ", [$student_id]);
    }
    
    public function getUpcomingStudentSessions($student_id, $limit = 10) {
        return $this->db->fetchAll("
            SELECT s.*, c.title as course_title, c.course_code, u.first_name, u.last_name 
            FROM sessions s 
            JOIN courses c ON s.course_id = c.id 
            JOIN enrollments e ON c.id = e.course_id 
            LEFT JOIN users u ON s.lecturer_id = u.id 
            WHERE e.student_id = ? AND e.is_active = 1 AND s.is_active = 1 AND s.session_date > NOW()
            ORDER BY s.session_date ASC 
            LIMIT " . intval($limit) . "
        ", [$student_id]);
    }
}
?>
