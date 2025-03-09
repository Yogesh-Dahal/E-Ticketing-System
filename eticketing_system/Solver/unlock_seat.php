<?php
session_start();
include('../php/db_connect.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $busID = $_POST['busID'];
    $seatNumber = $_POST['seatNumber'];
    $userID = $_SESSION['userID'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete the seat lock
        $deleteLock = "DELETE FROM lockedseats WHERE busID = ? AND seatNumber = ? AND lockedBy = ?";
        $stmt = $pdo->prepare($deleteLock);
        $stmt->execute([$busID, $seatNumber, $userID]);

        // Commit transaction
        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Seat unlocked successfully."]);
    } catch (Exception $e) {
        // Rollback in case of error
        $pdo->rollBack();
        error_log("Error unlocking seat: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Error unlocking seat. Please try again."]);
    }
}
?>