<?php
$host = 'localhost';
$user = 'admin_n';
$pass = 'WorkHard@123';
$dbname = 'ghostmail';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    
    // Table for email routing
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_aliases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source_email VARCHAR(255) NOT NULL,
        destination_email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_source (source_email)
    )");
    
    // Table for DNS records
    $pdo->exec("CREATE TABLE IF NOT EXISTS dns_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        domain_id INT,
        record_type VARCHAR(10) DEFAULT 'MX',
        record_value VARCHAR(255),
        priority INT DEFAULT 10,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
    )");
    
    // Table for email storage
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        domain_id INT,
        recipient VARCHAR(255),
        sender VARCHAR(255),
        subject TEXT,
        body LONGTEXT,
        headers TEXT,
        received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
    )");
    
    echo "✅ Email tables created successfully!";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>