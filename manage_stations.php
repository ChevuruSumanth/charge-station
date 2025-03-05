<?php
// Include database connection
session_start();

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}
include "db_connection.php";

// Handle Add, Update, Delete requests
if (isset($_POST['action'])) {
    $station_name = isset($_POST['station_name']) ? $_POST['station_name'] : '';
    $location_city = isset($_POST['location_city']) ? $_POST['location_city'] : '';
    $location_state = isset($_POST['location_state']) ? $_POST['location_state'] : '';
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : '';
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : '';
    $station_type = isset($_POST['station_type']) ? $_POST['station_type'] : '';
    $slots_available = isset($_POST['slots_available']) ? $_POST['slots_available'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $is_enabled = isset($_POST['is_enabled']) ? 1 : 0;
    $mobile_number = isset($_POST['mobile_number']) ? $_POST['mobile_number'] : '';
    $price = isset($_POST['price']) ? $_POST['price'] : ''; // Corrected field name

    if ($_POST['action'] == 'add') {
        $stmt = $conn->prepare("INSERT INTO charging_stations 
            (station_name, location_city, location_state, latitude, longitude, mobile_number, station_type, slots_available, price, status, is_enabled) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssidsis",
            $station_name,
            $location_city,
            $location_state,
            $latitude,
            $longitude,
            $mobile_number,
            $station_type,
            $slots_available,
            $price,
            $status,
            $is_enabled
        );
        if ($stmt->execute()) {
            echo "Station added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($_POST['action'] == 'update') {
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $stmt = $conn->prepare("UPDATE charging_stations 
            SET station_name = ?, location_city = ?, location_state = ?, latitude = ?, longitude = ?, mobile_number = ?, station_type = ?, slots_available = ?, price = ?, status = ?, is_enabled = ? 
            WHERE id = ?");
        $stmt->bind_param(
            "ssssssidsisi",
            $station_name,
            $location_city,
            $location_state,
            $latitude,
            $longitude,
            $mobile_number,
            $station_type,
            $slots_available,
            $price,
            $status,
            $is_enabled,
            $id
        );
        if ($stmt->execute()) {
            echo "Station updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($_POST['action'] == 'delete') {
        $id = isset($_POST['id']) ? $_POST['id'] : 0;

        // Check for dependent records
        $check = $conn->prepare("SELECT * FROM station_slots WHERE station_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "Cannot delete station as it has dependent slots.";
            $check->close();
            exit();
        }
        $check->close();

        $stmt = $conn->prepare("DELETE FROM charging_stations WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "Station deleted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}


// Fetch station data for display
$result = $conn->query("SELECT * FROM charging_stations");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Charging Stations</title>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCYXUqyHY-JKd-TvAWqY30rzyk9e4ubcjE"></script>

    <style>
/* General styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: #333;
}

h2, h3 {
    text-align: center;
    color: #4CAF50;
}

/* Navbar styles */
.navbar {
    background-color: #333;
    overflow: hidden;
    padding: 10px 0;
}

.navbar a {
    float: left;
    display: block;
    color: #f2f2f2;
    text-align: center;
    padding: 14px 20px;
    text-decoration: none;
}

.navbar a:hover {
    background-color: #ddd;
    color: black;
}

.navbar a.logout {
    float: right;
    background-color: #f44336;
    color: white;
}

/* Form container styles */
form {
    max-width: 500px;
    margin: 30px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Label and input styles */
label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
}

input[type="text"],
input[type="number"],
select {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

input[type="checkbox"] {
    margin-left: 10px;
}

button[type="submit"] {
    width: 100%;
    background-color: #4CAF50;
    color: white;
    padding: 12px;
    margin-top: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

/* Table styles */
table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
}

table, th, td {
    border: 1px solid #ddd;
    padding: 8px;
}

th {
    background-color: #4CAF50;
    color: white;
    text-align: left;
}

td {
    text-align: center;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #ddd;
}

button[type="submit"][name="action"][value="delete"] {
    background-color: #f44336;
    color: white;
}

button[type="submit"][name="action"][value="delete"]:hover {
    background-color: #e53935;
}

/* Google map container */
#map {
    height: 400px;
    width: 100%;
    margin-bottom: 20px;
}

/* Responsive layout */
@media (max-width: 600px) {
    .navbar a {
        float: none;
        display: block;
        text-align: left;
    }

    form {
        width: 95%;
        margin: 20px auto;
    }

    table {
        width: 100%;
    }
}

    </style>

</head>
<body>
<div class="navbar">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_stations.php">Manage Stations</a>
    <a href="manage_slots.php">Manage Slots</a>
    <a href="view_bookings.php">View Bookings</a>
    <a href="view_payments.php">View payments</a>
    <a href="view_feedback.php">View Feedback</a>
    <a href="reports_analytics.php">Station Reports</a>
    <a href="station_health.php">Station Health</a>
    <a href="cancelled_booking.php">Cancelled bookings</a>
    <a href="admin_logout.php" class="logout">Logout</a>
</div>
    <h2>Manage Charging Stations</h2>

    <!-- Add/Update Form -->
    <form method="POST">
    <input type="hidden" name="id" id="station_id">
    <label>Station Name: <input type="text" name="station_name" required></label><br>
    <label>Location (City): <input type="text" name="location_city" required></label><br>
    <label>Location (State): <input type="text" name="location_state" required></label><br>
    <label>Mobile Number: <input type="text" name="mobile_number" required></label><br>
    <label>Latitude: <input type="text" name="latitude" id="latitude" required></label><br>
    <label>Longitude: <input type="text" name="longitude" id="longitude" required></label><br>
    <div id="map"></div><br>
    <label>Station Type: 
        <select name="station_type" required>
            <option value="Fast">Fast</option>
            <option value="Slow">Slow</option>
        </select>
    </label><br>
    <label>Slots Available: <input type="number" name="slots_available" required></label><br>
    <label>Price: <input type="number" name="price" required></label><br>
    <label>Status: 
        <select name="status" required>
            <option value="Available">Available</option>
            <option value="Under Maintenance">Under Maintenance</option>
            <option value="Occupied">Occupied</option>
        </select>
    </label><br>
    <label>Enable Station: <input type="checkbox" name="is_enabled"></label><br>
    <button type="submit" name="action" value="add">Add Station</button>
    <button type="submit" name="action" value="update">Update Station</button>
</form>

    <!-- Display Stations -->
    <h3>Existing Charging Stations</h3>
    <table border="1">
<tr>
    <th>ID</th>
    <th>Station Name</th>
    <th>Location</th>
    <th>Coordinates</th>
    <th>Mobile Number</th>
    <th>Type</th>
    <th>Slots</th>
    <th>Price</th>
    <th>Status</th>
    <th>Enabled</th>
    <th>Actions</th>
</tr>
<?php while ($row = $result->fetch_assoc()) { ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['station_name']; ?></td>
    <td><?php echo $row['location_city'] . ', ' . $row['location_state']; ?></td>
    <td><?php echo $row['latitude'] . ', ' . $row['longitude']; ?></td>
    <td><?php echo $row['mobile_number']; ?></td>
    <td><?php echo $row['station_type']; ?></td>
    <td><?php echo $row['slots_available']; ?></td>
    <td><?php echo $row['price']; ?></td>
    <td><?php echo $row['status']; ?></td>
    <td><?php echo $row['is_enabled'] ? 'Yes' : 'No'; ?></td>
    <td>
        <form method="post">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <button type="submit" name="action" value="delete">Delete</button>
        </form>
    </td>
</tr>
<?php } ?>
</table>


    <!-- JS for Google Maps -->
    <script>
        let map, marker;

        function initMap() {
            // Create the map
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 14.4426, lng: 79.9865 }, // Default center (San Francisco)
                zoom: 12,
            });

            // Create a draggable marker
            marker = new google.maps.Marker({
                position: { lat: 37.7749, lng: -122.4194 }, // Default marker position
                map: map,
                draggable: true
            });

            // Update the latitude and longitude fields when the marker is dragged
            google.maps.event.addListener(marker, 'dragend', function () {
                let latLng = marker.getPosition();
                document.getElementById('latitude').value = latLng.lat();
                document.getElementById('longitude').value = latLng.lng();
            });

            // Update marker and fields when the map is clicked
            google.maps.event.addListener(map, 'click', function (event) {
                let clickedLocation = event.latLng;

                // Move marker to the clicked location
                marker.setPosition(clickedLocation);

                // Update the latitude and longitude fields
                document.getElementById('latitude').value = clickedLocation.lat();
                document.getElementById('longitude').value = clickedLocation.lng();
            });
        }

        google.maps.event.addDomListener(window, 'load', initMap);
    </script>

</body>
</html>
