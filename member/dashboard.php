<?php
/**
 * FIXED: Member Dashboard
 * Added: Authentication, CSS, Real data
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// ✅ ADDED: Proper authentication check
requireLogin();

// Get current cart count
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// ✅ ADDED: Get real user stats from database
$userId = getCurrentUserId();
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
$stmt->execute([$userId]);
$totalBookings = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <!-- ✅ ADDED: CSS Link -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- ✅ ADDED: Header with navigation -->
    <?php include '../includes/header.php'; ?>
    
    <main class="dashboard container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars(getCurrentUserName()); ?>!</h1>
            <p>Manage your lab bookings from your dashboard</p>
        </div>
        
        <!-- ✅ ADDED: Flash messages -->
        <?php displayFlash(); ?>
        
        <!-- Stats Grid (Your teammate's logic, with styling) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $cart_count; ?></div>
                <div class="stat-label">In Cart</div>
                <a href="cart.php" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">Manage Cart</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Total Bookings</div>
                <a href="history.php" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">View History</a>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Quick Actions</h2>
            </div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="book.php" class="btn btn-primary">🔬 Book a New Lab</a>
                <a href="history.php" class="btn btn-secondary">📋 View My History</a>
                <a href="profile.php" class="btn btn-secondary">👤 My Profile</a>
            </div>
        </div>
    </main>

    <!-- ✅ ADDED: Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>