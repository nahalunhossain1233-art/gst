<?php
// Database credentials - USE YOUR ACTUAL CREDENTIALS
$host = 'localhost';
$user = 'admin_n';
$pass = 'WorkHard@123';
$dbname = 'ghostmail';

try {
    // Connect to MySQL without selecting database first
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Connected to MySQL server successfully<br>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$dbname' created or already exists<br>";
    
    // Use the database
    $pdo->exec("USE $dbname");
    
    // 1. Create domains table
    $pdo->exec("CREATE TABLE IF NOT EXISTS domains (
        id INT AUTO_INCREMENT PRIMARY KEY,
        domain_name VARCHAR(255) NOT NULL UNIQUE,
        display_name VARCHAR(255) NOT NULL,
        status ENUM('active', 'paused', 'disabled') DEFAULT 'active',
        email_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Table 'domains' created<br>";
    
    // 2. Create login_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45),
        success BOOLEAN DEFAULT FALSE,
        attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Table 'login_logs' created<br>";
    
    // 3. Create emails table
    $pdo->exec("CREATE TABLE IF NOT EXISTS emails (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_email VARCHAR(255) NOT NULL,
        recipient_email VARCHAR(255) NOT NULL,
        subject TEXT,
        body LONGTEXT,
        verification_code VARCHAR(100),
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 24 HOUR)
    )");
    echo "✅ Table 'emails' created<br>";
    
    // 4. Insert default domains if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM domains");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        $defaultDomains = [
            ['ghostmail.dev', '@ghostmail.dev', 'active'],
            ['temp.shadow', '@temp.shadow', 'active'],
            ['phantom.box', '@phantom.box', 'paused'],
            ['spectre.me', '@spectre.me', 'disabled']
        ];
        
        foreach ($defaultDomains as $domain) {
            $stmt = $pdo->prepare("INSERT INTO domains (domain_name, display_name, status) VALUES (?, ?, ?)");
            $stmt->execute($domain);
        }
        echo "✅ Default domains inserted<br>";
    } else {
        echo "✅ Domains table already has data<br>";
    }
    
    // 5. Create admin user in config table (optional)
    $pdo->exec("CREATE TABLE IF NOT EXISTS config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ Table 'config' created<br>";
    
    // Insert initial config if empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM config");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO config (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute(['site_name', 'GhostMail']);
        $stmt->execute(['site_url', 'http://69.164.245.208']);
        echo "✅ Config settings inserted<br>";
    }
    
    echo "<br><strong>🎉 Database setup completed successfully!</strong><br>";
    echo "Database: <strong>$dbname</strong><br>";
    echo "User: <strong>$user</strong><br>";
    echo "Tables created: domains, login_logs, emails, config<br>";
    
    // Test connection
    $test = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$dbname'");
    $tables = $test->fetch(PDO::FETCH_ASSOC);
    echo "Total tables in database: <strong>" . $tables['table_count'] . "</strong><br>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; background: #ffeeee; padding: 10px; border: 1px solid red;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage() . "<br>";
    echo "Check your credentials:<br>";
    echo "- Host: $host<br>";
    echo "- Username: $user<br>";
    echo "- Password: " . str_repeat('*', strlen($pass)) . "<br>";
    echo "- Database: $dbname<br>";
    echo "</div>";
    
    // Debug connection without password
    echo "<br><strong>Debug info:</strong><br>";
    echo "Try connecting manually: <code>mysql -u $user -p</code><br>";
    echo "Check if MySQL is running: <code>sudo systemctl status mysql</code><br>";
    echo "Check if user exists: <code>mysql -u root -p -e \"SELECT User, Host FROM mysql.user WHERE User='$user';\"</code>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .success {
            color: green;
            background: #e8f5e8;
            padding: 10px;
            border: 1px solid green;
            margin: 5px 0;
        }
        .error {
            color: red;
            background: #ffeeee;
            padding: 10px;
            border: 1px solid red;
            margin: 5px 0;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border: 1px solid #2196f3;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="info">
        <strong>Important:</strong> Delete this file after setup!<br>
        <code>sudo rm /var/www/html/setup_database.php</code>
    </div>
    
    <h3>Next Steps:</h3>
    <ol>
        <li>Create <strong>config.php</strong> with your database credentials</li>
        <li>Generate admin password hash: <code>php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_DEFAULT);"</code></li>
        <li>Visit <a href="http://69.164.245.208">http://69.164.245.208</a></li>
        <li>Login to admin: <a href="http://69.164.245.208/login.php">http://69.164.245.208/login.php</a></li>
    </ol>
</body>
</html>