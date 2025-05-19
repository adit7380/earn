<?php
require_once __DIR__ . '/db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['telegram_id']) && !empty($_SESSION['telegram_id']);
    }
    
    // Login user with Telegram ID
    public function loginWithTelegramId($telegramId, $referrerId = null) {
        // Check if user exists
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE telegram_id = ?",
            [$telegramId]
        );
        
        // If user doesn't exist, create one
        if (empty($user)) {
            // Give referral bonus if referrer exists
            if (!empty($referrerId) && $referrerId != $telegramId) {
                // Check if referrer exists
                $referrer = $this->db->fetchOne(
                    "SELECT * FROM users WHERE telegram_id = ?",
                    [$referrerId]
                );
                
                if (!empty($referrer)) {
                    // Add referral bonus to referrer
                    $this->db->execute(
                        "UPDATE users SET coins = coins + ? WHERE telegram_id = ?",
                        [REFERRAL_BONUS, $referrerId]
                    );
                }
            }
            
            // Create new user
            $this->db->execute(
                "INSERT INTO users (telegram_id, referrer_id) VALUES (?, ?)",
                [$telegramId, $referrerId]
            );
        }
        
        // Set session
        $_SESSION['telegram_id'] = $telegramId;
        
        return true;
    }
    
    // Get user data
    public function getUserData() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $telegramId = $_SESSION['telegram_id'];
        
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE telegram_id = ?",
            [$telegramId]
        );
    }
    
    // Logout user
    public function logout() {
        unset($_SESSION['telegram_id']);
        session_destroy();
        
        return true;
    }
    
    // Admin login
    public function adminLogin($username, $password) {
        $admin = $this->db->fetchOne(
            "SELECT * FROM admins WHERE username = ?",
            [$username]
        );
        
        if (empty($admin) || !password_verify($password, $admin['password'])) {
            return false;
        }
        
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        return true;
    }
    
    // Check if admin is logged in
    public function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }
    
    // Admin logout
    public function adminLogout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        
        return true;
    }
    
    // Add coins to user
    public function addCoins($telegramId, $coins) {
        return $this->db->execute(
            "UPDATE users SET coins = coins + ? WHERE telegram_id = ?",
            [$coins, $telegramId]
        );
    }
    
    // Deduct coins from user
    public function deductCoins($telegramId, $coins) {
        // Check if user has enough coins
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE telegram_id = ?",
            [$telegramId]
        );
        
        if (empty($user) || $user['coins'] < $coins) {
            return false;
        }
        
        return $this->db->execute(
            "UPDATE users SET coins = coins - ? WHERE telegram_id = ?",
            [$coins, $telegramId]
        );
    }
    
    // Get user's USDT balance
    public function getUsdtBalance($telegramId) {
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE telegram_id = ?",
            [$telegramId]
        );
        
        if (empty($user)) {
            return 0;
        }
        
        return number_format($user['coins'] / CONVERSION_RATE, 2);
    }
}

// Create a global auth instance
$auth = new Auth();
?>