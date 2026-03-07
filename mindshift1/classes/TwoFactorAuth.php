<?php
require_once __DIR__ . '/../config/database.php';

class TwoFactorAuth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function generateSecret($user_id) {
        // Generate a random secret key
        $secret = $this->generateRandomSecret();
        
        // Store the secret in the database
        $result = $this->db->execute("
            UPDATE users 
            SET two_factor_secret = ?, two_factor_enabled = 1 
            WHERE id = ?
        ", [$secret, $user_id]);
        
        if ($result) {
            return $secret;
        }
        
        return false;
    }
    
    public function verifyCode($user_id, $code) {
        // Get user's 2FA secret
        $user = $this->db->fetch("
            SELECT two_factor_secret, two_factor_enabled 
            FROM users 
            WHERE id = ?
        ", [$user_id]);
        
        if (!$user || !$user['two_factor_enabled'] || !$user['two_factor_secret']) {
            return false;
        }
        
        // Verify the code (simple implementation - in production use proper TOTP library)
        return $this->verifyTOTP($user['two_factor_secret'], $code);
    }
    
    public function isEnabled($user_id) {
        $user = $this->db->fetch("
            SELECT two_factor_enabled 
            FROM users 
            WHERE id = ?
        ", [$user_id]);
        
        return $user && $user['two_factor_enabled'];
    }
    
    public function disable($user_id) {
        return $this->db->execute("
            UPDATE users 
            SET two_factor_enabled = 0, two_factor_secret = NULL 
            WHERE id = ?
        ", [$user_id]);
    }
    
    public function getQRCode($secret, $user_email) {
        // Generate QR code URL for Google Authenticator
        $issuer = 'Mind Shift';
        $url = "otpauth://totp/{$issuer}:{$user_email}?secret={$secret}&issuer={$issuer}";
        
        return $url;
    }
    
    private function generateRandomSecret() {
        // Generate a 32-character base32 secret
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $secret;
    }
    
    private function verifyTOTP($secret, $code) {
        // Simple TOTP verification (in production, use a proper library)
        $timeSlice = floor(time() / 30);
        
        // Check current time slice and adjacent slices
        for ($i = -1; $i <= 1; $i++) {
            $checkTimeSlice = $timeSlice + $i;
            $expectedCode = $this->generateTOTP($secret, $checkTimeSlice);
            
            if ($code === $expectedCode) {
                return true;
            }
        }
        
        return false;
    }
    
    private function generateTOTP($secret, $timeSlice) {
        // Simple TOTP generation (in production, use a proper library)
        $hash = hash_hmac('sha1', pack('N*', $timeSlice), $secret, true);
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
    
    public function generateBackupCodes($user_id) {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid()), 0, 8));
        }
        
        // Store backup codes (you might want a separate table for this)
        $backupCodesJson = json_encode($codes);
        $this->db->execute("
            UPDATE users 
            SET backup_codes = ? 
            WHERE id = ?
        ", [$backupCodesJson, $user_id]);
        
        return $codes;
    }
    
    public function verifyBackupCode($user_id, $code) {
        $user = $this->db->fetch("
            SELECT backup_codes 
            FROM users 
            WHERE id = ?
        ", [$user_id]);
        
        if (!$user || !$user['backup_codes']) {
            return false;
        }
        
        $backupCodes = json_decode($user['backup_codes'], true);
        $index = array_search(strtoupper($code), $backupCodes);
        
        if ($index !== false) {
            // Remove used backup code
            unset($backupCodes[$index]);
            $this->db->execute("
                UPDATE users 
                SET backup_codes = ? 
                WHERE id = ?
            ", [json_encode(array_values($backupCodes)), $user_id]);
            
            return true;
        }
        
        return false;
    }
}
?> 