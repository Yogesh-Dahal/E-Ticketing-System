<?php
$host = "localhost";
$dbname = "eticketing_system";
$username = "root"; // default username for XAMPP
$password = ""; // default password for XAMPP

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
