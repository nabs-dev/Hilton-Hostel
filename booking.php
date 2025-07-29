<?php
session_start();
include 'db.php';

// Check if hostel ID is provided
if(!isset($_GET['id'])) {
    echo "<script>window.location.href = 'search.php';</script>";
    exit;
}

$hostel_id = intval($_GET['id']);

// Get hostel details
$hostelQuery = "SELECT * FROM hostels WHERE id = $hostel_id";
$hostelResult = mysqli_query($conn, $hostelQuery);

if(mysqli_num_rows($hostelResult) == 0) {
    echo "<script>window.location.href = 'search.php';</script>";
    exit;
}

$hostel = mysqli_fetch_assoc($hostelResult);

// Get room types for this hostel
$roomsQuery = "SELECT * FROM rooms WHERE hostel_id = $hostel_id ORDER BY price";
$roomsResult = mysqli_query($conn, $roomsQuery);

// Process booking form
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        echo "<script>
            alert('Please login to book a room.');
            window.location.href = 'login.php';
        </script>";
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $room_id = intval($_POST['room_id']);
    $check_in = mysqli_real_escape_string($conn, $_POST['check_in']);
    $check_out = mysqli_real_escape_string($conn, $_POST['check_out']);
    
    // Validate dates
    if(empty($check_in) || empty($check_out)) {
        $error = "Please select check-in and check-out dates.";
    } elseif(strtotime($check_in) < strtotime(date('Y-m-d'))) {
        $error = "Check-in date cannot be in the past.";
    } elseif(strtotime($check_out) <= strtotime($check_in)) {
        $error = "Check-out date must be after check-in date.";
    } else {
        // Check room availability
        $availabilityQuery = "SELECT * FROM bookings 
                             WHERE room_id = $room_id 
                             AND ((check_in_date <= '$check_out' AND check_out_date >= '$check_in')
                             OR (check_in_date >= '$check_in' AND check_in_date <= '$check_out'))";
        $availabilityResult = mysqli_query($conn, $availabilityQuery);
        
        if(mysqli_num_rows($availabilityResult) > 0) {
            $error = "Sorry, this room is not available for the selected dates.";
        } else {
            // Calculate total price
            $roomQuery = "SELECT price FROM rooms WHERE id = $room_id";
            $roomResult = mysqli_query($conn, $roomQuery);
            $room = mysqli_fetch_assoc($roomResult);
            
            $price_per_night = $room['price'];
            $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
            $total_price = $price_per_night * $nights;
            
            // Create booking
            $bookingQuery = "INSERT INTO bookings (user_id, hostel_id, room_id, check_in_date, check_out_date, total_price, status, created_at) 
                            VALUES ($user_id, $hostel_id, $room_id, '$check_in', '$check_out', $total_price, 'Confirmed', NOW())";
            
            if(mysqli_query($conn, $bookingQuery)) {
                $booking_id = mysqli_insert_id($conn);
                $success = "Booking confirmed! Your booking ID is #$booking_id.";
                
                // Redirect to dashboard after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                </script>";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $hostel['name']; ?> - Hilton Hostel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #003580;
            --secondary: #0071c2;
            --accent: #febb02;
            --light: #f5f5f5;
            --dark: #333;
            --success: #008009;
            --danger: #e74c3c;
            --gray: #6b6b6b;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background-color: var(--primary);
            color: white;
            padding: 15px 0;
            box-shadow: var(--shadow);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 28px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
        }
        
        nav ul li a i {
            margin-right: 5px;
        }
        
        nav ul li a:hover {
            color: var(--accent);
        }
        
        .auth-buttons a {
            display: inline-block;
            padding: 8px 16px;
            margin-left: 10px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .login-btn {
            background-color: transparent;
            color: white;
            border: 1px solid white;
        }
        
        .signup-btn {
            background-color: var(--accent);
            color: var(--dark);
        }
        
        .login-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .signup-btn:hover {
            background-color: #ffa500;
        }
        
        /* Hostel Header */
        .hostel-header {
            background-color: white;
            padding: 30px 0;
            box-shadow: var(--shadow);
        }
        
        .hostel-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .hostel-title h1 {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .hostel-location {
            display: flex;
            align-items: center;
            color: var(--gray);
            margin-bottom: 10px;
        }
        
        .hostel-location i {
            margin-right: 5px;
            color: var(--secondary);
        }
        
        .hostel-rating {
            display: flex;
            align-items: center;
        }
        
        .rating-score {
            background-color: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .rating-text {
            color: var(--gray);
        }
        
        .hostel-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .action-btn i {
            margin-right: 8px;
        }
        
        .primary-btn {
            background-color: var(--secondary);
            color: white;
        }
        
        .primary-btn:hover {
            background-color: #005ea6;
        }
        
        .outline-btn {
            background-color: transparent;
            color: var(--secondary);
            border: 1px solid var(--secondary);
        }
        
        .outline-btn:hover {
            background-color: rgba(0, 113, 194, 0.1);
        }
        
        /* Hostel Gallery */
        .hostel-gallery {
            padding: 40px 0;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            grid-template-rows: 250px 250px;
            gap: 10px;
        }
        
        .gallery-item {
            overflow: hidden;
            border-radius: 8px;
            position: relative;
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        
        .gallery-item.main {
            grid-row: span 2;
        }
        
        .view-all-photos {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background-color: rgba(255, 255, 255, 0.9);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .view-all-photos i {
            margin-right: 8px;
        }
        
        .view-all-photos:hover {
            background-color: white;
        }
        
        /* Hostel Content */
        .hostel-content {
            padding: 40px 0;
            display: flex;
            gap: 30px;
        }
        
        .hostel-main {
            flex: 2;
        }
        
        .hostel-sidebar {
            flex: 1;
        }
        
        .content-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .card-title i {
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .hostel-description {
            margin-bottom: 20px;
        }
        
        .hostel-features {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
        }
        
        .feature-item i {
            margin-right: 10px;
            color: var(--secondary);
        }
        
        /* Room Cards */
        .room-card {
            display: flex;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .room-card:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .room-img {
            width: 150px;
            height: 100px;
            border-radius: 4px;
            overflow: hidden;
            margin-right: 20px;
        }
        
        .room-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .room-details {
            flex: 1;
        }
        
        .room-type {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .room-features {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .room-feature {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--gray);
        }
        
        .room-feature i {
            margin-right: 5px;
            color: var(--secondary);
        }
        
        .room-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .price {
            font-size: 20px;
            font-weight: bold;
            color: var(--success);
        }
        
        .price span {
            font-size: 14px;
            font-weight: normal;
            color: var(--gray);
        }
        
        .book-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .book-btn:hover {
            background-color: #005ea6;
        }
        
        /* Booking Form */
        .booking-form {
            margin-top: 20px;
            display: none;
        }
        
        .booking-form.active {
            display: block;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-submit {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .form-submit:hover {
            background-color: #006c07;
        }
        
        /* Map */
        .map-container {
            height: 300px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Reviews */
        .review-card {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .review-card:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reviewer-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .reviewer-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .reviewer-info h4 {
            margin-bottom: 3px;
            color: var(--primary);
        }
        
        .reviewer-info p {
            font-size: 12px;
            color: var(--gray);
        }
        
        .review-rating {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .review-rating .stars {
            color: #ffc107;
            margin-right: 10px;
        }
        
        .review-date {
            font-size: 12px;
            color: var(--gray);
        }
        
        .review-text {
            color: var(--dark);
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }
        
        .alert-success {
            background-color: rgba(0, 128, 9, 0.1);
            color: var(--success);
        }
        
        /* Footer */
        footer {
            background-color: #002855;
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        .footer-bottom {
            text-align: center;
            color: #ccc;
            font-size: 14px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hostel-content {
                flex-direction: column;
            }
            
            .hostel-sidebar {
                order: -1;
            }
            
            .gallery-grid {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: 200px 200px 200px;
            }
            
            .gallery-item.main {
                grid-column: span 2;
                grid-row: span 1;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin: 15px 0;
                justify-content: center;
            }
            
            .auth-buttons {
                margin-top: 15px;
            }
            
            .hostel-header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .hostel-actions {
                margin-top: 20px;
            }
            
            .room-card {
                flex-direction: column;
            }
            
            .room-img {
                width: 100%;
                height: 150px;
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .room-price {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">
                <i class="fas fa-hotel"></i>
                Hilton Hostel
            </a>
            
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="search.php"><i class="fas fa-search"></i> Find Hostels</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Destinations</a></li>
                    <li><a href="#"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="#"><i class="fas fa-phone"></i> Contact</a></li>
                </ul>
            </nav>
            
            <div class="auth-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="login-btn">My Account</a>
                    <a href="logout.php" class="signup-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="login-btn">Login</a>
                    <a href="signup.php" class="signup-btn">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <!-- Hostel Header -->
    <section class="hostel-header">
        <div class="container">
            <div class="hostel-header-content">
                <div class="hostel-title">
                    <h1><?php echo $hostel['name']; ?></h1>
                    <div class="hostel-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo $hostel['location']; ?></span>
                    </div>
                    <div class="hostel-rating">
                        <div class="rating-score"><?php echo $hostel['rating']; ?></div>
                        <div class="rating-text">
                            <?php 
                                if($hostel['rating'] >= 9) echo "Exceptional";
                                elseif($hostel['rating'] >= 8) echo "Excellent";
                                elseif($hostel['rating'] >= 7) echo "Very Good";
                                elseif($hostel['rating'] >= 6) echo "Good";
                                else echo "Average";
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="hostel-actions">
                    <a href="#rooms" class="action-btn primary-btn">
                        <i class="fas fa-bed"></i> Book Now
                    </a>
                    <a href="#" class="action-btn outline-btn">
                        <i class="fas fa-heart"></i> Save
                    </a>
                    <a href="#" class="action-btn outline-btn">
                        <i class="fas fa-share-alt"></i> Share
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Hostel Gallery -->
    <section class="hostel-gallery">
        <div class="container">
            <div class="gallery-grid">
                <div class="gallery-item main">
                    <img src="<?php echo $hostel['image']; ?>" alt="<?php echo $hostel['name']; ?> Main Image">
                    <a href="#" class="view-all-photos">
                        <i class="fas fa-images"></i> View All Photos
                    </a>
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1469&q=80" alt="Hostel Room">
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80" alt="Hostel Lounge">
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1471&q=80" alt="Hostel Bathroom">
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1621293954908-907159247fc8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Hostel Kitchen">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Hostel Content -->
    <section class="hostel-content">
        <div class="container">
            <div class="hostel-main">
                <!-- Description -->
                <div class="content-card">
                    <h2 class="card-title"><i class="fas fa-info-circle"></i> About This Hostel</h2>
                    <div class="hostel-description">
                        <p><?php echo $hostel['description']; ?></p>
                    </div>
                </div>
                
                <!-- Amenities -->
                <div class="content-card">
                    <h2 class="card-title"><i class="fas fa-concierge-bell"></i> Amenities</h2>
                    <div class="hostel-features">
                        <div class="feature-item">
                            <i class="fas fa-wifi"></i>
                            <span>Free WiFi</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-coffee"></i>
                            <span>Breakfast Available</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-lock"></i>
                            <span>Lockers</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shower"></i>
                            <span>Hot Showers</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-utensils"></i>
                            <span>Shared Kitchen</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-tv"></i>
                            <span>Common Room with TV</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-luggage-cart"></i>
                            <span>Luggage Storage</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-tshirt"></i>
                            <span>Laundry Facilities</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-fan"></i>
                            <span>Air Conditioning</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-smoking-ban"></i>
                            <span>Non-Smoking Rooms</span>
                        </div>
                    </div>
                </div>
                
                <!-- Rooms -->
                <div class="content-card" id="rooms">
                    <h2 class="card-title"><i class="fas fa-bed"></i> Available Rooms</h2>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-error">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(mysqli_num_rows($roomsResult) > 0): ?>
                        <?php while($room = mysqli_fetch_assoc($roomsResult)): ?>
                            <div class="room-card">
                                <div class="room-img">
                                    <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1469&q=80" alt="<?php echo $room['room_type']; ?>">
                                </div>
                                <div class="room-details">
                                    <h3 class="room-type"><?php echo $room['room_type']; ?></h3>
                                    <div class="room-features">
                                        <div class="room-feature">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo $room['capacity']; ?> Guests</span>
                                        </div>
                                        <div class="room-feature">
                                            <i class="fas fa-bed"></i>
                                            <span><?php echo $room['capacity']; ?> Beds</span>
                                        </div>
                                        <div class="room-feature">
                                            <i class="fas fa-bath"></i>
                                            <span>Shared Bathroom</span>
                                        </div>
                                    </div>
                                    <div class="room-price">
                                        <div class="price">
                                            $<?php echo $room['price']; ?> <span>/ night</span>
                                        </div>
                                        <button class="book-btn" onclick="showBookingForm(<?php echo $room['id']; ?>)">Book Now</button>
                                    </div>
                                    
                                    <!-- Booking Form -->
                                    <div class="booking-form" id="booking-form-<?php echo $room['id']; ?>">
                                        <form method="POST" action="">
                                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                            
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label for="check-in-<?php echo $room['id']; ?>">Check In Date</label>
                                                    <input type="date" id="check-in-<?php echo $room['id']; ?>" name="check_in" min="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="check-out-<?php echo $room['id']; ?>">Check Out Date</label>
                                                    <input type="date" id="check-out-<?php echo $room['id']; ?>" name="check_out" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="form-submit">Confirm Booking</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No rooms available for this hostel.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Reviews -->
                <div class="content-card">
                    <h2 class="card-title"><i class="fas fa-star"></i> Guest Reviews</h2>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-img">
                                <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Sarah Johnson">
                            </div>
                            <div class="reviewer-info">
                                <h4>Sarah Johnson</h4>
                                <p>United States</p>
                            </div>
                        </div>
                        <div class="review-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="review-date">Stayed in June 2023</div>
                        </div>
                        <p class="review-text">This hostel exceeded all my expectations! The location was perfect, just a short walk from the main attractions. The staff was incredibly friendly and helpful, providing great recommendations for local restaurants and activities. The rooms were clean and comfortable, and the common areas were great for meeting other travelers. I'll definitely stay here again on my next visit!</p>
                    </div>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-img">
                                <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="David Chen">
                            </div>
                            <div class="reviewer-info">
                                <h4>David Chen</h4>
                                <p>Canada</p>
                            </div>
                        </div>
                        <div class="review-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <div class="review-date">Stayed in May 2023</div>
                        </div>
                        <p class="review-text">Great value for money! The hostel was clean and well-maintained, with comfortable beds and good facilities. The shared kitchen was well-equipped, and the free breakfast was a nice touch. The only downside was that the WiFi was a bit slow during peak hours, but overall, I had a pleasant stay and would recommend it to other travelers.</p>
                    </div>
                    
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-img">
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Emma Wilson">
                            </div>
                            <div class="reviewer-info">
                                <h4>Emma Wilson</h4>
                                <p>United Kingdom</p>
                            </div>
                        </div>
                        <div class="review-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <div class="review-date">Stayed in April 2023</div>
                        </div>
                        <p class="review-text">I had an amazing time at this hostel! The atmosphere was very social and friendly, making it easy to meet other travelers. The staff organized fun activities every evening, from pub crawls to movie nights. The beds were comfortable, and the lockers were spacious enough for all my belongings. The location was also perfect, with public transportation just a few minutes away. Highly recommended!</p>
                    </div>
                </div>
            </div>
            
            <div class="hostel-sidebar">
                <!-- Map -->
                <div class="content-card">
                    <h2 class="card-title"><i class="fas fa-map-marked-alt"></i> Location</h2>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.835253576489!2d144.95372155113935!3d-37.81725397975177!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d4c2b349649%3A0xb6899234e561db11!2sHostel!5e0!3m2!1sen!2sus!4v1623159488797!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                    <p style="margin-top: 15px; color: var(--gray);">
                        <i class="fas fa-map-marker-alt" style="color: var(--secondary); margin-right: 5px;"></i>
                        <?php echo $hostel['location']; ?>
                    </p>
                </div>
                
                <!-- Hostel Rules -->
                <div class="content-card">
                    <h2 class="card-title"><i class="fas fa-clipboard-list"></i> Hostel Rules</h2>
                    <div class="hostel-features" style="display: flex; flex-direction: column; gap: 10px;">
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Check-in: 2:00 PM - 10:00 PM</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Check-out: 11:00 AM</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-smoking-ban"></i>
                            <span>No Smoking</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-paw"></i>
                            <span>No Pets</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-volume-mute"></i>
                            <span>Quiet Hours: 11:00 PM - 7:00 AM</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-id-card"></i>
                            <span>ID Required at Check-in</span>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="content-card">
                    <h2 class="card-title"><i class="fas fa-phone-alt"></i> Contact Information</h2>
                    <div class="hostel-features" style="display: flex; flex-direction: column; gap: 10px;">
                        <div class="feature-item">
                            <i class="fas fa-phone"></i>
                            <span>+1 234 567 890</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-envelope"></i>
                            <span>info@hiltonhostel.com</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-globe"></i>
                            <span>www.hiltonhostel.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2023 Hilton Hostel. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Show booking form
        function showBookingForm(roomId) {
            // Hide all booking forms first
            document.querySelectorAll('.booking-form').forEach(form => {
                form.classList.remove('active');
            });
            
            // Show the selected booking form
            document.getElementById('booking-form-' + roomId).classList.add('active');
            
            // Scroll to the form
            document.getElementById('booking-form-' + roomId).scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Set minimum dates for check-in and check-out
        document.addEventListener('DOMContentLoaded', function() {
            const checkInInputs = document.querySelectorAll('input[name="check_in"]');
            const checkOutInputs = document.querySelectorAll('input[name="check_out"]');
            
            checkInInputs.forEach((checkInInput, index) => {
                // Set min date for check-in to today
                const today = new Date().toISOString().split('T')[0];
                checkInInput.min = today;
                
                // Update check-out min date when check-in changes
                checkInInput.addEventListener('change', function() {
                    const checkInDate = new Date(this.value);
                    const nextDay = new Date(checkInDate);
                    nextDay.setDate(checkInDate.getDate() + 1);
                    
                    const nextDayString = nextDay.toISOString().split('T')[0];
                    checkOutInputs[index].min = nextDayString;
                    
                    // If check-out date is before new check-in date, update it
                    if(checkOutInputs[index].value && new Date(checkOutInputs[index].value) <= checkInDate) {
                        checkOutInputs[index].value = nextDayString;
                    }
                });
            });
        });
    </script>
</body>
</html>
