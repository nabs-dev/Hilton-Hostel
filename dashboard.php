<?php
session_start();
include 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user's bookings
$bookingsQuery = "SELECT b.*, h.name as hostel_name, h.location, h.image, r.room_type 
                 FROM bookings b 
                 JOIN hostels h ON b.hostel_id = h.id 
                 JOIN rooms r ON b.room_id = r.id 
                 WHERE b.user_id = $user_id 
                 ORDER BY b.created_at DESC";
$bookingsResult = mysqli_query($conn, $bookingsQuery);

// Get upcoming bookings (check-in date in the future)
$upcomingQuery = "SELECT b.*, h.name as hostel_name, h.location, h.image, r.room_type 
                 FROM bookings b 
                 JOIN hostels h ON b.hostel_id = h.id 
                 JOIN rooms r ON b.room_id = r.id 
                 WHERE b.user_id = $user_id AND b.check_in_date >= CURDATE() 
                 ORDER BY b.check_in_date ASC 
                 LIMIT 3";
$upcomingResult = mysqli_query($conn, $upcomingQuery);

// Get past bookings (check-out date in the past)
$pastQuery = "SELECT b.*, h.name as hostel_name, h.location, h.image, r.room_type 
             FROM bookings b 
             JOIN hostels h ON b.hostel_id = h.id 
             JOIN rooms r ON b.room_id = r.id 
             WHERE b.user_id = $user_id AND b.check_out_date < CURDATE() 
             ORDER BY b.check_out_date DESC 
             LIMIT 3";
