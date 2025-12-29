<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ghostmail'); 
define('DB_USER', 'admin_n'); 
define('DB_PASS', 'WorkHard@123');

// Security configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_TIME', 900); // 15 minutes

// Site configuration
define('SITE_URL', 'http://69.164.245.208');
define('ADMIN_EMAIL', 'admin@localhost');

// Admin password hash
// Run this command on your VPS to generate hash for your admin password:
// php -r "echo password_hash('YOUR_ADMIN_PASSWORD', PASSWORD_DEFAULT);"
// Then replace the hash below
define('ADMIN_PASSWORD_HASH', '$2a$12$389pag9t8c4Wdan4L9XrPOG4TtXqUvYt1OZ8984/4/fNfeY1FSxpG');
?>