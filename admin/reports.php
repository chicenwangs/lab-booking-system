// 1. Fetch booking counts for the last 7 days
$query = "SELECT booking_date, COUNT(*) as total_bookings 
          FROM bookings 
          WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          GROUP BY booking_date 
          ORDER BY booking_date ASC";

$stmt = $pdo->query($query);
$data = $stmt->fetchAll();

// 2. Prepare data for JavaScript
// We need two lists: one for Labels (Dates) and one for Data (Counts)
$labels = [];
$counts = [];

foreach ($data as $row) {
    $labels[] = $row['booking_date'];
    $counts[] = $row['total_bookings'];
}

// 3. Convert PHP arrays to JavaScript-friendly format (JSON)
$js_labels = json_encode($labels);
$js_counts = json_encode($counts);

<div class="chart-container" style="background: white; padding: 20px; border-radius: 8px;">
    <h3>Laboratory Booking Trends (Last 7 Days)</h3>
    <canvas id="myAnalyticsChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('myAnalyticsChart').getContext('2d');

new Chart(ctx, {
    type: 'line', // You can change this to 'bar' if you prefer
    data: {
        labels: <?php echo $js_labels; ?>, // The dates from PHP
        datasets: [{
            label: 'Total Bookings',
            data: <?php echo $js_counts; ?>, // The counts from PHP
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.2)',
            fill: true,
            borderWidth: 2,
            tension: 0.3 // Makes the line curvy
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 } // Since we can't have half a booking
            }
        }
    }
});
</script>

