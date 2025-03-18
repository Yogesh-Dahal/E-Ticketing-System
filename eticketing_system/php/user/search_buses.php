<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Database connection
include('../db_connect.php');

// Fetch all buses and their related driver details
$sql = "SELECT Bus.busID, Bus.bus_name, Bus.numberPlate, Bus.capacity, Bus.fare, Bus.departure_date, 
               Bus.departure_time, Bus.bus_image, Route.source, Route.destination, Route.stops, 
               Driver.driver_name, Driver.license_number, Driver.license_image 
        FROM Bus 
        JOIN Route ON Bus.routeID = Route.routeID
        JOIN Driver ON Bus.busID = Driver.busID";
$stmt = $pdo->query($sql);
$buses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Buses</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            padding: 15px;
        }
        .navbar a {
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            text-align: center;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            padding: 20px;
        }
        .bus-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .bus-card img {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .bus-card h3 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .bus-card p {
            color: #777;
            font-size: 14px;
        }
        .btn-book {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn-book:hover {
            background-color: #45a049;
        }
        .bus-info, .driver-info {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .driver-info img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .stops-list {
            list-style: none;
            padding-left: 0;
            margin-top: 10px;
        }
        .stop-item {
            background-color: #f9f9f9;
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 5px;
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

<!-- Search Buses Content -->
<div class="container">
    <h2>Search Buses</h2>

    <!-- Loop through each bus and display its information -->
    <?php foreach ($buses as $bus): ?>
        <div class="bus-card">
            <div class="bus-info">
                <div>
                    <h3><?php echo $bus['bus_name']; ?></h3>
                    <p>Number Plate: <?php echo $bus['numberPlate']; ?></p>
                    <p>Capacity: <?php echo $bus['capacity']; ?> seats</p>
                    <p>Fare: Rs.<?php echo $bus['fare']; ?></p>
                    <p>Departure: <?php echo $bus['departure_date']; ?> at <?php echo $bus['departure_time']; ?></p>
                    <p>Route: <?php echo $bus['source']; ?> - <?php echo $bus['destination']; ?></p>
                </div>
                <img src="../../uploads/bus_images/<?php echo $bus['bus_image']; ?>" alt="Bus Image">
            </div>

            <!-- Display Stops -->
            <div class="stops-info">
                <h4>Stops:</h4>
                <?php
                    // Assuming stops are stored as a comma-separated list in the Route table
                    $stops = explode(",", $bus['stops']);
                    echo '<ul class="stops-list">';
                    foreach ($stops as $stop):
                ?>
                    <li class="stop-item"><?php echo $stop; ?></li>
                <?php endforeach; ?>
                </ul>
            </div>

            <!-- Driver Information -->
            <div class="driver-info">
                <div>
                    <p><strong>Driver:</strong> <?php echo $bus['driver_name']; ?></p>
                    <p><strong>License Number:</strong> <?php echo $bus['license_number']; ?></p>
                </div>
                <img src="../../uploads/driver_images/<?php echo $bus['license_image']; ?>" alt="Driver License Image">
            </div>

            <!-- Booking Button -->
            <a href="book_ticket.php?busID=<?php echo $bus['busID']; ?>" class="btn-book">Book This Bus</a>
        </div>
    <?php endforeach; ?>

</div>

</body>
</html>
