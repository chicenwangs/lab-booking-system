<?php
/**
 * FIXED: Lab Booking Page
 * Added: Authentication, CSS, Real database labs
 * Kept: Your teammate's add-to-cart logic
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// ✅ ADDED: Proper authentication
requireLogin();

// ✅ CHANGED: Get real labs from database instead of mock data
$stmt = $pdo->query("SELECT * FROM labs WHERE status = 'active' ORDER BY name");
$labs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Selection</title>
    <!-- ✅ ADDED: CSS Link -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- ✅ ADDED: Header -->
    <?php include '../includes/header.php'; ?>
    
    <main class="container" style="padding: 2rem 0;">
        <h1>Select a Lab to Book</h1>
        
        <!-- ✅ ADDED: Flash messages -->
        <?php displayFlash(); ?>
        
        <!-- ✅ ADDED: Styled table with your CSS -->
        <div class="card">
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
                                <!-- ✅ KEPT: Your teammate's form logic -->
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
        </div>
        
        <br>
        <a href="cart.php" class="btn btn-secondary">🛒 Go to My Cart</a>
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </main>

    <!-- ✅ ADDED: Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>