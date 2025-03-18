<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['busID'], $_GET['seatNumber'])) {
    die("Invalid request: Missing busID or seatNumber.");
}

$busID = $_GET['busID'];
$seatNumber = $_GET['seatNumber'];
$userID = $_SESSION['userID'];

try {
    $pdo->beginTransaction();

    // Step 1: Ensure the seat is locked by the current user
    $checkLock = "SELECT COUNT(*) FROM lockedseats WHERE busID = ? AND seatNumber = ? AND userID = ? AND expiresAt > NOW() FOR UPDATE";
    $stmt = $pdo->prepare($checkLock);
    $stmt->execute([$busID, $seatNumber, $userID]);
    $lockCount = $stmt->fetchColumn();

    if ($lockCount == 0) {
        $pdo->rollBack();
        die("Error: Seat lock expired or not valid.");
    }

    // Step 2: Ensure the seat is not already booked
    $checkBooked = "SELECT COUNT(*) FROM bookings WHERE busID = ? AND seat_number = ?";
    $stmt = $pdo->prepare($checkBooked);
    $stmt->execute([$busID, $seatNumber]);
    $bookedCount = $stmt->fetchColumn();

    if ($bookedCount > 0) {
        $pdo->rollBack();
        die("Error: Seat is already booked.");
    }

    // Step 3: Fetch bus details (departure_date, departure_time, fare)
    $checkBusQuery = "SELECT departure_date, departure_time, fare FROM bus WHERE busID = ?";
    $stmt = $pdo->prepare($checkBusQuery);
    $stmt->execute([$busID]);
    $busDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: Check if bus details are retrieved
    if (!$busDetails) {
        die("Error: Bus details not found.");
    }

    echo "Bus Details - Departure Date: " . $busDetails['departure_date'] . 
         ", Departure Time: " . $busDetails['departure_time'] . 
         ", Fare: " . $busDetails['fare'];
    
    // Step 4: Insert booking into the bookings table
    $query = "INSERT INTO bookings (busID, userID, seat_number, booking_date, departure_time, departure_date, fare, status)
              SELECT b.busID, ?, ?, NOW(), b.departure_time, b.departure_date, b.fare, 'confirmed'
              FROM bus b 
              WHERE b.busID = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$userID, $seatNumber, $busID]);

    // Step 5: Remove lock after booking
    $removeLock = "DELETE FROM lockedseats WHERE busID = ? AND seatNumber = ?";
    $stmt = $pdo->prepare($removeLock);
    $stmt->execute([$busID, $seatNumber]);

    $pdo->commit();
    echo "Booking confirmed!";
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error confirming booking: " . $e->getMessage());
    die("Error confirming booking. Please try again later.");
}
?>
