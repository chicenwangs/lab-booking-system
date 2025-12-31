<?php
/**
 * ============================================================================
 * SQUAD 1: LOGIN PAGE
 * ============================================================================
 * Secure authentication with role-based redirection
 * Handles both admin and member login
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'member/dashboard.php');
}

$error = '';
$email = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (!isValidEmail($email)) {
        $error = 'Invalid email format';
    } else {
        // Query user from database
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Verify password
            if ($user && verifyPassword($password, $user['password'])) {
                // Login successful - set session
                loginUser($user['id'], $user['full_name'], $user['role']);
                
                // Remember me functionality (optional - sets cookie for 30 days)
                if ($remember) {
                    setcookie('remember_user', $user['id'], time() + (86400 * 30), '/');
                }
                
                // Set success message
                setFlash('Welcome back, ' . $user['full_name'] . '!', 'success');
                
                // Redirect based on role
                $redirectUrl = ($user['role'] === 'admin') 
                    ? 'admin/dashboard.php' 
                    : 'member/dashboard.php';
                redirect($redirectUrl);
            } else {
                $error = 'Invalid email or password';
                // Log failed login attempt
                logError("Failed login attempt for: " . $email);
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
            logError('Login error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lab Booking System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-wrapper {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-xl) 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            background: var(--white);
            padding: var(--spacing-2xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 0.5s ease;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .login-header h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .login-header p {
            color: var(--text-light);
            margin: 0;
        }
        
        .demo-accounts {
            margin-top: var(--spacing-xl);
            padding: var(--spacing-md);
            background: var(--light);
            border-radius: var(--radius);
            border-left: 4px solid var(--info-color);
        }
        
        .demo-accounts h4 {
            font-size: 0.875rem;
            margin-bottom: var(--spacing-sm);
            color: var(--dark);
        }
        
        .demo-accounts p {
            font-size: 0.875rem;
            margin: var(--spacing-xs) 0;
            color: var(--text);
        }
        
        .demo-accounts code {
            background: var(--white);
            padding: 0.125rem 0.375rem;
            border-radius: var(--radius-sm);
            font-family: 'Courier New', monospace;
            color: var(--primary-color);
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .remember-me input {
            width: auto;
            margin: 0;
        }
        
        .remember-me label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }
        
        .divider {
            text-align: center;
            margin: var(--spacing-lg) 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border);
        }
        
        .divider span {
            background: var(--white);
            padding: 0 var(--spacing-md);
            position: relative;
            color: var(--text-light);
            font-size: 0.875rem;
        }
        
        .register-link {
            text-align: center;
            margin-top: var(--spacing-lg);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <h2>🔒 Welcome Back</h2>
                <p>Sign in to access your lab bookings</p>
            </div>
            
            <!-- Display errors -->
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Display flash messages -->
            <?php displayFlash(); ?>
            
            <!-- Login Form -->
            <form method="POST" id="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="you@example.com"
                        autocomplete="email"
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                </div>
                
                <div class="form-group">
                    <div class="remember-me">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            id="remember"
                        >
                        <label for="remember">
                            Remember me for 30 days
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Login to Account
                </button>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <!-- Register Link -->
            <div class="register-link">
                <p style="margin: 0;">
                    Don't have an account? 
                    <a href="register.php" style="font-weight: 600;">Create one here</a>
                </p>
            </div>
            
            <!-- Demo Accounts Info -->
            <div class="demo-accounts">
                <h4>🔑 Demo Accounts (For Testing)</h4>
                <p>
                    <strong>Admin:</strong> 
                    <code>admin@lab.com</code> / 
                    <code>admin123</code>
                </p>
                <p style="margin: 0;">
                    <strong>Member:</strong> 
                    Register a new account to test member features
                </p>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>