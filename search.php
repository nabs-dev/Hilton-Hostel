<?php
session_start();
include 'db.php';

// Get search parameters
$location = isset($_GET['location']) ? mysqli_real_escape_string($conn, $_GET['location']) : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : 1000;
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;

// Build query
$query = "SELECT h.*, COUNT(r.id) as room_count, MIN(r.price) as min_room_price 
          FROM hostels h 
          LEFT JOIN rooms r ON h.id = r.hostel_id 
          WHERE 1=1";

if(!empty($location)) {
    $query .= " AND (h.location LIKE '%$location%' OR h.name LIKE '%$location%')";
}

if(!empty($check_in) && !empty($check_out)) {
    $query .= " AND r.id NOT IN (
                SELECT b.room_id FROM bookings b 
                WHERE (b.check_in_date <= '$check_out' AND b.check_out_date >= '$check_in')
                )";
}

$query .= " AND r.capacity >= $guests";
$query .= " AND r.price BETWEEN $min_price AND $max_price";

if($rating > 0) {
    $query .= " AND h.rating >= $rating";
}

$query .= " GROUP BY h.id";
$query .= " ORDER BY h.rating DESC";

$result = mysqli_query($conn, $query);
$hostel_count = mysqli_num_rows($result);

// Get all locations for filter
$locationsQuery = "SELECT DISTINCT location FROM hostels ORDER BY location";
$locationsResult = mysqli_query($conn, $locationsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hostels - Hilton Hostel</title>
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
        
        /* Search Section */
        .search-section {
            background-color: var(--primary);
            padding: 30px 0;
        }
        
        .search-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: var(--shadow);
        }
        
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .form-group {
            flex: 1 1 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .search-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
            margin-top: 24px;
        }
        
        .search-btn:hover {
            background-color: #005ea6;
        }
        
        /* Results Section */
        .results-section {
            padding: 40px 0;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .results-count {
            font-size: 18px;
            font-weight: 500;
        }
        
        .results-count span {
            color: var(--secondary);
        }
        
        .sort-options {
            display: flex;
            align-items: center;
        }
        
        .sort-label {
            margin-right: 10px;
            font-weight: 500;
        }
        
        .sort-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        /* Results Layout */
        .results-layout {
            display: flex;
            gap: 30px;
        }
        
        /* Filters Sidebar */
        .filters-sidebar {
            width: 280px;
            flex-shrink: 0;
        }
        
        .filter-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .filter-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
            display: flex;
            align-items: center;
        }
        
        .filter-title i {
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-group:last-child {
            margin-bottom: 0;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .filter-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .price-range {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .price-input {
            flex: 1;
        }
        
        .price-separator {
            color: var(--gray);
        }
        
        .checkbox-group {
            margin-bottom: 10px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .checkbox-input {
            margin-right: 10px;
        }
        
        .rating-filter {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .rating-option {
            display: flex;
            align-items: center;
        }
        
        .rating-stars {
            display: flex;
            margin-left: 10px;
            color: #ffc107;
        }
        
        .filter-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        
        .filter-btn:hover {
            background-color: #005ea6;
        }
        
        .clear-filters {
            background-color: transparent;
            color: var(--secondary);
            border: 1px solid var(--secondary);
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        
        .clear-filters:hover {
            background-color: rgba(0, 113, 194, 0.1);
        }
        
        /* Hostels List */
        .hostels-list {
            flex: 1;
        }
        
        .hostel-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: flex;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .hostel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .hostel-img {
            width: 250px;
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .hostel-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .hostel-card:hover .hostel-img img {
            transform: scale(1.05);
        }
        
        .hostel-details {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .hostel-name {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
            text-decoration: none;
        }
        
        .hostel-name:hover {
            color: var(--secondary);
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
        
        .hostel-description {
            margin-bottom: 15px;
            color: var(--dark);
            flex: 1;
        }
        
        .hostel-features {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .feature-tag {
            background-color: #f0f0f0;
            color: var(--gray);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            display: flex;
            align-items: center;
        }
        
        .feature-tag i {
            margin-right: 5px;
            color: var(--secondary);
        }
        
        .hostel-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
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
            font-size: 14px;
        }
        
        .hostel-price {
            text-align: right;
        }
        
        .price-from {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .price-value {
            font-size: 22px;
            font-weight: bold;
            color: var(--success);
            margin-bottom: 5px;
        }
        
        .price-night {
            font-size: 14px;
            color: var(--gray);
        }
        
        .view-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        
        .view-btn:hover {
            background-color: #005ea6;
        }
        
        .no-results {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 30px;
            text-align: center;
        }
        
        .no-results i {
            font-size: 48px;
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .no-results p {
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .pagination-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            margin: 0 5px;
            border-radius: 4px;
            background-color: white;
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .pagination-btn.active {
            background-color: var(--secondary);
            color: white;
        }
        
        .pagination-btn:hover:not(.active) {
            background-color: #f0f0f0;
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
        
        /* Range Slider */
        .range-slider {
            width: 100%;
            margin: 15px 0;
        }
        
        .range-slider-range {
            -webkit-appearance: none;
            width: 100%;
            height: 5px;
            border-radius: 5px;
            background: #d7dcdf;
            outline: none;
            padding: 0;
            margin: 0;
        }
        
        .range-slider-range::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--secondary);
            cursor: pointer;
            transition: background 0.15s ease-in-out;
        }
        
        .range-slider-range::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border: 0;
            border-radius: 50%;
            background: var(--secondary);
            cursor: pointer;
            transition: background 0.15s ease-in-out;
        }
        
        .range-slider-value {
            display: inline-block;
            position: relative;
            width: 60px;
            color: var(--dark);
            text-align: center;
            border-radius: 3px;
            background: #f5f5f5;
            padding: 5px 10px;
            margin-top: 10px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .results-layout {
                flex-direction: column;
            }
            
            .filters-sidebar {
                width: 100%;
                margin-bottom: 30px;
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
            
            .hostel-card {
                flex-direction: column;
            }
            
            .hostel-img {
                width: 100%;
                height: 200px;
            }
            
            .results-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .sort-options {
                width: 100%;
            }
            
            .sort-select {
                flex: 1;
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
    
    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <form action="search.php" method="GET" class="search-form">
                    <div class="form-group">
                        <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                        <input type="text" id="location" name="location" placeholder="Where are you going?" value="<?php echo $location; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="check-in"><i class="fas fa-calendar-alt"></i> Check In</label>
                        <input type="date" id="check-in" name="check_in" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $check_in; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="check-out"><i class="fas fa-calendar-alt"></i> Check Out</label>
                        <input type="date" id="check-out" name="check_out" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo $check_out; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="guests"><i class="fas fa-user"></i> Guests</label>
                        <select id="guests" name="guests">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($guests == $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> <?php echo ($i == 1) ? 'Person' : 'People'; ?>
                                </option>
                            <?php endfor; ?>
                            <option value="6" <?php echo ($guests >= 6) ? 'selected' : ''; ?>>6+ People</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Results Section -->
    <section class="results-section">
        <div class="container">
            <div class="results-header">
                <div class="results-count">
                    <span><?php echo $hostel_count; ?></span> hostels found
                    <?php if(!empty($location)): ?>
                        in <span><?php echo $location; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="sort-options">
                    <span class="sort-label">Sort by:</span>
                    <select class="sort-select" id="sort-select">
                        <option value="rating">Top Rated</option>
                        <option value="price-low">Price (Low to High)</option>
                        <option value="price-high">Price (High to Low)</option>
                        <option value="name">Name (A-Z)</option>
                    </select>
                </div>
            </div>
            
            <div class="results-layout">
                <!-- Filters Sidebar -->
                <div class="filters-sidebar">
                    <form action="search.php" method="GET" id="filter-form">
                        <!-- Hidden inputs to preserve search parameters -->
                        <input type="hidden" name="location" value="<?php echo $location; ?>">
                        <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                        <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
                        <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                        
                        <!-- Price Filter -->
                        <div class="filter-card">
                            <h3 class="filter-title"><i class="fas fa-dollar-sign"></i> Price Range</h3>
                            
                            <div class="filter-group">
                                <div class="price-range">
                                    <div class="price-input">
                                        <label class="filter-label">Min Price</label>
                                        <input type="number" name="min_price" class="filter-input" value="<?php echo $min_price; ?>" min="0" max="1000">
                                    </div>
                                    
                                    <span class="price-separator">-</span>
                                    
                                    <div class="price-input">
                                        <label class="filter-label">Max Price</label>
                                        <input type="number" name="max_price" class="filter-input" value="<?php echo $max_price; ?>" min="0" max="1000">
                                    </div>
                                </div>
                                
                                <div class="range-slider">
                                    <input type="range" class="range-slider-range" id="price-range" min="0" max="1000" step="10" value="<?php echo $min_price; ?>">
                                    <span class="range-slider-value" id="price-range-value">$<?php echo $min_price; ?></span>
                                </div>
                            </div>
                            
                            <button type="submit" class="filter-btn">Apply Filters</button>
                        </div>
                        
                        <!-- Rating Filter -->
                        <div class="filter-card">
                            <h3 class="filter-title"><i class="fas fa-star"></i> Rating</h3>
                            
                            <div class="filter-group">
                                <div class="rating-filter">
                                    <div class="rating-option">
                                        <input type="radio" id="rating-any" name="rating" value="0" <?php echo ($rating == 0) ? 'checked' : ''; ?>>
                                        <label for="rating-any">Any Rating</label>
                                    </div>
                                    
                                    <div class="rating-option">
                                        <input type="radio" id="rating-9" name="rating" value="9" <?php echo ($rating == 9) ? 'checked' : ''; ?>>
                                        <label for="rating-9">9+ Exceptional</label>
                                        <div class="rating-stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="rating-option">
                                        <input type="radio" id="rating-8" name="rating" value="8" <?php echo ($rating == 8) ? 'checked' : ''; ?>>
                                        <label for="rating-8">8+ Excellent</label>
                                        <div class="rating-stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="rating-option">
                                        <input type="radio" id="rating-7" name="rating" value="7" <?php echo ($rating == 7) ? 'checked' : ''; ?>>
                                        <label for="rating-7">7+ Very Good</label>
                                        <div class="rating-stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="rating-option">
                                        <input type="radio" id="rating-6" name="rating" value="6" <?php echo ($rating == 6) ? 'checked' : ''; ?>>
                                        <label for="rating-6">6+ Good</label>
                                        <div class="rating-stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="filter-btn">Apply Filters</button>
                        </div>
                        
                        <!-- Location Filter -->
                        <div class="filter-card">
                            <h3 class="filter-title"><i class="fas fa-map-marker-alt"></i> Location</h3>
                            
                            <div class="filter-group">
                                <select name="location" class="filter-input" id="location-select">
                                    <option value="">All Locations</option>
                                    <?php while($loc = mysqli_fetch_assoc($locationsResult)): ?>
                                        <option value="<?php echo $loc['location']; ?>" <?php echo ($location == $loc['location']) ? 'selected' : ''; ?>>
                                            <?php echo $loc['location']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="filter-btn">Apply Filters</button>
                        </div>
                        
                        <!-- Clear Filters -->
                        <button type="button" class="clear-filters" id="clear-filters">Clear All Filters</button>
                    </form>
                </div>
                
                <!-- Hostels List -->
                <div class="hostels-list">
                    <?php if($hostel_count > 0): ?>
                        <?php while($hostel = mysqli_fetch_assoc($result)): ?>
                            <div class="hostel-card">
                                <div class="hostel-img">
                                    <img src="<?php echo $hostel['image']; ?>" alt="<?php echo $hostel['name']; ?>">
                                </div>
                                <div class="hostel-details">
                                    <a href="booking.php?id=<?php echo $hostel['id']; ?>" class="hostel-name"><?php echo $hostel['name']; ?></a>
                                    <div class="hostel-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo $hostel['location']; ?></span>
                                    </div>
                                    
                                    <p class="hostel-description"><?php echo substr($hostel['description'], 0, 150) . '...'; ?></p>
                                    
                                    <div class="hostel-features">
                                        <div class="feature-tag"><i class="fas fa-wifi"></i> Free WiFi</div>
                                        <div class="feature-tag"><i class="fas fa-coffee"></i> Breakfast</div>
                                        <div class="feature-tag"><i class="fas fa-lock"></i> Lockers</div>
                                        <div class="feature-tag"><i class="fas fa-shower"></i> Hot Showers</div>
                                    </div>
                                    
                                    <div class="hostel-bottom">
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
                                        
                                        <div class="hostel-price">
                                            <div class="price-from">From</div>
                                            <div class="price-value">$<?php echo $hostel['min_room_price']; ?></div>
                                            <div class="price-night">per night</div>
                                            <a href="booking.php?id=<?php echo $hostel['id']; ?>" class="view-btn">View Rooms</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <!-- Pagination -->
                        <div class="pagination">
                            <a href="#" class="pagination-btn"><i class="fas fa-chevron-left"></i></a>
                            <a href="#" class="pagination-btn active">1</a>
                            <a href="#" class="pagination-btn">2</a>
                            <a href="#" class="pagination-btn">3</a>
                            <a href="#" class="pagination-btn"><i class="fas fa-chevron-right"></i></a>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No Hostels Found</h3>
                            <p>We couldn't find any hostels matching your search criteria. Try adjusting your filters or search for a different location.</p>
                            <a href="search.php" class="view-btn">Clear Search</a>
                        </div>
                    <?php endif; ?>
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
            
            // Set min date for check-in to today
            const today = new Date().toISOString().split('T')[0];
            checkInInput.min = today;
            
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
            
            // Price range slider
            const priceRange = document.getElementById('price-range');
            const priceRangeValue = document.getElementById('price-range-value');
            const minPriceInput = document.querySelector('input[name="min_price"]');
            
            if(priceRange && priceRangeValue && minPriceInput) {
                priceRange.addEventListener('input', function() {
                    priceRangeValue.textContent = '$' + this.value;
                    minPriceInput.value = this.value;
                });
            }
            
            // Clear filters
            const clearFiltersBtn = document.getElementById('clear-filters');
            if(clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    window.location.href = 'search.php';
                });
            }
            
            // Sort hostels
            const sortSelect = document.getElementById('sort-select');
            if(sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const hostelCards = document.querySelectorAll('.hostel-card');
                    const hostelsList = document.querySelector('.hostels-list');
                    
                    // Convert NodeList to Array for sorting
                    const hostelsArray = Array.from(hostelCards);
                    
                    // Sort based on selected option
                    switch(this.value) {
                        case 'rating':
                            hostelsArray.sort((a, b) => {
                                const ratingA = parseFloat(a.querySelector('.rating-score').textContent);
                                const ratingB = parseFloat(b.querySelector('.rating-score').textContent);
                                return ratingB - ratingA;
                            });
                            break;
                        case 'price-low':
                            hostelsArray.sort((a, b) => {
                                const priceA = parseFloat(a.querySelector('.price-value').textContent.replace('$', ''));
                                const priceB = parseFloat(b.querySelector('.price-value').textContent.replace('$', ''));
                                return priceA - priceB;
                            });
                            break;
                        case 'price-high':
                            hostelsArray.sort((a, b) => {
                                const priceA = parseFloat(a.querySelector('.price-value').textContent.replace('$', ''));
                                const priceB = parseFloat(b.querySelector('.price-value').textContent.replace('$', ''));
                                return priceB - priceA;
                            });
                            break;
                        case 'name':
                            hostelsArray.sort((a, b) => {
                                const nameA = a.querySelector('.hostel-name').textContent;
                                const nameB = b.querySelector('.hostel-name').textContent;
                                return nameA.localeCompare(nameB);
                            });
                            break;
                    }
                    
                    // Remove all hostel cards
                    hostelCards.forEach(card => card.remove());
                    
                    // Append sorted cards
                    hostelsArray.forEach(card => {
                        hostelsList.insertBefore(card, hostelsList.querySelector('.pagination'));
                    });
                });
            }
        });
    </script>
</body>
</html>
