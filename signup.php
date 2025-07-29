<?php
session_start();
include 'db.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$error = '';
$success = '';

// Process signup form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif($password != $confirm_password) {
        $error = "Passwords do not match";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if email already exists
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) > 0) {
            $error = "Email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $query = "INSERT INTO users (username, email, password, created_at) VALUES ('$username', '$email', '$hashed_password', NOW())";
            
            if(mysqli_query($conn, $query)) {
                $success = "Account created successfully! You can now login.";
                
                // Redirect to login page after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
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
    <title>Sign Up - Hilton Hostel</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        
        /* Signup Form */
        .signup-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 0;
        }
        
        .signup-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .signup-header h1 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .signup-header p {
            color: var(--gray);
        }
        
        .signup-form .form-group {
            margin-bottom: 20px;
        }
        
        .signup-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .signup-form .input-group {
            position: relative;
        }
        
        .signup-form .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .signup-form input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .signup-form input:focus {
            outline: none;
            border-color: var(--secondary);
        }
        
        .password-requirements {
            margin-top: 8px;
            font-size: 12px;
            color: var(--gray);
        }
        
        .terms {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .terms input {
            margin-right: 10px;
            margin-top: 5px;
            width: auto;
        }
        
        .terms label {
            font-size: 14px;
            color: var(--gray);
        }
        
        .terms label a {
            color: var(--secondary);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .terms label a:hover {
            color: var(--primary);
            text-decoration: underline;
        }
        
        .signup-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .signup-btn:hover {
            background-color: #005ea6;
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: var(--gray);
        }
        
        .login-link a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .login-link a:hover {
            color: var(--primary);
            text-decoration: underline;
        }
        
        .error-message {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            background-color: rgba(0, 128, 9, 0.1);
            color: var(--success);
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Password Strength Meter */
        .password-strength {
            margin-top: 8px;
            height: 5px;
            background-color: #eee;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .password-strength-text {
            font-size: 12px;
            margin-top: 5px;
            text-align: right;
        }
        
        /* Footer */
        footer {
            background-color: #002855;
            color: white;
            padding: 20px 0;
            margin-top: auto;
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
            
            .signup-container {
                padding: 30px 20px;
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
                    <li><a href="#"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="#"><i class="fas fa-phone"></i> Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Signup Section -->
    <section class="signup-section">
        <div class="container">
            <div class="signup-container">
                <div class="signup-header">
                    <h1>Create an Account</h1>
                    <p>Join Hilton Hostel and start booking your perfect stay</p>
                </div>
                
                <?php if(!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($success)): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <form class="signup-form" method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" placeholder="Choose a username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Create a password" required>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-meter" id="password-meter"></div>
                        </div>
                        <div class="password-strength-text" id="password-text"></div>
                        <div class="password-requirements">
                            Password must be at least 6 characters long
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        </div>
                    </div>
                    
                    <div class="terms">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="signup-btn">Create Account</button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign In</a>
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
        // Password strength meter
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordMeter = document.getElementById('password-meter');
            const passwordText = document.getElementById('password-text');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Calculate password strength
                if(password.length >= 6) {
                    strength += 25;
                }
                
                if(password.match(/[A-Z]/)) {
                    strength += 25;
                }
                
                if(password.match(/[0-9]/)) {
                    strength += 25;
                }
                
                if(password.match(/[^A-Za-z0-9]/)) {
                    strength += 25;
                }
                
                // Update password meter
                passwordMeter.style.width = strength + '%';
                
                // Set color based on strength
                if(strength <= 25) {
                    passwordMeter.style.backgroundColor = '#e74c3c';
                    passwordText.textContent = 'Weak';
                    passwordText.style.color = '#e74c3c';
                } else if(strength <= 50) {
                    passwordMeter.style.backgroundColor = '#f39c12';
                    passwordText.textContent = 'Fair';
                    passwordText.style.color = '#f39c12';
                } else if(strength <= 75) {
                    passwordMeter.style.backgroundColor = '#3498db';
                    passwordText.textContent = 'Good';
                    passwordText.style.color = '#3498db';
                } else {
                    passwordMeter.style.backgroundColor = '#27ae60';
                    passwordText.textContent = 'Strong';
                    passwordText.style.color = '#27ae60';
                }
            });
            
            // Check if passwords match
            confirmPasswordInput.addEventListener('input', function() {
                if(this.value !== passwordInput.value) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#27ae60';
                }
            });
            
            // Toggle password visibility
            const togglePassword = document.createElement('i');
            togglePassword.className = 'fas fa-eye';
            togglePassword.style.position = 'absolute';
            togglePassword.style.right = '15px';
            togglePassword.style.top = '50%';
            togglePassword.style.transform = 'translateY(-50%)';
            togglePassword.style.cursor = 'pointer';
            togglePassword.style.color = 'var(--gray)';
            
            passwordInput.parentElement.appendChild(togglePassword);
            
            togglePassword.addEventListener('click', function() {
                if(passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    togglePassword.className = 'fas fa-eye-slash';
                } else {
                    passwordInput.type = 'password';
                    togglePassword.className = 'fas fa-eye';
                }
            });
            
            // Toggle confirm password visibility
            const toggleConfirmPassword = document.createElement('i');
            toggleConfirmPassword.className = 'fas fa-eye';
            toggleConfirmPassword.style.position = 'absolute';
            toggleConfirmPassword.style.right = '15px';
            toggleConfirmPassword.style.top = '50%';
            toggleConfirmPassword.style.transform = 'translateY(-50%)';
            toggleConfirmPassword.style.cursor = 'pointer';
            toggleConfirmPassword.style.color = 'var(--gray)';
            
            confirmPasswordInput.parentElement.appendChild(toggleConfirmPassword);
            
            toggleConfirmPassword.addEventListener('click', function() {
                if(confirmPasswordInput.type === 'password') {
                    confirmPasswordInput.type = 'text';
                    toggleConfirmPassword.className = 'fas fa-eye-slash';
                } else {
                    confirmPasswordInput.type = 'password';
                    toggleConfirmPassword.className = 'fas fa-eye';
                }
            });
        });
    </script>
</body>
</html>
