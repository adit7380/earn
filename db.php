<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($this->connection->connect_error) {
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log($e->getMessage());
            die("Sorry, there was a problem connecting to the database. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->connection->error);
            }
            
            if (!empty($params)) {
                $types = '';
                $bindParams = [];
                
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                    $bindParams[] = $param;
                }
                
                array_unshift($bindParams, $types);
                call_user_func_array([$stmt, 'bind_param'], $this->refValues($bindParams));
            }
            
            $stmt->execute();
            
            if ($stmt->errno) {
                throw new Exception("Query execution failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        if ($result === false) {
            return [];
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        if ($result === false) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    public function execute($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        if ($result === false) {
            return false;
        }
        
        return $this->connection->affected_rows;
    }
    
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    // Helper function for binding parameters by reference
    private function refValues($arr) {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = [];
            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
    
    // Create tables if they don't exist
    public function initDatabase() {
        $tables = [
            'users' => "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    telegram_id VARCHAR(255) UNIQUE,
                    coins INT DEFAULT 0,
                    referrer_id VARCHAR(255),
                    last_spin_time DATETIME,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'withdrawals' => "
                CREATE TABLE IF NOT EXISTS withdrawals (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    telegram_id VARCHAR(255),
                    usdt_amount DECIMAL(10,2),
                    wallet_address VARCHAR(255),
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'ads' => "
                CREATE TABLE IF NOT EXISTS ads (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    type ENUM('quick', 'premium', 'direct'),
                    url TEXT,
                    reward INT,
                    duration INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'admins' => "
                CREATE TABLE IF NOT EXISTS admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) UNIQUE,
                    password VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'ad_views' => "
                CREATE TABLE IF NOT EXISTS ad_views (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    telegram_id VARCHAR(255),
                    ad_id INT,
                    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'spins' => "
                CREATE TABLE IF NOT EXISTS spins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    telegram_id VARCHAR(255),
                    reward INT,
                    spun_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            "
        ];
        
        foreach ($tables as $table => $sql) {
            $this->execute($sql);
        }
        
        // Check if default admin exists, if not create one
        $admin = $this->fetchOne("SELECT * FROM admins LIMIT 1");
        
        if (empty($admin)) {
            $defaultAdminUsername = 'admin';
            $defaultAdminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            
            $this->execute(
                "INSERT INTO admins (username, password) VALUES (?, ?)",
                [$defaultAdminUsername, $defaultAdminPassword]
            );
        }
        
        // Add some default ads
        $ads = $this->fetchAll("SELECT * FROM ads LIMIT 1");
        
        if (empty($ads)) {
            $sampleAds = [
                ['quick', 'https://example.com/ad1', QUICK_AD_REWARD, 10],
                ['premium', 'https://example.com/ad2', PREMIUM_AD_REWARD, 20],
                ['direct', 'https://example.com/ad3', DIRECT_LINK_REWARD, 5]
            ];
            
            foreach ($sampleAds as $ad) {
                $this->execute(
                    "INSERT INTO ads (type, url, reward, duration) VALUES (?, ?, ?, ?)",
                    $ad
                );
            }
        }
    }
}

// Initialize database
$db = Database::getInstance();
$db->initDatabase();
?>