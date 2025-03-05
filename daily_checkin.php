<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

require 'db_connection.php';
$user_id = $_SESSION['user_id'];
$date_today = date('Y-m-d');

// Handle AJAX Request Only
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check = $conn->query("SELECT * FROM daily_checkin WHERE user_id = '$user_id'");
    $user_data = $check->fetch_assoc();

    $points = [1 => 2, 2 => 5, 3 => 7, 4 => 10];
    $response = [];

    if ($user_data) {
        $last_date = $user_data['last_checkin_date'];
        $consecutive_days = $user_data['consecutive_days'];
        $total_points = $user_data['total_points'];

        $diff = (strtotime($date_today) - strtotime($last_date)) / (60 * 60 * 24);

        if ($diff == 1) {
            $consecutive_days++;
            $new_points = $points[$consecutive_days] ?? 10;
            $total_points += $new_points;

            $conn->query("UPDATE daily_checkin SET last_checkin_date='$date_today', consecutive_days='$consecutive_days', total_points='$total_points' WHERE user_id='$user_id'");
            $conn->query("INSERT INTO checkin_history (user_id, checkin_date) VALUES ('$user_id', '$date_today')");

            $response = ["status" => "success", "message" => "Check-in successful!", "new_points" => $new_points, "total_points" => $total_points];
        } elseif ($diff > 1) {
            $consecutive_days = 1;
            $new_points = $points[1];
            $total_points += $new_points;

            $conn->query("UPDATE daily_checkin SET last_checkin_date='$date_today', consecutive_days='1', total_points='$total_points' WHERE user_id='$user_id'");
            $conn->query("INSERT INTO checkin_history (user_id, checkin_date) VALUES ('$user_id', '$date_today')");

            $response = ["status" => "success", "message" => "Check-in successful after a missed day!", "new_points" => $new_points, "total_points" => $total_points];
        } else {
            $response = ["status" => "error", "message" => "You've already checked in today!", "total_points" => $total_points];
        }
    } else {
        $conn->query("INSERT INTO daily_checkin (user_id, last_checkin_date, consecutive_days, total_points) VALUES ('$user_id', '$date_today', 1, 2)");
        $conn->query("INSERT INTO checkin_history (user_id, checkin_date) VALUES ('$user_id', '$date_today')");
        $total_points = 2;

        $response = ["status" => "success", "message" => "First check-in successful!", "new_points" => 2, "total_points" => $total_points];
    }

    if ($total_points >= 1000) {
        $user_info = $conn->query("SELECT name, mobile_number, phonepe_number FROM users WHERE id = '$user_id'")->fetch_assoc();
        $conn->query("INSERT INTO coupon (user_id, name, mobile_number, phonepe_number, issued_date) VALUES ('$user_id', '{$user_info['name']}', '{$user_info['mobile_number']}', '{$user_info['phonepe_number']}', NOW())");

        $response['reward'] = "🎉 Congratulations, you won 100 rupees!";
    }

    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Check-in Rewards 🎯</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 400px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        #checkin-box {
            padding: 20px;
            border: 2px dashed #007BFF;
            border-radius: 10px;
            background-color: #e9f5ff;
        }

        #status {
            font-size: 18px;
            margin: 10px 0;
            color: #007BFF;
        }

        #checkin-btn {
            padding: 12px 25px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        #checkin-btn:hover {
            background-color: #0056b3;
        }

        #points-display {
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
            color: #333;
            transition: transform 0.4s ease;
        }

        #points-display.grow {
            transform: scale(1.5);
            color: #28a745;
        }

        #calendar {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day {
            width: 40px;
            height: 40px;
            background: #f0f0f0;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #555;
            font-weight: bold;
        }

        .checked-in {
            background-color: red !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daily Check-in Rewards 🎯</h1>

        <div id="checkin-box">
            <p id="status">Click the button below to check in and earn points!</p>
            <button id="checkin-btn">Check In Now</button>
            <p id="points-display">Total Points: 0</p>
        </div>

        <h2>📅 Your Check-in Calendar</h2>
        <div id="calendar"></div>
    </div>

    <script>
        document.getElementById("checkin-btn").addEventListener("click", function() {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "daily_checkin.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    document.getElementById("status").innerText = response.message;

                    if (response.total_points) {
                        const pointsDisplay = document.getElementById("points-display");
                        pointsDisplay.innerText = `Total Points: ${response.total_points}`;
                        pointsDisplay.classList.add("grow");

                        setTimeout(() => pointsDisplay.classList.remove("grow"), 500);
                    }

                    if (response.reward) {
                        alert(response.reward);
                    }

                    loadCalendar();
                }
            };

            xhr.send();
        });

        function loadCalendar() {
            const calendar = document.getElementById("calendar");
            calendar.innerHTML = "";

            const today = new Date();
            const daysInMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();

            for (let day = 1; day <= daysInMonth; day++) {
                const dayBox = document.createElement("div");
                dayBox.classList.add("calendar-day");
                dayBox.innerText = day;

                const xhr = new XMLHttpRequest();
                xhr.open("POST", "checkin_history.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.responseText.includes("checked-in")) {
                        dayBox.classList.add("checked-in");
                    }
                };
                xhr.send(`check_date=${today.getFullYear()}-${(today.getMonth() + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`);

                calendar.appendChild(dayBox);
            }
        }

        document.addEventListener("DOMContentLoaded", loadCalendar);
    </script>
</body>
</html>
