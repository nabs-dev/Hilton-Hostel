<?php
session_start();
include 'db.php';

// Fetch featured hostels
$featuredQuery = "SELECT * FROM hostels ORDER BY rating DESC LIMIT 6";
$featuredResult = mysqli_query($conn, $featuredQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hilton Hostel - Find Your Perfect Stay</title>
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
        
        /* Hero Section */
        .hero {
            background-image: linear-gradient(rgba(0, 53, 128, 0.7), rgba(0, 53, 128, 0.7)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }
        
        .hero-content {
            max-width: 800px;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        /* Search Box */
        .search-box {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-top: -50px;
            position: relative;
            z-index: 10;
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
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .search-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
            margin-top: 24px;
            width: 100%;
        }
        
        .search-btn:hover {
            background-color: #005ea6;
        }
        
        /* Featured Hostels */
        .featured {
            padding: 80px 0 60px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary);
            font-size: 32px;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background-color: var(--accent);
            margin: 15px auto 0;
            border-radius: 2px;
        }
        
        .hostels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .hostel-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .hostel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .hostel-img {
            height: 200px;
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
        
        .hostel-info {
            padding: 20px;
        }
        
        .hostel-name {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--primary);
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
            margin-bottom: 15px;
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
        
        .hostel-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .price {
            font-size: 22px;
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
            text-decoration: none;
            display: inline-block;
        }
        
        .book-btn:hover {
            background-color: #005ea6;
        }
        
        /* Why Choose Us */
        .why-us {
            background-color: white;
            padding: 80px 0;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        
        .feature-icon {
            font-size: 48px;
            color: var(--secondary);
            margin-bottom: 20px;
        }
        
        .feature-title {
            font-size: 20px;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        /* Testimonials */
        .testimonials {
            padding: 80px 0;
            background-color: #f9f9f9;
        }
        
        .testimonial-card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin: 20px 0;
            position: relative;
        }
        
        .testimonial-card::before {
            content: '\201C';
            font-size: 80px;
            position: absolute;
            top: -10px;
            left: 20px;
            color: #f0f0f0;
            font-family: Georgia, serif;
            z-index: 0;
        }
        
        .testimonial-text {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
            font-style: italic;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .author-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .author-info h4 {
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .author-info p {
            color: var(--gray);
            font-size: 14px;
        }
        
        /* Newsletter */
        .newsletter {
            background-color: var(--primary);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .newsletter h2 {
            margin-bottom: 20px;
        }
        
        .newsletter p {
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .newsletter-input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 4px 0 0 4px;
            font-size: 16px;
        }
        
        .newsletter-btn {
            background-color: var(--accent);
            color: var(--dark);
            border: none;
            padding: 0 20px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .newsletter-btn:hover {
            background-color: #ffa500;
        }
        
        /* Footer */
        footer {
            background-color: #002855;
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .footer-col h3 {
            font-size: 18px;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-col h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background-color: var(--accent);
        }
        
        .footer-col ul {
            list-style: none;
        }
        
        .footer-col ul li {
            margin-bottom: 10px;
        }
        
        .footer-col ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-col ul li a:hover {
            color: var(--accent);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background-color: var(--accent);
            color: var(--dark);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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
            }
            
            .auth-buttons {
                margin-top: 15px;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .newsletter-form {
                flex-direction: column;
                padding: 0 20px;
            }
            
            .newsletter-input {
                border-radius: 4px;
                margin-bottom: 10px;
            }
            
            .newsletter-btn {
                border-radius: 4px;
                padding: 12px;
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
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Find Your Perfect Hostel Stay</h1>
            <p>Discover amazing hostels worldwide at unbeatable prices. Book with confidence and enjoy your journey!</p>
        </div>
    </section>
    
    <!-- Search Box -->
    <div class="container">
        <div class="search-box">
            <form action="search.php" method="GET" class="search-form">
                <div class="form-group">
                    <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                    <input type="text" id="location" name="location" placeholder="Where are you going?">
                </div>
                
                <div class="form-group">
                    <label for="check-in"><i class="fas fa-calendar-alt"></i> Check In</label>
                    <input type="date" id="check-in" name="check_in" min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="check-out"><i class="fas fa-calendar-alt"></i> Check Out</label>
                    <input type="date" id="check-out" name="check_out" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="guests"><i class="fas fa-user"></i> Guests</label>
                    <select id="guests" name="guests">
                        <option value="1">1 Person</option>
                        <option value="2">2 People</option>
                        <option value="3">3 People</option>
                        <option value="4">4 People</option>
                        <option value="5">5+ People</option>
                    </select>
                </div>
                
                <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search Hostels</button>
            </form>
        </div>
    </div>
    
    <!-- Featured Hostels -->
    <section class="featured">
        <div class="container">
            <h2 class="section-title">Featured Hostels</h2>
            
            <div class="hostels-grid">
                <?php 
                if(mysqli_num_rows($featuredResult) > 0) {
                    while($hostel = mysqli_fetch_assoc($featuredResult)) {
                ?>
                <div class="hostel-card">
                    <div class="hostel-img">
                        <img src="<?php echo $hostel['image']; ?>" alt="<?php echo $hostel['name']; ?>">
                    </div>
                    <div class="hostel-info">
                        <h3 class="hostel-name"><?php echo $hostel['name']; ?></h3>
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
                        <p><?php echo substr($hostel['description'], 0, 100) . '...'; ?></p>
                        <div class="hostel-price">
                            <div class="price">
                                $<?php echo $hostel['price_per_night']; ?> <span>/ night</span>
                            </div>
                            <a href="booking.php?id=<?php echo $hostel['id']; ?>" class="book-btn">Book Now</a>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<p>No hostels found.</p>";
                }
                ?>
            </div>
        </div>
    </section>
    
    <!-- Why Choose Us -->
    <section class="why-us">
        <div class="container">
            <h2 class="section-title">Why Choose Hilton Hostel</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <h3 class="feature-title">Best Selection</h3>
                    <p>We offer the widest selection of hostels worldwide, ensuring you find the perfect place to stay.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="feature-title">Best Price Guarantee</h3>
                    <p>Find a lower price? We'll match it and give you an additional 10% discount.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="feature-title">Secure Booking</h3>
                    <p>Your payment and personal information are always protected with our secure booking system.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p>Our customer support team is available around the clock to assist you with any questions.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Guests Say</h2>
            
            <div class="testimonial-card">
                <p class="testimonial-text">I've stayed in hostels all over Europe, and Hilton Hostel has the best booking platform by far. The real-time availability feature saved me so much time, and I found an amazing hostel in Barcelona at a great price!</p>
                <div class="testimonial-author">
                    <div class="author-img">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Sarah Johnson">
                    </div>
                    <div class="author-info">
                        <h4>Sarah Johnson</h4>
                        <p>Backpacker from USA</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <p class="testimonial-text">The booking modification feature is a lifesaver! My travel plans changed last minute, and I was able to easily adjust my reservation without any hassle or extra fees. Highly recommend Hilton Hostel for all travelers!</p>
                <div class="testimonial-author">
                    <div class="author-img">
                        <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="David Chen">
                    </div>
                    <div class="author-info">
                        <h4>David Chen</h4>
                        <p>Digital Nomad from Canada</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <h2>Subscribe to Our Newsletter</h2>
            <p>Stay updated with the latest deals, travel tips, and exclusive offers. Join our community of travelers today!</p>
            
            <form class="newsletter-form">
                <input type="email" class="newsletter-input" placeholder="Your email address" required>
                <button type="submit" class="newsletter-btn">Subscribe</button>
            </form>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-col">
                    <h3>Hilton Hostel</h3>
                    <p>Find your perfect hostel stay with us. We offer the best selection of hostels worldwide at unbeatable prices.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="search.php">Find Hostels</a></li>
                        <li><a href="#">Destinations</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Cancellation Policy</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Hostel Street, City</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 890</li>
                        <li><i class="fas fa-envelope"></i> info@hiltonhostel.com</li>
                    </ul>
                </div>
            </div>
            
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
        });
        
        // Smooth scroll for navigation links
        document.querySelectorAll('nav a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                if(href.startsWith('#')) {
                    e.preventDefault();
                    const targetElement = document.querySelector(href);
                    
                    if(targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>
