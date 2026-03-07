<?php
require_once __DIR__ . '/../config/database.php';

class Course {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($course_code, $title, $description, $lecturer_id = null) {
        // Check if course code already exists
        $existing = $this->db->fetch("SELECT id FROM courses WHERE course_code = ?", [$course_code]);
        if ($existing) {
            return ['success' => false, 'message' => 'Course code already exists'];
        }
        
        $sql = "INSERT INTO courses (course_code, title, description, lecturer_id) VALUES (?, ?, ?, ?)";
        $result = $this->db->execute($sql, [$course_code, $title, $description, $lecturer_id]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Course created successfully', 'course_id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Failed to create course'];
        }
    }
    
    public function update($course_id, $course_code, $title, $description, $lecturer_id = null) {
        // Check if course code exists for other courses
        $existing = $this->db->fetch("SELECT id FROM courses WHERE course_code = ? AND id != ?", [$course_code, $course_id]);
        if ($existing) {
            return ['success' => false, 'message' => 'Course code already exists for another course'];
        }
        
        $sql = "UPDATE courses SET course_code = ?, title = ?, description = ?, lecturer_id = ? WHERE id = ?";
        $result = $this->db->execute($sql, [$course_code, $title, $description, $lecturer_id, $course_id]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Course updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update course'];
        }
    }
    
    public function delete($course_id) {
        $sql = "UPDATE courses SET is_active = 0 WHERE id = ?";
        $result = $this->db->execute($sql, [$course_id]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Course deactivated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to deactivate course'];
        }
    }
    
    public function getAll($active_only = true) {
        $sql = "SELECT c.*, u.first_name, u.last_name, u.email as lecturer_email,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.is_active = 1) as student_count
                FROM courses c 
                LEFT JOIN users u ON c.lecturer_id = u.id";
        
        if ($active_only) {
            $sql .= " WHERE c.is_active = 1";
        }
        
        $sql .= " ORDER BY c.title";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getById($course_id) {
        $sql = "SELECT c.*, u.first_name, u.last_name, u.email as lecturer_email, u.whatsapp_number,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.is_active = 1) as student_count
                FROM courses c 
                LEFT JOIN users u ON c.lecturer_id = u.id 
                WHERE c.id = ?";
        
        return $this->db->fetch($sql, [$course_id]);
    }
    
    public function getByLecturer($lecturer_id) {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.is_active = 1) as student_count
                FROM courses c 
                WHERE c.lecturer_id = ? AND c.is_active = 1 
                ORDER BY c.title";
        
        return $this->db->fetchAll($sql, [$lecturer_id]);
    }
    
    public function assignLecturer($course_id, $lecturer_id) {
        $sql = "UPDATE courses SET lecturer_id = ? WHERE id = ?";
        $result = $this->db->execute($sql, [$lecturer_id, $course_id]);
        
        if ($result) {
            // Send notification to lecturer
            $course = $this->getById($course_id);
            $admin = $this->db->fetch("SELECT first_name, last_name FROM users WHERE id = ?", [$_SESSION['user_id'] ?? 0]);
            $admin_name = $admin ? $admin['first_name'] . ' ' . $admin['last_name'] : 'Admin';
            
            $notification = new Notification();
            $notification->notifyLecturerAssignment($lecturer_id, $course['title'], $admin_name);
            
            return ['success' => true, 'message' => 'Lecturer assigned successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to assign lecturer'];
        }
    }
    
    public function enrollStudent($course_id, $student_id) {
        // Check if already enrolled
        $existing = $this->db->fetch("SELECT id FROM enrollments WHERE course_id = ? AND student_id = ?", [$course_id, $student_id]);
        if ($existing) {
            return ['success' => false, 'message' => 'Student is already enrolled in this course'];
        }
        
        $sql = "INSERT INTO enrollments (course_id, student_id) VALUES (?, ?)";
        $result = $this->db->execute($sql, [$course_id, $student_id]);
        
        if ($result) {
            // Send notification
            $course = $this->getById($course_id);
            $lecturer_name = $course['first_name'] . ' ' . $course['last_name'];
            
            $notification = new Notification();
            $notification->notifyEnrollment($student_id, $course['title'], $lecturer_name);
            
            return ['success' => true, 'message' => 'Student enrolled successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to enroll student'];
        }
    }
    
