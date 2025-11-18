<?php
    // Use database session handler for Docker persistence
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/session_handler.php';
    
    if (session_status() === PHP_SESSION_NONE){
        // Set session to database
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
        
        session_start();
    }
    
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['username'])){
        header('Location: ../index.php?error=not_logged_in');
        exit;
    }
    
    // Extended session: 7 days (604800 seconds)
    $timeout_duration = 604800;

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration){
        session_unset();
        session_destroy();
        header('Location: ../index.php?error=session_expired');
        exit;
    }

    $_SESSION['last_activity'] = time();

    $current_user_id = $_SESSION['user_id'];
    $current_username = $_SESSION['username'];
    $current_page_slug = $_SESSION['page_slug'] ?? '';
?>