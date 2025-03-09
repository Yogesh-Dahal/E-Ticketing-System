<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

include('../db_connect.php');

// Get the logged-in user's ID
$userID = $_SESSION['userID'];

// Fetch user bookings with bus details
$query = "SELECT B.bookingID, B.seat_number, B.booking_date, Bus.bus_name, Bus.numberPlate, 
                 Route.source, Route.destination
          FROM Bookings B
          JOIN Bus ON B.busID = Bus.busID
          JOIN Route ON Bus.routeID = Route.routeID
          WHERE B.userID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$userID]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - E-Ticketing</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #eef2f3;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #004466;
            padding: 15px;
            text-align: center;
        }

        .navbar a {
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            margin: 0 10px;
            font-size: 16px;
        }

        .navbar a:hover {
            background-color: #0077b6;
            border-radius: 5px;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #0077b6;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e3f2fd;
        }

        .no-bookings {
            text-align: center;
            font-size: 18px;
            color: #777;
        }

        .cancel-btn {
            background-color: #d9534f;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .cancel-btn:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <a href="user_dashboard.php">Dashboard</a>
    <a href="search_buses.php">Search Buses</a>
    <a href="my_bookings.php">My Bookings</a>
    <a href="../index.php">Logout</a>
</div>

<!-- My Bookings Content -->
<div class="container">
    <h2>My Bookings</h2>

    <?php if (count($bookings) > 0): ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>Bus Name</th>
                    <th>Number Plate</th>
                    <th>Route</th>
                    <th>Seat Numbers</th>
                    <th>Booking Date</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo $booking['bus_name']; ?></td>
                    <td><?php echo $booking['numberPlate']; ?></td>
                    <td><?php echo $booking['source'] . " - " . $booking['destination']; ?></td>
                    <td>
                        <?php 
                            $seats = json_decode($booking['seat_number'], true); // Decode JSON into PHP array
                            if (is_array($seats)) {
                                echo "Seats: " . implode(", ", $seats);
                            } else {
                                echo "Seats: " . $seats;
                            }
                        ?>
                    </td>
                    <td><?php echo date("d M Y, h:i A", strtotime($booking['booking_date'])); ?></td>
                    <td>
                        <form method="POST" action="cancel_bookings.php">
                            <input type="hidden" name="bookingID" value="<?php echo $booking['bookingID']; ?>">
                            <button type="submit" class="cancel-btn">Cancel</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php else: ?>
        <p class="no-bookings">No bookings found.</p>
    <?php endif; ?>
</div>

</body>
</html>
