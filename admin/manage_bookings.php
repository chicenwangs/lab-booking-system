<?php
/**
 * ADMIN: Manage Bookings - View and Cancel All Bookings
 */
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireAdmin();

// Handle booking cancellation
if (isset($_GET['cancel'])) {
    $bookingId = intval($_GET['cancel']);
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        if ($stmt->execute([$bookingId])) {
            setFlash('Booking cancelled successfully!', 'success');
        }
    } catch (PDOException $e) {
        setFlash('Failed to cancel booking', 'error');
        logError('Cancel booking error: ' . $e->getMessage());
    }
    
    header("Location: manage_bookings.php");
    exit();
}

// Handle booking deletion
if (isset($_GET['delete'])) {
    $bookingId = intval($_GET['delete']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        if ($stmt->execute([$bookingId])) {
            setFlash('Booking deleted successfully!', 'success');
        }
    } catch (PDOException $e) {
        setFlash('Failed to delete booking', 'error');
        logError('Delete booking error: ' . $e->getMessage());
    }
    
    header("Location: manage_bookings.php");
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query
$whereClause = "1=1";
if ($filter === 'confirmed') {
    $whereClause = "b.status = 'confirmed'";
} elseif ($filter === 'pending') {
    $whereClause = "b.status = 'pending'";
} elseif ($filter === 'cancelled') {
    $whereClause = "b.status = 'cancelled'";
} elseif ($filter === 'completed') {
    $whereClause = "b.status = 'completed'";
} elseif ($filter === 'today') {
    $whereClause = "b.booking_date = CURDATE()";
}

// Get all bookings
$stmt = $pdo->query("
    SELECT b.*, u.full_name, u.email, l.name as lab_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN labs l ON b.lab_id = l.id
    WHERE {$whereClause}
    ORDER BY b.booking_date DESC, b.start_time DESC
");
$bookings = $stmt->fetchAll();

// Get counts
$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
$totalBookings = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'");
$confirmedCount = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
$pendingCount = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'cancelled'");
$cancelledCount = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE booking_date = CURDATE()");
$todayCount = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
            transition: all var(--transition-fast);
        }
        
        .filter-tab:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .filter-tab.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container" style="padding: 2rem 0;">
        <h1>📅 Manage All Bookings</h1>
        
        <?php displayFlash(); ?>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="manage_bookings.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All (<?php echo $totalBookings; ?>)
            </a>
            <a href="manage_bookings.php?filter=today" class="filter-tab <?php echo $filter === 'today' ? 'active' : ''; ?>">
                Today (<?php echo $todayCount; ?>)
            </a>
            <a href="manage_bookings.php?filter=confirmed" class="filter-tab <?php echo $filter === 'confirmed' ? 'active' : ''; ?>">
                Confirmed (<?php echo $confirmedCount; ?>)
            </a>
            <a href="manage_bookings.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                Pending (<?php echo $pendingCount; ?>)
            </a>
            <a href="manage_bookings.php?filter=cancelled" class="filter-tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">
                Cancelled (<?php echo $cancelledCount; ?>)
            </a>
        </div>
        
        <!-- Bookings Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Booking Records</h2>
            </div>
            
            <?php if (empty($bookings)): ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-light);">No bookings found</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Lab</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Purpose</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($booking['full_name']); ?><br>
                                    <small style="color: var(--text-light);"><?php echo htmlspecialchars($booking['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($booking['lab_name']); ?></td>
                                <td><?php echo formatDate($booking['booking_date']); ?></td>
                                <td>
                                    <?php echo formatTime($booking['start_time']); ?><br>
                                    <small style="color: var(--text-light);">to <?php echo formatTime($booking['end_time']); ?></small>
                                </td>
                                <td><small><?php echo htmlspecialchars(substr($booking['purpose'] ?? '-', 0, 30)); ?>...</small></td>
                                <td><strong><?php echo formatCurrency($booking['total_cost']); ?></strong></td>
                                <td><?php echo displayStatusBadge($booking['status']); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <?php if ($booking['status'] === 'confirmed' || $booking['status'] === 'pending'): ?>
                                            <a href="manage_bookings.php?cancel=<?php echo $booking['id']; ?>" 
                                               class="btn btn-warning btn-sm"
                                               onclick="return confirm('Cancel this booking?')">
                                                Cancel
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="manage_bookings.php?delete=<?php echo $booking['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Permanently delete this booking? This cannot be undone!')">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>