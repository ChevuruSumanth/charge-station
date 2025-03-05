<?php
session_start();

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include 'db_connection.php';

// Function to get monthly report for station usage and revenue
function getMonthlyReport($month, $year) {
    global $conn;

    // Corrected query for station usage and revenue
    $query = "SELECT cs.station_name, COUNT(b.booking_id) AS total_bookings, 
                     SUM(b.amount) AS total_revenue
              FROM charging_stations cs
              JOIN bookings b ON cs.id = b.station_id
              WHERE MONTH(b.booking_datetime) = '$month' AND YEAR(b.booking_datetime) = '$year'
              GROUP BY cs.station_name";
              
    return $conn->query($query);
}

// Function to get custom report for user activity and peak hours
function getCustomReport($start_date, $end_date) {
    global $conn;

    // Corrected query for user activity and peak hours
    $query = "SELECT cs.station_name, COUNT(b.booking_id) AS total_bookings, 
                     SUM(b.amount) AS total_revenue
              FROM charging_stations cs
              JOIN bookings b ON cs.id = b.station_id
              WHERE b.booking_datetime BETWEEN '$start_date' AND '$end_date'
              GROUP BY cs.station_name";
              
    return $conn->query($query);
}

// Fetch monthly reports based on POST request
if (isset($_POST['action']) && $_POST['action'] == 'monthly_report') {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $monthly_report = getMonthlyReport($month, $year);
}

// Fetch custom reports based on POST request
if (isset($_POST['action']) && $_POST['action'] == 'custom_report') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $custom_report = getCustomReport($start_date, $end_date);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Station Reports & Analytics</title>
    <style>/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    color: #333;
    margin: 0;
    padding: 0;
}

h2 {
    text-align: center;
    color: #4CAF50;
    margin-top: 20px;
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
    padding: 10px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    max-width: 600px;
}

form label {
    margin-right: 10px;
    font-weight: bold;
}

form select, form input[type="number"], form input[type="date"] {
    padding: 8px;
    margin-right: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
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

/* Table Styles */
table {
    width: 80%;
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

/* Responsive Design */
@media (max-width: 600px) {
    .navbar a {
        float: none;
        display: block;
        text-align: left;
    }

    form {
        max-width: 100%;
        margin: 10px auto;
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
    <h2>Reports & Analytics</h2>

    <!-- Monthly Report Form -->
    <form method="post">
        <label>Month: 
            <select name="month" required>
            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
            </select>
        </label>
        <label>Year: 
            <input type="number" name="year" placeholder="YYYY" required>
        </label>
        <button type="submit" name="action" value="monthly_report">Generate Monthly Report</button>
    </form>

    <?php if (isset($monthly_report)) { ?>
    <h3>Monthly Report</h3>
    <table border="1">
        <tr>
            <th>Station Name</th>
            <th>Total Bookings</th>
            <th>Total Revenue</th>
        </tr>
        <?php while ($row = $monthly_report->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['station_name']; ?></td>
            <td><?php echo $row['total_bookings']; ?></td>
            <td><?php echo $row['total_revenue']; ?></td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>

    <!-- Custom Report Form -->
    <form method="post">
        <label>Start Date: <input type="date" name="start_date" required></label>
        <label>End Date: <input type="date" name="end_date" required></label>
        <button type="submit" name="action" value="custom_report">Generate Custom Report</button>
    </form>

    <?php if (isset($custom_report)) { ?>
    <h3>Custom Report</h3>
    <table border="1">
        <tr>
            <th>Station Name</th>
            <th>Total Bookings</th>
            <th>Total Revenue</th>
        </tr>
        <?php while ($row = $custom_report->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['station_name']; ?></td>
            <td><?php echo $row['total_bookings']; ?></td>
            <td><?php echo $row['total_revenue']; ?></td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>
</body>
</html>
