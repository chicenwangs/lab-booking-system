> member/dashboard.php
<?php
session_start();

// Mock user session data (Usually set during login)
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['name' => 'Member User', 'id' => 'MEM-99'];
}

// Get current cart count for the "Heaviest Logic" requirement
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['user']['name']; ?>!</h1>
    <hr>

    <div style="display: flex; gap: 20px;">
        <div style="border: 1px solid #000; padding: 20px; border-radius: 8px;">
            <h3>Current Cart</h3>
            <p>You have <strong><?php echo $cart_count; ?></strong> lab(s) in your selection.</p>
            <a href="cart.php">Manage Cart</a>
        </div>

        <div style="border: 1px solid #000; padding: 20px; border-radius: 8px;">
            <h3>Quick Actions</h3>
            <ul>
                <li><a href="book.php">Book a New Lab</a></li>
                <li><a href="history.php">View My History</a></li>
                <li><a href="profile.php">My Profile Settings</a></li>
            </ul>
        </div>
    </div>

    <br><br>
    <form method="POST" action="../logout.php">
        <button type="submit">Logout</button>
    </form>
</body>
</html>