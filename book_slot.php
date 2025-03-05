<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

require 'db_connection.php';

// Get the station ID from the query string
$station_id = $_GET['station_id'] ?? null;

if (!$station_id || !is_numeric($station_id)) {
    echo "<p style='color:red;'>Error: Station ID is missing or invalid. Please try again from the station selection page.</p>";
    echo '<a href="find_stations.php">Go back to Station Selection</a>';
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username, mobile FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Fetch station details
// Fetch station details with LEFT JOIN
$station_query = "
    SELECT s.station_name, s.price
    FROM charging_stations s
    LEFT JOIN station_slots ss ON s.id = ss.station_id
    WHERE s.id = $station_id";
$station_result = $conn->query($station_query);

if (!$station_result || $station_result->num_rows === 0) {
    echo "<p style='color:red;'>Error: Station not found or no available slots at the selected station.</p>";
    echo '<a href="find_stations.php">Go back to Station Selection</a>';
    exit();
}

$station = $station_result->fetch_assoc();



// Fetch vehicles for the logged-in user
$vehicle_query = "SELECT vehicle_id, vehicle_name, vehicle_number FROM vehicles WHERE user_id = $user_id";
$vehicles = $conn->query($vehicle_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $slot_type = $_POST['slot_type'] ?? null;
    $mobile_number = $_POST['mobile_number'] ?? null;
    $booking_datetime = $_POST['booking_datetime'] ?? null;
    $vehicle_id = $_POST['vehicle_id'] ?? null;
    $amount = $station['peak_price'];

    // Validate required fields
    if (!$slot_type || !$mobile_number || !$booking_datetime || !$vehicle_id) {
        echo "<p style='color:red;'>Error: All fields are required. Please fill out the form completely.</p>";
    } else {

        $price_query = "SELECT price FROM charging_stations WHERE id = $station_id";
        $price_result = $conn->query($price_query);

        if ($price_result && $price_result->num_rows > 0) {
            $price_row = $price_result->fetch_assoc();
            $amount = $price_row['price']; // Get the station's price
        
        // Insert booking into the database
        $booking_query = "INSERT INTO bookings (user_id, station_id, vehicle_id, slot_type, booking_datetime, amount, mobile_number) 
                          VALUES ('$user_id', '$station_id', '$vehicle_id', '$slot_type', '$booking_datetime', '$amount', '$mobile_number')";
        
        if ($conn->query($booking_query) === TRUE) {
            $booking_id = $conn->insert_id;
            header("Location: payment.php?booking_id=$booking_id");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Slot</title>
    <style>
        /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    color: #333;
    line-height: 1.6;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Header Styling */
.header {
    width: 100%;
    background-color: #35424a;
    color: #fff;
    padding: 10px 0;
    text-align: center;
    margin-bottom: 20px;
}

.header h1 {
    font-size: 24px;
}

.nav {
    list-style: none;
    display: flex;
    justify-content: center;
    margin-top: 10px;
}

.nav li {
    margin: 0 10px;
}

.nav li a {
    color: #fff;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 4px;
    transition: background 0.3s ease;
}

.nav li a:hover {
    background-color: #1abc9c;
}

/* Form Styling */
form {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
}

form h2 {
    margin-bottom: 20px;
    text-align: center;
    color: #35424a;
}

form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

form input,
form select,
form button {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

form input[readonly] {
    background-color: #f4f4f4;
    color: #777;
    cursor: not-allowed;
}

form button {
    background-color: #35424a;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background 0.3s ease;
}

form button:hover {
    background-color: #1abc9c;
}

/* Error/Message Styling */
p {
    font-size: 14px;
    color: red;
    text-align: center;
    margin-top: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    .header h1 {
        font-size: 20px;
    }

    .nav {
        flex-wrap: wrap;
    }

    .nav li {
        margin: 5px;
    }

    form {
        padding: 15px;
    }

    form button {
        font-size: 14px;
    }
}

</style>
</head>
<body>
<div class="header">
        <h1>User Dashboard</h1>
        <ul class="nav">
            <li><a href="user_dashboard.php">Dashboard</a></li>
            <li><a href="find_stations.php">Find Stations</a></li>
            <li><a href="user_location.php">Update Your Location</a></li>
            <li><a href="manage_vehicles.php">Manage Vehicles</a></li>
            <li><a href="manage_favourite.php">Favorite Stations</a></li>
            <li><a href="booking_history.php">Booking History</a></li>
            <li><a href="route_map.php">Route Map</a></li>
            <li><a href="feedback.php">Feedback</a></li>
            <li><a href="user_logout.php">Logout</a></li>
        </ul>
    </div>

<h2>Book Charging Slot</h2>

<form method="post" action="">
    <!-- Auto-filled User Details -->
    <label for="username">User Name:</label>
    <input type="text" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly><br>

    <label for="mobile_number">Mobile Number:</label>
    <input type="text" name="mobile_number" id="mobile_number" value="<?= htmlspecialchars($user['mobile']) ?>" required><br>

    <!-- Auto-filled Station Details -->
    <label for="station_name">Station Name:</label>
    <input type="text" id="station_name" value="<?= htmlspecialchars($station['station_name']) ?>" readonly><br>

    <label for="slot_type">Slot Type:</label>
    <select name="slot_type" id="slot_type" required>
        <option value="Fast">Fast</option>
        <option value="Slow">Slow</option>
    </select><br>

    <!-- Auto-filled Vehicle List -->
    <label for="vehicle_id">Select Vehicle:</label>
    <select name="vehicle_id" id="vehicle_id" required>
        <?php if ($vehicles->num_rows > 0): ?>
            <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                <option value="<?= $vehicle['vehicle_id'] ?>"><?= htmlspecialchars($vehicle['vehicle_name'] . ' (' . $vehicle['vehicle_number'] . ')') ?></option>
            <?php endwhile; ?>
        <?php else: ?>
            <option value="">No vehicles found</option>
        <?php endif; ?>
    </select><br>

    <label for="booking_datetime">Booking Date & Time:</label>
    <input type="datetime-local" name="booking_datetime" id="booking_datetime" required><br>

    <label for="amount">Amount:</label>
    <input type="text" id="amount" value="₹<?= htmlspecialchars($station['price']) ?>" readonly><br>

    
    <input type="hidden" name="action" value="book_slot">
    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
    <button type="submit">Book Slot</button>


</form>

</body>
</html>