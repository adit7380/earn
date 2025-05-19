<?php
class Auth {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function login($username, $password) {
        $user = $this->db->fetchOne("SELECT * FROM admins WHERE username = ?", [$username]);
        
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        session_destroy();
        return true;
    }
    
    public function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }
    
    public function getCurrentAdmin() {
        if (!$this->isAdminLoggedIn()) {
            return null;
        }
        
        return $this->db->fetchOne("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
    }
    
    public function createAdmin($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $result = $this->db->execute(
            "INSERT INTO admins (username, password) VALUES (?, ?)",
            [$username, $hashedPassword]
        );
        
        return $result > 0;
    }
    
    public function updateAdminPassword($adminId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $result = $this->db->execute(
            "UPDATE admins SET password = ? WHERE id = ?",
            [$hashedPassword, $adminId]
        );
        
        return $result > 0;
    }
    
    public function getAllAdmins() {
        return $this->db->fetchAll("SELECT id, username, created_at FROM admins ORDER BY id ASC");
    }
}

// Initialize Authentication
$auth = new Auth();
?>