<?php
session_start(); // Start the session

// Check if the user is already logged in, if so, redirect to the dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: user_dashboard.php');
    exit();
}

include 'db_connection.php'; // Include the database connection

// Initialize error message
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and bind to check if the user exists
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, create a session
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;

            // Redirect to the user dashboard
            header('Location: user_dashboard.php');
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
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
    <title>User Login</title>
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
    background-color: #f4f4f4;
    background-image: url('background.webp');
    background-repeat: no-repeat;
    background-size: cover;
    color: #333;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Container Styling */
.container {
    background:rgb(196, 229, 240);
    border-radius: 8px;
    padding: 30px 40px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    text-align: center;
}

.container h2 {
    margin-bottom: 20px;
    color: #35424a;
    font-size: 24px;
}

/* Error Message */
p {
    font-size: 14px;
    color: red;
    margin-bottom: 10px;
}

/* Form Styling */
form {
    text-align: left;
}

form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

form input[type="email"],
form input[type="password"],
form input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

form input[type="email"],
form input[type="password"] {
    background: #f9f9f9;
    font-size: 14px;
}

form input[type="submit"] {
    background:rgb(75, 20, 146);
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background 0.3s ease;
}

form input[type="submit"]:hover {
    background: #1abc9c;
}

/* Links Styling */
.register-link,
.forgot-password-link {
    margin-top: 10px;
}

.register-link p,
.forgot-password-link p {
    font-size: 14px;
}

.register-link a,
.forgot-password-link a {
    color: #1abc9c;
    text-decoration: none;
    font-weight: bold;
}

.register-link a:hover,
.forgot-password-link a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 20px;
    }

    .container h2 {
        font-size: 20px;
    }

    form input[type="email"],
    form input[type="password"],
    form input[type="submit"] {
        font-size: 14px;
    }
}

</style>

</head>
<body>

    <div class ="container">
    <h2>Login</h2>
    <?php if ($error) { echo "<p style='color:red;'>$error</p>"; } ?>
    <form action="user_login.php" method="post">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>
    <div class="register-link">
        <p>Don't have an account? <a href="user_register.php">Register here</a></p>
    </div>
    <div class="forgot-password-link">
    <p>Forgot your password? <a href="forgot_password.php">Click here to reset it</a></p>
</div>

</body>
</html>
