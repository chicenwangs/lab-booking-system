<?php
/**
 * FIXED: Shopping Cart
 * Added: Authentication, CSS
 * Kept: Your teammate's cart logic 100% intact
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// ✅ ADDED: Proper authentication
requireLogin();

// ========================================================================
// ✅ KEPT: Your teammate's cart logic (UNCHANGED)
// ========================================================================

// 1. Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 2. Logic to ADD item
if (isset($_POST['add'])) {
    $newItem = [
        'id' => $_POST['lab_id'],
        'name' => $_POST['lab_name'],
        'time' => date('Y-m-d H:i:s')
    ];
    $_SESSION['cart'][] = $newItem;
    
    // ✅ ADDED: Flash message
    setFlash('Lab added to cart!', 'success');
    
    header("Location: cart.php"); // Refresh to clear POST
    exit();
}

// 3. Logic to REMOVE specific item
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    
    // ✅ ADDED: Flash message
    setFlash('Lab removed from cart', 'info');
    
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <!-- ✅ ADDED: CSS Link -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- ✅ ADDED: Header -->
    <?php include '../includes/header.php'; ?>
    
    <main class="container" style="padding: 2rem 0;">
        <h1>🛒 Your Booking Cart</h1>
        
        <!-- ✅ ADDED: Flash messages -->
        <?php displayFlash(); ?>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <!-- Empty cart message with styling -->
            <div class="card" style="text-align: center; padding: 3rem;">
                <h2 style="color: var(--text-light);">Your cart is empty</h2>
                <p>Add some labs to get started!</p>
                <br>
                <a href="book.php" class="btn btn-primary">Browse Labs</a>
            </div>
        <?php else: ?>
            <!-- Cart items table with styling -->
            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Lab Name</th>
                                <th>Added At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td><?php echo date('M d, Y g:i A', strtotime($item['time'])); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $index; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Remove this lab from cart?')">
                                        Remove
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Action buttons -->
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: space-between; flex-wrap: wrap;">
                    <a href="book.php" class="btn btn-secondary">← Add More Labs</a>
                    <a href="confirmation.php" class="btn btn-primary">Confirm & Get Receipt →</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- ✅ ADDED: Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>