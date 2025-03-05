<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in; if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

// Fetch all users for selection (adjust column names as necessary)
$sql = "SELECT id, username FROM users"; // Adjust if the column name differs
$result = $conn->query($sql);
$users = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Handle form submission to add or update a vehicle
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $vehicle_name = $_POST['vehicle_name'];
    $vehicle_number = $_POST['vehicle_number'];
    $vehicle_type = $_POST['vehicle_type'];
    
    if (!empty($_POST['vehicle_id'])) {
        // Update vehicle
        $vehicle_id = $_POST['vehicle_id'];
        $sql = "UPDATE vehicles SET vehicle_name='$vehicle_name', vehicle_number='$vehicle_number', vehicle_type='$vehicle_type' WHERE vehicle_id=$vehicle_id";
    } else {
        // Insert new vehicle
        $sql = "INSERT INTO vehicles (user_id, vehicle_name, vehicle_number, vehicle_type) VALUES ('$user_id', '$vehicle_name', '$vehicle_number', '$vehicle_type')";
    }

    if ($conn->query($sql) === TRUE) {
        echo "Vehicle saved successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch all vehicles (adjust column names as necessary)
$sql = "SELECT vehicles.*, users.username FROM vehicles JOIN users ON vehicles.user_id = users.id"; // Adjust if the column name differs
$vehicles = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Vehicles</title>
    <style>/* General Reset */
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

/* Main Content Section */
h2 {
    margin: 20px 0;
    text-align: center;
}

form {
    max-width: 600px;
    margin: 20px auto;
    background: #ffffff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

form label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}

form select,
form input[type="text"] {
    padding: 8px 10px;
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1em;
}

form button[type="submit"] {
    margin: 15px 0;
    padding: 10px 15px;
    background-color: #35424a;
    color: #ffffff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button[type="submit"]:hover {
    background-color: #2a3e33;
}

/* Vehicle Table Section */
table {
    max-width: 800px;
    margin: 20px auto;
    border-collapse: collapse;
    background: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
}

table th,
table td {
    padding: 8px 10px;
    text-align: center;
    border: 1px solid #ddd;
}

table th {
    background: #35424a;
    color: #ffffff;
}

table tr:nth-child(even) {
    background: #f9f9f9;
}

table tr:hover {
    background: #f1f1f1;
}

/* Responsive Design */
@media (max-width: 768px) {
    form,
    table {
        width: 90%;
    }

    table td,
    table th {
        font-size: 0.9em;
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
<h2>Manage Vehicles</h2>

<form method="post" action="">
    <label for="user_id">User:</label>
    <select name="user_id" id="user_id" required>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['id'] ?>"><?= $user['username'] ?></option>
        <?php endforeach; ?>
    </select><br>

    <label for="vehicle_name">Vehicle Name:</label>
    <input type="text" name="vehicle_name" id="vehicle_name" required><br>

    <label for="vehicle_number">Vehicle Number:</label>
    <input type="text" name="vehicle_number" id="vehicle_number" required><br>

    <label for="vehicle_type">Vehicle Type:</label>
    <select name="vehicle_type" id="vehicle_type" required>
        <option value="Electric">Electric</option>
        <option value="Hybrid">Hybrid</option>
        
    </select><br>

    <input type="hidden" name="vehicle_id" id="vehicle_id">
    <button type="submit">Save Vehicle</button>
</form>

<h3 align = 'center'>All Vehicles</h3>
<table border="1">
    <tr>
        <th>User Name</th>
        <th>Vehicle Name</th>
        <th>Vehicle Number</th>
        <th>Vehicle Type</th>
        
    </tr>
    <?php if ($vehicles->num_rows > 0): ?>
        <?php while ($row = $vehicles->fetch_assoc()): ?>
            <tr>
                <td><?= $row['username'] ?></td>
                <td><?= $row['vehicle_name'] ?></td>
                <td><?= $row['vehicle_number'] ?></td>
                <td><?= $row['vehicle_type'] ?></td>
                <td>
                    <!-- You can add edit and delete functionality here -->
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No vehicles found</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
