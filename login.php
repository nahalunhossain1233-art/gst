<?php
session_start();
ob_start();

// Include config with your database credentials
require_once 'config.php';

// Database connection with YOUR credentials
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ghostmail",
        "admin_n",
        "WorkHard@123",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create login_logs table if not exists (for security)
$pdo->exec("CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45),
    success BOOLEAN DEFAULT FALSE,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$error = '';
$locked = false;

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit();
}

// Handle login attempts from session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
    $_SESSION['lock_until'] = 0;
}

// Check if account is locked
if (isset($_SESSION['lock_until']) && time() < $_SESSION['lock_until']) {
    $locked = true;
    $remaining = ceil(($_SESSION['lock_until'] - time()) / 60);
    $error = "Account locked. Try again in $remaining minute(s).";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $password = $_POST['password'] ?? '';
    
    // Verify password from config.php (ADMIN_PASSWORD_HASH)
    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        // Successful login
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['session_id'] = bin2hex(random_bytes(16));
        $_SESSION['login_attempts'] = 0;
        
        // Log successful login to database
        $stmt = $pdo->prepare("INSERT INTO login_logs (ip_address, success) VALUES (?, 1)");
        $stmt->execute([$_SERVER['REMOTE_ADDR']]);
        
        error_log("Admin login successful from IP: " . $_SERVER['REMOTE_ADDR']);
        
        header('Location: admin.php');
        exit();
    } else {
        // Failed login
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        
        // Lock after 3 failed attempts for 15 minutes
        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lock_until'] = time() + (15 * 60);
            $locked = true;
            $error = "Too many failed attempts. Account locked for 15 minutes.";
        } else {
            $remaining_attempts = 3 - $_SESSION['login_attempts'];
            $error = "Invalid password. $remaining_attempts attempt(s) remaining.";
        }
        
        // Log failed attempt to database
        $stmt = $pdo->prepare("INSERT INTO login_logs (ip_address, success) VALUES (?, 0)");
        $stmt->execute([$_SERVER['REMOTE_ADDR']]);
        
        error_log("Failed admin login attempt from IP: " . $_SERVER['REMOTE_ADDR']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GhostMail - Admin Login</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(0, 212, 255, 0.05) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(255, 107, 157, 0.05) 0%, transparent 20%);
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        
        .login-card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 40px 30px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            text-align: center;
        }
        
        .logo {
            font-size: 42px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: var(--text-secondary);
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text);
        }
        
        .form-input {
            width: 100%;
            padding: 16px 20px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-size: 16px;
            border: 2px solid var(--border);
            transition: border-color 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .password-input {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
            z-index: 2;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 20px rgba(0, 212, 255, 0.3);
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 212, 255, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-error {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        
        .alert-success {
            background: rgba(0, 255, 157, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .security-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .security-note i {
            color: var(--primary);
            margin-right: 5px;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.5s;
        }
        
        .hint {
            font-size: 11px;
            color: var(--text-secondary);
            margin-top: 5px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">GhostMail</div>
            <div class="subtitle">Administrator Access</div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Admin Password</label>
                    <div class="password-input">
                        <input type="password" class="form-input" id="password" name="password" placeholder="Enter admin password" required <?php echo $locked ? 'disabled' : ''; ?>>
                        <button type="button" class="toggle-password" id="togglePassword" <?php echo $locked ? 'disabled' : ''; ?>>
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="hint">Enter the admin password you set in config.php</div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="loginBtn" <?php echo $locked ? 'disabled' : ''; ?>>
                    <i class="fas fa-lock"></i> Access Admin Panel
                </button>
            </form>
            
            <div class="security-note">
                <p><i class="fas fa-shield-alt"></i> Database: ghostmail (connected)</p>
                <p><i class="fas fa-history"></i> Session expires after 1 hour</p>
                <p><i class="fas fa-exclamation-triangle"></i> 3 failed attempts = 15 minute lockout</p>
                <p><i class="fas fa-database"></i> Login logs stored in database</p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
        
        // Auto-focus password field if not locked
        <?php if (!$locked): ?>
        document.getElementById('password').focus();
        <?php endif; ?>
    </script>
</body>
</html>
<?php ob_end_flush(); ?>