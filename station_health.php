<?php
session_start();

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connection.php';

// Handle Add/Update/Delete Station Health Monitoring
if (isset($_POST['action']) && !empty($_POST['action'])) {
    if (isset($_POST['station_id'], $_POST['current_status'], $_POST['health_status'], $_POST['alert_type'])) {
        $station_id = $_POST['station_id'];
        $current_status = $_POST['current_status'];
        $health_status = $_POST['health_status'];
        $alert_type = $_POST['alert_type'];
        $alert_message = $_POST['alert_message'] ?? ''; // Optional field

        if ($_POST['action'] == 'add') {
            $sql = "INSERT INTO station_health (station_id, current_status, health_status, alert_type, alert_message) 
                    VALUES ('$station_id', '$current_status', '$health_status', '$alert_type', '$alert_message')";

            $update_station_status_sql = "UPDATE charging_stations SET status = '$current_status' WHERE id = '$station_id'";
        } elseif ($_POST['action'] == 'update') {
            if (isset($_POST['health_id'])) {
                $health_id = $_POST['health_id'];
                $sql = "UPDATE station_health SET current_status='$current_status', health_status='$health_status', alert_type='$alert_type', alert_message='$alert_message' WHERE health_id='$health_id'";

                $update_station_status_sql = "UPDATE charging_stations SET status = '$current_status' WHERE id = '$station_id'";
            }
        } elseif ($_POST['action'] == 'delete') {
            if (isset($_POST['health_id'])) {
                $health_id = $_POST['health_id'];
                $sql = "DELETE FROM station_health WHERE health_id='$health_id'";

                // Optional: Reset the status in charging_stations if a station's health is deleted
                $update_station_status_sql = "UPDATE charging_stations SET status = 'Unknown' WHERE id = '$station_id'";
            }
        }

        if (isset($sql) && $conn->query($sql) === TRUE) {
            if (isset($update_station_status_sql)) {
                $conn->query($update_station_status_sql);
            }
            echo "Action performed successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: Missing required fields.";
    }
}
// Fetch stations for dropdown
$stations = $conn->query("SELECT * FROM charging_stations WHERE is_enabled = 1");

// Fetch station health data for display
$station_health_data = $conn->query("SELECT * FROM station_health");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Station Health Monitoring</title>
    <style>/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    color: #333;
    margin: 0;
    padding: 0;
}

h2, h3 {
    text-align: center;
    color: #4CAF50;
    margin-top: 20px;
}

h3 {
    margin-bottom: 10px;
}

/* Navbar Styles */
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

/* Form Styles */
form {
    margin: 20px auto;
    text-align: center;
    padding: 15px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    max-width: 600px;
}

form label {
    margin-right: 10px;
    font-weight: bold;
    display: block;
    margin-bottom: 10px;
}

form input[type="text"], form select {
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
    max-width: 400px;
}

form button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
}

form button:hover {
    background-color: #45a049;
}

/* Table Styles */
table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 12px;
    text-align: center;
}

th {
    background-color: #4CAF50;
    color: white;
}

td {
    text-align: left;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #ddd;
}

button[type="submit"] {
    padding: 8px 16px;
    background-color: #f44336;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #e53935;
}

/* Responsive Design */
@media (max-width: 600px) {
    .navbar a {
        float: none;
        display: block;
        text-align: left;
    }

    form {
        max-width: 100%;
        padding: 10px;
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
<h2>Station Health Monitoring</h2>

<!-- Add/Update Station Health Monitoring Form -->
<form method="post">
    <input type="hidden" name="health_id" id="health_id">

    <!-- Select Station -->
    <label>Station:
        <select name="station_id" required>
            <option value="">Select Station</option>
            <?php while ($station = $stations->fetch_assoc()) { ?>
            <option value="<?php echo $station['id']; ?>"><?php echo $station['station_name']; ?></option>
            <?php } ?>
        </select>
    </label><br>

    <label>Current Status:
        <select name="current_status" required>
            <option value="Available">Available</option>
            <option value="Occupied">Occupied</option>
            <option value="Maintenance">Maintenance</option>
        </select>
    </label><br>

    <label>Health Status: <input type="text" name="health_status" placeholder="Describe health status/issues" required></label><br>

    <label>Alert Type:
        <select name="alert_type" required>
            <option value="None">None</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Breakdown">Breakdown</option>
        </select>
    </label><br>

    <label>Alert Message: <input type="text" name="alert_message" placeholder="Describe alert message (if any)"></label><br>

    <button type="submit" name="action" value="add">Add Station Health</button>
    
</form>

<!-- Display Station Health Data -->
<h3>Existing Station Health Data</h3>
<table border="1">
    <tr>
        <th>Health ID</th>
        <th>Station Name</th>
        <th>Current Status</th>
        <th>Health Status</th>
        <th>Alert Type</th>
        <th>Alert Message</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $station_health_data->fetch_assoc()) {
        $station = $conn->query("SELECT station_name FROM charging_stations WHERE id = " . $row['station_id'])->fetch_assoc();
    ?>
    <tr>
        <td><?php echo $row['health_id']; ?></td>
        <td><?php echo $station['station_name']; ?></td>
        <td><?php echo $row['current_status']; ?></td>
        <td><?php echo $row['health_status']; ?></td>
        <td><?php echo $row['alert_type']; ?></td>
        <td><?php echo $row['alert_message']; ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="health_id" value="<?php echo $row['health_id']; ?>">
                <input type="hidden" name="station_id" value="<?php echo $row['station_id']; ?>">
                <input type="hidden" name="current_status" value="<?php echo $row['current_status']; ?>">
                <input type="hidden" name="health_status" value="<?php echo $row['health_status']; ?>">
                <input type="hidden" name="alert_type" value="<?php echo $row['alert_type']; ?>">
                <input type="hidden" name="alert_message" value="<?php echo $row['alert_message']; ?>">
                
                <button type="submit" name="action" value="delete">Delete</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>
