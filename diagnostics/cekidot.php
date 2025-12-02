<?php
/**
 * Debug Tool - Check Session & Database
 */
session_start();
require_once 'config/db.php';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Tool - LinkMy</title>
    <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .debug-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        pre { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🔍 LinkMy Debug Tool</h1>

        <!-- Session Info -->
        <div class="debug-card">
            <h3>📋 Session Variables</h3>
            <pre><?php print_r($_SESSION); ?></pre>
            
            <div class="mt-3">
                <strong>Expected for Step 2 → 3:</strong>
                <ul>
                    <li>
                        <code>$_SESSION['reg_email']</code>: 
                        <?php echo isset($_SESSION['reg_email']) ? '<span class="status-ok">✓ SET</span>' : '<span class="status-error">✗ NOT SET</span>'; ?>
                    </li>
                    <li>
                        <code>$_SESSION['reg_step']</code>: 
                        <?php echo isset($_SESSION['reg_step']) ? '<span class="status-ok">✓ ' . $_SESSION['reg_step'] . '</span>' : '<span class="status-error">✗ NOT SET</span>'; ?>
                    </li>
                    <li>
                        <code>$_SESSION['email_verified']</code>: 
                        <?php echo isset($_SESSION['email_verified']) && $_SESSION['email_verified'] ? '<span class="status-ok">✓ TRUE</span>' : '<span class="status-error">✗ FALSE</span>'; ?>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Latest OTP -->
        <div class="debug-card">
            <h3>📧 Latest OTP Entries (Last 5)</h3>
            <?php
            $stmt = $conn->prepare("
                SELECT 
                    email, 
                    otp_code, 
                    created_at, 
                    expires_at,
                    is_used,
                    TIMESTAMPDIFF(SECOND, NOW(), expires_at) as seconds_remaining
                FROM email_verifications 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>OTP</th>
                        <th>Created</th>
                        <th>Remaining</th>
                        <th>Used?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><code><?php echo $row['otp_code']; ?></code></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <?php 
                                if ($row['seconds_remaining'] > 0) {
                                    echo '<span class="status-ok">' . $row['seconds_remaining'] . 's</span>';
                                } else {
                                    echo '<span class="status-error">EXPIRED</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php echo $row['is_used'] ? '<span class="status-error">✓ Used</span>' : '<span class="status-ok">✗ Fresh</span>'; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Rate Limit Check -->
        <div class="debug-card">
            <h3>⏱️ Rate Limit Check (Last 10 Minutes)</h3>
            <?php
            if (isset($_SESSION['reg_email'])) {
                $email = $_SESSION['reg_email'];
                $tenMinutesAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));
                
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count 
                    FROM email_verifications 
                    WHERE email = ? AND created_at > ?
                ");
                $stmt->bind_param("ss", $email, $tenMinutesAgo);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $count = $row['count'];
                ?>
                <p>
                    Email: <strong><?php echo htmlspecialchars($email); ?></strong><br>
                    OTP Requests in last 10 min: <strong><?php echo $count; ?></strong> / 3<br>
                    Status: 
                    <?php if ($count < 3): ?>
                        <span class="status-ok">✓ Can send OTP</span>
                    <?php else: ?>
                        <span class="status-error">✗ Rate limit exceeded</span>
                    <?php endif; ?>
                </p>
            <?php } else { ?>
                <p class="status-error">No email in session</p>
            <?php } ?>
        </div>

        <!-- Actions -->
        <div class="debug-card">
            <h3>🔧 Quick Actions</h3>
            <div class="btn-group" role="group">
                <a href="register.php" class="btn btn-primary">Go to Register</a>
                <a href="verify-otp.php" class="btn btn-secondary">Go to Verify OTP</a>
                <a href="index.php" class="btn btn-info">Go to Login</a>
            </div>
            
            <hr>
            
            <h5>Clear Data:</h5>
            <form method="POST" style="display: inline;">
                <button type="submit" name="clear_session" class="btn btn-warning">Clear Session</button>
            </form>
            
            <form method="POST" style="display: inline;">
                <button type="submit" name="clear_otp" class="btn btn-danger">Clear All OTP</button>
            </form>

            <?php
            if (isset($_POST['clear_session'])) {
                session_destroy();
                echo '<div class="alert alert-success mt-3">Session cleared! Refresh page.</div>';
            }
            
            if (isset($_POST['clear_otp'])) {
                $conn->query("DELETE FROM email_verifications WHERE created_at < NOW()");
                echo '<div class="alert alert-success mt-3">All OTP cleared!</div>';
            }
            ?>
        </div>

        <!-- Apache Error Log -->
        <div class="debug-card">
            <h3>📄 Recent Apache Error Log (Last 20 lines)</h3>
            <pre><?php
            $logFile = 'C:/xampp/apache/logs/error.log';
            if (file_exists($logFile)) {
                $lines = file($logFile);
                $lastLines = array_slice($lines, -20);
                foreach ($lastLines as $line) {
                    // Highlight OTP-related lines
                    if (stripos($line, 'otp') !== false || stripos($line, 'verify') !== false) {
                        echo '<span style="background: yellow; color: black;">' . htmlspecialchars($line) . '</span>';
                    } else {
                        echo htmlspecialchars($line);
                    }
                }
            } else {
                echo "Log file not found: $logFile";
            }
            ?></pre>
        </div>
    </div>
</body>
</html>