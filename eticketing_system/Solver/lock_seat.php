<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$busID = $data['busID'];
$seatNumber = $data['seatNumber'];
$lockedBy = $_SESSION['userID'];
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 minute')); // Lock for 1 minute

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if the seat is already locked
    $checkLock = "SELECT COUNT(*) FROM lockedseats WHERE busID = :busID AND seatNumber = :seatNumber AND expiresAt > NOW()";
    $stmt = $conn->prepare($checkLock);
    $stmt->execute([':busID' => $busID, ':seatNumber' => $seatNumber]);
    $lockCount = $stmt->fetchColumn();

    if ($lockCount > 0) {
        echo json_encode(['success' => false, 'message' => 'Seat is already locked.']);
    } else {
        // Lock the seat
        $lockID = uniqid(); // Generate a unique lock ID
        $sql = "INSERT INTO lockedseats (lockID, busID, seatNumber, lockedBy, expiresAt) VALUES (:lockID, :busID, :seatNumber, :lockedBy, :expiresAt)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':lockID' => $lockID,
            ':busID' => $busID,
            ':seatNumber' => $seatNumber,
            ':lockedBy' => $lockedBy,
            ':expiresAt' => $expiresAt
        ]);
        echo json_encode(['success' => true, 'message' => 'Seat locked successfully!']);
    }

    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error locking seat: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>