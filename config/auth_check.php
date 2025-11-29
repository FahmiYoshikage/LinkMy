<?php
    // Use database session handler for Docker persistence
    require_once __DIR__ . '/session_handler.php';
    
    if (session_status() === PHP_SESSION_NONE){
        init_db_session();
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
    
    // Multi-profile: Get slug from active profile
    if (isset($_SESSION['active_profile_id'])) {
        require_once __DIR__ . '/db.php';
        $profile = get_single_row(
            "SELECT slug FROM profiles WHERE profile_id = ?",
            [$_SESSION['active_profile_id']],
            'i'
        );
        $current_page_slug = $profile['slug'] ?? $_SESSION['page_slug'] ?? '';
    } else {
        $current_page_slug = $_SESSION['page_slug'] ?? '';
    }
?>