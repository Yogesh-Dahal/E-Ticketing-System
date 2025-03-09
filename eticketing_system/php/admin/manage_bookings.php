<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include('../db_connect.php');

// Fetch all buses
$sql = "SELECT Bus.busID, Bus.bus_name, Bus.numberPlate, Bus.capacity, Route.source, Route.destination 
        FROM Bus 
        JOIN Route ON Bus.routeID = Route.routeID";

$stmt = $pdo->prepare($sql); // Use $sql instead of $query
$stmt->execute();
$buses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f3;
        }
        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #0077b6;
            color: white;
        }
        .view-btn {
            padding: 5px 10px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .view-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Bus Bookings</h2>
    <table>
        <tr>
            <th>Bus Name</th>
            <th>Number Plate</th>
            <th>Route</th>
            <th>Total Seats</th>
            <th>View Seats</th>
        </tr>
        <?php foreach ($buses as $bus): ?>
        <tr>
            <td><?php echo $bus['bus_name']; ?></td>
            <td><?php echo $bus['numberPlate']; ?></td>
            <td><?php echo $bus['source'] . " - " . $bus['destination']; ?></td>
            <td><?php echo $bus['capacity']; ?></td> <!-- Corrected field name from total_seats to capacity -->
            <td>
                <a href="view_seats.php?busID=<?php echo $bus['busID']; ?>" class="view-btn">View Seats</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
