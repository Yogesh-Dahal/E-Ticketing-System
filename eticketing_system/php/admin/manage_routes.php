<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
include('../db_connect.php');

// Handling route addition
if (isset($_POST['add_route'])) {
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $stops = json_encode($_POST['stops']); // Assuming stops are sent as an array

    // Insert new route into the database
    $sql = "INSERT INTO Route (source, destination, stops) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);  // Changed from $conn to $pdo
    $stmt->execute([$source, $destination, $stops]);

    echo "Route added successfully!";
}

// Handling route deletion
if (isset($_GET['delete_route_id'])) {
    $routeID = $_GET['delete_route_id'];

    // Delete the route from the database
    $sql = "DELETE FROM Route WHERE routeID = ?";
    $stmt = $pdo->prepare($sql);  // Changed from $conn to $pdo
    $stmt->execute([$routeID]);

    echo "Route deleted successfully!";
}

// Fetching all routes
$sql = "SELECT * FROM Route";
$stmt = $pdo->query($sql);  // Changed from $conn to $pdo
$routes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Routes</title>
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
        .form-container {
            margin-top: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container h3 {
            margin-bottom: 20px;
        }
        .input-field {
            margin-bottom: 15px;
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .routes-table {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }
        .routes-table th, .routes-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .routes-table th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
    <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_buses.php">Manage Buses</a>
        <a href="manage_routes.php">Manage Routes</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="../index.php">Logout</a>
    </div>

    <!-- Add Route Form -->
    <div class="container">
        <div class="form-container">
            <h3>Add a New Route</h3>
            <form action="manage_routes.php" method="POST">
                <input type="text" name="source" class="input-field" placeholder="Source" required>
                <input type="text" name="destination" class="input-field" placeholder="Destination" required>
                <textarea name="stops[]" class="input-field" placeholder="Stops (comma separated)" required></textarea>
                <button type="submit" name="add_route" class="btn">Add Route</button>
            </form>
        </div>

        <!-- Display Routes -->
        <h3>Existing Routes</h3>
        <table class="routes-table">
            <tr>
                <th>Route ID</th>
                <th>Source</th>
                <th>Destination</th>
                <th>Stops</th>
                <th>Action</th>
            </tr>
            <?php foreach ($routes as $route) { ?>
            <tr>
                <td><?php echo $route['routeID']; ?></td>
                <td><?php echo $route['source']; ?></td>
                <td><?php echo $route['destination']; ?></td>
                <td><?php echo implode(", ", json_decode($route['stops'])); ?></td>
                <td>
                    <a href="manage_routes.php?delete_route_id=<?php echo $route['routeID']; ?>" class="btn">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

</body>
</html>
