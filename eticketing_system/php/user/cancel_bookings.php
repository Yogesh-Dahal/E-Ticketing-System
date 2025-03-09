<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookingID'])) {
    $bookingID = $_POST['bookingID'];

    try {
        $pdo->beginTransaction();

        // Step 1: Get the busID and seat_number of the booking
        $query = "SELECT busID, seat_number FROM bookings WHERE bookingID = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$bookingID]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            $busID = $booking['busID'];

            // Step 2: Delete the booking (removes booked seat)
            $deleteQuery = "DELETE FROM bookings WHERE bookingID = ?";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->execute([$bookingID]);

            $pdo->commit();

            // Redirect back to my_bookings.php after successful cancellation
            header("Location: my_bookings.php");
            exit();
        } else {
            throw new Exception("Booking not found.");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
