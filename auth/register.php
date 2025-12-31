<?php
/**
 * ============================================================================
 * SQUAD 1: REGISTRATION PAGE
 * ============================================================================
 * User account creation with strong validation
 * Creates new member accounts (not admin)
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'member/dashboard.php');
}

$errors = [];
$formData = [
    'full_name' => '',
    'email' => '',
];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and clean form data
    $formData['full_name'] = clean($_POST['full_name']);
    $formData['email'] = clean($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation - Full Name
    if (empty($formData['full_name'])) {
        $errors[] = 'Full name is required';
    } elseif (strlen($formData['full_name']) < 3) {
        $errors[] = 'Full name must be at least 3 characters';
    }
    
    // Validation - Email
    if (empty($formData['email'])) {
        $errors[] = 'Email is required';
    } elseif (!isValidEmail($formData['email'])) {
        $errors[] = 'Invalid email format';
    }
    
    // Validation - Password
    if (empty($password)) {
        $errors[] = 'Password is required';
    } else {
        // Validate password strength
        $passwordErrors = validatePassword($password);
        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }
    }
    
    // Validation - Confirm Password
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$formData['email']]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered. Please login instead.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
            logError('Registration check error: ' . $e->getMessage());
        }
    }
    
    // If no errors, create account
    if (empty($errors)) {
        try {
            $hashedPassword = hashPassword($password);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password, role) 
                VALUES (?, ?, ?, 'member')
            ");
            
            if ($stmt->execute([$formData['full_name'], $formData['email'], $hashedPassword])) {
                setFlash('Account created successfully! Please login.', 'success');
                redirect('login.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
            logError('Registration insert error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Lab Booking System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .register-wrapper {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-xl) 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
            background: var(--white);
            padding: var(--spacing-2xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            animation: fadeInUp 0.5s ease;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .register-header h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .register-header p {
            color: var(--text-light);
            margin: 0;
        }
        
        .password-requirements {
            background: var(--light);
            padding: var(--spacing-md);
            border-radius: var(--radius);
            margin-top: var(--spacing-md);
            border-left: 4px solid var(--info-color);
        }
        
        .password-requirements h4 {
            font-size: 0.875rem;
            margin-bottom: var(--spacing-sm);
            color: var(--dark);
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: var(--spacing-lg);
            font-size: 0.875rem;
        }
        
        .password-requirements li {
            color: var(--text-light);
            margin: var(--spacing-xs) 0;
        }
        
        #password-strength {
            margin-top: var(--spacing-sm);
            font-weight: 600;
            font-size: 0.875rem;
            display: block;
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
        
        .login-link {
            text-align: center;
            margin-top: var(--spacing-lg);
        }
        
        .benefits-box {
            background: linear-gradient(135deg, var(--primary-light), var(--success-light));
            padding: var(--spacing-md);
            border-radius: var(--radius);
            margin-bottom: var(--spacing-lg);
        }
        
        .benefits-box h4 {
            font-size: 0.9rem;
            margin-bottom: var(--spacing-sm);
            color: var(--dark);
        }
        
        .benefits-box ul {
            margin: 0;
            padding-left: var(--spacing-lg);
            font-size: 0.875rem;
        }
        
        .benefits-box li {
            color: var(--text);
            margin: var(--spacing-xs) 0;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="register-wrapper">
        <div class="register-container">
            <div class="register-header">
                <h2>✨ Create Account</h2>
                <p>Join us and start booking labs today</p>
            </div>
            
            <!-- Display errors -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: var(--spacing-lg);">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Benefits Box -->
            <div class="benefits-box">
                <h4>✓ What you'll get:</h4>
                <ul>
                    <li>Access to all laboratory facilities</li>
                    <li>Real-time booking availability</li>
                    <li>Booking history and receipts</li>
                    <li>Instant confirmation emails</li>
                </ul>
            </div>
            
            <!-- Registration Form -->
            <form method="POST" id="register-form">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        required
                        value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                        placeholder="John Doe"
                        autocomplete="name"
                        autofocus
                    >
                    <small class="form-help">Your full name as it should appear on bookings</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        value="<?php echo htmlspecialchars($formData['email']); ?>"
                        placeholder="you@example.com"
                        autocomplete="email"
                    >
                    <small class="form-help">We'll send booking confirmations to this email</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Create a strong password"
                        autocomplete="new-password"
                    >
                    <small id="password-strength"></small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        placeholder="Re-enter your password"
                        autocomplete="new-password"
                    >
                </div>
                
                <!-- Password Requirements -->
                <div class="password-requirements">
                    <h4>🔒 Password Requirements:</h4>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>One uppercase letter (A-Z)</li>
                        <li>One lowercase letter (a-z)</li>
                        <li>One number (0-9)</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="margin-top: var(--spacing-lg);">
                    Create My Account
                </button>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <!-- Login Link -->
            <div class="login-link">
                <p style="margin: 0;">
                    Already have an account? 
                    <a href="login.php" style="font-weight: 600;">Login here</a>
                </p>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Real-time password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDisplay = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDisplay.textContent = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            // Check length
            if (password.length >= 8) strength++;
            else feedback.push('8+ characters');
            
            // Check uppercase
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('uppercase letter');
            
            // Check lowercase
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('lowercase letter');
            
            // Check number
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('number');
            
            // Display strength
            if (strength === 4) {
                strengthDisplay.textContent = '✓ Strong password';
                strengthDisplay.style.color = '#10b981';
            } else if (strength >= 2) {
                strengthDisplay.textContent = '⚠ Medium strength - needs: ' + feedback.join(', ');
                strengthDisplay.style.color = '#f59e0b';
            } else {
                strengthDisplay.textContent = '✗ Weak password - needs: ' + feedback.join(', ');
                strengthDisplay.style.color = '#ef4444';
            }
        });
        
        // Confirm password matching
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>