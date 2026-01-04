<?php
/**
 * FIXED: Booking History
 * Added: Authentication, CSS
 * Kept: Your teammate's file-based history logic 100%
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// ✅ ADDED: Proper authentication
requireLogin();

// ========================================================================
// ✅ KEPT: Your teammate's save logic (UNCHANGED)
// ========================================================================

// If coming from confirmation, "save" the data to a text file
if (isset($_GET['save']) && !empty($_SESSION['cart'])) {
    $log = "Booking on " . date('Y-m-d H:i') . " by " . getCurrentUserName() . " for: ";
    foreach ($_SESSION['cart'] as $item) {
        $log .= $item['name'] . ", ";
    }
    $log = rtrim($log, ", ") . "\n";
    
    file_put_contents("history.txt", $log, FILE_APPEND);
    
    // ✅ ADDED: Flash message
    setFlash('Booking saved successfully! 🎉', 'success');
    
    unset($_SESSION['cart']); // Clear cart after saving
}

// ✅ KEPT: Your teammate's file reading logic (UNCHANGED)
$historyData = file_exists("history.txt") ? file("history.txt") : [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <!-- ✅ ADDED: CSS Link -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- ✅ ADDED: Header -->
    <?php include '../includes/header.php'; ?>
    
    <main class="container" style="padding: 2rem 0;">
        <h1>📋 My Booking History</h1>
        
        <!-- ✅ ADDED: Flash messages -->
        <?php displayFlash(); ?>
        
        <div class="card">
            <?php if (empty($historyData)): ?>
                <!-- Empty state -->
                <div style="text-align: center; padding: 3rem;">
                    <h2 style="color: var(--text-light);">No booking history yet</h2>
                    <p>Your completed bookings will appear here</p>
                    <br>
                    <a href="book.php" class="btn btn-primary">Make Your First Booking</a>
                </div>
            <?php else: ?>
                <!-- History list -->
                <div class="card-header">
                    <h2 class="card-title">Recent Bookings</h2>
                </div>
                
                <ul style="list-style: none; padding: 0;">
                    <?php foreach (array_reverse($historyData) as $line): ?>
                        <li style="padding: 1rem; border-left: 4px solid var(--success-color); background: var(--light); margin-bottom: 0.75rem; border-radius: var(--radius);">
                            ✓ <?php echo htmlspecialchars($line); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="book.php" class="btn btn-primary">Make New Booking</a>
                    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- ✅ ADDED: Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>