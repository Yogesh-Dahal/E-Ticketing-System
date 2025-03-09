<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
include('../db_connect.php');

// Handling bus addition
if (isset($_POST['add_bus'])) {
    $routeID = $_POST['routeID'];
    $busName = $_POST['bus_name'];
    $busNumberPlate = $_POST['number_plate'];
    $busCapacity = $_POST['capacity'];
    $busImage = $_FILES['bus_image']['name'];

    // Upload bus image
    $targetDir = "../../uploads/bus_images/";
    $targetFile = $targetDir . basename($busImage);
    
    if (move_uploaded_file($_FILES['bus_image']['tmp_name'], $targetFile)) {
        $sql = "INSERT INTO Bus (routeID, bus_name, numberPlate, capacity, bus_image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$routeID, $busName, $busNumberPlate, $busCapacity, $busImage]);

        // Get the busID of the newly inserted bus
        $busID = $pdo->lastInsertId(); 

        // Redirect to the driver addition page with busID as a parameter
        header("Location: add_driver.php?busID=$busID");
        exit();
    } else {
        echo "Sorry, there was an error uploading the image.";
    }
}

// Fetching all buses including busID
$sql = "SELECT busID, bus_name, routeID, numberPlate, capacity, bus_image FROM Bus";
$stmt = $pdo->query($sql);
$buses = $stmt->fetchAll();

// Fetching all routes for selection
$sql = "SELECT * FROM Route";
$stmt = $pdo->query($sql);
$routes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Buses</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #3b5998;
            padding: 15px;
            text-align: center;
            font-size: 18px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            padding: 8px 16px;
            border-radius: 5px;
        }

        .navbar a:hover {
            background-color: #5d74a3;
        }

        .container {
            width: 80%;
            margin: 40px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 28px;
            color: #333;
            margin-bottom: 30px;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-container input,
        .form-container select,
        .form-container button {
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .form-container input[type="file"] {
            border: none;
        }

        .form-container button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border: none;
        }

        .form-container button:hover {
            background-color: #45a049;
        }

        .buses-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .buses-table th, .buses-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .buses-table th {
            background-color: #4CAF50;
            color: white;
        }

        .buses-table td img {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .buses-table td a {
            padding: 6px 12px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .buses-table td a:hover {
            background-color: #e53935;
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

    <!-- Manage Buses Container -->
    <div class="container">
        <h2>Manage Buses</h2>

        <!-- Add New Bus Form -->
        <div class="form-container">
            <h3>Add a New Bus</h3>
            <form action="manage_buses.php" method="POST" enctype="multipart/form-data">
                <select name="routeID" required>
                    <option value="">Select Route</option>
                    <?php foreach ($routes as $route) { ?>
                        <option value="<?php echo $route['routeID']; ?>"><?php echo $route['source']; ?> - <?php echo $route['destination']; ?></option>
                    <?php } ?>
                </select>

                <input type="text" name="bus_name" placeholder="Bus Name" required>
                <input type="text" name="number_plate" placeholder="Bus Number Plate" required>
                <input type="text" name="capacity" placeholder="Bus Capacity" required>
                <input type="file" name="bus_image" accept="image/*" required>

                <button type="submit" name="add_bus">Add Bus</button>
            </form>
        </div>

        <!-- Existing Buses Table -->
        <h3>Existing Buses</h3>
        <table class="buses-table">
            <tr>
                <th>Bus ID</th>
                <th>Bus Name</th>
                <th>Route</th>
                <th>Number Plate</th>
                <th>Capacity</th>
                <th>Bus Image</th>
                <th>Action</th>
            </tr>
            <?php foreach ($buses as $bus) { ?>
                <tr>
                    <td><?php echo $bus['busID']; ?></td>
                    <td><?php echo $bus['bus_name']; ?></td>
                    <td>
                        <?php
                        $routeSql = "SELECT * FROM Route WHERE routeID = ?";
                        $stmt = $pdo->prepare($routeSql);
                        $stmt->execute([$bus['routeID']]);
                        $route = $stmt->fetch();
                        echo $route['source'] . " - " . $route['destination'];
                        ?>
                    </td>
                    <td><?php echo $bus['numberPlate']; ?></td>
                    <td><?php echo $bus['capacity']; ?></td>
                    <td><img src="../uploads/bus_images/<?php echo $bus['bus_image']; ?>" alt="Bus Image"></td>
                    <td><a href="delete_buses.php?busID=<?php echo $bus['busID']; ?>">Delete</a></td>
                </tr>
            <?php } ?>
        </table>
    </div>

</body>
</html>
