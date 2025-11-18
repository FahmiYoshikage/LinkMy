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
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function open($save_path, $session_name): bool {
        return true;
    }
    
    public function close(): bool {
        return true;
    }
    
    public function read($session_id): string|false {
        $stmt = mysqli_prepare($this->conn, "SELECT session_data FROM sessions WHERE session_id = ? AND session_expire > ?");
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
        $expire = time() + (int)ini_get('session.gc_maxlifetime');
        
        $stmt = mysqli_prepare($this->conn, 
            "INSERT INTO sessions (session_id, session_data, session_expire) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE session_data = ?, session_expire = ?");
        
        mysqli_stmt_bind_param($stmt, 'ssisi', $session_id, $session_data, $expire, $session_data, $expire);
        return mysqli_stmt_execute($stmt);
    }
    
    public function destroy($session_id): bool {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM sessions WHERE session_id = ?");
        mysqli_stmt_bind_param($stmt, 's', $session_id);
        return mysqli_stmt_execute($stmt);
    }
    
    public function gc($maxlifetime): int|false {
        $old = time();
        $stmt = mysqli_prepare($this->conn, "DELETE FROM sessions WHERE session_expire < ?");
        mysqli_stmt_bind_param($stmt, 'i', $old);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_affected_rows($stmt);
    }
}
