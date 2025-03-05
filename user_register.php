<?php
include 'db_connection.php'; // Include the database connection

// Initialize error message
$error = ""; // Add this line to initialize the $error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $phonepe = $_POST['mobile'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate if the passwords match
    if ($password === $confirm_password) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (username, email, mobile, phonepe, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $mobile, $phonepe, $hashed_password);

        if ($stmt->execute()) {
            echo "Registration successful! <a href='user_login.php'>Login here</a>";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error = "Passwords do not match.";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
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
    background-color: #f9f9f9;
    
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}

/* Container Styling */
.container {
    background-color:rgb(215, 244, 216);
    width: 100%;
    max-width: 400px;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(31, 94, 181, 0.1);
    text-align: center;
}

.container h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
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
    color: #555;
}

form input[type="text"],
form input[type="email"],
form input[type="tel"],
form input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f4f4f4;
    font-size: 14px;
}

form input[type="submit"] {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 5px;
    background-color:rgb(46, 114, 156);
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form input[type="submit"]:hover {
    background-color: #1abc9c;
}

/* Links Styling */
.login-link {
    margin-top: 10px;
}

.login-link p {
    font-size: 14px;
    color:rgb(235, 33, 191);
}

.login-link a {
    color:rgb(205, 150, 41);
    text-decoration: none;
    font-weight: bold;
}

.login-link a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px 20px;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="tel"],
    form input[type="password"],
    form input[type="submit"] {
        font-size: 14px;
    }

    .container h2 {
        font-size: 20px;
    }
}

</style>
</head>
<body>
    <div class ="container">
    <h2>Register</h2>
    <?php if ($error) { echo "<p style='color:red;'>$error</p>"; } ?>
    <form action="user_register.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="mobile">Mobile Number:</label>
        <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" required><br><br>

        <label for="mobile">Phonepe Number:</label>
        <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <input type="submit" value="Register">
    </form>
    <div class="login-link">
            <p>Already have an account? <a href="user_login.php">Login here</a></p>
        </div>
    </div>
    <script>document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("form").addEventListener("submit", function (event) {
        let isValid = true;

        // Get form fields
        let mobile = document.getElementById("mobile").value;
        let phonepe = document.getElementById("phonepe").value;
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("confirm_password").value;

        // Validate mobile number (10 digits)
        let mobilePattern = /^[0-9]{10}$/;
        if (!mobilePattern.test(mobile)) {
            alert("Mobile number must be exactly 10 digits.");
            isValid = false;
        }

        // Validate PhonePe number (10 digits)
        if (!mobilePattern.test(phonepe)) {
            alert("PhonePe number must be exactly 10 digits.");
            isValid = false;
        }

        // Validate password (uppercase, lowercase, special character, and at least 6 characters)
        let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{6,}$/;
        if (!passwordPattern.test(password)) {
            alert("Password must contain at least one uppercase letter, one lowercase letter, one special character, and be at least 6 characters long.");
            isValid = false;
        }

        // Check if passwords match
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            isValid = false;
        }

        // If validation fails, prevent form submission
        if (!isValid) {
            event.preventDefault();
        } else {
            // Show success popup message
            alert("Your registration was successfully completed.");
        }
    });
});
</script>
</body>
</html>
