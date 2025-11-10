<?php
    require_once 'config/db.php';
    $link_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if($link_id <= 0){
        die('Invalid link!');
    }

    $update_query = "UPDATE links SET click_count = click_count + 1 WHERE link_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'i', $link_id);
    mysqli_stmt_execute($stmt);
    
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