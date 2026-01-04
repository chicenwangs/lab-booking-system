<?php
/**
 * FIXED: User Profile
 * Added: Authentication, CSS, Real database updates
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// ✅ ADDED: Proper authentication
requireLogin();

$userId = getCurrentUserId();

// ✅ CHANGED: Get real user data from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$message = '';
$error = '';

// ✅ CHANGED: Update database instead of session
if (isset($_POST['update_profile'])) {
    $fullName = clean($_POST['full_name']);
    $email = clean($_POST['email']);
    
    // Validate
    if (empty($fullName) || empty($email)) {
        $error = 'All fields are required';
    } elseif (!isValidEmail($email)) {
        $error = 'Invalid email format';
    } else {
        // Check if email already exists (for other users)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        
        if ($stmt->fetch()) {
            $error = 'Email already in use by another account';
        } else {
            // Update database
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$fullName, $email, $userId])) {
                // Update session
                $_SESSION['user_name'] = $fullName;
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                setFlash('Profile updated successfully!', 'success');
                header("Location: profile.php");
                exit();
            } else {
                $error = 'Update failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <!-- ✅ ADDED: CSS Link -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- ✅ ADDED: Header -->
    <?php include '../includes/header.php'; ?>
    
    <main class="container" style="padding: 2rem 0;">
        <h1>👤 My Profile</h1>
        
        <!-- ✅ ADDED: Flash messages -->
        <?php displayFlash(); ?>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label><strong>Member ID:</strong></label>
                    <input type="text" value="MEM-<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?>" disabled>
                    <small class="form-help">ID cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="full_name"><strong>Full Name:</strong></label>
                    <input type="text" 
                           id="full_name"
                           name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="email"><strong>Email Address:</strong></label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label><strong>Account Role:</strong></label>
                    <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label><strong>Member Since:</strong></label>
                    <input type="text" value="<?php echo formatDate($user['created_at']); ?>" disabled>
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary">💾 Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            </form>
        </div>

        <!-- Password Change Section -->
        <div class="card" style="margin-top: 2rem; background: var(--light);">
            <h3>🔒 Account Security</h3>
            <p>To change your password, please contact the System Administrator.</p>
            <p style="margin: 0;"><small style="color: var(--text-light);">For security reasons, password changes require admin verification.</small></p>
        </div>
    </main>

    <!-- ✅ ADDED: Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>