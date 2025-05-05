<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | REMS</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --border-radius: 0.5rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            color: var(--gray-800);
            background-color: var(--gray-100);
            line-height: 1.5;
        }

        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
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
        

        /* About Page Styles */
        .about-hero {
            background-color: var(--primary);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .about-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .about-hero p {
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .about-content {
            padding: 4rem 0;
        }

        .about-section {
            margin-bottom: 4rem;
        }

        .about-section h2 {
            font-size: 2rem;
            color: var(--gray-800);
            margin-bottom: 2rem;
        }

        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .about-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .about-card h3 {
            font-size: 1.5rem;
            color: var(--gray-800);
            margin-bottom: 1rem;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .team-member {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .team-member img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .team-member-info {
            padding: 1.5rem;
        }

        .team-member-info h3 {
            font-size: 1.25rem;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .team-member-info p {
            color: var(--gray-600);
        }

        /* Footer Styles */
        .footer {
            background-color: var(--gray-800);
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-logo a {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .footer-logo span {
            color: var(--primary);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: var(--gray-400);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-700);
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
    <section class="about-hero">
        <div class="container">
            <h1>About REMS</h1>
            <p>Revolutionizing property management with innovative solutions and exceptional service.</p>
        </div>
    </section>
                     <!-- Auth Modal -->
    <div class="modal" id="authModal">
        <div class="modal-content">
            <!-- Login Options Section -->
            <div class="auth-option" id="loginOptionsSection">
                <div class="modal-header">
                    <h2>Login to REMS</h2>
                    <button class="close-modal" onclick="closeAuthModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Please select your account type:</p>
                    <div class="role-selector">
                        <div class="role-option" onclick="selectRole('tenant')">
                            <i class="fas fa-user"></i>
                            <h3>Tenant</h3>
                            <p>Access your rental information</p>
                        </div>
                        <div class="role-option" onclick="selectRole('agent')">
                            <i class="fas fa-id-badge"></i>
                            <h3>Agent</h3>
                            <p>Manage properties and clients</p>
                        </div>
                        <div class="role-option" onclick="selectRole('admin')">
                            <i class="fas fa-id-badge"></i>
                            <h3>as Admin</h3>
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login Form Section -->
            <div class="auth-option" id="loginFormSection">
                <div class="modal-header">
                    <h2>Login as <span id="roleTypeText">Tenant</span></h2>
                    <button class="close-modal" onclick="closeAuthModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <button class="back-button" onclick="backToLoginOptions()">
                        <i class="fas fa-arrow-left"></i> Back to options
                    </button>
                    <form action="login.php" method="POST">
                        <input type="hidden" id="user-role" name="role" value="tenant">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-toggle">
                                <input type="password" id="password" name="password" required>
                                <i class="fas fa-eye" id="togglePassword"></i>
                            </div>
                        </div>
                        <div class="form-group remember-forgot">
                            <div>
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember" style="display: inline;">Remember me</label>
                            </div>
                            <a href="forgot-password.php">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn">Login</button>
                    </form>
                    <div class="social-login">
                        <div class="social-login-divider">
                            <span>Or continue with</span>
                        </div>
                        <div class="social-buttons">
                            <button class="social-btn">
                                <i class="fab fa-google"></i> Google
                            </button>
                            <button class="social-btn">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <p>Don't have an account? <a href="#" onclick="switchToSignup()">Sign up</a></p>
                </div>
            </div>

            <!-- Sign Up Section -->
            <div class="auth-option" id="signupSection">
                <div class="modal-header">
                    <h2>Create an Account</h2>
                    <button class="close-modal" onclick="closeAuthModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="register.php" method="POST">
                        <div class="form-group">
                            <label for="signup-name">Full Name</label>
                            <input type="text" id="signup-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="signup-email">Email</label>
                            <input type="email" id="signup-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="signup-password">Password</label>
                            <div class="password-toggle">
                                <input type="password" id="signup-password" name="password" required>
                                <i class="fas fa-eye" id="toggleSignupPassword"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="signup-confirm-password">Confirm Password</label>
                            <div class="password-toggle">
                                <input type="password" id="signup-confirm-password" name="confirm_password" required>
                                <i class="fas fa-eye" id="toggleConfirmPassword"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Account Type</label>
                            <div class="role-selector">
                                <div class="role-option selected" onclick="selectSignupRole('tenant')">
                                    <i class="fas fa-user"></i>
                                    <h3>Tenant</h3>
                                    <p>Access your rental information</p>
                                </div>
                                <div class="role-option" onclick="selectSignupRole('agent')">
                                    <i class="fas fa-id-badge"></i>
                                    <h3>Agent</h3>
                                    <p>Manage properties and clients</p>
                                </div>
                            </div>
                            <input type="hidden" id="signup-role" name="role" value="tenant">
                        </div>
                        <div class="form-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms" style="display: inline;">I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a></label>
                        </div>
                        <button type="submit" class="btn">Create Account</button>
                    </form>
                    <div class="social-login">
                        <div class="social-login-divider">
                            <span>Or sign up with</span>
                        </div>
                        <div class="social-buttons">
                            <button class="social-btn">
                                <i class="fab fa-google"></i> Google
                            </button>
                            <button class="social-btn">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <p>Already have an account? <a href="#" onclick="switchToLogin()">Login</a></p>
                </div>
            </div>
        </div>
    </div>
    <!-- About Content -->
    <main class="about-content">
        <div class="container">
            <section class="about-section">
                <h2>Our Story</h2>
                <div class="about-grid">
                    <div class="about-card">
                        <h3>Our Mission</h3>
                        <p>To provide comprehensive property management solutions that simplify the lives of property owners and tenants while maximizing property value and ensuring satisfaction.</p>
                    </div>
                    <div class="about-card">
                        <h3>Our Vision</h3>
                        <p>To be the leading property management platform, setting new standards in efficiency, transparency, and customer service in the real estate industry.</p>
                    </div>
                    <div class="about-card">
                        <h3>Our Values</h3>
                        <p>Integrity, innovation, and excellence guide everything we do. We're committed to building lasting relationships and delivering exceptional results.</p>
                    </div>
                </div>
            </section>

            <section class="about-section">
                <h2>Our Team</h2>
                <div class="team-grid">
                    <div class="team-member">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Doe">
                        <div class="team-member-info">
                            <h3>John Doe</h3>
                            <p>Senior Property Manager</p>
                        </div>
                    </div>
                    <div class="team-member">
                        <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Mary Jane">
                        <div class="team-member-info">
                            <h3>Mary Jane</h3>
                            <p>Property Manager</p>
                        </div>
                    </div>
                    <div class="team-member">
                        <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="Michael Chen">
                        <div class="team-member-info">
                            <h3>Michael Chen</h3>
                            <p>Technical Lead</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

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