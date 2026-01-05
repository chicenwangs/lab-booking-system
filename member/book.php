<?php
/**
 * FIXED: Lab Booking Page
 * Shows only available labs (not already booked by current user for today)
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = getCurrentUserId();

// Get labs that user hasn't already booked for today
$stmt = $pdo->prepare("
    SELECT l.* 
    FROM labs l
    WHERE l.status = 'active'
    AND l.id NOT IN (
        SELECT lab_id 
        FROM bookings 
        WHERE user_id = ? 
        AND booking_date = CURDATE()
        AND status IN ('confirmed', 'pending')
    )
    ORDER BY l.name
");
$stmt->execute([$userId]);
$labs = $stmt->fetchAll();

// Get user's bookings for today to show as info
$stmt = $pdo->prepare("
    SELECT l.name, b.status
    FROM bookings b
    JOIN labs l ON b.lab_id = l.id
    WHERE b.user_id = ? 
    AND b.booking_date = CURDATE()
    AND b.status IN ('confirmed', 'pending')
");
$stmt->execute([$userId]);
$todayBookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Selection</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container" style="padding: 2rem 0;">
        <h1>Select a Lab to Book</h1>
        
        <?php displayFlash(); ?>
        
        <!-- Show today's bookings if any -->
        <?php if (!empty($todayBookings)): ?>
        <div class="alert alert-info">
            <strong>📅 Your bookings for today:</strong>
            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                <?php foreach ($todayBookings as $booking): ?>
                    <li><?php echo htmlspecialchars($booking['name']); ?> - <?php echo displayStatusBadge($booking['status']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <?php if (empty($labs)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <h2 style="color: var(--text-light);">No available labs</h2>
                    <p>You've either booked all labs for today, or all labs are currently unavailable.</p>
                    <br>
                    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                    <a href="history.php" class="btn btn-primary">View My Bookings</a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Lab ID</th>
                                <th>Lab Name</th>
                                <th>Capacity</th>
                                <th>Rate/Hour</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labs as $lab): ?>
                            <tr>
                                <td>L<?php echo str_pad($lab['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($lab['name']); ?></strong><br>
                                    <small style="color: var(--text-light);"><?php echo htmlspecialchars($lab['description']); ?></small>
                                </td>
                                <td><?php echo $lab['capacity']; ?> people</td>
                                <td><?php echo formatCurrency($lab['hourly_rate']); ?></td>
                                <td>
                                    <form method="POST" action="cart.php">
                                        <input type="hidden" name="lab_id" value="<?php echo $lab['id']; ?>">
                                        <input type="hidden" name="lab_name" value="<?php echo htmlspecialchars($lab['name']); ?>">
                                        <button type="submit" name="add" class="btn btn-primary btn-sm">Add to Cart</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <a href="cart.php" class="btn btn-secondary">🛒 Go to My Cart</a>
                    <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>