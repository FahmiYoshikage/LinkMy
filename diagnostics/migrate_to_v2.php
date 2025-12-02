<?php
/**
 * 🚀 LinkMy Database Migration Script
 * Migrate from complex v1 structure to simplified v2 structure
 * 
 * IMPORTANT: BACKUP YOUR DATABASE FIRST!
 * mysqldump -u root linkmy_db > backup_before_migration.sql
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes

require_once 'config/db.php';

// Configuration
$MIGRATION_MODE = 'SAFE'; // SAFE = create new tables with prefix, DIRECT = replace directly
$NEW_TABLE_PREFIX = 'new_';
$OLD_TABLE_PREFIX = 'old_';

// Migration status
$migration_log = [];
$errors = [];
$warnings = [];

function log_message($message, $type = 'info') {
    global $migration_log;
    $timestamp = date('Y-m-d H:i:s');
    $migration_log[] = "[{$timestamp}] [{$type}] {$message}";
    echo "<div class='log-{$type}'>[{$type}] {$message}</div>";
    flush();
}

function log_error($message) {
    global $errors;
    $errors[] = $message;
    log_message($message, 'ERROR');
}

function log_warning($message) {
    global $warnings;
    $warnings[] = $message;
    log_message($message, 'WARNING');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkMy Database Migration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content {
            padding: 30px;
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .warning-box h3 {
            color: #856404;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .warning-box ul {
            color: #856404;
            margin-left: 20px;
        }
        .info-box {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .info-box h3 {
            color: #0c5460;
            margin-bottom: 10px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .step h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .log-console {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .log-info { color: #4ec9b0; }
        .log-WARNING { color: #ffc107; }
        .log-ERROR { color: #f44336; }
        .log-SUCCESS { color: #4caf50; font-weight: bold; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-danger {
            background: #f44336;
            color: white;
        }
        .btn-success {
            background: #4caf50;
            color: white;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .progress-bar {
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 30px;
            margin-bottom: 20px;
        }
        .progress-fill {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.5s;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card h4 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 5px;
        }
        .stat-card p {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🚀 Database Migration to v2.0</h1>
        <p>Simplifying LinkMy database structure</p>
    </div>
    <div class="content">

<?php
// Check if migration requested
if (!isset($_POST['start_migration'])) {
    // Show pre-migration info
    ?>
    <div class="warning-box">
        <h3>⚠️ PERINGATAN PENTING!</h3>
        <ul>
            <li><strong>BACKUP DATABASE TERLEBIH DAHULU!</strong></li>
            <li>Proses ini akan mengubah struktur database Anda</li>
            <li>Estimasi waktu: 2-5 menit (tergantung jumlah data)</li>
            <li>Website akan down selama proses migrasi</li>
            <li>Pastikan tidak ada user yang sedang aktif</li>
        </ul>
    </div>

    <div class="info-box">
        <h3>📊 Data Saat Ini</h3>
        <?php
        $users_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
        $profiles_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM profiles"))['c'];
        $links_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM links"))['c'];
        ?>
        <div class="stats">
            <div class="stat-card">
                <h4><?= $users_count ?></h4>
                <p>Users</p>
            </div>
            <div class="stat-card">
                <h4><?= $profiles_count ?></h4>
                <p>Profiles</p>
            </div>
            <div class="stat-card">
                <h4><?= $links_count ?></h4>
                <p>Links</p>
            </div>
        </div>
    </div>

    <div class="step">
        <h3>📝 Langkah Backup (WAJIB!)</h3>
        <p>Sebelum melanjutkan, jalankan command ini di terminal:</p>
        <pre style="background:#1e1e1e;color:#4ec9b0;padding:15px;border-radius:8px;margin-top:10px;">mysqldump -u root linkmy_db > backup_<?= date('Ymd_His') ?>.sql</pre>
    </div>

    <div class="step">
        <h3>🔄 Yang Akan Dilakukan</h3>
        <ul style="margin-left:20px;line-height:1.8;">
            <li>✅ Rename tabel lama (users → old_users, dll)</li>
            <li>✅ Buat struktur tabel baru (simplified v2)</li>
            <li>✅ Migrasi data users (<?= $users_count ?> records)</li>
            <li>✅ Migrasi data profiles (<?= $profiles_count ?> records)</li>
            <li>✅ Migrasi data links (<?= $links_count ?> records)</li>
            <li>✅ Migrasi themes/appearance</li>
            <li>✅ Buat views & stored procedures</li>
            <li>✅ Verifikasi integritas data</li>
        </ul>
    </div>

    <form method="POST" onsubmit="return confirm('APAKAH ANDA SUDAH BACKUP DATABASE?\n\nKlik OK untuk melanjutkan migrasi.\nKlik Cancel untuk membatalkan.');">
        <div class="btn-group">
            <button type="submit" name="start_migration" class="btn btn-primary">
                🚀 Mulai Migrasi
            </button>
            <a href="admin/dashboard.php" class="btn btn-danger">
                ❌ Batalkan
            </a>
        </div>
    </form>

    <?php
    exit;
}

// Start migration process
log_message("Starting database migration...", 'info');
log_message("Mode: {$MIGRATION_MODE}", 'info');

?>
<div class="progress-bar">
    <div class="progress-fill" id="progress" style="width: 0%">0%</div>
</div>
<div class="log-console" id="log-console">
<?php

// Step 1: Rename old tables
log_message("STEP 1: Renaming old tables...", 'info');
$old_tables = ['users', 'profiles', 'links', 'user_appearance', 'themes', 
               'link_analytics', 'profile_analytics', 'sessions', 
               'password_resets', 'email_verifications'];

foreach ($old_tables as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '{$table}'");
    if (mysqli_num_rows($check) > 0) {
        $rename = mysqli_query($conn, "RENAME TABLE `{$table}` TO `{$OLD_TABLE_PREFIX}{$table}`");
        if ($rename) {
            log_message("✓ Renamed {$table} → {$OLD_TABLE_PREFIX}{$table}", 'SUCCESS');
        } else {
            log_error("✗ Failed to rename {$table}: " . mysqli_error($conn));
        }
    } else {
        log_warning("Table {$table} doesn't exist, skipping...");
    }
}
echo "<script>document.getElementById('progress').style.width='10%';document.getElementById('progress').innerText='10%';</script>";
flush();

// Step 2: Create new structure
log_message("STEP 2: Creating new database structure...", 'info');
$schema = file_get_contents(__DIR__ . '/database_simplified_v2.sql');
if (!$schema) {
    log_error("Cannot read database_simplified_v2.sql file!");
    exit;
}

// Remove CREATE DATABASE and USE statements
$schema = preg_replace('/CREATE DATABASE.*?;/i', '', $schema);
$schema = preg_replace('/USE.*?;/i', '', $schema);

// Handle DELIMITER for stored procedures
if (preg_match('/DELIMITER/', $schema)) {
    // Extract procedures separately
    preg_match_all('/DELIMITER \$\$(.*?)DELIMITER ;/s', $schema, $procedures);
    
    // Remove procedures from main schema
    $schema = preg_replace('/DELIMITER \$\$(.*?)DELIMITER ;/s', '', $schema);
    
    // Execute regular statements first
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        if (!mysqli_query($conn, $statement)) {
            log_error("Failed to execute: " . substr($statement, 0, 100) . "...");
            log_error("Error: " . mysqli_error($conn));
        }
    }
    
    // Now execute procedures
    foreach ($procedures[1] as $procedure) {
        $procedure = trim($procedure);
        if (!empty($procedure)) {
            if (!mysqli_query($conn, $procedure)) {
                log_warning("Failed to create procedure: " . mysqli_error($conn));
            }
        }
    }
} else {
    // No procedures, simple execution
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        if (!mysqli_query($conn, $statement)) {
            log_error("Failed to execute: " . substr($statement, 0, 100) . "...");
            log_error("Error: " . mysqli_error($conn));
        }
    }
}
log_message("✓ New structure created", 'SUCCESS');
echo "<script>document.getElementById('progress').style.width='20%';document.getElementById('progress').innerText='20%';</script>";
flush();

// Step 3: Migrate users
log_message("STEP 3: Migrating users data...", 'info');
$migrate_users = "
    INSERT INTO users (id, username, email, password, is_verified, created_at)
    SELECT 
        user_id,
        username,
        email,
        password_hash,
        CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END,
        created_at
    FROM {$OLD_TABLE_PREFIX}users
";
if (mysqli_query($conn, $migrate_users)) {
    $migrated = mysqli_affected_rows($conn);
    log_message("✓ Migrated {$migrated} users", 'SUCCESS');
} else {
    log_error("Failed to migrate users: " . mysqli_error($conn));
}
echo "<script>document.getElementById('progress').style.width='35%';document.getElementById('progress').innerText='35%';</script>";
flush();

// Step 4: Migrate profiles
log_message("STEP 4: Migrating profiles data...", 'info');
$migrate_profiles = "
    INSERT INTO profiles (id, user_id, slug, name, title, bio, avatar, is_active, created_at)
    SELECT 
        profile_id,
        user_id,
        slug,
        profile_name,
        profile_title,
        bio,
        profile_pic_filename,
        is_active,
        created_at
    FROM {$OLD_TABLE_PREFIX}profiles
";
if (mysqli_query($conn, $migrate_profiles)) {
    $migrated = mysqli_affected_rows($conn);
    log_message("✓ Migrated {$migrated} profiles", 'SUCCESS');
} else {
    log_error("Failed to migrate profiles: " . mysqli_error($conn));
}
echo "<script>document.getElementById('progress').style.width='50%';document.getElementById('progress').innerText='50%';</script>";
flush();

// Step 5: Migrate links
log_message("STEP 5: Migrating links data...", 'info');
$migrate_links = "
    INSERT INTO links (id, profile_id, title, url, icon, position, clicks, is_active, created_at)
    SELECT 
        link_id,
        profile_id,
        title,
        url,
        COALESCE(icon_class, 'bi-link-45deg'),
        order_index,
        click_count,
        is_active,
        created_at
    FROM {$OLD_TABLE_PREFIX}links
";
if (mysqli_query($conn, $migrate_links)) {
    $migrated = mysqli_affected_rows($conn);
    log_message("✓ Migrated {$migrated} links", 'SUCCESS');
} else {
    log_error("Failed to migrate links: " . mysqli_error($conn));
}
echo "<script>document.getElementById('progress').style.width='65%';document.getElementById('progress').innerText='65%';</script>";
flush();

// Step 6: Migrate themes
log_message("STEP 6: Migrating themes/appearance...", 'info');
$check_old_appearance = mysqli_query($conn, "SHOW TABLES LIKE '{$OLD_TABLE_PREFIX}user_appearance'");
if (mysqli_num_rows($check_old_appearance) > 0) {
    $migrate_themes = "
        INSERT INTO themes (profile_id, bg_type, bg_value, button_style, button_color, text_color, font, created_at)
        SELECT 
            profile_id,
            CASE 
                WHEN background_type = 'gradient' THEN 'gradient'
                WHEN background_type = 'image' THEN 'image'
                ELSE 'color'
            END as bg_type,
            COALESCE(gradient_css, background_color, 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') as bg_value,
            COALESCE(button_style, 'fill') as button_style,
            COALESCE(button_color, '#667eea') as button_color,
            COALESCE(text_color, '#ffffff') as text_color,
            COALESCE(font_family, 'Inter') as font,
            NOW()
        FROM {$OLD_TABLE_PREFIX}user_appearance
    ";
    if (mysqli_query($conn, $migrate_themes)) {
        $migrated = mysqli_affected_rows($conn);
        log_message("✓ Migrated {$migrated} themes", 'SUCCESS');
    } else {
        log_error("Failed to migrate themes: " . mysqli_error($conn));
    }
} else {
    log_warning("Old user_appearance table not found, creating default themes...");
    $profiles = mysqli_query($conn, "SELECT id FROM profiles");
    while ($profile = mysqli_fetch_assoc($profiles)) {
        mysqli_query($conn, "INSERT INTO themes (profile_id) VALUES ({$profile['id']})");
    }
    log_message("✓ Created default themes", 'SUCCESS');
}
echo "<script>document.getElementById('progress').style.width='80%';document.getElementById('progress').innerText='80%';</script>";
flush();

// Step 7: Migrate other tables
log_message("STEP 7: Migrating sessions, password_resets, email_verifications...", 'info');

// Sessions
$migrate_sessions = "
    INSERT INTO sessions (id, user_id, ip, user_agent, created_at, expires_at)
    SELECT session_id, user_id, ip_address, user_agent, created_at, expires_at
    FROM {$OLD_TABLE_PREFIX}sessions
";
if (mysqli_query($conn, $migrate_sessions)) {
    log_message("✓ Migrated sessions", 'SUCCESS');
}

// Password resets
$migrate_resets = "
    INSERT INTO password_resets (email, token, created_at, expires_at)
    SELECT email, token, created_at, expires_at
    FROM {$OLD_TABLE_PREFIX}password_resets
";
if (mysqli_query($conn, $migrate_resets)) {
    log_message("✓ Migrated password_resets", 'SUCCESS');
}

// Email verifications
$migrate_verifications = "
    INSERT INTO email_verifications (user_id, token, created_at, expires_at)
    SELECT user_id, token, created_at, expires_at
    FROM {$OLD_TABLE_PREFIX}email_verifications
";
if (mysqli_query($conn, $migrate_verifications)) {
    log_message("✓ Migrated email_verifications", 'SUCCESS');
}
echo "<script>document.getElementById('progress').style.width='90%';document.getElementById('progress').innerText='90%';</script>";
flush();

// Step 8: Verify data integrity
log_message("STEP 8: Verifying data integrity...", 'info');
$verify_queries = [
    'users' => "SELECT COUNT(*) as new_count FROM users",
    'profiles' => "SELECT COUNT(*) as new_count FROM profiles",
    'links' => "SELECT COUNT(*) as new_count FROM links"
];

$old_verify_queries = [
    'users' => "SELECT COUNT(*) as old_count FROM {$OLD_TABLE_PREFIX}users",
    'profiles' => "SELECT COUNT(*) as old_count FROM {$OLD_TABLE_PREFIX}profiles",
    'links' => "SELECT COUNT(*) as old_count FROM {$OLD_TABLE_PREFIX}links"
];

foreach ($verify_queries as $table => $query) {
    $new_result = mysqli_fetch_assoc(mysqli_query($conn, $query));
    $old_result = mysqli_fetch_assoc(mysqli_query($conn, $old_verify_queries[$table]));
    
    if ($new_result['new_count'] == $old_result['old_count']) {
        log_message("✓ {$table}: {$new_result['new_count']} records (verified)", 'SUCCESS');
    } else {
        log_error("{$table}: Mismatch! Old: {$old_result['old_count']}, New: {$new_result['new_count']}");
    }
}

echo "<script>document.getElementById('progress').style.width='100%';document.getElementById('progress').innerText='100%';</script>";
flush();

// Final summary
log_message("=====================================", 'info');
log_message("MIGRATION COMPLETED!", 'SUCCESS');
log_message("=====================================", 'info');

if (count($errors) > 0) {
    log_message("⚠️ Migration completed with " . count($errors) . " errors", 'WARNING');
    log_message("Please review errors above and fix manually", 'WARNING');
} else {
    log_message("✅ Migration completed successfully with no errors!", 'SUCCESS');
}

if (count($warnings) > 0) {
    log_message("⚠️ " . count($warnings) . " warnings were generated", 'WARNING');
}

?>
</div>

<div class="step">
    <h3>✅ Next Steps</h3>
    <ol style="margin-left:20px;line-height:1.8;">
        <li>Test the website thoroughly</li>
        <li>Check admin/profiles.php stats display</li>
        <li>Test profile switching</li>
        <li>Test link creation and clicking</li>
        <li>If everything works: Drop old tables with <code>DROP TABLE old_users, old_profiles, old_links, ...</code></li>
        <li>If problems occur: Rollback using backup</li>
    </ol>
</div>

<div class="btn-group">
    <a href="admin/dashboard.php" class="btn btn-success">
        ✅ Go to Dashboard
    </a>
    <a href="admin/profiles.php" class="btn btn-primary">
        👀 Check Profiles
    </a>
</div>

<?php
// Save log to file
file_put_contents('migration_log_' . date('Ymd_His') . '.txt', implode("\n", $migration_log));
?>

    </div>
</div>
</body>
</html>
