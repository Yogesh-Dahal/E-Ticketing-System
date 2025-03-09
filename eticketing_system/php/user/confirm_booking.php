<?php
session_start();
include('../db_connect.php');

// Ensure the user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Ensure busID and seatNumber are provided in the GET request
if (!isset($_GET['busID'], $_GET['seatNumber'])) {
    die("Invalid request: Missing busID or seatNumber.");
}

$busID = $_GET['busID'];
$seatNumber = $_GET['seatNumber'];
$userID = $_SESSION['userID'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // Ensure the seat is locked by the current user
    $checkLock = "SELECT COUNT(*) FROM lockedseats WHERE busID = ? AND seatNumber = ? AND userID = ? AND expiresAt > NOW() FOR UPDATE";
    $stmt = $pdo->prepare($checkLock);
    $stmt->execute([$busID, $seatNumber, $userID]);
    $lockCount = $stmt->fetchColumn();

    if ($lockCount == 0) {
        $pdo->rollBack();
        die("Error: Seat lock expired or not valid.");
    }

    // Ensure the seat is not already booked
    $checkBooked = "SELECT COUNT(*) FROM bookings WHERE busID = ? AND seatNumber = ?";
    $stmt = $pdo->prepare($checkBooked);
    $stmt->execute([$busID, $seatNumber]);
    $bookedCount = $stmt->fetchColumn();

    if ($bookedCount > 0) {
        $pdo->rollBack();
        die("Error: Seat is already booked.");
    }

    // Insert into bookings table
    $bookSeat = "INSERT INTO bookings (busID, seatNumber, userID) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($bookSeat);
    $stmt->execute([$busID, $seatNumber, $userID]);

    // Remove lock after booking
    $removeLock = "DELETE FROM lockedseats WHERE busID = ? AND seatNumber = ?";
    $stmt = $pdo->prepare($removeLock);
    $stmt->execute([$busID, $seatNumber]);

    // Commit transaction
    $pdo->commit();
    echo "Booking confirmed!";
} catch (Exception $e) {
    // Rollback in case of error
    $pdo->rollBack();
    error_log("Error confirming booking: " . $e->getMessage());
    die("Error confirming booking. Please try again later.");
}
?>