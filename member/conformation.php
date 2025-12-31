> member/confirmation.php
<?php
session_start();
$my_bookings = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirmation</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <h1>Confirm Your Booking</h1>
    <div id="receipt-area">
        <p>User: Member Account</p>
        <p>Date: <?php echo date('Y-m-d'); ?></p>
        <ul>
            <?php foreach ($my_bookings as $item): ?>
                <li><?php echo $item['name']; ?> (<?php echo $item['id']; ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <button onclick="downloadPDF()">Download PDF Receipt</button>
    <a href="history.php?save=true">Finish Booking</a>

    <script>
        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.text("OFFICIAL LAB RECEIPT", 20, 20);
            doc.text("-------------------------", 20, 30);
            
            let y = 40;
            <?php foreach ($my_bookings as $item): ?>
                doc.text("- <?php echo $item['name']; ?>", 20, y);
                y += 10;
            <?php endforeach; ?>
            
            doc.save("Lab_Receipt.pdf");
        }
    </script>
</body>
</html>