    public function unenrollStudent($course_id, $student_id) {
        $sql = "UPDATE enrollments SET is_active = 0 WHERE course_id = ? AND student_id = ?";
        $result = $this->db->execute($sql, [$course_id, $student_id]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Student unenrolled successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to unenroll student'];
        }
    }
    
    public function getEnrolledStudents($course_id) {
        $sql = "SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.phone, u.whatsapp_number, e.enrolled_at,
                       (SELECT COUNT(*) FROM resource_access ra 
                        JOIN resources r ON ra.resource_id = r.id 
                        WHERE ra.student_id = u.id AND r.course_id = ?) as resource_access_count
                FROM users u 
                JOIN enrollments e ON u.id = e.student_id 
                WHERE e.course_id = ? AND e.is_active = 1 AND u.is_active = 1 
                ORDER BY u.first_name, u.last_name";
        
        return $this->db->fetchAll($sql, [$course_id, $course_id]);
    }
    
    public function getStudentCourses($student_id) {
        $sql = "SELECT c.*, u.first_name, u.last_name, u.email as lecturer_email, u.whatsapp_number, e.enrolled_at,
                       (SELECT COUNT(*) FROM resources r WHERE r.course_id = c.id AND r.is_active = 1) as resource_count,
                       (SELECT COUNT(*) FROM sessions s WHERE s.course_id = c.id AND s.is_active = 1 AND s.session_date > NOW()) as upcoming_sessions
                FROM courses c 
                JOIN enrollments e ON c.id = e.course_id 
                LEFT JOIN users u ON c.lecturer_id = u.id 
                WHERE e.student_id = ? AND e.is_active = 1 AND c.is_active = 1 
                ORDER BY c.title";
        
        return $this->db->fetchAll($sql, [$student_id]);
    }
    
    public function getAvailableCourses($student_id) {
        $sql = "SELECT c.*, u.first_name, u.last_name, u.email as lecturer_email,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.is_active = 1) as student_count
                FROM courses c 
                LEFT JOIN users u ON c.lecturer_id = u.id 
                WHERE c.is_active = 1 
                AND c.id NOT IN (
                    SELECT course_id FROM enrollments 
                    WHERE student_id = ? AND is_active = 1
                )
                ORDER BY c.title";
        
        return $this->db->fetchAll($sql, [$student_id]);
    }
    
    public function getCourseStats($course_id) {
        $stats = [];
        
        // Student count
        $stats['student_count'] = $this->db->fetch("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ? AND is_active = 1", [$course_id])['count'];
        
        // Resource count
        $stats['resource_count'] = $this->db->fetch("SELECT COUNT(*) as count FROM resources WHERE course_id = ? AND is_active = 1", [$course_id])['count'];
        
        // Total views/downloads
        $stats['total_views'] = $this->db->fetch("SELECT SUM(views_count) as total FROM resources WHERE course_id = ?", [$course_id])['total'] ?? 0;
        $stats['total_downloads'] = $this->db->fetch("SELECT SUM(downloads_count) as total FROM resources WHERE course_id = ?", [$course_id])['total'] ?? 0;
        
        // Recent activity
        $stats['recent_enrollments'] = $this->db->fetchAll("
            SELECT u.first_name, u.last_name, e.enrolled_at 
            FROM enrollments e 
            JOIN users u ON e.student_id = u.id 
            WHERE e.course_id = ? AND e.is_active = 1 
            ORDER BY e.enrolled_at DESC LIMIT 5
        ", [$course_id]);
        
        return $stats;
    }
    
    public function searchCourses($query) {
        $search = "%{$query}%";
        $sql = "SELECT c.*, u.first_name, u.last_name, u.email as lecturer_email,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.is_active = 1) as student_count
                FROM courses c 
                LEFT JOIN users u ON c.lecturer_id = u.id 
                WHERE c.is_active = 1 
                AND (c.title LIKE ? OR c.description LIKE ? OR c.course_code LIKE ?)
                ORDER BY c.title";
        
        return $this->db->fetchAll($sql, [$search, $search, $search]);
    }
}
?> 