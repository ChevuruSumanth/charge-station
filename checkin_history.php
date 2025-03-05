<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

require 'db_connection.php';

$user_id = $_SESSION['user_id'];

if (isset($_POST['check_date'])) {
    $check_date = $_POST['check_date'];

    $query = $conn->prepare("SELECT * FROM checkin_history WHERE user_id = ? AND checkin_date = ?");
    $query->bind_param("is", $user_id, $check_date);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        echo "checked-in";
    } else {
        echo "not-checked";
    }
}
?>
