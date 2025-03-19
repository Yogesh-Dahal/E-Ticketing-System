process_payment.php
<?php
session_start();
// var_dump("Hello");
// die;
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
$numberOfSeats = count($seatNumbers);
$totalFare = $_POST['totalFare']/$numberOfSeats;
$down_payment = (0.2 * $totalFare);
$departure_time = $_POST['departure_time'];
$departure_date = $_POST['departure_date'];

// Simulate payment success (in real case, you would integrate a payment gateway here)
$paymentSuccess = true;  // Assume payment is successful

if ($paymentSuccess) {
    // Insert booking details into Bookings table
    foreach ($seatNumbers as $seat) {
        // $insertQuery = "INSERT INTO Bookings (busID, seat_number, userID) VALUES (?, ?, ?)";
        $insertQuery = "INSERT INTO bookings (busID, userID, seat_number, booking_date, departure_time, departure_date, total_fare,down_payment, refund_status)
              VALUES(?,?,?,NOW(),?,?,?,?,?)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([$busID,  $_SESSION['userID'],$seat,$departure_time,$departure_date,$totalFare,$down_payment,'not_requested']);
    }

    echo "<script>alert('Payment Successful! Seats have been booked.'); window.location.href = 'my_bookings.php';</script>";
} else {
    echo "<script>alert('Payment failed! Please try again.'); window.location.href = 'book_ticket.php?busID=$busID';</script>";
}
?>