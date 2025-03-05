<?php
// Start the session and check for login
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

require 'db_connection.php';

// Retrieve filters from the request
$latitude = $_GET['latitude'] ?? null;
$longitude = $_GET['longitude'] ?? null;
$city = $_GET['city'] ?? null;
$km_range = $_GET['km_range'] ?? 5; // Default range is 50 KM
$charging_type = $_GET['charging_type'] ?? null;

// Calculate distance function
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // Earth's radius in km
    $lat_diff = deg2rad($lat2 - $lat1);
    $lon_diff = deg2rad($lon2 - $lon1);
    $a = sin($lat_diff / 2) * sin($lat_diff / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($lon_diff / 2) * sin($lon_diff / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c; // Distance in km
}

// Base query
$sql = "SELECT charging_stations.*, 
        (SELECT status 
         FROM station_slots 
         WHERE station_slots.station_id = charging_stations.id 
         ORDER BY status ASC 
         LIMIT 1) AS slot_status,
        charging_stations.price AS peak_price,
        charging_stations.mobile_number
        FROM charging_stations 
        WHERE 1=1";


// Add city filter
if (!empty($city)) {
    $city = $conn->real_escape_string(trim($city));
    $sql .= " AND location_city LIKE '%$city%'";
}

// Add charging type filter
if (!empty($charging_type)) {
    $charging_type = $conn->real_escape_string($charging_type);
    $sql .= " AND station_type = '$charging_type'";
}

$result = $conn->query($sql);
$filtered_stations = [];

// Filter stations by distance if latitude and longitude are provided
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($latitude) && !empty($longitude)) {
            $distance = calculateDistance($latitude, $longitude, $row['latitude'], $row['longitude']);
            if ($distance <= $km_range) {
                $row['distance'] = round($distance, 2);
                $filtered_stations[] = $row;
            }
        } else {
            $filtered_stations[] = $row; // Add without distance filter
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Charging Stations</title>
    <style>
        /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f4f4;
    color: #333;
    line-height: 1.6;
}

/* Header Section */
.header {
    background: #35424a;
    color: #ffffff;
    padding: 10px 0;
    text-align: center;
}

.header h1 {
    margin: 0;
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

.nav a {
    text-decoration: none;
    color: #ffffff;
    font-weight: bold;
    transition: color 0.3s ease;
}

.nav a:hover {
    color: #a8d5ba;
}

/* Main Content */
.container {
    width: 80%;
    margin: 20px auto;
}

/* Form */
form {
    background: #ffffff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

form label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}

form input[type="text"],
form input[type="number"],
form select {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    transition: border-color 0.3s ease;
}

form input[type="text"]:focus,
form input[type="number"]:focus,
form select:focus {
    border-color: #35424a;
}

form button {
    background: #35424a;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

form button:hover {
    background: #2a3e33;
}

/* Table Section */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: #fff;
}

table th,
table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

table th {
    background: #35424a;
    color: #ffffff;
}

table tr:nth-child(even) {
    background: #f2f2f2;
}

table tr:hover {
    background: #e2f7e0;
}

/* Action Links */
a {
    text-decoration: none;
    color: #35424a;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header h1 {
        font-size: 20px;
    }

    .nav {
        flex-direction: column;
        align-items: center;
    }

    form {
        padding: 15px;
    }

    table td,
    table th {
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
<h2>Find Charging Stations</h2>


<form method="get" action="">
    <label for="city">City:</label>
    <input type="text" name="city" id="city" value="<?= $city ?>"><br>

    <label for="km_range">Range (in KM):</label>
    <input type="number" name="km_range" id="km_range" value="<?= $km_range ?>"><br>

    <label for="charging_type">Charging Type:</label>
    <select name="charging_type" id="charging_type">
        <option value="">Any</option>
        <option value="Fast" <?= $charging_type == 'Fast' ? 'selected' : '' ?>>Fast</option>
        <option value="Slow" <?= $charging_type == 'Slow' ? 'selected' : '' ?>>Slow</option>
    </select><br>

    <!-- Auto-filled values for current latitude and longitude -->
    <input type="hidden" name="latitude" id="latitude" value="<?= $latitude ?>">
    <input type="hidden" name="longitude" id="longitude" value="<?= $longitude ?>">

    <button type="submit">Search</button>
</form>

<h3>Available Stations</h3>
<table border="1">
    <tr>
        <th>Station Name</th>
        <th>City</th>
        <th>Distance (KM)</th>
        <th>Charging Type</th>
        <th>Mobile Number</th>
        <th>Price per Slot (Peak Price)</th>
        <th>Slot Availability</th>
        <th>Action</th>
    </tr>
    <?php if (!empty($filtered_stations)): ?>
        <?php foreach ($filtered_stations as $station): ?>
            <tr>
                <td><?= htmlspecialchars($station['station_name']) ?></td>
                <td><?= htmlspecialchars($station['location_city']) ?></td>
                <td><?= isset($station['distance']) ? $station['distance'] . ' KM' : 'N/A' ?></td>
                <td><?= htmlspecialchars($station['station_type']) ?></td>
                <td><?= htmlspecialchars($station['mobile_number']) ?></td>
                <td><?= htmlspecialchars($station['peak_price']) ?></td>
                <td><?= $station['slot_status'] == 'Available' ? 'Available' : 'Available' ?></td>
                <td>
                    <a href="book_slot.php?station_id=<?= $station['id'] ?>">Book Slot</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="8">No stations found</td></tr>
    <?php endif; ?>
</table>


<script>
// Assuming you have some JavaScript that auto-fills the latitude and longitude from the browser
navigator.geolocation.getCurrentPosition(function(position) {
    document.getElementById('latitude').value = position.coords.latitude;
    document.getElementById('longitude').value = position.coords.longitude;
});
</script>

</body>
</html>
