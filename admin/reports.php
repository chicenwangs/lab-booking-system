<?php
/**
 * ADMIN: Reports - Analytics and Data Visualization
 * Revenue reports, booking trends, and export functionality
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireAdmin();

// Get date range from filters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Revenue by date
$stmt = $pdo->prepare("
    SELECT booking_date, SUM(total_cost) as revenue, COUNT(*) as bookings
    FROM bookings
    WHERE booking_date BETWEEN ? AND ? AND status != 'cancelled'
    GROUP BY booking_date
    ORDER BY booking_date
");
$stmt->execute([$startDate, $endDate]);
$revenueByDate = $stmt->fetchAll();

// Revenue by lab
$stmt = $pdo->prepare("
    SELECT l.name, SUM(b.total_cost) as revenue, COUNT(b.id) as bookings
    FROM labs l
    LEFT JOIN bookings b ON l.id = b.lab_id AND b.booking_date BETWEEN ? AND ? AND b.status != 'cancelled'
    GROUP BY l.id, l.name
    ORDER BY revenue DESC
");
$stmt->execute([$startDate, $endDate]);
$revenueByLab = $stmt->fetchAll();

// Top users
$stmt = $pdo->prepare("
    SELECT u.full_name, u.email, COUNT(b.id) as bookings, SUM(b.total_cost) as total_spent
    FROM users u
    JOIN bookings b ON u.id = b.user_id
    WHERE b.booking_date BETWEEN ? AND ? AND b.status != 'cancelled'
    GROUP BY u.id
    ORDER BY total_spent DESC
    LIMIT 10
");
$stmt->execute([$startDate, $endDate]);
$topUsers = $stmt->fetchAll();

// Peak hours
$stmt = $pdo->prepare("
    SELECT HOUR(start_time) as hour, COUNT(*) as bookings
    FROM bookings
    WHERE booking_date BETWEEN ? AND ?
    GROUP BY HOUR(start_time)
    ORDER BY hour
");
$stmt->execute([$startDate, $endDate]);
$peakHours = $stmt->fetchAll();

// Summary stats
$stmt = $pdo->prepare("SELECT SUM(total_cost) as total FROM bookings WHERE booking_date BETWEEN ? AND ? AND status != 'cancelled'");
$stmt->execute([$startDate, $endDate]);
$totalRevenue = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE booking_date BETWEEN ? AND ?");
$stmt->execute([$startDate, $endDate]);
$totalBookings = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE booking_date BETWEEN ? AND ? AND status = 'cancelled'");
$stmt->execute([$startDate, $endDate]);
$cancelledBookings = $stmt->fetch()['total'];

$averageBookingValue = $totalBookings > 0 ? $totalRevenue / ($totalBookings - $cancelledBookings) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 350px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container" style="padding: 2rem 0;">
        <h1>📊 Reports & Analytics</h1>
        
        <?php displayFlash(); ?>
        
        <!-- Date Filter -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📅 Date Range Filter</h2>
            </div>
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="reports.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        
        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo formatCurrency($totalRevenue); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $cancelledBookings; ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo formatCurrency($averageBookingValue); ?></div>
                <div class="stat-label">Avg Booking Value</div>
            </div>
        </div>
        
        <!-- Charts Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <!-- Revenue Over Time -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">💰 Revenue Over Time</h2>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <!-- Revenue by Lab -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🔬 Revenue by Lab</h2>
                </div>
                <div class="chart-container">
                    <canvas id="labRevenueChart"></canvas>
                </div>
            </div>
            
            <!-- Peak Hours -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">⏰ Peak Booking Hours</h2>
                </div>
                <div class="chart-container">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Top Users Table -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 class="card-title">🏆 Top Users by Spending</h2>
            </div>
            
            <?php if (empty($topUsers)): ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-light);">No data for selected period</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Bookings</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topUsers as $index => $user): ?>
                            <tr>
                                <td><strong><?php echo $index + 1; ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['bookings']; ?></td>
                                <td><strong style="color: var(--success-color);"><?php echo formatCurrency($user['total_spent']); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Export Button -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📥 Export Data</h2>
            </div>
            <p>Export report data to CSV for further analysis in Excel or other tools.</p>
            <button onclick="exportToCSV()" class="btn btn-success">📊 Export to CSV</button>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </main>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Revenue Over Time Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($revenueByDate, 'booking_date')); ?>,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: <?php echo json_encode(array_column($revenueByDate, 'revenue')); ?>,
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toFixed(0);
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Revenue by Lab Chart
        const labRevenueCtx = document.getElementById('labRevenueChart');
        if (labRevenueCtx) {
            new Chart(labRevenueCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($revenueByLab, 'name')); ?>,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: <?php echo json_encode(array_column($revenueByLab, 'revenue')); ?>,
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
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Peak Hours Chart
        const peakHoursCtx = document.getElementById('peakHoursChart');
        if (peakHoursCtx) {
            new Chart(peakHoursCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_map(function($h) { return $h['hour'] . ':00'; }, $peakHours)); ?>,
                    datasets: [{
                        label: 'Number of Bookings',
                        data: <?php echo json_encode(array_column($peakHours, 'bookings')); ?>,
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderColor: 'rgba(245, 158, 11, 1)',
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
        
        // Export to CSV
        function exportToCSV() {
            const data = [
                ['Date Range', '<?php echo $startDate; ?>', 'to', '<?php echo $endDate; ?>'],
                [],
                ['Summary Statistics'],
                ['Total Revenue', '<?php echo $totalRevenue; ?>'],
                ['Total Bookings', '<?php echo $totalBookings; ?>'],
                ['Cancelled Bookings', '<?php echo $cancelledBookings; ?>'],
                ['Average Booking Value', '<?php echo $averageBookingValue; ?>'],
                [],
                ['Revenue by Lab'],
                ['Lab Name', 'Revenue', 'Bookings'],
                <?php foreach ($revenueByLab as $lab): ?>
                ['<?php echo addslashes($lab['name']); ?>', '<?php echo $lab['revenue']; ?>', '<?php echo $lab['bookings']; ?>'],
                <?php endforeach; ?>
                [],
                ['Top Users'],
                ['Rank', 'Name', 'Email', 'Bookings', 'Total Spent'],
                <?php foreach ($topUsers as $index => $user): ?>
                ['<?php echo $index + 1; ?>', '<?php echo addslashes($user['full_name']); ?>', '<?php echo addslashes($user['email']); ?>', '<?php echo $user['bookings']; ?>', '<?php echo $user['total_spent']; ?>'],
                <?php endforeach; ?>
            ];
            
            let csv = '';
            data.forEach(row => {
                csv += row.join(',') + '\n';
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'lab_report_<?php echo date('Ymd'); ?>.csv';
            a.click();
            window.URL.revokeObjectURL(url);
            
            alert('✓ Report exported successfully!');
        }
    </script>
</body>
</html>