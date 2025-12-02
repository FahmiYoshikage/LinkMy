<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Advanced Form - LinkMy</title>
    <link href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            padding: 20px;
        }
        .debug-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .code-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            border-left: 4px solid #667eea;
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Advanced Form Submission</h1>
        <p class="text-muted">Testing if POST data is received correctly</p>
        
        <?php
        require_once '../config/auth_check.php';
        require_once '../config/db.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo '<div class="debug-box">';
            echo '<h3>✅ POST Request Received!</h3>';
            echo '<p class="success">Form was submitted successfully</p>';
            
            echo '<h4>POST Data:</h4>';
            echo '<div class="code-block">';
            print_r($_POST);
            echo '</div>';
            
            echo '<h4>Parsed Values:</h4>';
            echo '<ul>';
            echo '<li><strong>gradient_preset:</strong> ' . ($_POST['gradient_preset'] ?? 'NULL') . '</li>';
            echo '<li><strong>custom_bg_color:</strong> ' . ($_POST['custom_bg_color'] ?? 'NULL') . '</li>';
            echo '<li><strong>custom_button_color:</strong> ' . ($_POST['custom_button_color'] ?? 'NULL') . '</li>';
            echo '<li><strong>custom_text_color:</strong> ' . ($_POST['custom_text_color'] ?? 'NULL') . '</li>';
            echo '<li><strong>profile_layout:</strong> ' . ($_POST['profile_layout'] ?? 'NULL') . '</li>';
            echo '<li><strong>show_profile_border:</strong> ' . (isset($_POST['show_profile_border']) ? 'YES (1)' : 'NO (0)') . '</li>';
            echo '<li><strong>enable_animations:</strong> ' . (isset($_POST['enable_animations']) ? 'YES (1)' : 'NO (0)') . '</li>';
            echo '<li><strong>update_advanced:</strong> ' . (isset($_POST['update_advanced']) ? '<span class="success">PRESENT ✓</span>' : '<span class="error">MISSING ✗</span>') . '</li>';
            echo '</ul>';
            
            // Try to execute the update
            echo '<h4>Database Update Test:</h4>';
            
            $gradient_preset = $_POST['gradient_preset'] ?? null;
            $custom_bg_color = !empty($_POST['custom_bg_color']) ? $_POST['custom_bg_color'] : null;
            $custom_button_color = !empty($_POST['custom_button_color']) ? $_POST['custom_button_color'] : null;
            $custom_text_color = !empty($_POST['custom_text_color']) ? $_POST['custom_text_color'] : null;
            $profile_layout = $_POST['profile_layout'] ?? 'centered';
            $show_profile_border = isset($_POST['show_profile_border']) ? 1 : 0;
            $enable_animations = isset($_POST['enable_animations']) ? 1 : 0;
            
            $query = "UPDATE appearance SET 
                      gradient_preset = ?, 
                      custom_bg_color = ?, 
                      custom_button_color = ?,
                      custom_text_color = ?,
                      profile_layout = ?,
                      show_profile_border = ?,
                      enable_animations = ?
                      WHERE user_id = ?";
            
            echo '<div class="code-block">' . $query . '</div>';
            
            $stmt = mysqli_prepare($conn, $query);
            
            if (!$stmt) {
                echo '<p class="error">❌ Prepare failed: ' . mysqli_error($conn) . '</p>';
            } else {
                echo '<p class="success">✓ Prepare successful</p>';
                
                mysqli_stmt_bind_param($stmt, 'ssssiiii', 
                    $gradient_preset, $custom_bg_color, $custom_button_color, $custom_text_color,
                    $profile_layout, $show_profile_border, $enable_animations, $current_user_id);
                
                echo '<p class="success">✓ Bind param successful</p>';
                
                if (mysqli_stmt_execute($stmt)) {
                    $affected = mysqli_stmt_affected_rows($stmt);
                    echo '<p class="success">✅ Execute successful! Affected rows: ' . $affected . '</p>';
                    
                    if ($affected > 0) {
                        echo '<p class="success">✅ DATABASE UPDATED!</p>';
                    } else {
                        echo '<p class="error">⚠ No rows affected (data might be same as before)</p>';
                    }
                } else {
                    echo '<p class="error">❌ Execute failed: ' . mysqli_stmt_error($stmt) . '</p>';
                }
                
                mysqli_stmt_close($stmt);
            }
            
            echo '<h4>Verify Saved Data:</h4>';
            $check = get_single_row("SELECT * FROM appearance WHERE user_id = ?", [$current_user_id], 'i');
            echo '<div class="code-block">';
            echo 'gradient_preset: ' . ($check['gradient_preset'] ?? 'NULL') . "\n";
            echo 'custom_bg_color: ' . ($check['custom_bg_color'] ?? 'NULL') . "\n";
            echo 'custom_button_color: ' . ($check['custom_button_color'] ?? 'NULL') . "\n";
            echo 'custom_text_color: ' . ($check['custom_text_color'] ?? 'NULL') . "\n";
            echo 'profile_layout: ' . ($check['profile_layout'] ?? 'NULL') . "\n";
            echo 'show_profile_border: ' . ($check['show_profile_border'] ?? 'NULL') . "\n";
            echo 'enable_animations: ' . ($check['enable_animations'] ?? 'NULL') . "\n";
            echo '</div>';
            
            echo '</div>';
        } else {
            echo '<div class="debug-box">';
            echo '<h3>📝 Test Form</h3>';
            echo '<p>Fill this form and click submit to test if POST data is received</p>';
            ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Gradient Preset</label>
                    <select name="gradient_preset" class="form-control">
                        <option value="">-- None --</option>
                        <option value="Purple Dream">Purple Dream</option>
                        <option value="Ocean Blue">Ocean Blue</option>
                        <option value="Sunset Orange">Sunset Orange</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Custom Background Color</label>
                    <input type="color" name="custom_bg_color" class="form-control" value="#667eea">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Custom Button Color</label>
                    <input type="color" name="custom_button_color" class="form-control" value="#764ba2">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Custom Text Color</label>
                    <input type="color" name="custom_text_color" class="form-control" value="#ffffff">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Profile Layout</label>
                    <div>
                        <input type="radio" name="profile_layout" value="centered" checked> Centered
                        <input type="radio" name="profile_layout" value="left" class="ms-3"> Left
                        <input type="radio" name="profile_layout" value="minimal" class="ms-3"> Minimal
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="show_profile_border" class="form-check-input" id="border" checked>
                        <label class="form-check-label" for="border">Show Profile Border</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="enable_animations" class="form-check-input" id="anim" checked>
                        <label class="form-check-label" for="anim">Enable Animations</label>
                    </div>
                </div>
                
                <button type="submit" name="update_advanced" class="btn btn-primary">
                    Test Submit
                </button>
            </form>
            
            <?php
            echo '</div>';
        }
        
        mysqli_close($conn);
        ?>
        
        <div class="debug-box">
            <h3>📋 What This Tests:</h3>
            <ul>
                <li>✓ Is POST request received?</li>
                <li>✓ Is <code>update_advanced</code> parameter present?</li>
                <li>✓ Are all form fields sent correctly?</li>
                <li>✓ Does SQL prepare work?</li>
                <li>✓ Does SQL execute work?</li>
                <li>✓ Are rows actually updated?</li>
                <li>✓ Can we read back the saved data?</li>
            </ul>
        </div>
        
        <a href="appearance.php" class="btn btn-secondary">← Back to Appearance</a>
    </div>
</body>
</html>
