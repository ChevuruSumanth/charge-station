<?php
session_start();

// Destroy the session to log out the user
session_destroy();

// Redirect to the user login page
header("Location: user_login.php");
exit();
?>
