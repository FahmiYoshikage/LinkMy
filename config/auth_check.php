<?php
    if (session_status() === PHP_SESSION_NONE){
        session_start();
    }
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['username'])){
        header('Location: ../index.php?error=not_logged_in');
        exit;
    }
    $timeout_duration = 1800;

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