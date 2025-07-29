<?php
session_start();
include 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if booking ID is provided
if(!isset($_GET['id'])) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$booking_id = intval($_GET['id']);

// Get booking details
$bookingQuery = "SELECT b.*, h.name as hostel_name, h.location, h.image, h.address, r.room_type, r.price, r.capacity 
                FROM bookings b 
                JOIN hostels h ON b.hostel_id = h.id 
                JOIN rooms r ON b.room_id = r.id 
                WHERE b.id = $booking_id AND b.user_id = $user_id";
$bookingResult = mysqli_query($conn, $bookingQuery);

if(mysqli_num_rows($bookingResult) == 0) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$booking = mysqli_fetch_assoc($bookingResult);

// Calculate nights
$check_in = new DateTime($booking['check_in_date']);
$check_out = new DateTime($booking['check_out_date']);
$nights = $check_out->diff($check_in)->days;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Hilton Hostel</title>
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
            --warning: #f39c12;
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
        
        /* Booking Details Section */
        .booking-details-section {
            padding: 40px 0;
        }
        
        .page-title {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-right: 15px;
            color: var(--secondary);
        }
        
        .booking-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .booking-header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .booking-id {
            font-size: 18px;
            font-weight: 600;
        }
        
        .booking-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-confirmed {
            background-color: var(--success);
            color: white;
        }
        
        .status-pending {
            background-color: var(--accent);
            color: var(--dark);
        }
        
        .status-cancelled {
            background-color: var(--danger);
            color: white;
        }
        
        .status-completed {
            background-color: var(--secondary);
            color: white;
        }
        
        .booking-body {
            padding: 30px;
        }
        
        .booking-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .hostel-info {
            display: flex;
            margin-bottom: 20px;
        }
        
        .hostel-img {
            width: 200px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 20px;
        }
        
        .hostel-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .hostel-details {
            flex: 1;
        }
        
        .hostel-name {
            font-size: 22px;
            font-weight: 600;
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
        
        .hostel-address {
            margin-bottom: 10px;
            color: var(--gray);
        }
        
        .room-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .room-type {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .room-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .room-info-item {
            display: flex;
            align-items: center;
        }
        
        .room-info-item i {
            margin-right: 5px;
            color: var(--secondary);
        }
        
        .booking-dates {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .date-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            width: 48%;
        }
        
        .date-label {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .date-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .price-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .price-label {
            color: var(--gray);
        }
        
        .price-value {
            font-weight: 500;
        }
        
        .price-total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 18px;
            font-weight: 600;
        }
        
        .booking-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .action-btn {
            flex: 1;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
        }
        
        .modify-btn {
            background-color: var(--secondary);
            color: white;
        }
        
        .modify-btn:hover {
            background-color: #005ea6;
        }
        
        .cancel-btn {
            background-color: white;
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        .cancel-btn:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }
        
        .back-btn {
            background-color: var(--gray);
            color: white;
        }
        
        .back-btn:hover {
            background-color: #555;
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
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin: 15px 0;
                justify-content: center;
            }
            
            .hostel-info {
                flex-direction: column;
            }
            
            .hostel-img {
                width: 100%;
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .booking-dates {
                flex-direction: column;
                gap: 15px;
            }
            
            .date-box {
                width: 100%;
            }
            
            .booking-actions {
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
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Booking Details Section -->
    <section class="booking-details-section">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-clipboard-check"></i> Booking Details #<?php echo $booking['id']; ?>
            </h1>
            
            <div class="booking-card">
                <div class="booking-header">
                    <div class="booking-id">
                        Booking #<?php echo $booking['id']; ?>
                    </div>
                    <div class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                        <?php echo $booking['status']; ?>
                    </div>
                </div>
                
                <div class="booking-body">
                    <!-- Hostel Information -->
                    <div class="booking-section">
                        <h2 class="section-title">Hostel Information</h2>
                        <div class="hostel-info">
                            <div class="hostel-img">
                                <img src="<?php echo $booking['image']; ?>" alt="<?php echo $booking['hostel_name']; ?>">
                            </div>
                            <div class="hostel-details">
                                <h3 class="hostel-name"><?php echo $booking['hostel_name']; ?></h3>
                                <div class="hostel-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo $booking['location']; ?></span>
                                </div>
                                <p class="hostel-address">
                                    <i class="fas fa-map"></i> <?php echo $booking['address']; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="room-details">
                            <h3 class="room-type"><?php echo $booking['room_type']; ?></h3>
                            <div class="room-info">
                                <div class="room-info-item">
                                    <i class="fas fa-user"></i>
                                    <span>Capacity: <?php echo $booking['capacity']; ?> Guests</span>
                                </div>
                                <div class="room-info-item">
                                    <i class="fas fa-bed"></i>
                                    <span>Beds: <?php echo $booking['capacity']; ?></span>
                                </div>
                                <div class="room-info-item">
                                    <i class="fas fa-users"></i>
                                    <span>Guests: <?php echo $booking['guests']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Details -->
                    <div class="booking-section">
                        <h2 class="section-title">Booking Details</h2>
                        
                        <div class="booking-dates">
                            <div class="date-box">
                                <div class="date-label">Check In</div>
                                <div class="date-value">
                                    <i class="fas fa-calendar-check"></i>
                                    <?php echo date('F d, Y', strtotime($booking['check_in_date'])); ?>
                                </div>
                            </div>
                            
                            <div class="date-box">
                                <div class="date-label">Check Out</div>
                                <div class="date-value">
                                    <i class="fas fa-calendar-times"></i>
                                    <?php echo date('F d, Y', strtotime($booking['check_out_date'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="price-details">
                            <div class="price-row">
                                <div class="price-label">Price per night</div>
                                <div class="price-value">$<?php echo number_format($booking['price'], 2); ?></div>
                            </div>
                            
                            <div class="price-row">
                                <div class="price-label">Number of nights</div>
                                <div class="price-value"><?php echo $nights; ?> nights</div>
                            </div>
                            
                            <div class="price-total">
                                <div>Total Price</div>
                                <div>$<?php echo number_format($booking['total_price'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Actions -->
                    <div class="booking-actions">
                        <?php if($booking['status'] == 'Confirmed'): ?>
                            <a href="manage_booking.php?id=<?php echo $booking['id']; ?>&action=modify" class="action-btn modify-btn">Modify Booking</a>
                            <a href="manage_booking.php?id=<?php echo $booking['id']; ?>&action=cancel" class="action-btn cancel-btn" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel Booking</a>
                        <?php endif; ?>
                        <a href="dashboard.php" class="action-btn back-btn">Back to Dashboard</a>
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
</body>
</html>
