<?php  
session_start();  

// Check if the user is logged in; if not, redirect to the login page 
if (!isset($_SESSION['user_id'])) {     
    header('Location: user_login.php');     
    exit(); 
}  

require 'db_connection.php';  

// Fetch user ID from session 
$user_id = $_SESSION['user_id'];  

if ($_SERVER['REQUEST_METHOD'] == 'POST') {     
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);     
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);      

    // Check if the user already has a location saved     
    $query = "SELECT * FROM user_locations WHERE user_id = '$user_id'";     
    $result = mysqli_query($conn, $query);      

    if (!$result) {         
        die('Error executing query: ' . mysqli_error($conn));     
    }      

    if (mysqli_num_rows($result) > 0) {         
        // Update the existing location         
        $update_query = "UPDATE user_locations SET latitude = '$latitude', longitude = '$longitude', updated_at = NOW() WHERE user_id = '$user_id'";         
        if (!mysqli_query($conn, $update_query)) {             
            die('Error updating location: ' . mysqli_error($conn));         
        }     
    } else {         
        // Insert new location         
        $insert_query = "INSERT INTO user_locations (user_id, latitude, longitude, updated_at) VALUES ('$user_id', '$latitude', '$longitude', NOW())";         
        if (!mysqli_query($conn, $insert_query)) {             
            die('Error inserting location: ' . mysqli_error($conn));         
        }     
    }      

    echo "<script>alert('Location updated successfully!'); window.location.href='user_location.php';</script>";
}  
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Location - EV Charging Station Finder</title>
    <style>/* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f4f4;
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

/* Main Content Section */
.container {
    padding: 20px;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin: 20px auto;
    max-width: 800px;
}

/* Form Section */
form {
    margin: 20px 0;
}

form label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}

form input[type="text"] {
    padding: 8px 10px;
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1em;
}

form button[type="submit"] {
    margin: 15px 0;
    padding: 10px 15px;
    background-color: #35424a;
    color: #ffffff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button[type="submit"]:hover {
    background-color: #2a3e33;
}

/* Map Section */
#map {
    width: 100%;
    height: 400px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 20px 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }

    form input[type="text"],
    form button[type="submit"] {
        font-size: 0.9em;
    }

    #map {
        height: 300px;
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
        <h1>Update Location</h1>
        <form method="POST" action="">
            <label for="latitude">Latitude:</label>
            <input type="text" id="latitude" name="latitude" readonly required>

            <label for="longitude">Longitude:</label>
            <input type="text" id="longitude" name="longitude" readonly required>

            <button type="submit">Update Location</button>
        </form>
        <div id="map" style="width: 100%; height: 400px;"></div>
    </div>
<script>
        function initMap() {
    // Create map with more flexible initial settings
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: { lat: 0, lng: 0 }, // Default center
        mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    var marker = null;
    var infoWindow = new google.maps.InfoWindow();

    function updateLocationOnMap(pos) {
        // Clear previous marker if exists
        if (marker) {
            marker.setMap(null);
        }

        // Center and zoom map
        map.setCenter(pos);
        map.setZoom(15);

        // Create new marker
        marker = new google.maps.Marker({
            position: pos,
            map: map,
            title: 'Your Current Location',
            animation: google.maps.Animation.DROP // Add drop animation
        });

        // Add info window
        infoWindow.setContent(`Latitude: ${pos.lat}<br>Longitude: ${pos.lng}`);
        infoWindow.open(map, marker);

        // Update form fields
        document.getElementById('latitude').value = pos.lat.toFixed(6);
        document.getElementById('longitude').value = pos.lng.toFixed(6);

        console.log("Location Updated:", {
            latitude: pos.lat,
            longitude: pos.lng,
            accuracy: window.currentPositionAccuracy || 'N/A'
        });
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                // Store accuracy for diagnostic purposes
                window.currentPositionAccuracy = position.coords.accuracy;

                var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                // Log detailed position information
                console.log("Geolocation Details:", {
                    latitude: pos.lat,
                    longitude: pos.lng,
                    accuracy: position.coords.accuracy + " meters",
                    timestamp: new Date(position.timestamp).toLocaleString()
                });

                updateLocationOnMap(pos);
            },
            function (error) {
                console.error("Geolocation Error:", {
                    code: error.code,
                    message: error.message
                });

                // Provide fallback or user guidance
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        alert("Please enable location permissions for accurate tracking.");
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert("Location information is unavailable. Check your device settings.");
                        break;
                    case error.TIMEOUT:
                        alert("Location request timed out. Please try again.");
                        break;
                }
            },
            {
                enableHighAccuracy: true,  // Prioritize GPS
                timeout: 15000,            // 15 seconds timeout
                maximumAge: 30000          // Accept cached location up to 30 seconds
            }
        );

        // Optional: Watch position for continuous updates
        navigator.geolocation.watchPosition(
            function(position) {
                var pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                updateLocationOnMap(pos);
            },
            function(error) {
                console.warn("Position watch error:", error);
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 30000
            }
        );
    } else {
        alert("Geolocation is not supported by this browser.");
    }
}
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCYXUqyHY-JKd-TvAWqY30rzyk9e4ubcjE&callback=initMap">
</script>
</body>
</html>
