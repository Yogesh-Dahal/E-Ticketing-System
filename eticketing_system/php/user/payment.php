payments.php
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Database connection
include('../db_connect.php');

// Check if busID, seatNumbers, and totalFare are set
if (!isset($_GET['busID'], $_GET['seatNumbers'], $_GET['totalFare']) || empty($_GET['busID']) || empty($_GET['seatNumbers']) || empty($_GET['totalFare'])) {
    die("Invalid request! Missing parameters.");
}

$busID = $_GET['busID'];
$seatNumbers = explode(",", $_GET['seatNumbers']);
$totalFare = $_GET['totalFare'];

// Calculate 20% of the total fare
$initialPayment = $totalFare * 0.20;

// Fetch Bus Details
$sql = "SELECT bus_name, numberPlate, fare, source, destination,departure_time,departure_date FROM Bus 
        JOIN Route ON Bus.routeID = Route.routeID 
        WHERE busID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$busID]);
$bus = $stmt->fetch();

if (!$bus) {
    die("Bus not found!");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('https://www.shutterstock.com/image-photo/white-passenger-bus-side-view-600nw-2488920331.jpg');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            background-attachment: fixed;
            position: relative;
        }

        /* Adding a dark overlay to improve text visibility */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7); /* Dark overlay for better contrast */
            z-index: 0;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px 40px;
            text-align: center;
            width: 100%;
            max-width: 500px;
            color: white;
            position: relative;
            z-index: 1;
        }

        h2 {
            color: #ff7e5f;
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: bold;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
        }

        p {
            font-size: 18px;
            margin: 10px 0;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
        }

        .amount {
            font-size: 22px;
            font-weight: bold;
            color: #ffcc00;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
        }

        .button-container {
            margin-top: 30px;
        }

        button {
            background-color: #ff7e5f;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 50px;
            transition: 0.3s;
            width: 100%;
            font-weight: bold;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
        }

        button:hover {
            background-color: #feb47b;
        }

        .highlight {
            font-weight: bold;
            color: #ff7e5f;
        }

        .details {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: white;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
        }

        .details p {
            font-size: 16px;
            margin: 5px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Payment for <?php echo $bus['bus_name']; ?> (<?php echo $bus['numberPlate']; ?>)</h2>
    <div class="details">
        <p><strong>Route:</strong> <?php echo $bus['source']; ?> - <?php echo $bus['destination']; ?></p>
        <p><strong>Selected Seats:</strong> <?php echo implode(", ", $seatNumbers); ?></p>
        <p><strong>Total Fare:</strong> Rs<?php echo $totalFare; ?></p>
        <p class="amount"><strong>Amount to Pay (20%):</strong> Rs<?php echo number_format($initialPayment, 2); ?></p>
    </div>

    <form action="process_payment.php" method="POST">
        <input type="hidden" name="busID" value="<?php echo $busID; ?>">
        <input type="hidden" name="seatNumbers" value="<?php echo implode(",", $seatNumbers); ?>">
        <input type="hidden" name="totalFare" value="<?php echo $totalFare; ?>">
        <input type="hidden" name="initialPayment" value="<?php echo $initialPayment; ?>">
        <input type="hidden" name="departure_time" value="<?php echo isset($bus['departure_time']) ? $bus['departure_time'] : ''; ?>">
        <input type="hidden" name="departure_date" value="<?php echo isset($bus['departure_date']) ? $bus['departure_date'] : ''; ?>">  


        <div class="button-container">
            <button type="submit">Proceed to Payment</button>
        </div>
    </form>
</div>

</body>
</html>