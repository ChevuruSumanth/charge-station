<?php
include 'db_connection.php';
session_start();

// Ensure session variables are set
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'user'; // Default to 'user'

if (!$user_id) {
    die("Error: User not logged in!");
}

// Function to add a notification
function addNotification($conn, $user_id, $message, $is_admin = false) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_admin) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iss", $user_id, $message, $is_admin); // Corrected bind_param type

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

// Handle notifications based on actions
if (isset($_POST['action']) && $user_id !== null) {  
    $action = $_POST['action'];
    $booking_id = $_POST['booking_id'] ?? null;

    // Fetch user details
    $userQuery = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userQuery->bind_result($username);
    $userQuery->fetch();
    $userQuery->close();

    // Fetch booking details if available
    if ($booking_id) {
        $bookingQuery = $conn->prepare("
            SELECT cs.station_name, b.booking_datetime, cs.price 
            FROM bookings b 
            JOIN charging_stations cs ON b.station_id = cs.id 
            WHERE b.booking_id = ?
        ");
        if (!$bookingQuery) {
            die("Prepare failed: " . $conn->error);
        }
    
        $bookingQuery->bind_param("i", $booking_id);
        $bookingQuery->execute();
        $bookingQuery->bind_result($station_name, $booking_datetime, $price);
        
        if (!$bookingQuery->fetch()) {
            die("Error: Booking not found!");
        }
    
        $bookingQuery->close();
    }

    // Booking Confirmation Notification
    if ($action == "book_slot") {
        $message = "$username - Booking confirmed at $station_name on $booking_datetime.";
        addNotification($conn, $user_id, $message);
        addNotification($conn, $user_id, $message, true); // Notify admin
    }

    // Booking Cancellation Notification
    elseif ($action == "cancel_booking") {
        $message = "$username - Booking cancelled at $station_name on $booking_datetime.";
        addNotification($conn, $user_id, $message);
        addNotification($conn, $user_id, $message, true); // Notify admin
    }

    // Payment Confirmation Notification
    elseif ($action == "confirm_payment") {
        $message = "$username - Payment confirmed of ₹$price for $station_name.";
        addNotification($conn, $user_id, $message);
        addNotification($conn, $user_id, $message, true); // Notify admin
    }
}

// Fetch notifications for user and admin
$notifications = [];
if ($user_role == 'admin') {
    $notifQuery = "SELECT message, timestamp FROM notifications WHERE is_admin = 1 ORDER BY timestamp DESC LIMIT 10";
    $stmt = $conn->prepare($notifQuery);
} else {
    if ($user_id !== null) {
        $notifQuery = "SELECT message, timestamp FROM notifications WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10";
        $stmt = $conn->prepare($notifQuery);
        
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
    } else {
        die("Error: No user ID found.");
    }
}

if (isset($stmt)) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        die("Error: Query failed!");
    }

    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .notification-container { width: 50%; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; }
        .notification { padding: 10px; border-bottom: 1px solid #ddd; }
        .notification:last-child { border-bottom: none; }
        .timestamp { font-size: 12px; color: gray; }
    </style>
</head>
<body>

<div class="notification-container">
    <h2>Notifications</h2>
    <?php if (empty($notifications)): ?>
        <p>No notifications available.</p>
    <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
            <div class="notification">
                <p><?php echo htmlspecialchars($notif['message']); ?></p>
                <span class="timestamp"><?php echo htmlspecialchars($notif['timestamp']); ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
