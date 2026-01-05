<?php
/**
 * ADMIN: Manage Users - View and Manage User Accounts
 * View users, change roles, view user bookings
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireAdmin();

// Handle Change Role
if (isset($_POST['change_role'])) {
    $userId = intval($_POST['user_id']);
    $newRole = clean($_POST['role']);
    
    // Don't allow changing own role
    if ($userId == getCurrentUserId()) {
        setFlash('You cannot change your own role!', 'error');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$newRole, $userId])) {
                setFlash('User role updated successfully!', 'success');
            }
        } catch (PDOException $e) {
            setFlash('Failed to update role', 'error');
            logError('Change role error: ' . $e->getMessage());
        }
    }
    
    header("Location: manage_users.php");
    exit();
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    
    // Don't allow deleting own account
    if ($userId == getCurrentUserId()) {
        setFlash('You cannot delete your own account!', 'error');
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$userId])) {
                setFlash('User deleted successfully!', 'success');
            }
        } catch (PDOException $e) {
            setFlash('Failed to delete user', 'error');
            logError('Delete user error: ' . $e->getMessage());
        }
    }
    
    header("Location: manage_users.php");
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query based on filter
$whereClause = "1=1";
if ($filter === 'members') {
    $whereClause = "role = 'member'";
} elseif ($filter === 'admins') {
    $whereClause = "role = 'admin'";
}

// Get all users
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(b.id) as booking_count,
           SUM(CASE WHEN b.status != 'cancelled' THEN b.total_cost ELSE 0 END) as total_spent
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    WHERE {$whereClause}
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Get counts
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'member'");
$memberCount = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$adminCount = $stmt->fetch()['total'];

// Get user details if viewing
$viewUser = null;
$userBookings = [];
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['view']]);
    $viewUser = $stmt->fetch();
    
    if ($viewUser) {
        $stmt = $pdo->prepare("
            SELECT b.*, l.name as lab_name
            FROM bookings b
            JOIN labs l ON b.lab_id = l.id
            WHERE b.user_id = ?
            ORDER BY b.booking_date DESC
            LIMIT 20
        ");
        $stmt->execute([$viewUser['id']]);
        $userBookings = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
        <h1>👥 Manage Users</h1>
        
        <?php displayFlash(); ?>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="manage_users.php?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All Users (<?php echo $totalUsers; ?>)
            </a>
            <a href="manage_users.php?filter=members" class="filter-tab <?php echo $filter === 'members' ? 'active' : ''; ?>">
                Members (<?php echo $memberCount; ?>)
            </a>
            <a href="manage_users.php?filter=admins" class="filter-tab <?php echo $filter === 'admins' ? 'active' : ''; ?>">
                Admins (<?php echo $adminCount; ?>)
            </a>
        </div>
        
        <!-- Users List -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">User Accounts</h2>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Bookings</th>
                            <th>Total Spent</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong>#<?php echo $user['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge badge-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Member</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['booking_count']; ?></td>
                            <td><?php echo formatCurrency($user['total_spent'] ?? 0); ?></td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="manage_users.php?view=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">
                                        View
                                    </a>
                                    
                                    <?php if ($user['id'] != getCurrentUserId()): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="role" value="<?php echo $user['role'] === 'admin' ? 'member' : 'admin'; ?>">
                                            <button type="submit" name="change_role" class="btn btn-warning btn-sm"
                                                    onclick="return confirm('Change this user\'s role?')">
                                                Make <?php echo $user['role'] === 'admin' ? 'Member' : 'Admin'; ?>
                                            </button>
                                        </form>
                                        
                                        <a href="manage_users.php?delete=<?php echo $user['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this user? All their bookings will also be deleted!')">
                                            Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-info">You</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- User Details Modal (if viewing) -->
        <?php if ($viewUser): ?>
        <div class="card" style="border: 3px solid var(--primary-color);">
            <div class="card-header" style="background: var(--primary-light);">
                <h2 class="card-title">User Details: <?php echo htmlspecialchars($viewUser['full_name']); ?></h2>
                <a href="manage_users.php" class="btn btn-secondary btn-sm">Close</a>
            </div>
            
            <!-- User Info -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <strong>User ID:</strong><br>
                    #<?php echo $viewUser['id']; ?>
                </div>
                <div>
                    <strong>Full Name:</strong><br>
                    <?php echo htmlspecialchars($viewUser['full_name']); ?>
                </div>
                <div>
                    <strong>Email:</strong><br>
                    <?php echo htmlspecialchars($viewUser['email']); ?>
                </div>
                <div>
                    <strong>Role:</strong><br>
                    <?php echo ucfirst($viewUser['role']); ?>
                </div>
                <div>
                    <strong>Registered:</strong><br>
                    <?php echo formatDate($viewUser['created_at']); ?>
                </div>
            </div>
            
            <!-- User's Bookings -->
            <h3>Booking History (<?php echo count($userBookings); ?>)</h3>
            
            <?php if (empty($userBookings)): ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-light);">No bookings yet</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lab</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userBookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['lab_name']); ?></td>
                                <td><?php echo formatDate($booking['booking_date']); ?></td>
                                <td><?php echo formatTime($booking['start_time']); ?> - <?php echo formatTime($booking['end_time']); ?></td>
                                <td><?php echo formatCurrency($booking['total_cost']); ?></td>
                                <td><?php echo displayStatusBadge($booking['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>