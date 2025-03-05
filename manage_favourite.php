<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch available stations (not already in favorites)
$available_stations_query = "
    SELECT s.id AS station_id, s.station_name, CONCAT(s.location_city, ', ', s.location_state) AS location
    FROM charging_stations s
    LEFT JOIN favorite_stations fs ON s.id = fs.station_id AND fs.user_id = $user_id
    WHERE fs.station_id IS NULL";
$available_stations_result = $conn->query($available_stations_query);

// Handle adding stations to favorites
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['station_ids'])) {
    $station_ids = $_POST['station_ids']; // Array of selected stations

    foreach ($station_ids as $station_id) {
        $station_id = (int) $station_id; // Sanitize input

        // Insert into favorites if not already present
        $insert_query = "INSERT INTO favorite_stations (user_id, station_id) VALUES ($user_id, $station_id)";
        $conn->query($insert_query);
    }

    // Redirect to dashboard after successful addition
    header('Location: user_dashboard.php?success=added');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Favorite Stations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            text-align: center;
        }
        h2 {
            margin-top: 20px;
            color: #333;
        }
        table {
            width: 70%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #35424a;
            color: white;
        }
        button {
            background: #35424a;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #2a3e33;
        }
        /* Header Section */
.header {
    background: #35424a;
    color: #ffffff;
    padding: 10px 0;
    text-align: center;
}

.nav {
    list-style: none;
    display: flex;
    justify-content: center;
    margin: 10px 0;
    padding: 0;
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
<h2>Select Stations to Add to Favorites</h2>

<?php if ($available_stations_result->num_rows > 0): ?>
    <form method="post" action="">
        <table>
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Station Name</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($station = $available_stations_result->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="station_ids[]" value="<?= $station['station_id'] ?>"></td>
                        <td><?= $station['station_name'] ?></td>
                        <td><?= $station['location'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <button type="submit">Add to Favorites</button>
    </form>
<?php else: ?>
    <p>No available stations to add to favorites.</p>
<?php endif; ?>

</body>
</html>
