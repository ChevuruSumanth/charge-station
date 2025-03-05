<?php
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

require 'db_connection.php';

// Initialize user ID (this should be retrieved from session in real-world applications)
$user_id = $_SESSION['user_id']; // Use user_id from session

// Initialize selected date (default to today's date)
$selected_date = $_GET['selected_date'] ?? date('Y-m-d');

// Handle cancellation request
if (isset($_POST['cancel_booking_id'])) {
    $booking_id = $_POST['cancel_booking_id'];
    // Update the booking status and set cancellation datetime
    $cancel_query = "UPDATE bookings 
                     SET booking_status = 'Cancelled', 
                         cancellation_datetime = NOW() 
                     WHERE booking_id = $booking_id AND user_id = $user_id AND booking_status != 'Cancelled'";
    if ($conn->query($cancel_query)) {
        echo "<script>alert('Booking cancelled. Money will be refundable only 85%.');</script>";
    } else {
        echo "<script>alert('Error cancelling booking. Please try again.');</script>";
    }
}

// Fetch booking history for the logged-in user based on the selected date
$history_query = "
    SELECT 
        b.booking_id,
        s.station_name,
        b.booking_datetime,
        b.booking_status,
        b.payment_status,
        b.charging_status,
        TIMESTAMPDIFF(MINUTE, NOW(), b.booking_datetime) AS minutes_to_booking
    FROM bookings b
    JOIN charging_stations s ON b.station_id = s.id
    WHERE b.user_id = $user_id
    AND DATE(b.booking_datetime) = '$selected_date'
    ORDER BY b.booking_datetime ASC";

$history_result = $conn->query($history_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <style>
        /* Your existing CSS styles */
                    /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
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

/* Page Title Section */
h2 {
    text-align: center;
    margin: 20px 0;
    color: #35424a;
}

/* Date Filter Form Styling */
form {
    text-align: center;
    margin: 20px 0;
}

form label {
    font-size: 16px;
    margin-right: 10px;
}

form input[type="date"] {
    padding: 5px 10px;
    font-size: 14px;
    border: 1px solid #aaa;
    border-radius: 5px;
}

form button[type="submit"] {
    padding: 8px 15px;
    font-size: 14px;
    background-color: #35424a;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button[type="submit"]:hover {
    background-color: #2a3e33;
}

/* Table Styling */
table {
    width: 80%;
    margin: 20px auto;
    border-collapse: collapse;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

table thead {
    background-color: #35424a;
    color: #fff;
}

table th,
table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

table tbody tr:nth-child(odd) {
    background-color: #f2f2f2;
}

table tbody tr:hover {
    background-color: #e0f2f1;
}

/* No Results Message */
p {
    text-align: center;
    margin: 10px 0;
    font-size: 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    table {
        width: 100%;
    }

    .nav {
        flex-direction: column;
    }

    .nav li {
        margin: 5px 0;
    }

    form input[type="date"],
    form button[type="submit"] {
        width: 100%;
        box-sizing: border-box;
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

<h2>Booking History</h2>

<!-- Date Filter Form -->
<form method="get" action="Booking_History.php">
    <label for="selected_date">Select Date:</label>
    <input type="date" name="selected_date" id="selected_date" value="<?= $selected_date ?>" required>
    <button type="submit">Filter</button>
</form>

<?php if ($history_result && $history_result->num_rows > 0): ?>
    <h3>Bookings for <?= $selected_date ?>:</h3>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Station Name</th>
                <th>Date/Time</th>
                <th>Booking Status</th>
                <th>Payment Status</th>
                <th>Charging Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($booking = $history_result->fetch_assoc()): ?>
                <?php 
                    $is_cancellable = $booking['minutes_to_booking'] > 15; 
                    $disable_message = $is_cancellable ? "" : "disabled";
                ?>
                <tr>
                    <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                    <td><?= htmlspecialchars($booking['station_name']) ?></td>
                    <td><?= htmlspecialchars($booking['booking_datetime']) ?></td>
                    <td><?= htmlspecialchars($booking['booking_status']) ?></td>
                    <td><?= htmlspecialchars($booking['payment_status']) ?></td>
                    <td><?= htmlspecialchars($booking['charging_status']) ?></td>
                    
                    <td>
                        <form method="post" action="Booking_History.php" onsubmit="return confirmCancellation(<?= $is_cancellable ?>);">
                            <input type="hidden" name="cancel_booking_id" value="<?= $booking['booking_id'] ?>">
                            <form method="POST" action="notification.php">
                                <input type="hidden" name="action" value="cancel_booking">
                                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                                <button type="submit" <?= $disable_message ?>>Cancel Booking</button>
                            </form>
                            
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No bookings found for <?= $selected_date ?>.</p>
<?php endif; ?>

<script>
function confirmCancellation(isCancellable) {
    if (!isCancellable) {
        alert('Booking cannot be canceled within 15 minutes of the scheduled time.');
        return false;
    }
    return confirm('Are you sure you want to cancel this booking? Only 85% of the amount will be refunded.The refund amount will be done within 1 hour.');
}
</script>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>