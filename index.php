<?php
// Start PHP session (useful for login functionality)
session_start();

// Define page title and other dynamic variables
$pageTitle = "REMS | Real Estate Management System";
$currentYear = date("Y");

// Define color scheme variables for easier theming
$primaryColor = "#2563eb";
$secondaryColor = "#0f172a";
$accentColor = "#f59e0b";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional custom styles */
        :root {
            --primary-color: <?php echo $primaryColor; ?>;
            --secondary-color: <?php echo $secondaryColor; ?>;
            --accent-color: <?php echo $accentColor; ?>;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            position: relative;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--secondary-color);
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        /* Auth option styles */
        .auth-option {
            display: none;
        }
        
        /* Role selector styles */
        .role-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .role-option {
            flex: 1;
            padding: 20px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .role-option:hover {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
        }
        
        .role-option.selected {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.1);
        }
        
        .role-option i {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--primary-color);
            display: block;
        }
        
        .role-option h3 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }
        
        .role-option p {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .remember-forgot a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .remember-forgot a:hover {
            text-decoration: underline;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
            width: 100%;
        }
        
        .btn:hover {
            background-color: #1d4ed8;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: rgba(37, 99, 235, 0.1);
        }
        
        .social-login {
            margin-top: 20px;
        }
        
        .social-login-divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .social-login-divider::before,
        .social-login-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: #e2e8f0;
        }
        
        .social-login-divider span {
            padding: 0 10px;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .social-buttons {
            display: flex;
            gap: 10px;
        }
        
        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .social-btn:hover {
            background-color: #f8fafc;
        }
        
        .social-btn i {
            margin-right: 8px;
        }
        
        /* Back button */
        .back-button {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 0;
        }
        
        .back-button i {
            margin-right: 5px;
        }
        
        /* Animation */
        .fade-in {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.php"><i class="fas fa-building"></i><span>REMS</span></a>
            </div>
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item active"><a href="index.php">Home</a></li>
                    <li class="nav-item dropdown">
                        <a href="properties.php" class="dropdown-toggle">Properties <i class="fas fa-chevron-down"></i></a>
                     <!-- <div class="dropdown-menu">
                            <a href="properties.php"><i class="fas fa-building"></i> All Properties</a>
                            <a href="properties.php"><i class="fas fa-home"></i> Residential</a>
                            <a href="properties.php"><i class="fas fa-briefcase"></i> Commercial</a>
                            <a href="properties.php"><i class="fas fa-building-user"></i> Mixed Use</a>
                        </div> -->
                    </li>
                    <li class="nav-item"><a href="aboutus.php">About Us</a></li>
                    <li class="nav-item"><a href="contactus.php">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-user"></i> Dashboard
                    </a>
                <?php else: ?>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="tenant/register.php" class="btn signup-btn" style="background: linear-gradient(90deg, #2563eb, #0f172a); color: #fff; border-radius: 2rem; padding: 0.5rem 1.5rem; font-weight: 600; box-shadow: 0 2px 8px rgba(37,99,235,0.08); display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.2s;">
                            <i class="fas fa-user-plus"></i> 
                            <span>Sign Up</span>
                        </a>
                        <button class="login-btn" onclick="showCustomLoginModal()" style="background: #fff; color: #2563eb; border: 2px solid #2563eb; border-radius: 2rem; padding: 0.5rem 1.5rem; font-weight: 600; box-shadow: 0 2px 8px rgba(37,99,235,0.05); display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.2s, color 0.2s, border 0.2s;">
                            <i class="fas fa-sign-in-alt"></i> 
                            <span>Login</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn" aria-label="Toggle navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>



    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h1>Streamline Your Property Management</h1>
                <p>Comprehensive solutions for landlords and property managers</p>
                <div class="hero-buttons">
                    <a href="properties.php" class="btn btn-primary">List a Property</a>
                    <a href="contactus.php" class="btn btn-secondary">Talk to us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Key Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h3>Tenant Screening</h3>
                    <p>Comprehensive background checks and application processing to find reliable tenants.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <h3>Rent Collection</h3>
                    <p>Automated rent collection with multiple payment options and late fee management.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-tools"></i>
                    </div>
                    <h3>Maintenance Tracking</h3>
                    <p>Streamlined maintenance requests, vendor coordination, and repair tracking.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3>Financial Reporting</h3>
                    <p>Detailed financial reports, expense tracking, and tax document preparation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Property Showcase -->
    <section class="property-showcase">
        <div class="container">
            <h2 class="section-title">Featured Properties</h2>
            <div class="property-grid">
                <div class="property-card">
                    <div class="property-image">
                        <span class="property-status occupied">Occupied</span>
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80" alt="Luxury Apartment">
                    </div>
                    <div class="property-details">
                        <h3>Luxury Apartment</h3>
                        <p class="property-location"><i class="fa-solid fa-location-dot"></i> Downtown, New York</p>
                        <p class="property-type"><i class="fa-solid fa-building"></i> Apartment</p>
                    </div>
                </div>
                <div class="property-card">
                    <div class="property-image">
                        <span class="property-status vacant">Vacant</span>
                        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80" alt="Modern Villa">
                    </div>
                    <div class="property-details">
                        <h3>Modern Villa</h3>
                        <p class="property-location"><i class="fa-solid fa-location-dot"></i> Beverly Hills, CA</p>
                        <p class="property-type"><i class="fa-solid fa-home"></i> Single Family</p>
                    </div>
                </div>
                <div class="property-card">
                    <div class="property-image">
                        <span class="property-status occupied">Occupied</span>
                        <img src="https://images.unsplash.com/photo-1605276374104-dee2a0ed3cd6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80" alt="Office Space">
                    </div>
                    <div class="property-details">
                        <h3>Office Space</h3>
                        <p class="property-location"><i class="fa-solid fa-location-dot"></i> Financial District, Chicago</p>
                        <p class="property-type"><i class="fa-solid fa-briefcase"></i> Commercial</p>
                    </div>
                </div>
                <div class="property-card">
                    <div class="property-image">
                        <span class="property-status vacant">Vacant</span>
                        <img src="https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80" alt="Beachfront Condo">
                    </div>
                    <div class="property-details">
                        <h3>Beachfront Condo</h3>
                        <p class="property-location"><i class="fa-solid fa-location-dot"></i> Miami Beach, FL</p>
                        <p class="property-type"><i class="fa-solid fa-building"></i> Condominium</p>
                    </div>
                </div>
            </div>
            <div class="view-all">
                <a href="properties.php" class="btn btn-outline">View All Properties</a>
            </div>
        </div>
    </section>

    <!-- Statistics Dashboard -->
    <section class="statistics">
        <div class="container">
            <div class="stats-header">
                <h2 class="section-title">Statistics Dashboard</h2>
                <button id="refresh-stats" class="btn btn-refresh">
                    <i class="fa-solid fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Properties</h3>
                    <div class="stat-value" id="total-properties">128</div>
                    <div class="stat-chart">
                        <div class="chart-bar" style="height: 80%;"></div>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Occupancy Rate</h3>
                    <div class="stat-value" id="occupancy-rate">92%</div>
                    <div class="stat-chart">
                        <div class="chart-circle">
                            <svg viewBox="0 0 36 36">
                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="circle" stroke-dasharray="92, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Monthly Revenue</h3>
                    <div class="stat-value" id="monthly-revenue">$245,800</div>
                    <div class="stat-chart">
                        <div class="chart-line">
                            <svg viewBox="0 0 100 30">
                                <polyline points="0,20 20,15 40,18 60,10 80,12 100,5" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Clients Say</h2>
            <div class="testimonial-carousel">
                <div class="testimonial-track" id="testimonial-track">
                    <div class="testimonial-slide">
                        <div class="testimonial-content">
                            <p>"REMS has revolutionized how we handle our rental properties. The automated rent collection alone has saved us countless hours each month."</p>
                            <div class="testimonial-author">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Smith">
                                <div>
                                    <h4>John Smith</h4>
                                    <p>Property Owner, 15 units</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-slide">
                        <div class="testimonial-content">
                            <p>"The maintenance tracking feature has been a game-changer for our team. We can now respond to tenant requests faster and keep better records."</p>
                            <div class="testimonial-author">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson">
                                <div>
                                    <h4>Sarah Johnson</h4>
                                    <p>Property Manager, Skyline Properties</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-slide">
                        <div class="testimonial-content">
                            <p>"The financial reporting tools have made tax season so much easier. I can generate all the reports I need with just a few clicks."</p>
                            <div class="testimonial-author">
                                <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Michael Chen">
                                <div>
                                    <h4>Michael Chen</h4>
                                    <p>Real Estate Investor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-nav">
                    <button class="nav-dot active" data-index="0"></button>
                    <button class="nav-dot" data-index="1"></button>
                    <button class="nav-dot" data-index="2"></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <a href="index.php"><i class="fas fa-building"></i><span>REMS</span></a>
                    </div>
                    <p>Comprehensive property management solutions for landlords and property managers.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="properties.php">Properties</a></li>
                        <li><a href="aboutus.php">About Us</a></li>
                        <li><a href="contactus.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Resources</h3>
                    <ul class="footer-links">
                        <li><a href="terms-of-service.php">Terms of Service</a></li>
                        <li><a href="privacy-policy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul class="contact-info">
                        <li><i class="fa-solid fa-location-dot"></i> 123 Business Ave, Suite 500<br>New York, NY 10001</li>
                        <li><i class="fa-solid fa-phone"></i> (555) 123-4567</li>
                        <li><i class="fa-solid fa-envelope"></i> info@rems.com</li>
                        
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo $currentYear; ?> REMS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Custom Login Modal -->
    <div class="modal" id="customLoginModal">
        <div class="modal-content" style="max-width:350px;">
            <div class="modal-header">
                <h2>Login as...</h2>
                <button class="close-modal" onclick="closeCustomLoginModal()">&times;</button>
            </div>
            <div class="modal-body" style="display:flex; flex-direction:column; gap:20px;">
                <a href="tenant/login.php" class="btn">Tenant</a>
                <a href="agent/agent-login.php" class="btn btn-outline">Agent</a>
                <a href="admin/login.php" style="text-decoration: underline;">as Admin</a>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/carousel.js"></script>
    <script src="js/chart.js"></script>
    <script>
        // Auth modal functionality
        const authModal = document.getElementById('authModal');
        const loginOptionsSection = document.getElementById('loginOptionsSection');
        const loginFormSection = document.getElementById('loginFormSection');
        const signupSection = document.getElementById('signupSection');
        const roleTypeText = document.getElementById('roleTypeText');
        
        // Show login options (tenant vs agent selection)
        function showLoginOptions() {
            authModal.style.display = 'flex';
            hideAllAuthSections();
            loginOptionsSection.style.display = 'block';
            loginOptionsSection.classList.add('fade-in');
        }
        
        // Show signup form
        function showSignupModal() {
            authModal.style.display = 'flex';
            hideAllAuthSections();
            signupSection.style.display = 'block';
            signupSection.classList.add('fade-in');
        }
        
        // Hide all auth sections
        function hideAllAuthSections() {
            loginOptionsSection.style.display = 'none';
            loginFormSection.style.display = 'none';
            signupSection.style.display = 'none';
            
            loginOptionsSection.classList.remove('fade-in');
            loginFormSection.classList.remove('fade-in');
            signupSection.classList.remove('fade-in');
        }
        
        // Close auth modal
        function closeAuthModal() {
            authModal.style.display = 'none';
        }
        
        // Select role (tenant or agent) and proceed to login form
        function selectRole(role) {
            document.getElementById('user-role').value = role;
            roleTypeText.textContent = role.charAt(0).toUpperCase() + role.slice(1);
            
            hideAllAuthSections();
            loginFormSection.style.display = 'block';
            loginFormSection.classList.add('fade-in');
        }
        
        // Select role for signup
        function selectSignupRole(role) {
            document.getElementById('signup-role').value = role;
            
            // Update UI to show selected role
            document.querySelectorAll('#signupSection .role-option').forEach(option => {
                option.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }
        
        // Go back to login options from login form
        function backToLoginOptions() {
            hideAllAuthSections();
            loginOptionsSection.style.display = 'block';
            loginOptionsSection.classList.add('fade-in');
        }
        
        // Switch from login to signup
        function switchToSignup() {
            hideAllAuthSections();
            signupSection.style.display = 'block';
            signupSection.classList.add('fade-in');
        }
        
        // Switch from signup to login
        function switchToLogin() {
            hideAllAuthSections();
            loginOptionsSection.style.display = 'block';
            loginOptionsSection.classList.add('fade-in');
        }
        
        // Toggle password visibility for login
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            togglePasswordVisibility(passwordInput, this);
        });
        
        // Toggle password visibility for signup
        document.getElementById('toggleSignupPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('signup-password');
            togglePasswordVisibility(passwordInput, this);
        });
        
        // Toggle password visibility for confirm password
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('signup-confirm-password');
            togglePasswordVisibility(passwordInput, this);
        });
        
        // Helper function to toggle password visibility
        function togglePasswordVisibility(input, icon) {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === authModal) {
                closeAuthModal();
            }
        }
        
        // Testimonial carousel navigation
        document.querySelectorAll('.nav-dot').forEach(dot => {
            dot.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                const track = document.getElementById('testimonial-track');
                
                // Update active dot
                document.querySelectorAll('.nav-dot').forEach(d => {
                    d.classList.remove('active');
                });
                this.classList.add('active');
                
                // Move track to show selected testimonial
                track.style.transform = `translateX(-${index * 100}%)`;
            });
        });

        // Custom login modal logic
        function showCustomLoginModal() {
            document.getElementById('customLoginModal').style.display = 'flex';
        }
        function closeCustomLoginModal() {
            document.getElementById('customLoginModal').style.display = 'none';
        }
        // Optional: close modal when clicking outside
        window.addEventListener('click', function(event) {
            var modal = document.getElementById('customLoginModal');
            if (event.target === modal) {
                closeCustomLoginModal();
            }
        });
    </script>
</body>
</html>