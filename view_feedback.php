<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}
require 'db_connection.php';

// Fetch feedback from the database
$feedback_query = "SELECT * FROM feedback ORDER BY created_at DESC";
$feedback_result = mysqli_query($conn, $feedback_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback</title>
    <style>
       /* General Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f4f4f9;
    color: #333;
    font-size: 16px;
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


/* Container Styles */
.container {
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Header Styles */
h1 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

td {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Message Styles */
p {
    text-align: center;
    font-size: 18px;
    color: #333;
}

/* Responsive Design */
@media (max-width: 600px) {
    .navbar {
        display: block;
    }
    
    .navbar a {
        margin: 5px 0;
        width: 100%;
    }

    .container {
        margin: 20px;
        padding: 15px;
    }

    table {
        font-size: 14px;
    }

    th, td {
        padding: 10px;
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
<div class="container">
    <h1>Feedback List</h1>

    <?php if (mysqli_num_rows($feedback_result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Station Name</th>
                    <th>Feedback</th>
                    <th>Submitted On</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($feedback_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['station_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['feedback']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No feedback available.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
