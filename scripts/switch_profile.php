<?php
// scripts/switch_profile.php
require_once __DIR__ . '/../config/session_handler.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$profile_id_to_switch = $_GET['profile_id'] ?? null;

if ($profile_id_to_switch) {
    // Verify that the profile belongs to the current user
    $profile = get_single_row(
        "SELECT id, slug FROM profiles WHERE id = ? AND user_id = ?",
        [$profile_id_to_switch, $user_id],
        'ii'
    );

    if ($profile) {
        // Switch the active profile in the session
        $_SESSION['active_profile_id'] = $profile['id'];
        $_SESSION['page_slug'] = $profile['slug'];
        $_SESSION['success'] = "Berhasil beralih ke profil lain.";
    } else {
        // Profile does not belong to the user or does not exist
        $_SESSION['error'] = "Gagal beralih profil. Profil tidak ditemukan atau tidak valid.";
    }
}

// Redirect back to the dashboard or the previous page
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '../admin/dashboard.php';
header("Location: " . $redirect_url);
exit;
