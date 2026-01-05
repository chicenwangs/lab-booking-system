<?php
// Start the session to track who is logged in
session_start();

// THE SECURITY GUARD LOGIC
// Check if the user is logged in AND if their role is 'admin'
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    
    // If they are not an admin, send them away to the login page
    header("Location: ../auth/login.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 50px; }
        .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success-msg { color: green; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Admin Control Room</h1>
        <p class="success-msg">Mission Accomplished: You have successfully accessed the Admin Dashboard!</p>
        <p>This is where Squad 3 will build the Lab Management and Analytics tools.</p>
        
        <hr>
        <a href="../auth/logout.php">Log Out</a>
    </div>

</body>
</html>