<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include "db_connection.php";

// Initialize variables
$selected_date = $_GET['selected_date'] ?? date('Y-m-d');

// Securely fetch bookings for the selected date
$bookings_query = "
    SELECT 
        b.booking_id, 
        u.username, 
        v.vehicle_name, 
        v.vehicle_number, 
        b.mobile_number, 
        b.booking_datetime, 
        b.booking_status, 
        b.payment_status, 
        b.charging_status 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN vehicles v ON b.vehicle_id = v.vehicle_id
    WHERE DATE(b.booking_datetime) = ?
    ORDER BY b.booking_datetime ASC";

$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$bookings_result = $stmt->get_result();

// CSRF Protection Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Update charging status logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("<p style='color: red;'>CSRF token mismatch! Request denied.</p>");
    }

    $booking_id = $_POST['booking_id'];
    $allowed_statuses = ['Not Started', 'Ongoing', 'Complete'];
    $new_status = in_array($_POST['charging_status'], $allowed_statuses) ? $_POST['charging_status'] : 'Not Started';

    $update_query = "UPDATE bookings SET charging_status = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $booking_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Charging status updated successfully for Booking ID $booking_id.</p>";
    } else {
        echo "<p style='color: red;'>Error updating status: " . $conn->error . "</p>";
    }

    $stmt->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
    <style>
        
         /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    color: #333;
    margin: 0;
    padding: 0;
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

h2 {
    text-align: center;
    color: #4CAF50;
    margin-top: 20px;
}

/* Filter form styles */
form {
    text-align: center;
    margin: 20px 0;
}

form label {
    font-weight: bold;
    margin-right: 10px;
}

form input[type="date"] {
    padding: 10px;
    margin-right: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

form button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

form button:hover {
    background-color: #45a049;
}

/* Table styles */
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
    text-align: left;
}

th {
    background-color: #4CAF50;
    color: white;
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

/* Responsive layout */
@media (max-width: 600px) {
    .navbar a {
        float: none;
        display: block;
        text-align: left;
    }

    table {
        width: 100%;
    }

    form {
        width: 100%;
        margin: 10px auto;
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

<h2>View Bookings</h2>

<!-- Date Filter Form -->
<form method="get" action="view_bookings.php">
    <label for="selected_date">Select Date:</label>
    <input type="date" name="selected_date" id="selected_date" value="<?= htmlspecialchars($selected_date) ?>" required>
    <button type="submit">Filter</button>
</form>

<?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
    <h3>Bookings for <?= htmlspecialchars($selected_date) ?>:</h3>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Vehicle Name/Number</th>
                <th>Booking ID</th>
                <th>Mobile Number</th>
                <th>Date/Time of Booking</th>
                <th>Booking Status</th>
                <th>Payment Status</th>
                <th>Charging Status</th>
                <th>Update Charging Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['username']) ?></td>
                    <td><?= htmlspecialchars($booking['vehicle_name']) . ' (' . htmlspecialchars($booking['vehicle_number']) . ')' ?></td>
                    <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                    <td><?= htmlspecialchars($booking['mobile_number']) ?></td>
                    <td><?= htmlspecialchars($booking['booking_datetime']) ?></td>
                    <td><?= htmlspecialchars($booking['booking_status']) ?></td>
                    <td><?= htmlspecialchars($booking['payment_status']) ?></td>
                    <td><?= htmlspecialchars($booking['charging_status']) ?></td>
                    <td>
                        <!-- Update Charging Status Form -->
                        <form method="post" action="view_bookings.php?selected_date=<?= htmlspecialchars($selected_date) ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
                            <select name="charging_status" required>
                                <option value="" disabled>Select Status</option>
                                <option value="Not Started" <?= ($booking['charging_status'] === 'Not Started') ? 'selected' : '' ?>>Pending</option>
                                <option value="Ongoing" <?= ($booking['charging_status'] === 'Ongoing') ? 'selected' : '' ?>>In Progress</option>
                                <option value="Complete" <?= ($booking['charging_status'] === 'Complete') ? 'selected' : '' ?>>Complete</option>
                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No bookings found for <?= htmlspecialchars($selected_date) ?>.</p>
<?php endif; ?>

</body>
</html>

