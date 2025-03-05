<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EV Charging Station Finder & Slot Booking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            color: white;
            padding: 10px 20px;
        }
        header .logo {
            display: flex;
            align-items: center;
        }
        header .logo img {
            height: 60px;
            margin-right: 20px;
        }
        header nav a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }
        main {
            padding: 20px;
            text-align: center;
        }
        .video-section video {
            width: 600px;
            height: 400px;
            border-radius: 10px;
        }
        .description {
            margin-top: 20px;
            line-height: 1.6;
            text-align: center;
            
        }
        .description p {
            margin: 10px 0;
        }
        .description p:nth-child(odd) {
            color:rgb(212, 83, 152);
            
        }
        .description p:nth-child(even) {
            color:rgb(58, 205, 183);
        }
        .image-gallery {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            margin-top: 20px;
        }
        .image-gallery img {
            width: 300px;
            height: 200px;
            border-radius: 10px;
            flex-shrink: 0;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
        }
        .scrolling-text {
    font-size: 24px;
    font-weight: bold;
    font-family: Arial, sans-serif;
    background: linear-gradient(90deg, red, orange, yellow, green, blue, indigo, violet);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    white-space: nowrap;
    overflow: hidden;
    display: inline-block;
    animation: scroll 5s linear infinite alternate;
}
@keyframes scroll {
    0% {
        transform: translateX(100%);
    }
    50% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="encharge.png" alt="Project Logo">
        <div>EnCharge-EV Finder & Slot Booking</div>
    </div>
    <nav>
        <a href="admin_login.php">Admin</a>
        <a href="user_login.php">User</a>
    </nav>
</header>

<main>
<div class="scrolling-text">
    Welcome to EnCharge....!
</div>

    <section class="video-section">
        <img src="ion-blue-0.gif" autoplay loop muted></img>
        
       
    </section>
    
    <section class="image-gallery">
        <img src="ev infrastructure.avif" alt="EV Station 1">
        <img src="station2.webp" alt="EV Station 2">
        <img src="station3.jpg" alt="EV Station 3">
        <img src="station4.jpg" alt="EV Station 4">
        <img src="station5.jpg" alt="EV Station 5">
        
    </section>

    <section class="image-gallery">
        <img src="station7.avif" alt="EV Station 6">
        <img src="station4.jpg" alt="EV Station 7">
        <img src="station9.jpg" alt="EV Station 8">
        <img src="station10.png" alt="EV Station 9">
        <img src="station6.jpg" alt="EV Station 10">
        
    </section>

    <section class="description">
        <h2>Why This Project is Helpful for Users</h2>
        <p>Finding charging stations is now easier than ever with our streamlined locator system.</p>
        <p>Book charging slots in advance to save time and avoid waiting.</p>
        <p>Access real-time information about slot availability and station status.</p>
        <p>Save your favorite stations for quick access whenever needed.</p>
        <p>View dynamic pricing and choose the most cost-effective charging times.</p>
        <p>Securely manage payments with QR payment available.</p>
        <p>Initially, update the location before finding the stations.</p>
        <p>Track your booking history and manage vehicle information effortlessly.</p>
        <p>Enjoy a seamless user experience with a simple and intuitive interface.</p>
        <p>Stay updated with real-time notifications and alerts about your bookings.</p>
        <p>Contribute to sustainable living by optimizing your EV charging habits.</p>
    </section>

</main>

<footer>
    &copy; 2024 EnCharge-EV Finder & Slot Booking | All rights reserved.
</footer>

</body>
</html>
