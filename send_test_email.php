<?php
/**
 * Send test email endpoint
 */

require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $to = $data['to'] ?? '';
    $from = $data['from'] ?? 'test@ghostmail.dev';
    $subject = $data['subject'] ?? 'Test Email';
    $body = $data['body'] ?? 'This is a test email.';
    
    if (empty($to)) {
        echo json_encode(['success' => false, 'error' => 'No recipient specified']);
        exit;
    }
    
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Extract domain
        $domain_name = explode('@', $to)[1] ?? '';
        $stmt = $pdo->prepare("SELECT id FROM domains WHERE domain_name = ? AND status = 'active'");
        $stmt->execute([$domain_name]);
        $domain = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$domain) {
            echo json_encode(['success' => false, 'error' => 'Domain not found or inactive']);
            exit;
        }
        
        $domain_id = $domain['id'];
        
        // Generate verification code
        $verification_code = strval(rand(100000, 999999));
        
        // Insert test email
        $stmt = $pdo->prepare("
            INSERT INTO emails 
            (domain_id, recipient_email, sender_email, subject, body, verification_code, created_at, expires_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ");
        
        $stmt->execute([
            $domain_id,
            $to,
            $from,
            $subject,
            $body . "\n\nVerification Code: " . $verification_code,
            $verification_code
        ]);
        
        // Update domain email count
        $stmt = $pdo->prepare("UPDATE domains SET email_count = email_count + 1 WHERE id = ?");
        $stmt->execute([$domain_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Test email sent successfully',
            'code' => $verification_code
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
?>