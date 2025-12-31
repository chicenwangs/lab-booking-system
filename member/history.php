> member/history.php
<?php
session_start();

// If coming from confirmation, "save" the data to a text file
if (isset($_GET['save']) && !empty($_SESSION['cart'])) {
    $log = "Booking on " . date('Y-m-d H:i') . " for: ";
    foreach ($_SESSION['cart'] as $item) {
        $log .= $item['name'] . ", ";
    }
    $log .= "\n";
    
    file_put_contents("history.txt", $log, FILE_APPEND);
    unset($_SESSION['cart']); // Clear cart after saving
}

$historyData = file_exists("history.txt") ? file("history.txt") : [];
?>

<!DOCTYPE html>
<html>
<head><title>Booking History</title></head>
<body>
    <h1>My Booking History</h1>
    <ul>
        <?php foreach (array_reverse($historyData) as $line): ?>
            <li><?php echo htmlspecialchars($line); ?></li>
        <?php endforeach; ?>
    </ul>
    <a href="book.php">Make New Booking</a>
</body>
</html>