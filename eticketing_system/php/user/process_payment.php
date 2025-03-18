<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Database connection
include('../db_connect.php');

// Check if necessary POST data is set
if (!isset($_POST['busID'], $_POST['seatNumbers'], $_POST['totalFare']) || empty($_POST['busID']) || empty($_POST['seatNumbers']) || empty($_POST['totalFare'])) {
    die("Invalid request! Missing data.");
}

$busID = $_POST['busID'];
$seatNumbers = explode(",", $_POST['seatNumbers']);
$totalFare = $_POST['totalFare'];

// Simulate payment success (in real case, you would integrate a payment gateway here)
$paymentSuccess = true;  // Assume payment is successful

if ($paymentSuccess) {
    // Insert booking details into Bookings table
    foreach ($seatNumbers as $seat) {
        $insertQuery = "INSERT INTO Bookings (busID, seat_number, userID) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([$busID, $seat, $_SESSION['userID']]);
    }

    echo "<script>alert('Payment Successful! Seats have been booked.'); window.location.href = 'my_bookings.php';</script>";
} else {
    echo "<script>alert('Payment failed! Please try again.'); window.location.href = 'book_ticket.php?busID=$busID';</script>";
}
?>
