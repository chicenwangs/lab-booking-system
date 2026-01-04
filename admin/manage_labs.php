<?php
// 1. Start the session to access the user's login information
session_start();

/**
 * ADMIN SECURITY SHIELD
 * This logic verifies if the user has permission to be here.
 */

// 2. Check: Is the user logged in? AND Is their role specifically 'admin'?
// Note: Double-check with Squad 1 if they used 'user_role' or just 'role' in their login code.
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    
    // 3. If they are NOT an admin, kick them out to the login page.
    // The "../" tells the computer to look outside the 'admin' folder for the 'auth' folder.
    header("Location: ../auth/login.php?error=access_denied");
    
    // 4. Stop the rest of the page from loading for unauthorized users.
    exit();
}

// If the code reaches this point, the user is a verified Admin!
?>

require_once('../includes/db.php'); 

// Fetch all labs from the database
$stmt = $pdo->query("SELECT * FROM labs");
$labs = $stmt->fetchAll();

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Labs - EZLab</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #2c3e50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        .btn-edit { background: #f39c12; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <a href="dashboard.php">← Back to Dashboard</a>
    <h1>Manage Laboratory Rooms</h1>

    <table>
        <thead>
            <tr>
                <th>Lab Name</th>
                <th>Location</th>
                <th>Price (RM/hr)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($labs as $lab): ?>
            <tr>
                <td><?php echo $lab['lab_name']; ?></td>
                <td><?php echo $lab['location']; ?></td>
                <td><?php echo $lab['price']; ?></td>
                <td><?php echo $lab['status']; ?></td>
                <td>
                    <a href="#" class="btn btn-edit">Edit</a>
                    <a href="#" class="btn btn-delete">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

<?php
include 'security_shield.php'; // Or paste the shield code here
require_once('../includes/db.php');

// We only run this code IF the user clicks the "Submit" button
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Get the data from the form
    $name  = $_POST['lab_name'];
    $loc   = $_POST['location'];
    $price = $_POST['price'];
    $stat  = $_POST['status'];

    // 2. Prepare the SQL (Safety First!)
    // We use ":" placeholders to prevent SQL Injection
    $sql = "INSERT INTO labs (lab_name, location, price, status) 
            VALUES (:name, :loc, :price, :stat)";
    
    $stmt = $pdo->prepare($sql);
    
    // 3. Execute and save to Database
    if ($stmt->execute(['name' => $name, 'loc' => $loc, 'price' => $price, 'stat' => $stat])) {
        // Redirect back to the list with a success message
        header("Location: manage_labs.php?msg=Lab Added Successfully");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Lab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #27ae60; color: white; padding: 10px 20px; border: none; cursor: pointer; width: 100%; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add New Laboratory</h2>
    <form method="POST" action="add_lab.php">
        <label>Lab Name:</label>
        <input type="text" name="lab_name" placeholder="e.g. Computing Lab 1" required>

        <label>Location:</label>
        <input type="text" name="location" placeholder="e.g. Block G" required>

        <label>Price per Hour (RM):</label>
        <input type="number" step="0.01" name="price" placeholder="50.00" required>

        <label>Availability:</label>
        <select name="status">
            <option value="Available">Available</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Occupied">Occupied</option>
        </select>

        <button type="submit">Save Lab</button>
        <a href="manage_labs.php" style="display:block; text-align:center; margin-top:10px;">Cancel</a>
    </form>
</div>

</body>
</html>