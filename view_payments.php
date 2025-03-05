<?php
session_start();

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

require 'db_connection.php';

// Fetch all payment details for displaying in the admin panel
$payment_query = "SELECT p.payment_id, p.amount, p.payment_status, p.payment_time, b.booking_id, 
                         s.station_name, b.booking_datetime, u.username, p.receipt_path
                  FROM payments p
                  JOIN bookings b ON p.booking_id = b.booking_id
                  JOIN charging_stations s ON b.station_id = s.id
                  JOIN users u ON p.user_id = u.id
                  ORDER BY p.payment_time DESC";
$payment_result = $conn->query($payment_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Payments</title>
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
            background-color: #45a045;
        }
        .no-data {
            text-align: center;
            color: red;
            font-size: 18px;
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

<h2>Admin: View All Payments</h2>

<?php if ($payment_result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>User Name</th>
                <th>Station Name</th>
                <th>Booking ID</th>
                <th>Booking Date/Time</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Payment Time</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($payment = $payment_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($payment['payment_id']) ?></td>
                <td><?= htmlspecialchars($payment['username']) ?></td>
                <td><?= htmlspecialchars($payment['station_name']) ?></td>
                <td><?= htmlspecialchars($payment['booking_id']) ?></td>
                <td><?= htmlspecialchars($payment['booking_datetime']) ?></td>
                <td>₹<?= htmlspecialchars($payment['amount']) ?></td>
                <td><?= htmlspecialchars($payment['payment_status']) ?></td>
                <td><?= $payment['payment_time'] ? date('Y-m-d H:i:s', strtotime($payment['payment_time'])) : 'N/A' ?></td>
                <td>
                    <?php if (!empty($payment['receipt_path'])): ?>
                        <a href="<?= htmlspecialchars($payment['receipt_path']) ?>" target="_blank">View Receipt</a>
                    <?php else: ?>
                        No Receipt
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-data">No payment records found.</p>
<?php endif; ?>

<?php
// Close the database connection
$conn->close();
?>

</body>
</html>
