<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
include('../db_connect.php');

// Get busID from URL
$busID = $_GET['busID'] ?? null;

if (!$busID) {
    echo "Bus ID is required.";
    exit();
}

// Handling driver addition
if (isset($_POST['add_driver'])) {
    $driverName = $_POST['driver_name'];
    $driverLicenseNumber = $_POST['license_number'];
    $driverLicenseImage = $_FILES['license_image']['name'];

    // Upload license image
    $targetDir = "../../uploads/driver_images/";
    $targetFile = $targetDir . basename($driverLicenseImage);
    
    if (move_uploaded_file($_FILES['license_image']['tmp_name'], $targetFile)) {
        $sql = "INSERT INTO Driver (busID, driver_name, license_number, license_image) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$busID, $driverName, $driverLicenseNumber, $driverLicenseImage]);

        echo "Driver added successfully!";
    } else {
        echo "Sorry, there was an error uploading the license image.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Driver</title>
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

    <!-- Add Driver Form -->
    <div class="container">
        <h2>Add Driver for Bus ID: <?php echo $busID; ?></h2>

        <!-- Driver Addition Form -->
        <div class="form-container">
            <form action="add_driver.php?busID=<?php echo $busID; ?>" method="POST" enctype="multipart/form-data">
                <input type="text" name="driver_name" placeholder="Driver Name" required>
                <input type="text" name="license_number" placeholder="Driver License Number" required>
                <input type="file" name="license_image" accept="image/*" required>
                <button type="submit" name="add_driver">Add Driver</button>
            </form>
        </div>
    </div>

</body>
</html>
