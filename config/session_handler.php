<?php
/**
 * Database Session Handler
 * Store sessions in MySQL instead of /tmp for Docker persistence
 * 
 * Usage: Call init_db_session() at the start of any PHP file before session_start()
 */

// Initialize database session (call this before session_start)
function init_db_session() {
    global $conn;
    
    // Only initialize once
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    
    // Check if sessions table exists
    if (!isset($conn) || !$conn) {
        require_once __DIR__ . '/db.php';
    }
    
    $check = @mysqli_query($conn, "SHOW TABLES LIKE 'sessions'");
    if (!$check || mysqli_num_rows($check) === 0) {
        // Sessions table doesn't exist, use default file handler
        error_log("Sessions table not found, using default file session handler");
        return;
    }
    
    // Set up database session handler
    $handler = new DatabaseSessionHandler($conn);
    session_set_save_handler($handler, true);
    
    // Extended session lifetime (7 days)
    ini_set('session.gc_maxlifetime', 604800);
    session_set_cookie_params([
        'lifetime' => 604800,
        'path' => '/',
        'secure' => false, // Set true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

class DatabaseSessionHandler implements SessionHandlerInterface {
    private $conn;
    private $db_host;
    private $db_user;
    private $db_pass;
    private $db_name;
    
    public function __construct($connection = null) {
        // Store database credentials for creating persistent connection
        $this->db_host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $this->db_user = defined('DB_USER') ? DB_USER : 'root';
        $this->db_pass = defined('DB_PASS') ? DB_PASS : '';
        $this->db_name = defined('DB_NAME') ? DB_NAME : 'linkmy_db';
        
        // Don't rely on passed connection, we'll create our own
        $this->conn = null;
    }
    
    private function getConnection() {
        // Create a fresh connection for session operations if needed
        try {
            if (!$this->conn || @$this->conn->ping() === false) {
                $this->conn = @mysqli_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
                if ($this->conn) {
                    mysqli_set_charset($this->conn, "utf8mb4");
                }
            }
        } catch (Exception $e) {
            // Connection is closed, recreate it
            $this->conn = @mysqli_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
            if ($this->conn) {
                mysqli_set_charset($this->conn, "utf8mb4");
            }
        }
        return $this->conn;
    }
    
    public function open($save_path, $session_name): bool {
        return true;
    }
    
    public function close(): bool {
        // Don't close the connection - let PHP handle it
        return true;
    }
    
    public function read($session_id): string|false {
        $conn = $this->getConnection();
        if (!$conn) return '';
        
        $stmt = mysqli_prepare($conn, "SELECT session_data FROM sessions WHERE session_id = ? AND session_expire > ?");
        if (!$stmt) return '';
        
        $current_time = time();
        mysqli_stmt_bind_param($stmt, 'si', $session_id, $current_time);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['session_data'];
        }
        return '';
    }
    
    public function write($session_id, $session_data): bool {
        $conn = $this->getConnection();
        if (!$conn) return false;
        
        $expire = time() + (int)ini_get('session.gc_maxlifetime');
        
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO sessions (session_id, session_data, session_expire) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE session_data = ?, session_expire = ?");
        
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, 'ssisi', $session_id, $session_data, $expire, $session_data, $expire);
        return mysqli_stmt_execute($stmt);
    }
    
    public function destroy($session_id): bool {
        $conn = $this->getConnection();
        if (!$conn) return false;
        
        $stmt = mysqli_prepare($conn, "DELETE FROM sessions WHERE session_id = ?");
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        return mysqli_stmt_execute($stmt);
    }
    
    public function gc($maxlifetime): int|false {
        $conn = $this->getConnection();
        if (!$conn) return false;
        
        $old = time();
        $stmt = mysqli_prepare($conn, "DELETE FROM sessions WHERE session_expire < ?");
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, 'i', $old);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_affected_rows($stmt);
    }
}
