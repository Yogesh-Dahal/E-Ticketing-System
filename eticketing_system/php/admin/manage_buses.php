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
    $departureDate = $_POST['departure_date'];
    $departureTime = $_POST['departure_time'];
    $fare = $_POST['fare'];
    $busImage = $_FILES['bus_image']['name'];

    // Validate bus number plate (max 4 characters, letters, numbers, and hyphen allowed)
    if (!preg_match('/^[A-Za-z0-9-]{1,4}$/', $busNumberPlate)) {
        echo "<script>alert('Bus number plate must be a maximum of 4 characters (letters, numbers, or hyphen).'); window.history.back();</script>";
        exit();
    }

    // Upload bus image
    $targetDir = "../../uploads/bus_images/";
    $targetFile = $targetDir . basename($busImage);

    if (move_uploaded_file($_FILES['bus_image']['tmp_name'], $targetFile)) {
        $sql = "INSERT INTO Bus (routeID, bus_name, numberPlate, capacity, departure_date, departure_time, fare, bus_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$routeID, $busName, $busNumberPlate, $busCapacity, $departureDate, $departureTime, $fare, $busImage]);

        // Get the busID of the newly inserted bus
        $busID = $pdo->lastInsertId();

        // Redirect to the driver addition page with busID as a parameter
        header("Location: add_driver.php?busID=$busID");
        exit();
    } else {
        echo "Sorry, there was an error uploading the image.";
    }
}

// Handling bus deletion
if (isset($_GET['delete_bus'])) {
    $busID = $_GET['delete_bus'];

    // First, delete the image file if it exists
    $sql = "SELECT bus_image FROM Bus WHERE busID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$busID]);
    $bus = $stmt->fetch();
    $busImagePath = "../../uploads/bus_images/" . $bus['bus_image'];
    if (file_exists($busImagePath)) {
        unlink($busImagePath);  // Delete the image file
    }

    // Delete the bus record from the database
    $sql = "DELETE FROM Bus WHERE busID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$busID]);

    // Redirect back to the manage buses page
    header("Location: manage_buses.php");
    exit();
}

// Fetching all buses
$sql = "SELECT busID, bus_name, routeID, numberPlate, capacity, departure_date, departure_time, fare, bus_image FROM Bus";
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

    <script>
        function validateBusForm() {
            let numberPlate = document.getElementById("number_plate").value;
            let numberPattern = /^[A-Za-z0-9-]{1,4}$/;  // Allow up to 4 characters (letters, numbers, and hyphen)

            // Bus number plate validation
            if (!numberPattern.test(numberPlate)) {
                alert("Bus number plate must be a maximum of 4 characters (letters, numbers, or hyphen).");
                return false; // Prevent form submission
            }

            // Date validation (date should not be in the past, should be from tomorrow)
            let departureDate = document.querySelector('input[name="departure_date"]').value;
            let today = new Date();
            let tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1); // Set to tomorrow's date

            // Format tomorrow date to match the input date format (yyyy-mm-dd)
            let tomorrowDateString = tomorrow.toISOString().split('T')[0];

            if (departureDate < tomorrowDateString) {
                alert("The departure date must be tomorrow or a future date.");
                return false; // Prevent form submission
            }

            return true;
        }
    </script>
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
            <form action="manage_buses.php" method="POST" enctype="multipart/form-data" onsubmit="return validateBusForm()">
                <select name="routeID" required>
                    <option value="">Select Route</option>
                    <?php foreach ($routes as $route) { ?>
                        <option value="<?php echo $route['routeID']; ?>"><?php echo $route['source']; ?> - <?php echo $route['destination']; ?></option>
                    <?php } ?>
                </select>

                <input type="text" name="bus_name" placeholder="Bus Name" required>
                <input type="text" id="number_plate" name="number_plate" placeholder="Bus Number Plate" required>
                <input type="number" name="capacity" placeholder="Bus Capacity" required>
                <input type="date" name="departure_date" required>
                <input type="time" name="departure_time" required>
                <input type="number" name="fare" placeholder="Fare Amount" step="0.01" required>
                <input type="file" name="bus_image" accept="image/*" required>

                <button type="submit" name="add_bus">Add Bus</button>
            </form>
        </div>

        <!-- Display Existing Buses -->
        <h3>Existing Buses</h3>
        <table class="buses-table">
            <thead>
                <tr>
                    <th>Bus Name</th>
                    <th>Route</th>
                    <th>Number Plate</th>
                    <th>Capacity</th>
                    <th>Fare</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($buses as $bus) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($bus['bus_name']); ?></td>
                        <td>
                            <?php
                                $routeID = $bus['routeID'];
                                $routeQuery = "SELECT source, destination FROM Route WHERE routeID = ?";
                                $routeStmt = $pdo->prepare($routeQuery);
                                $routeStmt->execute([$routeID]);
                                $route = $routeStmt->fetch();
                                echo htmlspecialchars($route['source']) . " - " . htmlspecialchars($route['destination']);
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($bus['numberPlate']); ?></td>
                        <td><?php echo htmlspecialchars($bus['capacity']); ?></td>
                        <td><?php echo htmlspecialchars($bus['fare']); ?></td>
                        <td><img src="../../uploads/bus_images/<?php echo htmlspecialchars($bus['bus_image']); ?>" alt="Bus Image"></td>
                        <td>
                            <!-- Delete button -->
                            <a href="manage_buses.php?delete_bus=<?php echo $bus['busID']; ?>" onclick="return confirm('Are you sure you want to delete this bus?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>

</body>
</html>
