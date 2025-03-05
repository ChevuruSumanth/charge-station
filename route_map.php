<?php
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

require 'db_connection.php';

$city = '';
$stations = [];
$cities = [];

// Fetch all unique cities from the charging_stations table
$cityQuery = "SELECT DISTINCT location_city FROM charging_stations";
$cityResult = $conn->query($cityQuery);

if ($cityResult) {
    while ($row = $cityResult->fetch_assoc()) {
        $cities[] = $row['location_city'];
    }
}

// Check if a city is selected
if (isset($_POST['city'])) {
    $city = $_POST['city'];

    // Fetch stations in the selected city
    $query = "SELECT station_name, latitude, longitude FROM charging_stations WHERE location_city = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $stations[] = $row;
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
    <title>Route to Charging Stations</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCYXUqyHY-JKd-TvAWqY30rzyk9e4ubcjE&libraries=places"></script>
    <script>
        let map, userLocation, markers = [], stations = <?php echo json_encode($stations); ?>;
        let directionsService, directionsRenderer;

        // Initialize and load the Google Map
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {lat: 20.5937, lng: 78.9629}, // Default center (India)
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer();
            directionsRenderer.setMap(map);

            // Try to get the user's current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        
                        // Add a marker for the user's location
                        new google.maps.Marker({
                            position: userLocation,
                            map: map,
                            title: 'Your Location',
                            icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                        });

                        // Center the map on the user's location
                        map.setCenter(userLocation);
                    },
                    () => {
                        alert('Error: The Geolocation service failed.');
                    }
                );
            } else {
                alert('Error: Your browser doesn\'t support geolocation.');
            }

            // Add markers for stations
            stations.forEach(station => {
                const marker = new google.maps.Marker({
                    position: {lat: parseFloat(station.latitude), lng: parseFloat(station.longitude)},
                    map: map,
                    title: station.station_name,
                    icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                });

                // Add a click listener to show route and redirect to Google Maps
                marker.addListener('click', () => {
                    const destination = {lat: parseFloat(station.latitude), lng: parseFloat(station.longitude)};
                    calculateAndDisplayRoute(userLocation, destination);
                    
                    // Redirect to Google Maps on click
                    window.open(`https://www.google.com/maps/dir/?api=1&origin=${userLocation.lat},${userLocation.lng}&destination=${destination.lat},${destination.lng}&travelmode=driving`, '_blank');
                });

                markers.push(marker);
            });
        }

        // Calculate and display the route
        function calculateAndDisplayRoute(start, end) {
            directionsService.route(
                {
                    origin: start,
                    destination: end,
                    travelMode: 'DRIVING'
                },
                (response, status) => {
                    if (status === 'OK') {
                        directionsRenderer.setDirections(response);
                    } else {
                        window.alert('Directions request failed due to ' + status);
                    }
                }
            );
        }
    </script>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
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

        /* Page Title */
        h3 {
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            color: #35424a;
        }

        /* Map Container */
        #map {
            height: 500px;
            width: 90%;
            max-width: 1200px;
            border: 2px solid #35424a;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #eaeaea;
        }

        /* Form Styling */
        form {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        select, button {
            padding: 10px;
            font-size: 16px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #map {
                width: 100%;
            }

            h3 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body onload="initMap()">
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

<h3>ROUTE MAP</h3>

<!-- City Selection Form -->
<form method="post" action="">
    <label for="city">Select City:</label>
    <select name="city" id="city" required>
        <option value="">--Select--</option>
        <?php foreach ($cities as $cityOption): ?>
            <option value="<?php echo htmlspecialchars($cityOption); ?>" <?php if ($city === $cityOption) echo 'selected'; ?>>
                <?php echo htmlspecialchars($cityOption); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Find Stations</button>
</form>

<!-- Map Display -->
<div id="map"></div>
</body>
</html>
