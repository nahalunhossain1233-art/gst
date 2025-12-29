<?php
/**
 * GhostMail Email Receiver
 * This script receives emails via Postfix and stores them in database
 */

// Disable error display in production
error_reporting(0);
ini_set('display_errors', 0);

// Include database configuration
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

/**
 * Parse raw email content
 */
function parseEmail($raw_email) {
    $email = [
        'headers' => [],
        'body' => '',
        'subject' => '',
        'from' => '',
        'to' => '',
        'date' => date('Y-m-d H:i:s'),
        'text_body' => '',
        'html_body' => ''
    ];
    
    // Split headers and body
    list($headers, $body) = explode("\r\n\r\n", $raw_email, 2);
    
    // Parse headers
    $header_lines = explode("\r\n", $headers);
    foreach ($header_lines as $line) {
        if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
            $key = strtolower(trim($matches[1]));
            $value = trim($matches[2]);
            $email['headers'][$key] = $value;
            
            // Extract important headers
            switch ($key) {
                case 'subject':
                    $email['subject'] = $value;
                    break;
                case 'from':
                    $email['from'] = $value;
                    // Extract email address from "Name <email@domain.com>"
                    if (preg_match('/<([^>]+)>/', $value, $email_match)) {
                        $email['from_email'] = $email_match[1];
                    } else {
                        $email['from_email'] = $value;
                    }
                    break;
                case 'to':
                case 'delivered-to':
                case 'envelope-to':
                    $email['to'] = $value;
                    // Extract email address
                    if (preg_match('/<([^>]+)>/', $value, $email_match)) {
                        $email['to_email'] = $email_match[1];
                    } else {
                        $email['to_email'] = $value;
                    }
                    break;
                case 'date':
                    $email['date'] = date('Y-m-d H:i:s', strtotime($value));
                    break;
            }
        }
    }
    
    // Parse multipart email
    if (isset($email['headers']['content-type']) && 
        strpos($email['headers']['content-type'], 'multipart/') !== false) {
        
        $boundary = '';
        if (preg_match('/boundary="([^"]+)"/', $email['headers']['content-type'], $matches)) {
            $boundary = $matches[1];
        } elseif (preg_match('/boundary=([^\s;]+)/', $email['headers']['content-type'], $matches)) {
            $boundary = $matches[1];
        }
        
        if ($boundary) {
            $parts = explode("--$boundary", $body);
            
            foreach ($parts as $part) {
                if (strpos($part, 'Content-Type: text/plain') !== false) {
                    // Extract plain text body
                    $part_parts = explode("\r\n\r\n", $part, 2);
                    if (count($part_parts) > 1) {
                        $email['text_body'] = trim($part_parts[1]);
                        $email['body'] = $email['text_body'];
                    }
                } elseif (strpos($part, 'Content-Type: text/html') !== false) {
                    // Extract HTML body
                    $part_parts = explode("\r\n\r\n", $part, 2);
                    if (count($part_parts) > 1) {
                        $email['html_body'] = trim($part_parts[1]);
                    }
                }
            }
        }
    } else {
        // Simple email, use entire body
        $email['body'] = $body;
        $email['text_body'] = $body;
    }
    
    // Clean up body
    $email['body'] = trim($email['body']);
    $email['text_body'] = trim($email['text_body']);
    
    // Create preview (first 100 chars)
    $preview = strip_tags($email['body']);
    $email['preview'] = strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
    
    // Extract verification codes (common patterns)
    $code_patterns = [
        '/\b\d{6}\b/', // 6-digit code
        '/\b\d{4}\b/', // 4-digit code
        '/verification code:?\s*(\w+)/i',
        '/code:?\s*(\w+)/i',
        '/\b[A-Z0-9]{6,12}\b/' // Alphanumeric codes
    ];
    
    $email['verification_code'] = '';
    foreach ($code_patterns as $pattern) {
        if (preg_match($pattern, $email['body'], $matches)) {
            $email['verification_code'] = $matches[1] ?? $matches[0];
            break;
        }
    }
    
    return $email;
}

/**
 * Store email in database
 */
function storeEmail($email_data) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Check if recipient domain exists
        $domain_name = explode('@', $email_data['to_email'])[1] ?? '';
        $stmt = $pdo->prepare("SELECT id FROM domains WHERE domain_name = ? AND status = 'active'");
        $stmt->execute([$domain_name]);
        $domain = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$domain) {
            return ['success' => false, 'error' => 'Domain not active or not found'];
        }
        
        $domain_id = $domain['id'];
        
        // Insert email into database
        $stmt = $pdo->prepare("
            INSERT INTO emails 
            (domain_id, recipient_email, sender_email, subject, body, verification_code, created_at, expires_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ");
        
        $stmt->execute([
            $domain_id,
            $email_data['to_email'],
            $email_data['from_email'],
            $email_data['subject'],
            $email_data['body'],
            $email_data['verification_code']
        ]);
        
        $email_id = $pdo->lastInsertId();
        
        // Update domain email count
        $stmt = $pdo->prepare("UPDATE domains SET email_count = email_count + 1 WHERE id = ?");
        $stmt->execute([$domain_id]);
        
        // Log the receipt
        error_log("Email received: {$email_data['to_email']} from {$email_data['from_email']}");
        
        return [
            'success' => true, 
            'email_id' => $email_id,
            'message' => 'Email stored successfully'
        ];
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Main execution
 */
try {
    // Get raw email content
    $raw_email = file_get_contents('php://input');
    
    if (empty($raw_email)) {
        // For testing: create a test email
        if (isset($_GET['test'])) {
            $raw_email = "From: test@sender.com\r\n"
                . "To: test@ghostmail.dev\r\n"
                . "Subject: Test Email\r\n"
                . "Date: " . date('r') . "\r\n"
                . "\r\n"
                . "This is a test email body with code: 123456";
        } else {
            echo json_encode(['success' => false, 'error' => 'No email content received']);
            exit;
        }
    }
    
    // Parse the email
    $email_data = parseEmail($raw_email);
    
    // Store in database
    $result = storeEmail($email_data);
    
    // Save raw email to file for debugging (optional)
    if (isset($_GET['debug'])) {
        $filename = '/tmp/ghostmail_' . date('Y-m-d_H-i-s') . '.eml';
        file_put_contents($filename, $raw_email);
        $result['debug_file'] = $filename;
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Alternative: Webhook endpoint for testing email reception
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $test_email = [
        'to_email' => $_POST['to'] ?? 'test@ghostmail.dev',
        'from_email' => $_POST['from'] ?? 'sender@example.com',
        'subject' => $_POST['subject'] ?? 'Test Email',
        'body' => $_POST['body'] ?? 'This is a test email body.',
        'verification_code' => $_POST['code'] ?? ''
    ];
    
    $result = storeEmail($test_email);
    echo json_encode($result);
    exit;
}
?>