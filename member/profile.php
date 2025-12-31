> member/profile.php
<?php
session_start();

// Initialize user if not exists
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'name' => 'Member User',
        'email' => 'member@example.com',
        'id' => 'MEM-99'
    ];
}

// Handle Manual Update (Logic to update session)
if (isset($_POST['update_profile'])) {
    $_SESSION['user']['name'] = $_POST['full_name'];
    $_SESSION['user']['email'] = $_POST['email'];
    $message = "Profile updated successfully!";
}
?>
<!DOCTYPE html>
<html>
<head><title>My Profile</title></head>
<body>
    <h1>My Profile</h1>
    <a href="dashboard.php">← Back to Dashboard</a>
    <hr>

    <?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <form method="POST">
        <p>
            <strong>Member ID:</strong><br>
            <input type="text" value="<?php echo $_SESSION['user']['id']; ?>" disabled>
            <small>(ID cannot be changed)</small>
        </p>
        
        <p>
            <strong>Full Name:</strong><br>
            <input type="text" name="full_name" value="<?php echo $_SESSION['user']['name']; ?>" required>
        </p>

        <p>
            <strong>Email Address:</strong><br>
            <input type="email" name="email" value="<?php echo $_SESSION['user']['email']; ?>" required>
        </p>

        <button type="submit" name="update_profile">Save Changes</button>
    </form>

    <hr>
    <h3>Account Security</h3>
    <p>To change your password, please contact the System Admin.</p>
</body>
</html>