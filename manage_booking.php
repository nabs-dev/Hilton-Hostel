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
$bookingQuery = "SELECT b.*, h.name as hostel_name, h.location, h.image, r.room_type, r.price 
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

// Check if action is provided in URL
if(isset($_GET['action']) && $_GET['action'] == 'cancel' && $booking['status'] == 'Confirmed') {
    // Cancel booking directly
    $updateQuery = "UPDATE bookings SET status = 'Cancelled' WHERE id = $booking_id";
    
    if(mysqli_query($conn, $updateQuery)) {
        echo "<script>
            alert('Booking cancelled successfully!');
            window.location.href = 'dashboard.php';
        </script>";
        exit;
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Process booking modification
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if($action == 'modify') {
        $check_in = mysqli_real_escape_string($conn, $_POST['check_in']);
        $check_out = mysqli_real_escape_string($conn, $_POST['check_out']);
        $guests = intval($_POST['guests']);
        
        // Validate dates
        if(empty($check_in) || empty($check_out)) {
            $error = "Please select check-in and check-out dates.";
        } elseif(strtotime($check_in) < strtotime(date('Y-m-d'))) {
            $error = "Check-in date cannot be in the past.";
        } elseif(strtotime($check_out) <= strtotime($check_in)) {
            $error = "Check-out date must be after check-in date.";
        } else {
            // Check room availability (excluding current booking)
            $room_id = $booking['room_id'];
            $availabilityQuery = "SELECT * FROM bookings 
                                 WHERE room_id = $room_id 
                                 AND id != $booking_id
                                 AND ((check_in_date <= '$check_out' AND check_out_date >= '$check_in')
                                 OR (check_in_date >= '$check_in' AND check_in_date <= '$check_out'))";
            $availabilityResult = mysqli_query($conn, $availabilityQuery);
            
            if(mysqli_num_rows($availabilityResult) > 0) {
                $error = "Sorry, this room is not available for the selected dates.";
            } else {
                // Calculate new total price
                $price_per_night = $booking['price'];
                $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
                $total_price = $price_per_night * $nights;
                
                // Update booking
                $updateQuery = "UPDATE bookings 
                               SET check_in_date = '$check_in', 
                                   check_out_date = '$check_out', 
                                   guests = $guests,
                                   total_price = $total_price 
                               WHERE id = $booking_id";
                
                if(mysqli_query($conn, $updateQuery)) {
                    $success = "Booking updated successfully!";
                    
                    // Refresh booking data
                    $bookingResult = mysqli_query($conn, $bookingQuery);
                    $booking = mysqli_fetch_assoc($bookingResult);
                    
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
    } elseif($action == 'cancel') {
        // Cancel booking
        $updateQuery = "UPDATE bookings SET status = 'Cancelled' WHERE id = $booking_id";
        
        if(mysqli_query($conn, $updateQuery)) {
            $success = "Booking cancelled successfully!";
            
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Booking - Hilton Hostel</title>
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
        
        /* Manage Booking Section */
        .manage-booking {
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
        
        .booking-details {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 30px;
        }
        
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
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .booking-id {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .booking-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
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
        
        .booking-content {
            display: flex;
            gap: 30px;
        }
        
        .booking-info {
            flex: 2;
        }
        
        .hostel-card {
            display: flex;
            margin-bottom: 20px;
        }
        
        .hostel-img {
            width: 150px;
            height: 100px;
            border-radius: 4px;
            overflow: hidden;
            margin-right: 20px;
        }
        
        .hostel-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .hostel-details h3 {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .hostel-location {
            display: flex;
            align-items: center;
            color: var(--gray);
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .hostel-location i {
            margin-right: 5px;
            color: var(--secondary);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .info-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
        }
        
        .info-label {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .booking-actions {
            flex: 1;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .action-title {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
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
        
        .action-btn {
            width: 100%;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
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
            
            .booking-content {
                flex-direction: column;
            }
            
            .hostel-card {
                flex-direction: column;
            }
            
            .hostel-img {
                width: 100%;
                margin-right: 0;
                margin-bottom: 15px;
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
    
    <!-- Manage Booking Section -->
    <section class="manage-booking">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-clipboard-check"></i> Manage Booking #<?php echo $booking['id']; ?>
            </h1>
            
            <div class="booking-details">
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
                
                <div class="booking-header">
                    <div class="booking-id">
                        Booking #<?php echo $booking['id']; ?>
                    </div>
                    <div class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                        <?php echo $booking['status']; ?>
                    </div>
                </div>
                
                <div class="booking-content">
                    <div class="booking-info">
                        <div class="hostel-card">
                            <div class="hostel-img">
                                <img src="<?php echo $booking['image']; ?>" alt="<?php echo $booking['hostel_name']; ?>">
                            </div>
                            <div class="hostel-details">
                                <h3><?php echo $booking['hostel_name']; ?></h3>
                                <div class="hostel-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo $booking['location']; ?></span>
                                </div>
                                <p>Room Type: <?php echo $booking['room_type']; ?></p>
                            </div>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Check In</div>
                                <div class="info-value"><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Check Out</div>
                                <div class="info-value"><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Duration</div>
                                <div class="info-value">
                                    <?php 
                                        $nights = (strtotime($booking['check_out_date']) - strtotime($booking['check_in_date'])) / (60 * 60 * 24);
                                        echo $nights . ' ' . ($nights == 1 ? 'Night' : 'Nights');
                                    ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Price Per Night</div>
                                <div class="info-value">$<?php echo $booking['price']; ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Total Price</div>
                                <div class="info-value">$<?php echo $booking['total_price']; ?></div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Booking Date</div>
                                <div class="info-value"><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-actions">
                        <h3 class="action-title">Manage Your Booking</h3>
                        
                        <?php if($booking['status'] == 'Confirmed'): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="modify">
                                
                                <div class="form-group">
                                    <label for="check-in">Check In Date</label>
                                    <input type="date" id="check-in" name="check_in" value="<?php echo $booking['check_in_date']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="check-out">Check Out Date</label>
                                    <input type="date" id="check-out" name="check_out" value="<?php echo $booking['check_out_date']; ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="guests">Number of Guests</label>
                                    <select name="guests" id="guests" required>
                                        <?php for($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($booking['guests'] == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> <?php echo ($i == 1) ? 'Guest' : 'Guests'; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="action-btn modify-btn">Modify Booking</button>
                            </form>
                            
                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="action-btn cancel-btn">Cancel Booking</button>
                            </form>
                        <?php else: ?>
                            <p style="margin-bottom: 20px; color: var(--gray);">This booking cannot be modified because it is <?php echo strtolower($booking['status']); ?>.</p>
                        <?php endif; ?>
                        
                        <a href="dashboard.php" class="action-btn back-btn" style="display: block; text-align: center; text-decoration: none;">Back to Dashboard</a>
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
        // Set minimum dates for check-in and check-out
        document.addEventListener('DOMContentLoaded', function() {
            const checkInInput = document.getElementById('check-in');
            const checkOutInput = document.getElementById('check-out');
            
            if(checkInInput && checkOutInput) {
                // Update check-out min date when check-in changes
                checkInInput.addEventListener('change', function() {
                    const checkInDate = new Date(this.value);
                    const nextDay = new Date(checkInDate);
                    nextDay.setDate(checkInDate.getDate() + 1);
                    
                    const nextDayString = nextDay.toISOString().split('T')[0];
                    checkOutInput.min = nextDayString;
                    
                    // If check-out date is before new check-in date, update it
                    if(checkOutInput.value && new Date(checkOutInput.value) <= checkInDate) {
                        checkOutInput.value = nextDayString;
                    }
                });
            }
        });
    </script>
</body>
</html>
