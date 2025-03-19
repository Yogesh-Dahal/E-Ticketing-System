<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Database connection
include('../db_connect.php');

if (!isset($_GET['busID']) || empty($_GET['busID'])) {
    die("Invalid request! Bus ID is missing.");
}
$busID = $_GET['busID'];

// Fetch Bus Details
$sql = "SELECT Bus.busID, Bus.bus_name, Bus.numberPlate, Bus.capacity, Route.source, Route.destination, Bus.fare 
        FROM Bus 
        JOIN Route ON Bus.routeID = Route.routeID 
        WHERE Bus.busID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$busID]);
$bus = $stmt->fetch();

if (!$bus) {
    die("Bus not found!");
}

// Fetch Booked Seats
$bookedSeatsQuery = "SELECT seat_number FROM Bookings WHERE busID = ?";
$bookedSeatsStmt = $pdo->prepare($bookedSeatsQuery);
$bookedSeatsStmt->execute([$busID]);
$bookedSeats = $bookedSeatsStmt->fetchAll(PDO::FETCH_COLUMN);
$bookedSeatsArray = array_flip($bookedSeats);

// Calculate seat distribution
$totalSeats = $bus['capacity'];
$rows = floor(($totalSeats - 5) / 4); // Rows with 2x2 seats
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket</title>
    <link rel="stylesheet" href="styles.css">
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
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
        }
        .available { background: #4CAF50; color: white; }
        .booked { background: red; color: white; pointer-events: none; }
        .selected { background: blue; color: white; }
        .button-container { margin-top: 20px; }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h2>Book a Seat for <?php echo $bus['bus_name']; ?> (<?php echo $bus['numberPlate']; ?>)</h2>
    <p>Route: <?php echo $bus['source']; ?> - <?php echo $bus['destination']; ?></p>
    <div class="bus-layout">
        <div class="driver">Driver</div>
        <?php
        $seatNumber = 1;
        for ($i = 0; $i < $rows; $i++) {
            echo "<div class='seat-row'>";
            for ($j = 0; $j < 2; $j++) {
                $isBooked = isset($bookedSeatsArray[$seatNumber]);
                echo "<div class='seat " . ($isBooked ? 'booked' : 'available') . "' data-seat='$seatNumber'>$seatNumber</div>";
                $seatNumber++;
            }
            echo "<div style='width: 30px;'></div>";
            for ($j = 0; $j < 2; $j++) {
                $isBooked = isset($bookedSeatsArray[$seatNumber]);
                echo "<div class='seat " . ($isBooked ? 'booked' : 'available') . "' data-seat='$seatNumber'>$seatNumber</div>";
                $seatNumber++;
            }
            echo "</div>";
        }
        echo "<div class='seat-row'>";
        for ($i = 0; $i < 5; $i++) {
            $isBooked = isset($bookedSeatsArray[$seatNumber]);
            echo "<div class='seat " . ($isBooked ? 'booked' : 'available') . "' data-seat='$seatNumber'>$seatNumber</div>";
            $seatNumber++;
        }
        echo "</div>";
        ?>
    </div>
    <div class="button-container">
        <button onclick="submitBooking()">Book Selected Seats</button>
    </div>
</div>
<script>
let selectedSeats = [];

document.querySelectorAll('.seat.available').forEach(seat => {
    seat.addEventListener('click', function () {
        let seatNum = this.dataset.seat;
        if (selectedSeats.includes(seatNum)) {
            selectedSeats = selectedSeats.filter(s => s !== seatNum);
            this.classList.remove('selected');
        } else {
            selectedSeats.push(seatNum);
            this.classList.add('selected');
        }
    });
});

function submitBooking() {
    if (selectedSeats.length === 0) {
        alert("Please select at least one seat.");
        return;
    }

    fetch('check_seat_availability.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            busID: "<?php echo $busID; ?>",
            selectedSeats: selectedSeats
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.bookedSeats.length > 0) {
            alert("Some seats are already booked: " + data.bookedSeats.join(", "));
            data.bookedSeats.forEach(seat => {
                document.querySelector(`.seat[data-seat='${seat}']`).classList.add('booked');
                document.querySelector(`.seat[data-seat='${seat}']`).classList.remove('selected');
            });
        } else {
            const totalFare = selectedSeats.length * <?php echo $bus['fare']; ?>;
            window.location.href = `payment.php?busID=<?php echo $busID; ?>&seatNumbers=${selectedSeats.join(",")}&totalFare=${totalFare}`;
        }
    });
}
</script>
</body>
</html>
