<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include('../db_connect.php');

if (!isset($_GET['busID']) || empty($_GET['busID'])) {
    die("Invalid request! Bus ID is missing.");
}
$busID = $_GET['busID'];

// Fetch bus details
$sql = "SELECT Bus.busID, Bus.bus_name, Bus.numberPlate, Bus.capacity, Route.source, Route.destination 
        FROM Bus 
        JOIN Route ON Bus.routeID = Route.routeID 
        WHERE Bus.busID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$busID]);
$bus = $stmt->fetch();

if (!$bus) {
    die("Bus not found!");
}

// Fetch booked seats
$bookedSeatsQuery = "SELECT seat_number, Users.name, Users.email 
                     FROM Bookings 
                     JOIN Users ON Bookings.userID = Users.id
                     WHERE Bookings.busID = ?";
$bookedSeatsStmt = $pdo->prepare($bookedSeatsQuery);
$bookedSeatsStmt->execute([$busID]);
$bookedSeats = $bookedSeatsStmt->fetchAll(PDO::FETCH_ASSOC);

$bookedSeatsArray = [];
foreach ($bookedSeats as $seat) {
    $bookedSeatsArray[$seat['seat_number']] = $seat;
}

$totalSeats = $bus['capacity'];
$rows = floor(($totalSeats - 5) / 4);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Seats</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        h2 {
            margin-bottom: 10px;
        }
        .bus-layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        .seat-row {
            display: flex;
            gap: 15px;
        }
        .driver {
            background: red;
            color: white;
            padding: 10px;
            font-weight: bold;
            border-radius: 5px;
        }
        .seat {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid black;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        .available {
            background: #4CAF50;
            color: white;
        }
        .booked {
            background: red;
            color: white;
        }
        .booked:hover {
            background: darkred;
        }
        .aisle {
            visibility: hidden;
            width: 50px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Bus Seat Layout - Admin View</h2>
    <p>Bus: <?php echo $bus['bus_name']; ?> (<?php echo $bus['numberPlate']; ?>)</p>
    <p>Route: <?php echo $bus['source']; ?> - <?php echo $bus['destination']; ?></p>

    <div class="bus-layout">
        <div class="driver">Driver</div>

        <?php
        $seatNumber = 1;
        for ($i = 0; $i < $rows; $i++) {
            echo "<div class='seat-row'>";
            for ($j = 0; $j < 2; $j++) {
                $isBooked = isset($bookedSeatsArray[$seatNumber]);
                $seatClass = $isBooked ? 'booked' : 'available';
                echo "<div class='seat $seatClass' data-seat='$seatNumber' ";
                if ($isBooked) {
                    echo "data-user='" . htmlspecialchars(json_encode($bookedSeatsArray[$seatNumber])) . "'"; 
                }
                echo ">$seatNumber</div>";
                $seatNumber++;
            }
            echo "<div class='aisle'></div>";
            for ($j = 0; $j < 2; $j++) {
                $isBooked = isset($bookedSeatsArray[$seatNumber]);
                $seatClass = $isBooked ? 'booked' : 'available';
                echo "<div class='seat $seatClass' data-seat='$seatNumber' ";
                if ($isBooked) {
                    echo "data-user='" . htmlspecialchars(json_encode($bookedSeatsArray[$seatNumber])) . "'"; 
                }
                echo ">$seatNumber</div>";
                $seatNumber++;
            }
            echo "</div>";
        }

        echo "<div class='seat-row'>";
        for ($i = 0; $i < 5; $i++) {
            $isBooked = isset($bookedSeatsArray[$seatNumber]);
            $seatClass = $isBooked ? 'booked' : 'available';
            echo "<div class='seat $seatClass' data-seat='$seatNumber' ";
            if ($isBooked) {
                echo "data-user='" . htmlspecialchars(json_encode($bookedSeatsArray[$seatNumber])) . "'"; 
            }
            echo ">$seatNumber</div>";
            $seatNumber++;
        }
        echo "</div>";
        ?>
    </div>
</div>

<script>
    document.querySelectorAll('.booked').forEach(seat => {
        seat.addEventListener('click', function () {
            let userData = JSON.parse(this.dataset.user);
            alert(`Seat: ${this.dataset.seat}\nName: ${userData.name}\nEmail: ${userData.email}`);
        });
    });
</script>

</body>
</html>
