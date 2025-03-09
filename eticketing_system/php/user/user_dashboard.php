<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

include('../db_connect.php');

// Get the logged-in user ID
$userID = $_SESSION['userID'];

// Fetch total user bookings
$bookingQuery = "SELECT COUNT(*) AS total_bookings FROM Bookings WHERE userID = ?";
$bookingStmt = $pdo->prepare($bookingQuery);
$bookingStmt->execute([$userID]);
$bookingCount = $bookingStmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
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
        .welcome {
            font-size: 24px;
            color: #333;
        }
        .cards {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            width: 45%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .card h3 {
            margin: 0;
            font-size: 36px;
        }
        .card p {
            margin: 10px 0 0;
            color: #777;
        }
        .card .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #45a049;
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

    <!-- Dashboard Content -->
    <div class="container">
        <div class="welcome">
            <h2>Welcome, User!</h2>
            <p>You are logged in as a User.</p>
        </div>

        <div class="cards">
            <!-- Card 1: My Bookings -->
            <div class="card">
                <h3><?php echo $bookingCount; ?></h3>
                <p>My Bookings</p>
                <button class="btn" onclick="window.location.href='my_bookings.php'">View Bookings</button>
            </div>
            <!-- Card 2: Search for Buses -->
            <div class="card">
                <h3>Find Buses</h3>
                <p>Search for available buses</p>
                <button class="btn" onclick="window.location.href='search_buses.php'">Search Buses</button>
            </div>
        </div>
    </div>

</body>
</html>
