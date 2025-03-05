<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in; if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $station_name = mysqli_real_escape_string($conn, $_POST['station_name']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

    $insert_query = "INSERT INTO feedback (name, email, station_name, feedback) VALUES ('$name', '$email', '$station_name', '$feedback')";
    
    if (mysqli_query($conn, $insert_query)) {
        $success_message = "Feedback submitted successfully!";
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback</title>
    <style>
        /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
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
    margin: 10px 0;
}

.nav li {
    margin: 0 10px;
}

.nav a {
    text-decoration: none;
    color: #ffffff;
    font-size: 14px;
    transition: color 0.3s ease;
}

.nav a:hover {
    color: #a8d5c7;
}

/* Main Container */
.container {
    max-width: 600px;
    margin: 30px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.container h1 {
    text-align: center;
    color: #35424a;
    margin-bottom: 20px;
}

/* Messages */
.message {
    text-align: center;
    margin-bottom: 20px;
    font-size: 16px;
}

/* Form Styling */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

label {
    font-size: 14px;
    color: #555;
}

input[type="text"],
input[type="email"],
textarea {
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
    box-sizing: border-box;
}

textarea {
    resize: none;
}

input:focus,
textarea:focus {
    border-color: #35424a;
    outline: none;
}

/* Submit Button */
button[type="submit"] {
    padding: 10px;
    background-color: #35424a;
    color: #ffffff;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #2a3e33;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }

    .nav {
        flex-direction: column;
    }

    .nav li {
        margin: 5px 0;
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
<div class="container">
    <h1>Submit Feedback</h1>

    <?php if (isset($success_message)): ?>
        <div class="message" style="color: green;">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="message" style="color: red;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="station_name">Station Name:</label>
            <input type="text" name="station_name" id="station_name" required>
        </div>
        <div class="form-group">
            <label for="feedback">Feedback:</label>
            <textarea name="feedback" id="feedback" rows="5" required></textarea>
        </div>
        <button type="submit">Submit Feedback</button>
    </form>
</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
