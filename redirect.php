<?php
    require_once 'config/db.php';
    $link_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if($link_id <= 0){
        die('Invalid link!');
    }

    // Update click counter
    $update_query = "UPDATE links SET click_count = click_count + 1 WHERE link_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'i', $link_id);
    mysqli_stmt_execute($stmt);
    
    // Capture analytics data
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $ip_address = '';
    
    // Get real IP address (handle proxies)
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    
    // Get country from IP using free ip-api.com service
    $country = 'Unknown';
    $city = '';
    
    // Only do geolocation for non-local IPs
    if (!preg_match('/^(127\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $ip_address)) {
        try {
            $geo_data = @file_get_contents("http://ip-api.com/json/{$ip_address}?fields=status,country,city");
            if ($geo_data) {
                $geo = json_decode($geo_data, true);
                if ($geo && $geo['status'] === 'success') {
                    $country = $geo['country'] ?? 'Unknown';
                    $city = $geo['city'] ?? '';
                }
            }
        } catch (Exception $e) {
            // Silent fail - keep default values
        }
    } else {
        $country = 'Local Network';
    }
    
    // Insert analytics record (realtime tracking)
    $analytics_query = "INSERT INTO link_analytics (link_id, referrer, user_agent, ip_address, country, city, clicked_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt_analytics = mysqli_prepare($conn, $analytics_query);
    if ($stmt_analytics) {
        mysqli_stmt_bind_param($stmt_analytics, 'isssss', $link_id, $referrer, $user_agent, $ip_address, $country, $city);
        mysqli_stmt_execute($stmt_analytics);
        mysqli_stmt_close($stmt_analytics);
    }
    
    // Get link URL and redirect
    $link_data = get_single_row("SELECT url FROM links WHERE link_id = ?", [$link_id], 'i');

    if ($link_data && !empty($link_data['url'])){
        $url = $link_data['url'];
        if(!preg_match("~^(?:f|ht)tps?://~i", $url)){
            $url = "https://". $url;
        }
        header('Location: ' . $url);
        exit;
    } else {
        die('Link not found!');
    }
?>