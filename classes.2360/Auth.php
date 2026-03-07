<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function register($username, $email, $password, $first_name, $last_name, $role = 'student', $phone = '', $whatsapp_number = '') {
        // Check if user already exists
        $existing = $this->db->fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
        if ($existing) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (username, email, password_hash, first_name, last_name, role, phone, whatsapp_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->db->execute($sql, [$username, $email, $password_hash, $first_name, $last_name, $role, $phone, $whatsapp_number]);
        
        if ($result) {
            return ['success' => true, 'message' => 'User registered successfully', 'user_id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    public function login($username, $password, $remember_me = false) {
        // Get user from database
        $user = $this->db->fetch("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1", [$username, $username]);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Check for existing active sessions
        $existing_sessions = $this->db->fetchAll("SELECT * FROM user_sessions WHERE user_id = ? AND is_active = 1 AND expires_at > NOW()", [$user['id']]);
        
        // If session control is enabled, handle existing sessions
        if (!empty($existing_sessions)) {
            // For now, we'll allow multiple sessions but log them
            $this->logActivity($user['id'], 'Multiple session detected', 'login', null, json_encode(['session_count' => count($existing_sessions)]));
        }
        
        // Create new session
        $session_token = $this->generateSessionToken();
        $device_info = $this->getDeviceInfo();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $expires_at = date('Y-m-d H:i:s', strtotime($remember_me ? '+30 days' : '+24 hours'));
        
        $session_sql = "INSERT INTO user_sessions (user_id, session_token, device_info, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->execute($session_sql, [$user['id'], $session_token, $device_info, $ip_address, $user_agent, $expires_at]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['session_token'] = $session_token;
        $_SESSION['logged_in'] = true;
        
        // Set remember me cookie if requested
        if ($remember_me) {
            setcookie('remember_token', $session_token, time() + (30 * 24 * 60 * 60), '/');
        }
        
        // Log login activity
        $this->logActivity($user['id'], 'User logged in', 'login', null, json_encode(['ip' => $ip_address, 'device' => $device_info]));
        
        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
    }
    
    public function logout($session_token = null) {
        $token = $session_token ?? $_SESSION['session_token'] ?? '';
        
        if ($token) {
            // Deactivate session in database
            $this->db->execute("UPDATE user_sessions SET is_active = 0 WHERE session_token = ?", [$token]);
        }
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        return true;
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return false;
        }
        
        // Verify session token
        if (isset($_SESSION['session_token'])) {
            $session = $this->db->fetch("SELECT * FROM user_sessions WHERE session_token = ? AND is_active = 1 AND expires_at > NOW()", [$_SESSION['session_token']]);
            if (!$session) {
                $this->logout();
                return false;
            }
            
            // Update last activity
            $this->db->execute("UPDATE user_sessions SET last_activity = NOW() WHERE session_token = ?", [$_SESSION['session_token']]);
        }
        
        return true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit;
        }
    }
    
    public function requireRole($required_role) {
        $this->requireLogin();
        
        if ($_SESSION['role'] !== $required_role) {
            header('Location: ../unauthorized.php');
            exit;
        }
    }
    
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
    
    public function getUserSessions($user_id) {
        return $this->db->fetchAll("SELECT * FROM user_sessions WHERE user_id = ? AND is_active = 1 ORDER BY last_activity DESC", [$user_id]);
    }
    
    public function terminateSession($session_token) {
        return $this->db->execute("UPDATE user_sessions SET is_active = 0 WHERE session_token = ?", [$session_token]);
    }
    
    public function terminateAllSessions($user_id, $except_current = true) {
        $sql = "UPDATE user_sessions SET is_active = 0 WHERE user_id = ?";
        $params = [$user_id];
        
        if ($except_current && isset($_SESSION['session_token'])) {
            $sql .= " AND session_token != ?";
            $params[] = $_SESSION['session_token'];
        }
        
        return $this->db->execute($sql, $params);
    }
    
    public function changePassword($user_id, $old_password, $new_password) {
        $user = $this->db->fetch("SELECT password_hash FROM users WHERE id = ?", [$user_id]);
        
        if (!$user || !password_verify($old_password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $result = $this->db->execute("UPDATE users SET password_hash = ? WHERE id = ?", [$new_hash, $user_id]);
        
        if ($result) {
            $this->logActivity($user_id, 'Password changed', 'security', null);
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }
    
    public function updateProfile($user_id, $data) {
        $allowed_fields = ['first_name', 'last_name', 'phone', 'whatsapp_number'];
        $update_fields = [];
        $params = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($update_fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
        
        $result = $this->db->execute($sql, $params);
        
        if ($result) {
            $this->logActivity($user_id, 'Profile updated', 'profile', null, json_encode($data));
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    }
    
    private function generateSessionToken() {
        return bin2hex(random_bytes(32));
    }
    
    private function getDeviceInfo() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Simple device detection
        if (strpos($user_agent, 'Mobile') !== false) {
            return 'Mobile Device';
        } elseif (strpos($user_agent, 'Tablet') !== false) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }
    
    private function logActivity($user_id, $action, $type = 'general', $target_id = null, $details = null) {
        $sql = "INSERT INTO admin_logs (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)";
        $this->db->execute($sql, [$user_id, $action, $type, $target_id, $details]);
    }
    
    public function cleanExpiredSessions() {
        return $this->db->execute("DELETE FROM user_sessions WHERE expires_at < NOW() OR is_active = 0");
    }
}
?> 