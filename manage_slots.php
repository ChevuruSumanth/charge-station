<?php
// Include database connection
session_start();

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connection.php';  // Ensure db_connection.php establishes the $conn object

// Check if form is submitted
if (isset($_POST['submit'])) {
    $station_id = $_POST['station_id'];
    $slot_type = $_POST['slot_type'];
    $available_slots = $_POST['available_slots'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    //$peak_price = $_POST['peak_price'];
    //$off_peak_price = $_POST['off_peak_price'];

    // Insert or update slot information in the station_slots table
    $stmt = $conn->prepare("
        INSERT INTO station_slots (station_id, slot_type, available_slots, start_time, end_time)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        available_slots = VALUES(available_slots), 
        start_time = VALUES(start_time), 
        end_time = VALUES(end_time)
    ");
    
    $stmt->bind_param("issis", $station_id, $slot_type, $available_slots, $start_time, $end_time);

    if ($stmt->execute()) {
        // Update the slots_available field in the charging_stations table
        $update_stmt = $conn->prepare("
            UPDATE charging_stations 
            SET slots_available = slots_available - ?
            WHERE id = ?
        ");
        $update_stmt->bind_param("ii", $available_slots, $station_id);

        if ($update_stmt->execute()) {
            echo "Slots updated successfully and charging station slots count adjusted.";
        } else {
            echo "Error updating slots in charging_stations: " . $update_stmt->error;
        }

        $update_stmt->close();
    } else {
        echo "Error updating station_slots: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Charging Slots</title>
    <style>
        /* General styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
        }
        /* Navbar styles */
        .navbar {
            background-color: #333;
            overflow: hidden;
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
            margin: 50px auto;
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
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        /* Success message styles */
        .success-message {
            color: green;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
        /* Error message styles */
        .error-message {
            color: red;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
        /* Responsive layout */
        @media (max-width: 600px) {
            .navbar a {
                float: none;
                display: block;
                text-align: left;
            }
            form {
                width: 90%;
                margin: 20px auto;
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
    <a href="view_payments.php">View Payments</a>
    <a href="view_feedback.php">View Feedback</a>
    <a href="reports_analytics.php">Station Reports</a>
    <a href="station_health.php">Station Health</a>
    <a href="cancelled_booking.php">Cancelled bookings</a>
    <a href="admin_logout.php" class="logout">Logout</a>
</div>
    <h2>Manage Charging Slots</h2>

    <!-- Add/Update Slot Form -->
    <form method="POST" action="manage_slots.php">
        <label for="station">Select Station:</label>
        <select name="station_id" required>
            <?php
            // Fetch stations from database
            $result = $conn->query("SELECT id, station_name FROM charging_stations");

            if ($result->num_rows > 0) {
                // Display stations as options in the dropdown
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['station_name'] . "</option>";
                }
            } else {
                echo "<option value=''>No stations available</option>";
            }
            ?>
        </select><br>

        <label for="slot_type">Slot Type:</label>
        <select name="slot_type" required>
            <option value="fast">Fast Charging</option>
            <option value="slow">Slow Charging</option>
        </select><br>

        <label for="available_slots">Reserved Slots:</label>
        <input type="number" name="available_slots" required><br>

        <label for="start_time">Start Time:</label>
        <input type="datetime-local" name="start_time" required><br>

        <label for="end_time">End Time:</label>
        <input type="datetime-local" name="end_time" required><br>

        

        <input type="submit" name="submit" value="Update Slots">
    </form>

<?php
$conn->close(); // Close the connection at the end of the script
?>
</body>
</html>
