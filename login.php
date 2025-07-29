<?php
session_start();
include 'db.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$error = '';

// Process login form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if(empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        // Check if user exists
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password
            if(password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Redirect to dashboard
                echo "<script>window.location.href = 'dashboard.php';</script>";
                exit;
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hilton Hostel</title>
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
        
        /* Login Form */
        .login-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 0;
        }
        
        .login-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: var(--gray);
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .login-form .input-group {
            position: relative;
        }
        
        .login-form .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .login-form input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: var(--secondary);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
            width: auto;
        }
        
        .forgot-password {
            color: var(--secondary);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .forgot-password:hover {
            color: var(--primary);
            text-decoration: underline;
        }
        
        .login-btn {
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
        
        .login-btn:hover {
            background-color: #005ea6;
        }
        
        .login-divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
        }
        
        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #ddd;
        }
        
        .login-divider span {
            padding: 0 15px;
            color: var(--gray);
            font-size: 14px;
        }
        
        .social-login {
            display: flex;
            gap: 10px;
        }
        
        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            color: var(--dark);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-btn i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .social-btn.facebook {
            color: #3b5998;
        }
        
        .social-btn.google {
            color: #db4437;
        }
        
        .social-btn:hover {
            background-color: #f5f5f5;
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            color: var(--gray);
        }
        
        .register-link a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .register-link a:hover {
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
            
            .login-container {
                padding: 30px 20px;
            }
            
            .social-login {
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
                    <li><a href="#"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="#"><i class="fas fa-phone"></i> Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Login Section -->
    <section class="login-section">
        <div class="container">
            <div class="login-container">
                <div class="login-header">
                    <h1>Welcome Back</h1>
                    <p>Sign in to access your account</p>
                </div>
                
                <?php if(!empty($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form class="login-form" method="POST" action="">
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
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="login-btn">Sign In</button>
                </form>
                
                <div class="login-divider">
                    <span>OR</span>
                </div>
                
                <div class="social-login">
                    <a href="#" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="#" class="social-btn google">
                        <i class="fab fa-google"></i> Google
                    </a>
                </div>
                
                <div class="register-link">
                    Don't have an account? <a href="signup.php">Sign Up</a>
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
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
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
        });
    </script>
</body>
</html>
