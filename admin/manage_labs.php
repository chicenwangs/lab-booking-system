<?php
session_start();
// Security Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}
// Database Connection (Squad 1's file)
require_once('../includes/db.php');
?>

<?php include('security.php'); 
// Fetch labs
$stmt = $pdo->query("SELECT * FROM labs ORDER BY id DESC");
$labs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Labs</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Lab Management</h1>
    <a href="add_lab.php" class="btn-add"> + Add New Lab</a>
    
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Location</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($labs as $lab): ?>
        <tr>
            <td><?php echo htmlspecialchars($lab['lab_name']); ?></td>
            <td><?php echo htmlspecialchars($lab['location']); ?></td>
            <td><?php echo $lab['status']; ?></td>
            <td>
                <a href="edit_lab.php?id=<?php echo $lab['id']; ?>">Edit</a> | 
                <a href="delete_lab.php?id=<?php echo $lab['id']; ?>" onclick="return confirm('Delete this?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

<?php include('security.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO labs (lab_name, location, status) VALUES (:n, :l, :s)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['n' => $_POST['lab_name'], 'l' => $_POST['location'], 's' => $_POST['status']]);
    header("Location: manage_labs.php?msg=Added");
}
?>
<form method="POST">
    <input type="text" name="lab_name" placeholder="Lab Name" required>
    <input type="text" name="location" placeholder="Location" required>
    <select name="status">
        <option value="Available">Available</option>
        <option value="Maintenance">Maintenance</option>
    </select>
    <button type="submit">Save Lab</button>
</form>

<?php include('security.php'); 

$id = $_GET['id'];
// Fetch current data for this specific ID
$stmt = $pdo->prepare("SELECT * FROM labs WHERE id = ?");
$stmt->execute([$id]);
$lab = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "UPDATE labs SET lab_name = :n, location = :l, status = :s WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['n' => $_POST['lab_name'], 'l' => $_POST['location'], 's' => $_POST['status'], 'id' => $id]);
    header("Location: manage_labs.php?msg=Updated");
}
?>
<form method="POST">
    <input type="text" name="lab_name" value="<?php echo $lab['lab_name']; ?>">
    <input type="text" name="location" value="<?php echo $lab['location']; ?>">
    <select name="status">
        <option value="Available" <?php if($lab['status'] == 'Available') echo 'selected'; ?>>Available</option>
        <option value="Maintenance" <?php if($lab['status'] == 'Maintenance') echo 'selected'; ?>>Maintenance</option>
    </select>
    <button type="submit">Update Lab</button>
</form>

<?php 
include('security.php'); 

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM labs WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: manage_labs.php?msg=Deleted");
exit();
?>