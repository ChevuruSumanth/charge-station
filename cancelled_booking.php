<?php
session_start();

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include "db_connection.php";

// Initialize selected date (default to today's date if no date is selected)
$selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

// Fetch canceled bookings based on the selected date
$query = "
    SELECT 
        u.username,
        u.mobile AS mobile_number,
        u.email,
        u.phonepe AS phonepe_number,
        s.station_name,
        b.booking_datetime,
        b.cancellation_datetime,
        ROUND(b.amount * 0.85, 2) AS refund_amount
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN charging_stations s ON b.station_id = s.id
    WHERE b.booking_status = 'Cancelled'
    AND DATE(b.cancellation_datetime) = '$selected_date'
    ORDER BY b.cancellation_datetime ASC
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelled Bookings</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .header {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .filter-form {
            margin: 20px auto;
            text-align: center;
        }

        .filter-form label {
            font-size: 16px;
            margin-right: 10px;
        }

        .filter-form input[type="date"] {
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .filter-form button {
            padding: 8px 12px;
            font-size: 14px;
            background-color: #45a049;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: #45a049;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table thead {
            background-color: #45a049;
            color: white;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        table tbody tr:hover {
            background-color: #f1f8ff;
        }

        .no-data {
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
            color: #666;
        }
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
    
        <h1>Cancelled Bookings</h1>
    

    <!-- Filter Form -->
    <div class="filter-form">
        <form method="get" action="">
            <label for="selected_date">Filter by Date:</label>
            <input type="date" id="selected_date" name="selected_date" value="<?= htmlspecialchars($selected_date) ?>" required>
            <button type="submit">Filter</button>
        </form>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Mobile Number</th>
                    <th>Phonepe Number</th>
                    <th>Email</th>
                    <th>Station Name</th>
                    <th>Booking Date/Time</th>
                    <th>Cancelled Booking Date/Time</th>
                    <th>Refund Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['mobile_number']) ?></td>
                        <td><?= htmlspecialchars($row['phonepe_number']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['station_name']) ?></td>
                        <td><?= htmlspecialchars($row['booking_datetime']) ?></td>
                        <td><?= htmlspecialchars($row['cancellation_datetime']) ?></td>
                        <td><?= htmlspecialchars($row['refund_amount']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No cancelled bookings found for <?= htmlspecialchars($selected_date) ?>.</p>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
