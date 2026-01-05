<?php
/**
 * ADMIN: Dashboard - Overview with Statistics
 * Shows system stats, recent bookings, and charts
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireAdmin();


// Handle booking cancellation
if (isset($_GET['cancel_booking'])) {
    $bookingId = intval($_GET['cancel_booking']);
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        if ($stmt->execute([$bookingId])) {
            setFlash('Booking cancelled successfully!', 'success');
        } else {
            setFlash('Failed to cancel booking', 'error');
        }
    } catch (PDOException $e) {
        setFlash('Error cancelling booking', 'error');
        logError('Cancel booking error: ' . $e->getMessage());
    }
    
    // Redirect to remove the GET parameter
    header("Location: dashboard.php");
    exit();
}


// Get statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'member'");
    $totalUsers = $stmt->fetch()['total'];
    
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $totalBookings = $stmt->fetch()['total'];
    
    // Today's bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE booking_date = CURDATE()");
    $todayBookings = $stmt->fetch()['total'];
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_cost) as revenue FROM bookings WHERE status != 'cancelled'");
    $totalRevenue = $stmt->fetch()['revenue'] ?? 0;
    
    // Active labs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM labs WHERE status = 'active'");
    $activeLabs = $stmt->fetch()['total'];
    
    // This week's bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE WEEK(booking_date) = WEEK(CURDATE())");
    $weekBookings = $stmt->fetch()['total'];
    
    // Recent bookings (last 10)
    $stmt = $pdo->query("
        SELECT b.*, u.full_name, l.name as lab_name 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN labs l ON b.lab_id = l.id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $recentBookings = $stmt->fetchAll();
    
    // Popular labs (most booked)
    $stmt = $pdo->query("
        SELECT l.name, COUNT(b.id) as booking_count
        FROM labs l
        LEFT JOIN bookings b ON l.id = b.lab_id
        GROUP BY l.id, l.name
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $popularLabs = $stmt->fetchAll();
    
    // Bookings by status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM bookings
        GROUP BY status
    ");
    $bookingsByStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
} catch (PDOException $e) {
    logError('Admin dashboard error: ' . $e->getMessage());
    $totalUsers = $totalBookings = $todayBookings = $totalRevenue = $activeLabs = $weekBookings = 0;
    $recentBookings = $popularLabs = [];
    $bookingsByStatus = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container dashboard">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1 style="color: white; margin-bottom: 0.5rem;">👨‍💼 Admin Dashboard</h1>
            <p style="margin: 0; opacity: 0.9;">Welcome back, <?php echo htmlspecialchars(getCurrentUserName()); ?></p>
        </div>
        
        <?php displayFlash(); ?>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $todayBookings; ?></div>
                <div class="stat-label">Today's Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $weekBookings; ?></div>
                <div class="stat-label">This Week</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo formatCurrency($totalRevenue); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $activeLabs; ?></div>
                <div class="stat-label">Active Labs</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <!-- Popular Labs Chart -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📊 Most Popular Labs</h2>
                </div>
                <div class="chart-container">
                    <canvas id="popularLabsChart"></canvas>
                </div>
            </div>
            
            <!-- Bookings by Status Chart -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📈 Bookings by Status</h2>
                </div>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
                <!-- Recent Bookings -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 class="card-title">🕐 Recent Bookings</h2>
            </div>
            
            <?php if (empty($recentBookings)): ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-light);">No bookings yet</p>
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
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['lab_name']); ?></td>
                                <td><?php echo formatDate($booking['booking_date']); ?></td>
                                <td><?php echo formatTime($booking['start_time']); ?> - <?php echo formatTime($booking['end_time']); ?></td>
                                <td><?php echo formatCurrency($booking['total_cost']); ?></td>
                                <td><?php echo displayStatusBadge($booking['status']); ?></td>
                                <td>
                                    <?php if ($booking['status'] === 'confirmed' || $booking['status'] === 'pending'): ?>
                                        <a href="?cancel_booking=<?php echo $booking['id']; ?>" 
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Cancel this booking?')">
                                            Cancel
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">⚡ Quick Actions</h2>
            </div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="manage_bookings.php" class="btn btn-primary">📅 Manage Bookings</a>
                <a href="manage_labs.php" class="btn btn-primary">🔬 Manage Labs</a>
                <a href="manage_users.php" class="btn btn-primary">👥 Manage Users</a>
                <a href="reports.php" class="btn btn-primary">📊 View Reports</a>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Popular Labs Chart
        const labsCtx = document.getElementById('popularLabsChart');
        if (labsCtx) {
            new Chart(labsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($popularLabs, 'name')); ?>,
                    datasets: [{
                        label: 'Number of Bookings',
                        data: <?php echo json_encode(array_column($popularLabs, 'booking_count')); ?>,
                        backgroundColor: 'rgba(37, 99, 235, 0.7)',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        
        // Status Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
                    datasets: [{
                        data: [
                            <?php echo $bookingsByStatus['pending'] ?? 0; ?>,
                            <?php echo $bookingsByStatus['confirmed'] ?? 0; ?>,
                            <?php echo $bookingsByStatus['cancelled'] ?? 0; ?>,
                            <?php echo $bookingsByStatus['completed'] ?? 0; ?>
                        ],
                        backgroundColor: [
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(59, 130, 246, 0.7)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    </script>
</body>
</html>