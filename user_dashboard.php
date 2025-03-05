<?php
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

// Fetch user information from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Include database connection
require 'db_connection.php';

// Fetch favorite stations with mobile number & price
$favorites_query = "
    SELECT s.id AS station_id, s.station_name, 
           CONCAT(s.location_city, ', ', s.location_state) AS location, 
           s.mobile_number, s.price
    FROM favorite_stations fs
    INNER JOIN charging_stations s ON fs.station_id = s.id
    WHERE fs.user_id = $user_id";
$favorites_result = $conn->query($favorites_query);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - EV Charging Station Finder</title>
    <style>
       /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #e9ecef;
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

.header h1 {
    margin: 0;
}

.nav {
    list-style: none;
    display: flex;
    justify-content: center;
    margin-top: 10px;
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

/* Content Area */
.content {
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin: 20px auto;
    width: 80%;
    max-width: 1200px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Welcome Message */
.welcome,p {
    font-size: 1.2em;
    margin-bottom: 10px;
    color: #555;
}

/* Image Section */
img {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 10px auto;
}

/* Table Section */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: #ffffff;
}

table th,
table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

table th {
    background-color: #35424a;
    color: #ffffff;
}

table tr:nth-child(even) {
    background: #f2f2f2;
}

table tr:hover {
    background: #d3e4dc;
}

/* Booking Button */
button[type="submit"] {
    background: #35424a;
    color: #ffffff;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

button[type="submit"]:hover {
    background: #2a3e33;
}
.clickable-image {
            width: 700px;
            height: auto;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .clickable-image:hover {
            transform: scale(1.1);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

/* Responsive Design */
@media (max-width: 768px) {
    .nav {
        flex-direction: column;
        align-items: center;
    }

    .content {
        padding: 10px;
    }

    table td,
    table th {
        font-size: 14px;
    }

    .welcome {
        font-size: 1em;
    }
}

    </style>
</head>
<body>

<div class="container">
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

    <div class="content">
        <p class="welcome">Welcome, <?php echo htmlspecialchars($username); ?></p>
        <p>Click on the image to collect the daily bonus.</p>
        <div class="container">
        <a href="daily_checkin.php">
    <img src="https://st5.depositphotos.com/5532432/69007/v/450/depositphotos_690075512-stock-illustration-easy-use-isometric-icon-car.jpg" class="clickable-image">
</div>
    </div>
</div>
<h2>Favorite Charging Stations</h2>

<?php if (isset($_GET['success']) && $_GET['success'] == 'added'): ?>
    <p style="color: green;">Stations successfully added to favorites!</p>
<?php endif; ?>

<?php if ($favorites_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Station Name</th>
                        <th>Location</th>
                        <th>Mobile Number</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($favorite = $favorites_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($favorite['station_name']) ?></td>
                            <td><?= htmlspecialchars($favorite['location']) ?></td>
                            <td><?= htmlspecialchars($favorite['mobile_number']) ?></td>
                            <td>₹<?= number_format($favorite['price'], 2) ?></td>
                            <td>
                                <a href="book_slot.php?station_id=<?= $favorite['station_id'] ?>" class="book-btn">Book Slot</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No favorite stations added yet.</p>
        <?php endif; ?>

        <p>Select an option from the menu to get started.</p>
    </div>
</div>

</body>
</html>