<?php
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

require 'db_connection.php';

// Fetch booking ID from the query string (this could be passed after successful booking)
$booking_id = $_GET['booking_id'] ?? null;

// If booking ID is missing, display an error and exit
if (!$booking_id) {
    echo "<p style='color:red;'>Error: Booking ID is missing. Please try again from the booking page.</p>";
    exit();
}

// Fetch booking and payment details
$booking_query = "
    SELECT b.booking_id, b.amount AS amount, u.email, u.username
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.booking_id = ?";
$stmt = $conn->prepare($booking_query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking_result = $stmt->get_result();

if ($booking_result->num_rows === 0) {
    echo "<p style='color:red;'>Error: Booking not found.</p>";
    exit();
}

$booking = $booking_result->fetch_assoc();

// Handle payment confirmation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $receipt_path = null;

    // Validate uploaded file
    if (!empty($_FILES['receipt']['name'])) {
        $target_dir = "uploads/receipts/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $receipt_path = $target_dir . basename($_FILES["receipt"]["name"]);
        if (!move_uploaded_file($_FILES["receipt"]["tmp_name"], $receipt_path)) {
            echo "<p style='color:red;'>Error uploading receipt file. Please try again.</p>";
            exit();
        }
    }

    // Insert payment details into the database
$payment_status = 'Completed';
$payment_method = 'QR Code';

// Fetch the amount directly from bookings to ensure accuracy
$amount = $booking['amount'];

$payment_query = "
    INSERT INTO payments (booking_id, user_id, payment_status, payment_method, amount, transaction_id, receipt_path) 
    VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($payment_query);
$stmt->bind_param(
    "iissdss",
    $booking_id,
    $_SESSION['user_id'],
    $payment_status,
    $payment_method,
    $amount, // Use the amount retrieved from bookings
    $transaction_id,
    $receipt_path
);

if ($stmt->execute()) {
    // Update booking with payment status and ensure amount consistency
    $update_booking_query = "UPDATE bookings SET payment_status = 'Paid' WHERE booking_id = ?";
    $stmt = $conn->prepare($update_booking_query);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();

    // Send notifications to user and admin
    //sendPaymentNotification($booking['email'], $booking['username'], $transaction_id, $amount);
    //sendAdminNotification($transaction_id, $amount);

    echo "<p style='color:green;'>Payment completed successfully! Transaction ID: $transaction_id</p>";
} else {
    echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
}

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Confirmation</title>
    <style>/* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f9f9f9;
    color: #333;
    line-height: 1.6;
}

/* Header Section */
h2 {
    text-align: center;
    margin: 20px 0;
    color: #35424a;
}

/* Instructions Section */
p {
    text-align: center;
    margin: 10px 0;
    font-size: 16px;
}

/* QR Code Section */
img {
    display: block;
    margin: 10px auto;
    border: 2px solid #35424a;
    border-radius: 5px;
}

/* Payment Form */
form {
    background: #ffffff;
    max-width: 500px;
    margin: 20px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Form Elements Styling */
form label {
    font-weight: bold;
    display: block;
    margin: 10px 0 5px;
}

form input[type="text"],
form input[type="file"] {
    width: 100%;
    padding: 8px 10px;
    margin: 5px 0 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

form button[type="submit"] {
    background-color: #35424a;
    color: #ffffff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 16px;
}

form button[type="submit"]:hover {
    background-color: #2a3e33;
}

/* Responsive Design */
@media (max-width: 768px) {
    img {
        width: 150px;
        height: 150px;
    }

    form {
        padding: 15px;
    }

    form input[type="text"],
    form input[type="file"],
    form button[type="submit"] {
        font-size: 14px;
    }
}
</style>
</head>
<body>

<h2>Payment Confirmation</h2>
<p>Your booking was successful. Please complete your payment by scanning the QR code below:</p>

<!-- Display QR Code for Payment -->
<img src="./sumanth-phonepe-qr-code.jpeg" alt="QR Code for Payment" style="width:200px;height:200px;"><br>

<p>Once you have made the payment, please fill in the details below to confirm:</p>

<form method="post" enctype="multipart/form-data">
    <label for="transaction_id">Transaction ID:</label>
    <input type="text" name="transaction_id" id="transaction_id" required><br><br>

    <label for="receipt">Upload Payment Receipt Screenshot:</label>
    <input type="file" name="receipt" id="receipt" required><br><br>

    <form method="POST" action="notification.php">
    <input type="hidden" name="action" value="confirm_payment">
    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
    <button type="submit">Confirm Payment</button>
</form>

</form>

</body>
</html>