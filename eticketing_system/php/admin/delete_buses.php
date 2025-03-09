<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
include('../db_connect.php');

// Ensure busID is set and valid
if (isset($_GET['busID']) && is_numeric($_GET['busID'])) {
    $busID = $_GET['busID'];

    // Check if bus exists before deletion
    $checkSql = "SELECT * FROM Bus WHERE busID = ?";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([$busID]);

    if ($stmt->rowCount() > 0) {
        // Proceed with deletion if the bus exists
        $deleteSql = "DELETE FROM Bus WHERE busID = ?";
        $stmt = $pdo->prepare($deleteSql);
        $stmt->execute([$busID]);

        // Check if deletion was successful
        if ($stmt->rowCount() > 0) {
            echo "Bus deleted successfully.";
            header("Location: manage_buses.php");
            exit();
        } else {
            echo "Failed to delete bus.";
        }
    } else {
        echo "Invalid bus ID.";
    }
} else {
    echo "Invalid bus ID.";
}
?>
