> member/cart.php
<?php
session_start();

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
    header("Location: cart.php"); // Refresh to clear POST
    exit();
}

// 3. Logic to REMOVE specific item
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Your Cart</title></head>
<body>
    <h1>Your Booking Cart</h1>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <p>Your cart is empty. <a href="book.php">Go back to selection.</a></p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Lab Name</th>
                <th>Added At</th>
                <th>Action</th>
            </tr>
            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
            <tr>
                <td><?php echo $item['name']; ?></td>
                <td><?php echo $item['time']; ?></td>
                <td><a href="cart.php?remove=<?php echo $index; ?>">Remove</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br>
        <a href="book.php">Add More</a> | 
        <a href="conformation.php"><b>Confirm & Get Receipt →</b></a>
    <?php endif; ?>
</body>
</html>