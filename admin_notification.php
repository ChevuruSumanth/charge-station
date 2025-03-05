<?php
session_start();

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

require 'db_connection.php';

// Initialize filter variables
$start_datetime = isset($_GET['start_datetime']) ? $_GET['start_datetime'] : '';
$end_datetime = isset($_GET['end_datetime']) ? $_GET['end_datetime'] : '';

// Base query
$notification_query = "SELECT p.payment_id, p.payment_time, p.amount, u.username, s.station_name
                       FROM payments p
                       JOIN users u ON p.user_id = u.id
                       JOIN bookings b ON p.booking_id = b.booking_id
                       JOIN charging_stations s ON b.station_id = s.id
                       WHERE p.payment_status = 'Completed'";

// Add date filter if both start and end date-time are provided
if (!empty($start_datetime) && !empty($end_datetime)) {
    $notification_query .= " AND p.payment_time BETWEEN '" . $conn->real_escape_string($start_datetime) . "' AND '" . $conn->real_escape_string($end_datetime) . "'";
}

$notification_query .= " ORDER BY p.payment_time DESC";
$notification_result = $conn->query($notification_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .navbar {
            overflow: hidden;
            background-color: #333;
            padding: 10px;
        }
        .navbar a {
            float: left;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .navbar .logout {
            float: right;
        }
        h2 {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .no-data {
            text-align: center;
            color: red;
            font-size: 18px;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form label {
            margin-right: 10px;
        }
        .filter-form input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
<div class="navbar">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_stations.php">Manage Stations</a>
    <a href="manage_slots.php">Manage Slots</a>
    <a href="view_bookings.php">View Bookings</a>
    <a href="view_feedback.php">View Feedback</a>
    <a href="reports_analytics.php">Station Reports</a>
    <a href="station_health.php">Station Health</a>
    <a href="admin_logout.php" class="logout">Logout</a>
</div>

<h2>Admin: Payment Notifications</h2>

<form class="filter-form" method="get">
    <label for="start_datetime">Start Date-Time:</label>
    <input type="datetime-local" id="start_datetime" name="start_datetime" value="<?= htmlspecialchars($start_datetime) ?>">
    <label for="end_datetime">End Date-Time:</label>
    <input type="datetime-local" id="end_datetime" name="end_datetime" value="<?= htmlspecialchars($end_datetime) ?>">
    <button type="submit">Filter</button>
</form>

<?php if ($notification_result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>User Name</th>
                <th>Station Name</th>
                <th>Amount</th>
                <th>Payment Date/Time</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($notification = $notification_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($notification['payment_id']) ?></td>
                <td><?= htmlspecialchars($notification['username']) ?></td>
                <td><?= htmlspecialchars($notification['station_name']) ?></td>
                <td>₹<?= htmlspecialchars($notification['amount']) ?></td>
                <td><?= $notification['payment_time'] ? date('Y-m-d H:i:s', strtotime($notification['payment_time'])) : 'N/A' ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-data">No payment notifications available.</p>
<?php endif; ?>

<?php
// Close the database connection
$conn->close();
?>

</body>
</html>
