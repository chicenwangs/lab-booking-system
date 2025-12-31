<?php
// Mock data for your labs
$labs = [
    ['id' => 'L001', 'name' => 'Advanced Robotics Lab'],
    ['id' => 'L002', 'name' => 'Chemical Analysis Room'],
    ['id' => 'L003', 'name' => 'Cyber Security Hub'],
    ['id' => 'L004', 'name' => 'General Purpose PC Lab']
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lab Selection</title>
</head>
<body>
    <h1>Select a Lab to Book</h1>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Lab ID</th>
                <th>Lab Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($labs as $lab): ?>
            <tr>
                <td><?php echo $lab['id']; ?></td>
                <td><?php echo $lab['name']; ?></td>
                <td>
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="lab_id" value="<?php echo $lab['id']; ?>">
                        <input type="hidden" name="lab_name" value="<?php echo $lab['name']; ?>">
                        <button type="submit" name="add">Add to Cart</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <a href="cart.php">Go to My Cart →</a>
</body>
</html>