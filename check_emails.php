<?php
/**
 * Check emails for a specific address
 */

require_once 'config.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $recipient_email = $_GET['email'] ?? '';
    
    if (empty($recipient_email)) {
        echo json_encode(['error' => 'No email specified']);
        exit;
    }
    
    // Get emails for this recipient
    $stmt = $pdo->prepare("
        SELECT e.*, d.domain_name 
        FROM emails e 
        JOIN domains d ON e.domain_id = d.id 
        WHERE e.recipient_email = ? 
        AND e.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY e.created_at DESC
        LIMIT 50
    ");
    
    $stmt->execute([$recipient_email]);
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for frontend
    $formatted_emails = [];
    foreach ($emails as $email) {
        $formatted_emails[] = [
            'id' => $email['id'],
            'sender' => $email['sender_email'],
            'subject' => $email['subject'],
            'body' => $email['body'],
            'preview' => strlen($email['body']) > 100 ? substr($email['body'], 0, 100) . '...' : $email['body'],
            'code' => $email['verification_code'],
            'date' => $email['created_at'],
            'read' => (bool)$email['is_read']
        ];
    }
    
    echo json_encode($formatted_emails);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>