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