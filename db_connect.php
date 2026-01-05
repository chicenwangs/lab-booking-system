<?php $host = 'sql111.infinityfree.com'; $dbname = 'if0_40827849_ezlab'; $username = 'if0_40827849'; $password = 'NLQhanPOMim3uv'; 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "<h1>✅ Success! Your website is connected to the database.</h1>";
} catch (PDOException $e) {
    echo "<h1>❌ Connection Failed:</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>