$pastResult = mysqli_query($conn, $pastQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hilton Hostel</title>
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
        
        .user-menu {
            position: relative;
        }
        
        .user-menu-btn {
            display: flex;
            align-items: center;
            color: white;
            font-weight: 500;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .user-menu-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .user-menu-btn i {
            margin-right: 8px;
        }
        
        .user-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border-radius: 4px;
            box-shadow: var(--shadow);
            width: 200px;
            z-index: 100;
            margin-top: 10px;
            display: none;
        }
        
        .user-menu-dropdown.active {
            display: block;
        }
        
        .user-menu-dropdown ul {
            list-style: none;
            padding: 10px 0;
        }
        
        .user-menu-dropdown ul li {
            margin: 0;
        }
        
        .user-menu-dropdown ul li a {
            color: var(--dark);
            padding: 10px 15px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .user-menu-dropdown ul li a:hover {
            background-color: #f5f5f5;
            color: var(--secondary);
        }
        
        .user-menu-dropdown ul li a i {
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .user-menu-dropdown ul li.divider {
            height: 1px;
            background-color: #eee;
            margin: 5px 0;
        }
        
        /* Dashboard Styles */
        .dashboard {
            padding: 40px 0;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .welcome-message h1 {
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .welcome-message p {
            color: var(--gray);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
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
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            display: flex;
            align-items: center;
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }
        
        .card-icon.blue {
            background-color: rgba(0, 113, 194, 0.1);
            color: var(--secondary);
        }
        
        .card-icon.green {
            background-color: rgba(0, 128, 9, 0.1);
            color: var(--success);
        }
        
        .card-icon.orange {
            background-color: rgba(254, 187, 2, 0.1);
            color: var(--accent);
        }
        
        .card-content h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .card-content p {
            color: var(--gray);
            font-size: 14px;
        }
        
        /* Booking Sections */
        .section-title {
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .booking-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .booking-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .booking-img {
            height: 150px;
            overflow: hidden;
        }
        
        .booking-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .booking-card:hover .booking-img img {
            transform: scale(1.05);
        }
        
        .booking-details {
            padding: 20px;
        }
        
        .booking-hostel {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .booking-location {
            display: flex;
            align-items: center;
            color: var(--gray);
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .booking-location i {
            margin-right: 5px;
            color: var(--secondary);
        }
        
        .booking-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 3px;
        }
        
        .info-value {
            font-weight: 500;
        }
        
        .booking-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .status-confirmed {
            background-color: rgba(0, 128, 9, 0.1);
            color: var(--success);
        }
        
        .status-pending {
            background-color: rgba(254, 187, 2, 0.1);
            color: #d97706;
        }
        
        .status-cancelled {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }
        
        .status-completed {
            background-color: rgba(0, 113, 194, 0.1);
            color: var(--secondary);
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
        }
        
        .booking-btn {
            flex: 1;
            padding: 8px 0;
            text-align: center;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .view-btn {
            background-color: var(--secondary);
            color: white;
        }
        
        .view-btn:hover {
            background-color: #005ea6;
        }
        
        .cancel-btn {
            background-color: transparent;
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        .cancel-btn:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }
        
        .modify-btn {
            background-color: transparent;
            color: var(--warning);
            border: 1px solid var(--warning);
        }
        
        .modify-btn:hover {
            background-color: rgba(243, 156, 18, 0.1);
        }
        
        .no-bookings {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .no-bookings i {
            font-size: 48px;
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        .no-bookings h3 {
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .no-bookings p {
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        /* All Bookings Table */
        .bookings-table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            overflow-x: auto;
        }
        
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .bookings-table th {
            text-align: left;
            padding: 12px 15px;
            background-color: #f5f5f5;
            color: var(--primary);
            font-weight: 600;
        }
        
        .bookings-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .bookings-table tr:last-child td {
            border-bottom: none;
        }
        
        .bookings-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .table-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .table-actions {
            display: flex;
            gap: 5px;
        }
        
        .table-btn {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.3s;
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
            
            .user-menu {
                margin-top: 15px;
            }
            
            .dashboard-header {
                flex-direction: column;
                text-align: center;
            }
            
            .welcome-message {
                margin-bottom: 20px;
            }
            
            .booking-cards {
                grid-template-columns: 1fr;
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
                </ul>
            </nav>
            
            <div class="user-menu">
                <div class="user-menu-btn" id="userMenuBtn">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $username; ?></span>
                    <i class="fas fa-chevron-down" style="margin-left: 8px; font-size: 12px;"></i>
                </div>
                
                <div class="user-menu-dropdown" id="userMenuDropdown">
                    <ul>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="#"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                        <li class="divider"></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Dashboard -->
    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <div class="welcome-message">
                    <h1>Welcome, <?php echo $username; ?>!</h1>
                    <p>Manage your bookings and account information</p>
                </div>
                
                <div class="action-buttons">
                    <a href="search.php" class="action-btn primary-btn">
                        <i class="fas fa-search"></i> Find Hostels
                    </a>
                    <a href="#" class="action-btn outline-btn">
                        <i class="fas fa-user"></i> Edit Profile
                    </a>
                </div>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="card-icon blue">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="card-content">
                        <h3><?php echo mysqli_num_rows($upcomingResult); ?></h3>
                        <p>Upcoming Bookings</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon green">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="card-content">
                        <h3><?php echo mysqli_num_rows($pastResult); ?></h3>
                        <p>Past Bookings</p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-icon orange">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="card-content">
                        <h3>0</h3>
                        <p>Reviews Written</p>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Bookings -->
            <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Upcoming Bookings</h2>
            
            <?php if(mysqli_num_rows($upcomingResult) > 0): ?>
                <div class="booking-cards">
                    <?php while($booking = mysqli_fetch_assoc($upcomingResult)): ?>
                        <div class="booking-card">
                            <div class="booking-img">
                                <img src="<?php echo $booking['image']; ?>" alt="<?php echo $booking['hostel_name']; ?>">
                            </div>
                            <div class="booking-details">
                                <h3 class="booking-hostel"><?php echo $booking['hostel_name']; ?></h3>
                                <div class="booking-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo $booking['location']; ?></span>
                                </div>
                                
                                <div class="booking-info">
                                    <div class="info-item">
                                        <span class="info-label">Check In</span>
                                        <span class="info-value"><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Check Out</span>
                                        <span class="info-value"><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Room Type</span>
                                        <span class="info-value"><?php echo $booking['room_type']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Total Price</span>
                                        <span class="info-value">$<?php echo $booking['total_price']; ?></span>
                                    </div>
                                </div>
                                
                                <div class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                                    <?php echo $booking['status']; ?>
                                </div>
                                
                                <?php
// Add this code to the existing dashboard.php file to update the booking action buttons

// Find this section in the existing dashboard.php file:
// <div class="booking-actions">
//     <a href="#" class="booking-btn view-btn">View Details</a>
//     <a href="#" class="booking-btn modify-btn">Modify</a>
//     <a href="#" class="booking-btn cancel-btn">Cancel</a>
// </div>

// Replace it with this code:
?>
<div class="booking-actions">
    <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="booking-btn view-btn">View Details</a>
    <?php if($booking['status'] == 'Confirmed'): ?>
        <a href="manage_booking.php?id=<?php echo $booking['id']; ?>&action=modify" class="booking-btn modify-btn">Modify</a>
        <a href="manage_booking.php?id=<?php echo $booking['id']; ?>&action=cancel" class="booking-btn cancel-btn" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</a>
    <?php endif; ?>
</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Upcoming Bookings</h3>
                    <p>You don't have any upcoming bookings. Start planning your next adventure!</p>
                    <a href="search.php" class="action-btn primary-btn">
                        <i class="fas fa-search"></i> Find Hostels
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Past Bookings -->
            <h2 class="section-title"><i class="fas fa-history"></i> Past Bookings</h2>
            
            <?php if(mysqli_num_rows($pastResult) > 0): ?>
                <div class="booking-cards">
                    <?php while($booking = mysqli_fetch_assoc($pastResult)): ?>
                        <div class="booking-card">
                            <div class="booking-img">
                                <img src="<?php echo $booking['image']; ?>" alt="<?php echo $booking['hostel_name']; ?>">
                            </div>
                            <div class="booking-details">
                                <h3 class="booking-hostel"><?php echo $booking['hostel_name']; ?></h3>
                                <div class="booking-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo $booking['location']; ?></span>
                                </div>
                                
                                <div class="booking-info">
                                    <div class="info-item">
                                        <span class="info-label">Check In</span>
                                        <span class="info-value"><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Check Out</span>
                                        <span class="info-value"><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Room Type</span>
                                        <span class="info-value"><?php echo $booking['room_type']; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Total Price</span>
                                        <span class="info-value">$<?php echo $booking['total_price']; ?></span>
                                    </div>
                                </div>
                                
                                <div class="booking-status status-completed">
                                    Completed
                                </div>
                                
                                <div class="booking-actions">
                                    <a href="#" class="booking-btn view-btn">View Details</a>
                                    <a href="#" class="booking-btn primary-btn">Write Review</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-history"></i>
                    <h3>No Past Bookings</h3>
                    <p>You don't have any past bookings. Book your first stay with us!</p>
                    <a href="search.php" class="action-btn primary-btn">
                        <i class="fas fa-search"></i> Find Hostels
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- All Bookings -->
            <h2 class="section-title"><i class="fas fa-list"></i> All Bookings</h2>
            
            <?php if(mysqli_num_rows($bookingsResult) > 0): ?>
                <div class="bookings-table-container">
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Hostel</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Room Type</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($booking = mysqli_fetch_assoc($bookingsResult)): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo $booking['hostel_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                    <td><?php echo $booking['room_type']; ?></td>
                                    <td>$<?php echo $booking['total_price']; ?></td>
                                    <td>
                                        <span class="table-status status-<?php echo strtolower($booking['status']); ?>">
                                            <?php echo $booking['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="#" class="table-btn view-btn">View</a>
                                            <?php if(strtotime($booking['check_in_date']) > time()): ?>
                                                <a href="#" class="table-btn modify-btn">Modify</a>
                                                <a href="#" class="table-btn cancel-btn">Cancel</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Bookings Found</h3>
                    <p>You haven't made any bookings yet. Start your journey with us today!</p>
                    <a href="search.php" class="action-btn primary-btn">
                        <i class="fas fa-search"></i> Find Hostels
                    </a>
                </div>
            <?php endif; ?>
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
        // User menu dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userMenuDropdown = document.getElementById('userMenuDropdown');
            
            userMenuBtn.addEventListener('click', function() {
                userMenuDropdown.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userMenuBtn.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                    userMenuDropdown.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
