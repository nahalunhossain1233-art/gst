<?php
/**
 * Real DNS checking backend
 */

header('Content-Type: application/json');

$domain = $_GET['domain'] ?? '';

if (empty($domain)) {
    echo json_encode(['error' => 'No domain specified']);
    exit;
}

// Remove protocol if present
$domain = str_replace(['http://', 'https://', 'www.'], '', $domain);

$checks = [];
$allGood = true;

// Check MX record
exec("dig mx $domain +short", $mx_output, $mx_return);
$checks[] = [
    'type' => 'MX',
    'status' => !empty($mx_output) && strpos(implode(' ', $mx_output), '69.164.245.208') !== false,
    'message' => 'Mail server record'
];

// Check A record for mail subdomain
exec("dig a mail.$domain +short", $a_output, $a_return);
$checks[] = [
    'type' => 'A',
    'status' => !empty($a_output) && in_array('69.164.245.208', $a_output),
    'message' => 'Mail subdomain'
];

// Check TXT/SPF record
exec("dig txt $domain +short", $txt_output, $txt_return);
$has_spf = false;
foreach ($txt_output as $txt) {
    if (strpos($txt, 'v=spf1') !== false && strpos($txt, '69.164.245.208') !== false) {
        $has_spf = true;
        break;
    }
}
$checks[] = [
    'type' => 'TXT',
    'status' => $has_spf,
    'message' => 'SPF record'
];

// Check if all are good
$allGood = count(array_filter($checks, function($c) { return $c['status']; })) >= 2;

echo json_encode([
    'checks' => $checks,
    'allGood' => $allGood,
    'domain' => $domain
]);
?>