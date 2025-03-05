<?php
session_start();

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Set the admin username for displaying
$admin_username = "CSE-B12";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EV Charging Station Finder</title>
    <style>
        /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body Styling */
body {
    font-family: 'Arial', sans-serif;
    background-color:rgb(209, 194, 194);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
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

/* Logout Button */
.logout {
    background-color: #ff4d4d;
    color: white;
    padding: 10px;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.logout:hover {
    background-color: #ff3333;
}

/* Container Styling */
.container {
    flex: 1;
    padding: 20px;
    background-color: #fff;
    margin: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(152, 17, 17, 0.1);
    max-width: 800px;
    align-self: center;
}

/* Headings Styling */
h1 {
    color: #333;
    margin-bottom: 20px;
    font-size: 2.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        text-align: center;
    }

    .navbar a {
        padding: 10px;
        font-size: 0.9rem;
    }

    .container {
        margin: 10px;
        padding: 15px;
    }

    h1 {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .navbar a {
        font-size: 0.8rem;
        padding: 8px 12px;
    }

    h1 {
        font-size: 1.8rem;
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
    <h1>Welcome, <?php echo $admin_username; ?>!</h1>
    
    <img src="https://st5.depositphotos.com/5532432/69007/v/450/depositphotos_690075512-stock-illustration-easy-use-isometric-icon-car.jpg"
</div>

</body>
</html>
