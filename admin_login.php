<?php
session_start();

// Static admin username and password (these should ideally be stored in a secure database)
$admin_username = "admin";
$admin_password = "password123";
$admin_email = "sumanthchevuru9494@gmail.com";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the username and password from the form and sanitize inputs
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Check if the entered username and password match the static ones
    if ($username === $admin_username && $password === $admin_password) {
        // Login successful, store session and redirect to admin dashboard
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // Login failed, show error message
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
    background-color: #f4f4f9;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Login Container Styling */
.login-container {
    background-color: #ffffff;
    width: 100%;
    max-width: 400px;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.login-container h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    font-weight: bold;
}

/* Form Styling */
form {
    display: flex;
    flex-direction: column;
}

form input[type="text"],
form input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
    font-size: 14px;
}

form input[type="text"]:focus,
form input[type="password"]:focus {
    border-color: #1abc9c;
    outline: none;
    background-color: #ffffff;
}

form input[type="submit"] {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    border: none;
    border-radius: 5px;
    background-color: #1abc9c;
    color: #ffffff;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form input[type="submit"]:hover {
    background-color: #16a085;
}

/* Error Message */
.error {
    color: red;
    font-size: 14px;
    margin-bottom: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-container {
        padding: 15px 20px;
    }

    form input[type="text"],
    form input[type="password"],
    form input[type="submit"] {
        font-size: 14px;
    }

    .login-container h2 {
        font-size: 20px;
    }
}

    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <?php if (isset($error)) { ?>
        <p class="error"><?php echo $error; ?></p>
    <?php } ?>
    <form action="admin_login.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
</div>

</body>
</html>
