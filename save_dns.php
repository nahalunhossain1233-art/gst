<?php
session_start();
require_once 'config.php';

// Database connection
$pdo = new PDO(
    "mysql:host=localhost;dbname=ghostmail",
    "admin_n",
    "WorkHard@123"
);

$data = json_decode(file_get_contents('php://input'), true);
$domain_id = $data['domain_id'] ?? 0;
$domain_name = $data['domain_name'] ?? '';

if ($domain_name) {
    // Generate and store DNS records
    $records = [
        ['MX', '@', 'mail.69.164.245.208', 10],
        ['A', 'mail.' . $domain_name, '69.164.245.208', 3600],
        ['TXT', '@', 'v=spf1 mx ip4:69.164.245.208 ~all', 3600],
        ['TXT', 'default._domainkey', 'v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD...', 3600],
        ['TXT', '_dmarc', 'v=DMARC1; p=none; rua=mailto:admin@' . $domain_name, 3600]
    ];
    
    foreach ($records as $record) {
        $stmt = $pdo->prepare("INSERT INTO dns_records (domain_id, record_type, record_name, record_value, ttl) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$domain_id, $record[0], $record[1], $record[2], $record[3]]);
    }
    
    echo json_encode(['success' => true, 'message' => 'DNS records saved']);
}
?>
