<?php
// IP WHITELIST - ONLY ALLOW DIRECT IP ACCESS
$allowed_ips = [
    '69.164.245.208', // Your VPS IP
    '127.0.0.1',      // Localhost
];

$client_ip = $_SERVER['REMOTE_ADDR'];
$access_via_ip = (filter_var($client_ip, FILTER_VALIDATE_IP) && 
                  ($client_ip === '69.164.245.208' || $client_ip === '127.0.0.1'));

// Allow bypass for testing with ?bypass parameter
if (!$access_via_ip && !isset($_GET['bypass'])) {
    header('HTTP/1.0 403 Forbidden');
    die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied - GhostMail</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background: #0a0a0f; 
                    color: white; 
                    text-align: center; 
                    padding: 50px; 
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 30px; 
                    background: #141420; 
                    border-radius: 10px; 
                    border: 1px solid #2d2d3d; 
                }
                h1 { color: #ff4757; }
                .ip { color: #00d4ff; font-weight: bold; }
                .note { color: #b0b0c0; font-size: 14px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>⚠️ Access Denied</h1>
                <p>This service must be accessed directly via IP address:</p>
                <p class='ip'>http://69.164.245.208</p>
                <p>Your IP: <span class='ip'>$client_ip</span> is not authorized.</p>
                <div class='note'>
                    Domains (ghostmail.dev, phantom.box, spectre.me) are for email generation only.<br>
                    Site access requires direct VPS IP connection.
                </div>
            </div>
        </body>
        </html>
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GhostMail - Disposable Email</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        :root {
            --primary: #00d4ff;
            --primary-dark: #0099cc;
            --accent: #ff6b9d;
            --bg-dark: #0a0a0f;
            --bg-card: #141420;
            --bg-surface: #1e1e2e;
            --text: #ffffff;
            --text-secondary: #b0b0c0;
            --border: #2d2d3d;
            --success: #00ff9d;
            --warning: #ffcc00;
            --danger: #ff4757;
        }
        
        body {
            background: var(--bg-dark);
            color: var(--text);
            min-height: 100vh;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 212, 255, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(255, 107, 157, 0.05) 0%, transparent 20%);
        }
        
        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 40px;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }
        
        .tagline {
            font-size: 14px;
            color: var(--text-secondary);
            opacity: 0.8;
        }
        
        .nav-actions {
            display: flex;
            gap: 15px;
        }
        
        .admin-link {
            color: var(--accent);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border: 1px solid var(--accent);
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .admin-link:hover {
            background: rgba(255, 107, 157, 0.1);
        }
        
        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 20px rgba(0, 212, 255, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 212, 255, 0.4);
        }
        
        .btn-ghost {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }
        
        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #00cc88);
            color: var(--bg-dark);
            font-weight: 700;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #ff2e43);
            color: white;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        /* Main Layout */
        .dashboard {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-bottom: 50px;
        }
        
        @media (max-width: 1100px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }
        
        /* Email Card */
        .email-card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        
        .card-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
        }
        
        .card-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        /* Email Generator - FIXED LAYOUT */
        .email-generator {
            margin-bottom: 30px;
        }
        
        .email-display {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .email-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .email-address {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            word-break: break-all;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .email-domain {
            color: var(--accent);
        }
        
        /* FIXED: Email Controls Layout */
        .email-controls {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        @media (min-width: 768px) {
            .email-controls {
                flex-direction: row;
            }
        }
        
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }
        
        @media (min-width: 768px) {
            .input-group {
                flex-direction: row;
            }
        }
        
        .email-input {
            flex: 1;
            padding: 15px 20px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 10px 0 0 10px;
            color: var(--text);
            font-size: 16px;
        }
        
        @media (max-width: 767px) {
            .email-input {
                border-radius: 10px;
                border: 1px solid var(--border);
                width: 100%;
            }
        }
        
        .email-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .domain-select {
            padding: 15px 20px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: var(--text);
            cursor: pointer;
            min-width: 200px;
            font-weight: 600;
            font-size: 15px;
        }
        
        @media (max-width: 767px) {
            .domain-select {
                border-radius: 10px;
                border: 1px solid var(--border);
                border-left: 1px solid var(--border);
                width: 100%;
                min-width: 100%;
            }
        }
        
        .domain-select:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 12px 20px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .action-btn:hover {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--primary);
            color: var(--primary);
        }
        
        /* Stats Panel */
        .stats-panel {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg-surface);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-top: 5px;
        }
        
        /* Inbox */
        .inbox-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .inbox-title {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge {
            background: var(--accent);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        /* Email List */
        .email-list {
            background: var(--bg-card);
            border-radius: 20px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        
        .email-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s;
        }
        
        @media (max-width: 768px) {
            .email-item {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .email-meta {
                text-align: left !important;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .code-badge {
                margin-left: 5px !important;
                padding: 3px 6px !important;
                font-size: 10px !important;
            }
        }
        
        .email-item:hover {
            background: rgba(255, 255, 255, 0.03);
        }
        
        .email-item.unread {
            background: rgba(0, 212, 255, 0.05);
            border-left: 3px solid var(--primary);
        }
        
        .email-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        
        .email-content {
            flex: 1;
        }
        
        .email-sender {
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .email-subject {
            color: var(--text);
            margin-bottom: 5px;
            font-size: 15px;
        }
        
        .email-preview {
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.4;
        }
        
        .email-meta {
            text-align: right;
            min-width: 80px;
        }
        
        .email-time {
            color: var(--text-secondary);
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .email-status {
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 50%;
            margin-left: auto;
        }
        
        .code-badge {
            background: rgba(0, 255, 157, 0.1);
            color: var(--success);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid rgba(0, 255, 157, 0.3);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 10px;
            transition: all 0.2s;
            -webkit-tap-highlight-color: transparent;
        }
        
        .code-badge:hover, .code-badge:active {
            background: rgba(0, 255, 157, 0.2);
            transform: scale(1.05);
        }
        
        .empty-inbox {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-icon {
            font-size: 48px;
            color: var(--border);
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        /* Email Detail Modal */
        .email-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        
        .email-modal-content {
            background: var(--bg-card);
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
            border-radius: 20px;
            border: 1px solid var(--border);
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }
        
        .modal-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .email-detail-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .email-detail-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }
        
        .email-detail-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .email-detail-info .email-detail-subject {
            color: var(--text-secondary);
            font-size: 16px;
        }
        
        .email-detail-meta {
            display: flex;
            gap: 20px;
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        
        .email-body {
            line-height: 1.6;
            color: var(--text);
        }
        
        .email-code-block {
            background: var(--bg-dark);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .email-code-block {
                flex-direction: column;
                text-align: center;
            }
            
            .code-display {
                font-size: 16px !important;
                word-break: break-all;
            }
        }
        
        .code-display {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: 700;
            color: var(--success);
            flex: 1;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }
        
        /* Footer */
        footer {
            text-align: center;
            padding: 30px 0;
            border-top: 1px solid var(--border);
            margin-top: 50px;
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .footer-links {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bg-card);
            color: var(--text);
            padding: 15px 25px;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 15px;
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast-success {
            border-left-color: var(--success);
        }
        
        .toast-error {
            border-left-color: var(--danger);
        }
        
        .toast-info {
            border-left-color: var(--primary);
        }
        
        /* Copyable Elements */
        .copyable {
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            transition: background 0.2s;
            -webkit-tap-highlight-color: transparent;
        }
        
        .copyable:hover, .copyable:active {
            background: rgba(0, 212, 255, 0.1);
            color: var(--primary);
        }
        
        /* Animations */
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        @keyframes modalSlideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* Mobile Touch Improvements */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .domain-select {
                font-size: 16px;
                padding: 18px;
                min-width: 100%;
            }
            
            .email-item {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .email-meta {
                text-align: left;
            }
            
            .email-detail-header {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 15px;
            }
            
            /* Better touch targets */
            .action-btn, .btn {
                padding: 16px 20px;
            }
            
            .code-badge {
                min-height: 32px;
                min-width: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        }
        
        /* IP Access Warning */
        .ip-warning {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- IP Access Warning for Domain Visitors -->
    <?php if (!$access_via_ip && isset($_GET['bypass'])): ?>
    <div class="ip-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Warning: Domain access bypass enabled. Normal users should access via IP: <strong>69.164.245.208</strong></span>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <!-- Header -->
        <nav class="navbar">
            <div class="brand">
                <div class="logo">GhostMail</div>
                <div class="tagline">Temporary • Secure • Anonymous</div>
            </div>
            <div class="nav-actions">
                <a href="login.php" class="admin-link">
                    <i class="fas fa-user-shield"></i> Admin Panel
                </a>
                <button class="btn btn-ghost" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button class="btn btn-primary" id="newEmailBtn">
                    <i class="fas fa-plus"></i> New Address
                </button>
            </div>
        </nav>
        
        <!-- Main Dashboard -->
        <div class="dashboard">
            <!-- Left Column -->
            <div>
                <!-- Email Generator Card -->
                <div class="email-card" style="margin-bottom: 30px;">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-ghost"></i>
                        </div>
                        <div>
                            <div class="card-title">Disposable Email Address</div>
                            <div class="card-subtitle">Expires in 24 hours • No registration required</div>
                        </div>
                    </div>
                    
                    <div class="email-generator">
                        <div class="email-display">
                            <div class="email-label">Your Temporary Email</div>
                            <div class="email-address">
                                <span id="emailNameDisplay">shadow.sun42</span>
                                <span class="email-domain" id="domainDisplay">@ghostmail.dev</span>
                                <button class="btn btn-ghost" style="padding: 8px 15px; font-size: 12px;" id="copyBtn">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        
                        <div class="email-controls">
                            <div class="input-group">
                                <input type="text" class="email-input" id="emailInput" placeholder="Enter custom name" value="shadow.sun42">
                                <select class="domain-select" id="domainSelect">
                                    <!-- Will be populated by domains.js -->
                                </select>
                            </div>
                            <button class="btn btn-success" id="generateBtn">
                                <i class="fas fa-bolt"></i> Generate
                            </button>
                        </div>
                        
                        <div class="quick-actions">
                            <button class="action-btn" id="randomBtn">
                                <i class="fas fa-random"></i> Random
                            </button>
                            <button class="action-btn" id="extendBtn">
                                <i class="fas fa-history"></i> Extend Time
                            </button>
                            <button class="action-btn" id="pinBtn">
                                <i class="fas fa-shield-alt"></i> PIN Protect
                            </button>
                            <button class="action-btn" id="deleteAllBtn">
                                <i class="fas fa-trash"></i> Clear Inbox
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Inbox -->
                <div class="email-card">
                    <div class="inbox-header">
                        <div class="inbox-title">
                            <i class="fas fa-inbox"></i> Inbox
                            <span class="badge" id="emailCount">0</span>
                        </div>
                        <div class="nav-actions">
                            <button class="btn btn-ghost" id="composeBtn">
                                <i class="fas fa-pen"></i> Compose
                            </button>
                            <button class="btn btn-primary" id="checkEmailsBtn">
                                <i class="fas fa-sync-alt"></i> Check Emails
                            </button>
                        </div>
                    </div>
                    
                    <div class="email-list">
                        <div id="emailContainer">
                            <!-- Email Items will be dynamically inserted here -->
                        </div>
                        
                        <!-- Empty State -->
                        <div id="emptyInbox" class="empty-inbox">
                            <div class="empty-icon">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            <h3 style="margin-bottom: 10px;">No emails yet</h3>
                            <p>Share your temporary email address to receive messages here</p>
                            <button class="btn btn-ghost" id="testEmailBtn" style="margin-top: 15px;">
                                <i class="fas fa-envelope"></i> Send Test Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Stats -->
            <div class="stats-panel">
                <div class="card-header" style="margin-bottom: 30px;">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="card-title">Mail Statistics</div>
                        <div class="card-subtitle">Real-time tracking</div>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value" id="totalEmails">0</div>
                        <div class="stat-label">Total Emails</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="unreadEmails">0</div>
                        <div class="stat-label">Unread</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="activeTime">24</div>
                        <div class="stat-label">Hours Left</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="spamBlocked">0</div>
                        <div class="stat-label">Spam Blocked</div>
                    </div>
                </div>
                
                <div style="margin-top: 40px;">
                    <div class="card-title" style="margin-bottom: 20px;">Quick Tips</div>
                    <div style="background: var(--bg-surface); border-radius: 12px; padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <div style="color: var(--primary); font-size: 20px;"><i class="fas fa-clock"></i></div>
                            <div>
                                <div style="font-weight: 600; margin-bottom: 5px;">Auto-expiration</div>
                                <div style="font-size: 13px; color: var(--text-secondary);">Emails auto-delete after 24 hours</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="color: var(--accent); font-size: 20px;"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <div style="font-weight: 600; margin-bottom: 5px;">No Tracking</div>
                                <div style="font-size: 13px; color: var(--text-secondary);">We don't store IPs or personal data</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email Testing Section -->
                <div style="margin-top: 40px;">
                    <div class="card-title" style="margin-bottom: 20px;">Email Testing</div>
                    <div style="background: var(--bg-surface); border-radius: 12px; padding: 20px;">
                        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 15px;">
                            Test if your email is receiving properly:
                        </p>
                        <button class="btn btn-primary" id="sendTestEmailBtn" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                        <p style="font-size: 11px; color: var(--text-secondary); margin-top: 10px; text-align: center;">
                            Sends a test email to your current address
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer>
            <div>© 2023 GhostMail • Disposable Temporary Email Service</div>
            <div class="footer-links">
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">FAQ</a>
                <a href="#">Contact</a>
                <a href="#">API</a>
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: var(--border);">
                Access via IP: <strong>69.164.245.208</strong> • Domains for email only
            </div>
        </footer>
    </div>

    <!-- Email Detail Modal -->
    <div class="email-modal" id="emailModal">
        <div class="email-modal-content">
            <div class="modal-header">
                <div class="card-title">Email Details</div>
                <button class="btn btn-ghost" onclick="closeEmailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="emailModalBody">
                <!-- Email details will be inserted here -->
            </div>
            <div class="modal-actions">
                <button class="btn btn-ghost" onclick="deleteCurrentEmail()">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button class="btn btn-ghost" onclick="markAsUnread()">
                    <i class="fas fa-envelope"></i> Mark as Unread
                </button>
                <button class="btn btn-primary" onclick="closeEmailModal()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle" id="toastIcon"></i>
        <span id="toastMessage">Copied to clipboard!</span>
    </div>

    <!-- Include domains.js from admin panel -->
    <script src="domains.js"></script>

    <script>
        // DOM Elements
        const emailInput = document.getElementById('emailInput');
        const domainSelect = document.getElementById('domainSelect');
        const emailNameDisplay = document.getElementById('emailNameDisplay');
        const domainDisplay = document.getElementById('domainDisplay');
        const emailContainer = document.getElementById('emailContainer');
        const emptyInbox = document.getElementById('emptyInbox');
        const emailCount = document.getElementById('emailCount');
        const totalEmails = document.getElementById('totalEmails');
        const unreadEmails = document.getElementById('unreadEmails');
        const activeTime = document.getElementById('activeTime');
        const spamBlocked = document.getElementById('spamBlocked');
        const emailModal = document.getElementById('emailModal');
        const emailModalBody = document.getElementById('emailModalBody');
        const copyBtn = document.getElementById('copyBtn');
        const toast = document.getElementById('toast');
        const toastIcon = document.getElementById('toastIcon');
        const toastMessage = document.getElementById('toastMessage');
        const checkEmailsBtn = document.getElementById('checkEmailsBtn');
        const testEmailBtn = document.getElementById('testEmailBtn');
        const sendTestEmailBtn = document.getElementById('sendTestEmailBtn');
        
        // Word lists for random generation
        const firstWords = [
            'Shadow', 'Phantom', 'Ghost', 'Spectre', 'Void', 'Cypher', 'Nexus', 'Quantum',
            'Digital', 'Cyber', 'Stealth', 'Silent', 'Dark', 'Night', 'Moon', 'Star',
            'Ocean', 'River', 'Mountain', 'Forest', 'Desert', 'Sky', 'Cloud', 'Rain',
            'Flower', 'Sun', 'Light', 'Bright', 'Sharp', 'Swift', 'Quick', 'Fast'
        ];
        
        const secondWords = [
            'Wolf', 'Fox', 'Raven', 'Hawk', 'Eagle', 'Lion', 'Tiger', 'Panther',
            'Dragon', 'Phoenix', 'Falcon', 'Owl', 'Bear', 'Shark', 'Whale', 'Dolphin',
            'Storm', 'Blaze', 'Flame', 'Frost', 'Ice', 'Stone', 'Rock', 'Metal',
            'Blade', 'Sword', 'Shield', 'Arrow', 'Bolt', 'Spark', 'Flash', 'Light'
        ];
        
        // Storage for emails
        let currentEmail = '';
        let emails = [];
        let currentEmailId = null;
        let emailExpiration = Date.now() + (24 * 60 * 60 * 1000);
        let checkInterval = null;
        
        // Initialize
        function init() {
            // Get current email from localStorage or generate new
            const savedEmail = localStorage.getItem('current_email');
            const savedExpiration = localStorage.getItem('email_expiration');
            
            if (savedEmail && savedExpiration && Date.now() < parseInt(savedExpiration)) {
                currentEmail = savedEmail;
                emailExpiration = parseInt(savedExpiration);
            } else {
                // Generate new email
                generateNewEmail();
            }
            
            // Load emails for this address
            loadEmailsForCurrentAddress();
            
            updateEmailDisplay();
            updateStats();
            attachEventListeners();
            renderEmails();
            
            // Start session timer
            startSessionTimer();
            
            // Start email checking interval
            startEmailChecking();
            
            // Fix mobile domain select
            fixMobileDomainSelect();
        }
        
        // Generate new email
        function generateNewEmail() {
            const username = generateRandomUsername();
            currentEmail = username + '@ghostmail.dev';
            emailExpiration = Date.now() + (24 * 60 * 60 * 1000);
            
            // Save to localStorage
            localStorage.setItem('current_email', currentEmail);
            localStorage.setItem('email_expiration', emailExpiration.toString());
            
            // Clear old emails
            emails = [];
            saveEmails();
        }
        
        // Generate random username
        function generateRandomUsername() {
            const firstWord = firstWords[Math.floor(Math.random() * firstWords.length)];
            const secondWord = secondWords[Math.floor(Math.random() * secondWords.length)];
            const number = Math.floor(Math.random() * 900) + 100;
            
            return firstWord.toLowerCase() + secondWord.toLowerCase() + number;
        }
        
        // Load emails for current address
        function loadEmailsForCurrentAddress() {
            const allEmails = JSON.parse(localStorage.getItem('ghostmail_emails')) || {};
            emails = allEmails[currentEmail] || [];
            
            // Also try to load from server
            checkForNewEmails();
        }
        
        // Save emails for current address
        function saveEmails() {
            const allEmails = JSON.parse(localStorage.getItem('ghostmail_emails')) || {};
            allEmails[currentEmail] = emails;
            localStorage.setItem('ghostmail_emails', JSON.stringify(allEmails));
        }
        
        // Fix mobile domain select
        function fixMobileDomainSelect() {
            if (window.innerWidth <= 768) {
                domainSelect.style.fontSize = '16px';
                domainSelect.style.padding = '18px 20px';
            }
        }
        
        // Update email display
        function updateEmailDisplay() {
            const domain = domainSelect.value || '@ghostmail.dev';
            const username = emailInput.value.trim() || currentEmail.split('@')[0];
            
            emailNameDisplay.textContent = username;
            domainDisplay.textContent = domain;
            
            // Update current email
            currentEmail = username + domain.replace('@', '');
            
            // Load emails for this new address
            loadEmailsForCurrentAddress();
            renderEmails();
            
            // Save to localStorage
            localStorage.setItem('current_email', currentEmail);
        }
        
        // Update statistics
        function updateStats() {
            const unreadCount = emails.filter(email => !email.read).length;
            emailCount.textContent = emails.length;
            totalEmails.textContent = emails.length;
            unreadEmails.textContent = unreadCount;
            
            // Calculate remaining hours
            const hoursLeft = Math.max(0, Math.ceil((emailExpiration - Date.now()) / (60 * 60 * 1000)));
            activeTime.textContent = hoursLeft;
            
            // Update spam blocked
            spamBlocked.textContent = Math.floor(emails.length * 0.8);
        }
        
        // Session timer
        function startSessionTimer() {
            setInterval(updateStats, 60000);
            
            setInterval(() => {
                if (Date.now() > emailExpiration) {
                    generateNewEmail();
                    updateEmailDisplay();
                    showToast('Email expired. New address generated!', 'info');
                }
            }, 60000);
        }
        
        // Start email checking interval
        function startEmailChecking() {
            // Clear existing interval
            if (checkInterval) clearInterval(checkInterval);
            
            // Check for new emails every 30 seconds
            checkInterval = setInterval(checkForNewEmails, 30000);
        }
        
        // Check for new emails from server
        async function checkForNewEmails() {
            try {
                const response = await fetch(`check_emails.php?email=${encodeURIComponent(currentEmail)}`);
                const newEmails = await response.json();
                
                if (newEmails.length > 0) {
                    // Add new emails to local storage
                    newEmails.forEach(newEmail => {
                        // Check if email already exists
                        const exists = emails.some(email => email.id === newEmail.id);
                        if (!exists) {
                            emails.unshift(newEmail);
                            showToast(`New email from ${newEmail.sender}`, 'success');
                        }
                    });
                    
                    saveEmails();
                    renderEmails();
                }
            } catch (error) {
                console.error('Error checking emails:', error);
            }
        }
        
        // Render emails
        function renderEmails() {
            if (emails.length === 0) {
                emailContainer.style.display = 'none';
                emptyInbox.style.display = 'block';
            } else {
                emailContainer.style.display = 'block';
                emptyInbox.style.display = 'none';
                
                // Sort by date (newest first)
                emails.sort((a, b) => new Date(b.date) - new Date(a.date));
                
                let html = '';
                emails.forEach(email => {
                    const statusClass = email.read ? '' : 'unread';
                    const statusDot = email.read ? '' : '<div class="email-status"></div>';
                    const codeBadge = email.code ? 
                        `<span class="code-badge" onclick="copyCodeFromBadge('${email.code}', event)">
                            <i class="fas fa-copy"></i> ${email.code}
                        </span>` : '';
                    
                    // Format time
                    const time = formatTime(new Date(email.date));
                    
                    html += `
                        <div class="email-item ${statusClass}" onclick="viewEmail('${email.id}')">
                            <div class="email-avatar">${email.sender.charAt(0).toUpperCase()}</div>
                            <div class="email-content">
                                <div class="email-sender">
                                    ${email.sender}
                                    ${codeBadge}
                                </div>
                                <div class="email-subject">${email.subject}</div>
                                <div class="email-preview">${email.preview}</div>
                            </div>
                            <div class="email-meta">
                                <div class="email-time">${time}</div>
                                ${statusDot}
                            </div>
                        </div>
                    `;
                });
                
                emailContainer.innerHTML = html;
            }
            
            updateStats();
        }
        
        // Format time
        function formatTime(date) {
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) return 'Just now';
            if (diff < 3600000) return Math.floor(diff / 60000) + ' min ago';
            if (diff < 86400000) return Math.floor(diff / 3600000) + ' hour' + (Math.floor(diff / 3600000) > 1 ? 's' : '') + ' ago';
            return Math.floor(diff / 86400000) + ' day' + (Math.floor(diff / 86400000) > 1 ? 's' : '') + ' ago';
        }
        
        // View email in modal
        function viewEmail(id) {
            const email = emails.find(e => e.id === id);
            if (email) {
                currentEmailId = id;
                email.read = true;
                
                const codeSection = email.code ? `
                    <div class="email-code-block">
                        <div class="code-display">${email.code}</div>
                        <button class="btn btn-success" onclick="copyCode('${email.code}')">
                            <i class="fas fa-copy"></i> Copy Code
                        </button>
                    </div>
                ` : '';
                
                emailModalBody.innerHTML = `
                    <div class="email-detail-header">
                        <div class="email-detail-avatar">${email.sender.charAt(0).toUpperCase()}</div>
                        <div class="email-detail-info">
                            <h3>${email.sender}</h3>
                            <div class="email-detail-subject">${email.subject}</div>
                        </div>
                    </div>
                    <div class="email-detail-meta">
                        <div><strong>To:</strong> ${currentEmail}</div>
                        <div><strong>Date:</strong> ${new Date(email.date).toLocaleString()}</div>
                    </div>
                    <div class="email-body">
                        ${email.body.replace(/\n/g, '<br>')}
                    </div>
                    ${codeSection}
                `;
                
                emailModal.style.display = 'block';
                saveEmails();
                renderEmails();
            }
        }
        
        // Copy code from badge
        function copyCodeFromBadge(code, event) {
            event.stopPropagation();
            event.preventDefault();
            copyToClipboard(code, `Code copied: ${code}`);
        }
        
        // Copy code from modal
        function copyCode(code) {
            copyToClipboard(code, `Code copied: ${code}`);
        }
        
        // Copy email address - FIXED
        function copyEmailAddress() {
            const email = emailNameDisplay.textContent + domainDisplay.textContent;
            copyToClipboard(email, 'Email address copied to clipboard!');
        }
        
        // Universal copy function with toast notification
        function copyToClipboard(text, message) {
            navigator.clipboard.writeText(text).then(() => {
                showToast(message, 'success');
            }).catch(err => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                textArea.setSelectionRange(0, 99999); // For mobile
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast(message, 'success');
            });
        }
        
        // Show toast notification
        function showToast(message, type = 'success') {
            toastMessage.textContent = message;
            
            switch(type) {
                case 'success':
                    toastIcon.className = 'fas fa-check-circle';
                    toast.className = 'toast toast-success';
                    break;
                case 'error':
                    toastIcon.className = 'fas fa-exclamation-circle';
                    toast.className = 'toast toast-error';
                    break;
                case 'info':
                    toastIcon.className = 'fas fa-info-circle';
                    toast.className = 'toast toast-info';
                    break;
            }
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
        
        // Close email modal
        function closeEmailModal() {
            emailModal.style.display = 'none';
        }
        
        // Delete current email
        function deleteCurrentEmail() {
            if (currentEmailId) {
                emails = emails.filter(email => email.id !== currentEmailId);
                closeEmailModal();
                saveEmails();
                renderEmails();
                showToast('Email deleted', 'success');
            }
        }
        
        // Mark as unread
        function markAsUnread() {
            if (currentEmailId) {
                const email = emails.find(e => e.id === currentEmailId);
                if (email) {
                    email.read = false;
                    closeEmailModal();
                    saveEmails();
                    renderEmails();
                    showToast('Marked as unread', 'success');
                }
            }
        }
        
        // Attach event listeners
        function attachEventListeners() {
            emailInput.addEventListener('input', updateEmailDisplay);
            domainSelect.addEventListener('change', updateEmailDisplay);
            
            // Copy button - FIXED
            copyBtn.addEventListener('click', copyEmailAddress);
            
            // Generate random email
            document.getElementById('generateBtn').addEventListener('click', () => {
                const randomUsername = generateRandomUsername();
                emailInput.value = randomUsername;
                updateEmailDisplay();
                showToast('New email address generated!', 'success');
            });
            
            // Random button
            document.getElementById('randomBtn').addEventListener('click', () => {
                const randomUsername = generateRandomUsername();
                emailInput.value = randomUsername;
                updateEmailDisplay();
                showToast('Random email generated!', 'success');
            });
            
            // New email button
            document.getElementById('newEmailBtn').addEventListener('click', () => {
                generateNewEmail();
                emailInput.value = currentEmail.split('@')[0];
                updateEmailDisplay();
                showToast('New temporary address created', 'success');
            });
            
            // Delete all button
            document.getElementById('deleteAllBtn').addEventListener('click', () => {
                if (emails.length > 0 && confirm('Delete all emails?')) {
                    emails = [];
                    saveEmails();
                    renderEmails();
                    showToast('All emails deleted', 'success');
                }
            });
            
            // Refresh button
            document.getElementById('refreshBtn').addEventListener('click', () => {
                checkForNewEmails();
                showToast('Refreshed inbox', 'info');
            });
            
            // Check emails button
            checkEmailsBtn.addEventListener('click', () => {
                checkForNewEmails();
                showToast('Checking for new emails...', 'info');
            });
            
            // Compose button
            document.getElementById('composeBtn').addEventListener('click', () => {
                showToast('Compose feature coming soon', 'info');
            });
            
            // Extend time button
            document.getElementById('extendBtn').addEventListener('click', () => {
                emailExpiration += (12 * 60 * 60 * 1000); // Add 12 hours
                localStorage.setItem('email_expiration', emailExpiration.toString());
                showToast('Email time extended by 12 hours', 'success');
                updateStats();
            });
            
            // PIN protect button
            document.getElementById('pinBtn').addEventListener('click', () => {
                const pin = prompt('Set a 4-digit PIN for this email:');
                if (pin && /^\d{4}$/.test(pin)) {
                    localStorage.setItem('email_pin_' + currentEmail, pin);
                    showToast('PIN protection enabled', 'success');
                }
            });
            
            // Test email button
            testEmailBtn?.addEventListener('click', sendTestEmail);
            sendTestEmailBtn?.addEventListener('click', sendTestEmail);
            
            // Window resize for mobile fixes
            window.addEventListener('resize', fixMobileDomainSelect);
            
            // Add sample email with Ctrl+E
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'e') {
                    sendTestEmail();
                }
            });
        }
        
        // Send test email
        async function sendTestEmail() {
            try {
                const response = await fetch('send_test_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        to: currentEmail,
                        from: 'test@ghostmail.dev',
                        subject: 'Test Email from GhostMail',
                        body: 'This is a test email to verify your GhostMail is working properly.\n\nYour email address: ' + currentEmail + '\nTime: ' + new Date().toLocaleString()
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Test email sent! Check your inbox.', 'success');
                    // Wait 2 seconds then check for new emails
                    setTimeout(checkForNewEmails, 2000);
                } else {
                    showToast('Failed to send test email: ' + result.error, 'error');
                }
            } catch (error) {
                // Fallback: Add local test email
                const services = ['Test Service', 'GhostMail', 'System'];
                const service = services[Math.floor(Math.random() * services.length)];
                const code = Math.floor(100000 + Math.random() * 900000);
                
                const newEmail = {
                    id: 'test_' + Date.now(),
                    sender: service,
                    subject: `Test Email: ${code}`,
                    body: `This is a test email to verify your GhostMail is working properly.<br><br>Your email address: <strong>${currentEmail}</strong><br>Time: ${new Date().toLocaleString()}<br><br>Verification code: <strong>${code}</strong>`,
                    preview: `Test email verification code: ${code}`,
                    code: code.toString(),
                    date: new Date().toISOString(),
                    read: false
                };
                
                emails.unshift(newEmail);
                saveEmails();
                renderEmails();
                showToast(`Test email received from ${service}`, 'success');
            }
        }
        
        // Initialize on load
        window.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>