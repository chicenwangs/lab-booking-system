<?php
/**
 * ADMIN: Manage Labs - CRUD Operations
 * Add, Edit, Delete, and Change Status of Labs
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireAdmin();

// Handle Add Lab
if (isset($_POST['add_lab'])) {
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $capacity = intval($_POST['capacity']);
    $equipment = clean($_POST['equipment']);
    $hourlyRate = floatval($_POST['hourly_rate']);
    $status = clean($_POST['status']);
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'Lab name is required';
    if ($capacity < 1) $errors[] = 'Capacity must be at least 1';
    if ($hourlyRate < 0) $errors[] = 'Hourly rate cannot be negative';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO labs (name, description, capacity, equipment, hourly_rate, status) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $description, $capacity, $equipment, $hourlyRate, $status])) {
                setFlash('Lab added successfully!', 'success');
                header("Location: manage_labs.php");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = 'Failed to add lab';
            logError('Add lab error: ' . $e->getMessage());
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['lab_errors'] = $errors;
    }
}

// Handle Edit Lab
if (isset($_POST['edit_lab'])) {
    $labId = intval($_POST['lab_id']);
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $capacity = intval($_POST['capacity']);
    $equipment = clean($_POST['equipment']);
    $hourlyRate = floatval($_POST['hourly_rate']);
    $status = clean($_POST['status']);
    
    try {
        $stmt = $pdo->prepare("UPDATE labs SET name = ?, description = ?, capacity = ?, equipment = ?, hourly_rate = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $capacity, $equipment, $hourlyRate, $status, $labId])) {
            setFlash('Lab updated successfully!', 'success');
            header("Location: manage_labs.php");
            exit();
        }
    } catch (PDOException $e) {
        setFlash('Failed to update lab', 'error');
        logError('Edit lab error: ' . $e->getMessage());
    }
}

// Handle Delete Lab
if (isset($_GET['delete'])) {
    $labId = intval($_GET['delete']);
    
    try {
        // Check if lab has bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE lab_id = ?");
        $stmt->execute([$labId]);
        $bookingCount = $stmt->fetch()['count'];
        
        if ($bookingCount > 0) {
            setFlash("Cannot delete lab with existing bookings. Set to inactive instead.", 'error');
        } else {
            $stmt = $pdo->prepare("DELETE FROM labs WHERE id = ?");
            if ($stmt->execute([$labId])) {
                setFlash('Lab deleted successfully!', 'success');
            }
        }
    } catch (PDOException $e) {
        setFlash('Failed to delete lab', 'error');
        logError('Delete lab error: ' . $e->getMessage());
    }
    
    header("Location: manage_labs.php");
    exit();
}

// Get all labs
$stmt = $pdo->query("SELECT * FROM labs ORDER BY name");
$labs = $stmt->fetchAll();

// Get lab for editing
$editLab = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM labs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editLab = $stmt->fetch();
}

$labErrors = $_SESSION['lab_errors'] ?? [];
unset($_SESSION['lab_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Labs - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container" style="padding: 2rem 0;">
        <h1>🔬 Manage Laboratories</h1>
        
        <?php displayFlash(); ?>
        
        <?php if (!empty($labErrors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($labErrors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Add/Edit Lab Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><?php echo $editLab ? 'Edit Laboratory' : 'Add New Laboratory'; ?></h2>
            </div>
            
            <form method="POST">
                <?php if ($editLab): ?>
                    <input type="hidden" name="lab_id" value="<?php echo $editLab['id']; ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="name">Lab Name *</label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo $editLab ? htmlspecialchars($editLab['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity">Capacity *</label>
                        <input type="number" id="capacity" name="capacity" required min="1"
                               value="<?php echo $editLab ? $editLab['capacity'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="hourly_rate">Hourly Rate ($) *</label>
                        <input type="number" id="hourly_rate" name="hourly_rate" required min="0" step="0.01"
                               value="<?php echo $editLab ? $editLab['hourly_rate'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo ($editLab && $editLab['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editLab && $editLab['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="maintenance" <?php echo ($editLab && $editLab['status'] === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="2"><?php echo $editLab ? htmlspecialchars($editLab['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="equipment">Equipment (comma-separated)</label>
                    <textarea id="equipment" name="equipment" rows="2"><?php echo $editLab ? htmlspecialchars($editLab['equipment']) : ''; ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="<?php echo $editLab ? 'edit_lab' : 'add_lab'; ?>" class="btn btn-success">
                        <?php echo $editLab ? '✓ Update Lab' : '+ Add Lab'; ?>
                    </button>
                    <?php if ($editLab): ?>
                        <a href="manage_labs.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Labs List -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">All Laboratories (<?php echo count($labs); ?>)</h2>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Capacity</th>
                            <th>Rate/Hour</th>
                            <th>Equipment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($labs as $lab): ?>
                        <tr>
                            <td>L<?php echo str_pad($lab['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($lab['name']); ?></strong><br>
                                <small style="color: var(--text-light);"><?php echo htmlspecialchars(substr($lab['description'], 0, 50)); ?>...</small>
                            </td>
                            <td><?php echo $lab['capacity']; ?> people</td>
                            <td><strong><?php echo formatCurrency($lab['hourly_rate']); ?></strong></td>
                            <td><small><?php echo substr(htmlspecialchars($lab['equipment']), 0, 40); ?>...</small></td>
                            <td>
                                <?php
                                $badgeClass = getLabStatusBadgeClass($lab['status']);
                                echo "<span class='badge {$badgeClass}'>" . ucfirst($lab['status']) . "</span>";
                                ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="manage_labs.php?edit=<?php echo $lab['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="manage_labs.php?delete=<?php echo $lab['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Delete this lab? This cannot be undone if it has no bookings.')">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>