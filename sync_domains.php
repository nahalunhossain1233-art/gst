<?php
// Auto-sync domains from database to frontend
require_once 'config.php';

$pdo = new PDO(
    "mysql:host=localhost;dbname=ghostmail",
    "admin_n",
    "WorkHard@123"
);

$stmt = $pdo->query("SELECT display_name FROM domains WHERE status = 'active'");
$domains = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

$js = "const availableDomains = " . json_encode($domains) . ";\n";
$js .= "function updateDomainSelect() {\n";
$js .= "    const select = document.getElementById('domainSelect');\n";
$js .= "    if (select && availableDomains.length > 0) {\n";
$js .= "        select.innerHTML = '';\n";
$js .= "        availableDomains.forEach(domain => {\n";
$js .= "            const option = document.createElement('option');\n";
$js .= "            option.value = domain;\n";
$js .= "            option.textContent = domain;\n";
$js .= "            select.appendChild(option);\n";
$js .= "        });\n";
$js .= "        select.selectedIndex = 0;\n";
$js .= "    }\n";
$js .= "}\n";
$js .= "window.addEventListener('DOMContentLoaded', updateDomainSelect);";

file_put_contents('/var/www/html/domains.js', $js);
echo "✅ Domains synced: " . implode(', ', $domains);
?>