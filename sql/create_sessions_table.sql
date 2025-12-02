-- Create sessions table for persistent sessions
CREATE TABLE IF NOT EXISTS sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    session_data TEXT NOT NULL,
    session_expire INT(11) NOT NULL,
    INDEX idx_expire (session_expire)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
