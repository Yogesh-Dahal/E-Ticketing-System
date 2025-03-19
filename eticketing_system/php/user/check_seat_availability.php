<?php
include('../db_connect.php');

$data = json_decode(file_get_contents("php://input"), true);
$busID = $data['busID'];
$selectedSeats = $data['selectedSeats'];

$response = ["status" => "success", "bookedSeats" => []];

$query = "SELECT seat_number FROM Bookings WHERE busID = ? AND seat_number IN (" . implode(',', array_fill(0, count($selectedSeats), '?')) . ")";
$stmt = $pdo->prepare($query);
$stmt->execute(array_merge([$busID], $selectedSeats));
$bookedSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($bookedSeats)) {
    $response["bookedSeats"] = $bookedSeats;
}

echo json_encode($response);
?>
