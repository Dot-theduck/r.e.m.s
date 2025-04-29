<?php
// Start the session
session_start();

// No database connection needed

// Handle login
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUsername = trim($_POST['username'] ?? '');
    $adminPassword = trim($_POST['password'] ?? '');

    // Hardcoded admin credentials
    $validUsername = 'admin@rems.com';
    $validPassword = 'admin123';

    if (!empty($adminUsername) && !empty($adminPassword)) {
        if ($adminUsername === $validUsername && $adminPassword === $validPassword) {
            $_SESSION['admin_id'] = 1; // Just a dummy id
            $_SESSION['admin_username'] = $validUsername;
            session_regenerate_id(true);
            header('Location: dashboard.php');
            exit();
        } else {
            $loginError = 'Invalid username or password.';
        }
    } else {
        $loginError = 'Please fill in all fields.';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Tenant Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
        }
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --danger-color: #f72585;
            --success-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--dark-color);
        }

        .header {
            background-color: white;
            box-shadow: var(--shadow);
            padding: 1rem 0;
            position: relative;
            z-index: 100;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--dark-color);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .logo span {
            color: var(--primary-color);
        }

        .admin-highlight {
            color: var(--danger-color);
            font-weight: 700;
        }

        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            transform: translateY(0);
            transition: var(--transition);
            animation: fadeInUp 0.5s ease;
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .login-avatar {
            width: 80px;
            height: 80px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            border: 3px solid white;
        }

        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .login-body {
            padding: 2rem;
        }

        .login-alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .login-alert.error {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .login-alert i {
            margin-right: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.2);
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-color);
            cursor: pointer;
            font-size: 1rem;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-checkbox {
            margin-right: 0.5rem;
        }

        .remember-label {
            font-size: 0.9rem;
            color: var(--gray-color);
        }

        .forgot-password {
            font-size: 0.9rem;
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .login-footer {
            padding: 1.5rem 2rem;
            background-color: #f8f9fa;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .login-footer-text {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray-color);
        }

        .login-footer-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .login-footer-link:hover {
            text-decoration: underline;
        }

        .footer {
            background-color: white;
            padding: 1.5rem 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1rem;
        }

        .footer-logo a {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark-color);
            text-decoration: none;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
        }

        .footer-link {
            color: var(--gray-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .footer-link:hover {
            color: var(--primary-color);
        }

        .footer-bottom {
            text-align: center;
            font-size: 0.8rem;
            color: var(--gray-color);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .login-container {
                max-width: 100%;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.html">
                    <i class="fas fa-building" aria-hidden="true"></i>
                    <span>Tenant</span>Portal <span class="admin-highlight">Admin</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="login-section">
        <div class="container">
            <div class="login-container">
                <div class="login-header">
                    <div class="login-avatar">
                        <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    </div>
                    <h1>Admin Login</h1>
                    <p>Secure access to system administration</p>
                </div>
                <div class="login-body">
                    <?php if ($loginError): ?>
                        <div class="login-alert error" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($loginError); ?>
                        </div>
                    <?php endif; ?>
                    <form id="loginForm" method="POST" action="login.php" novalidate>
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-input" placeholder="Enter your username" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="password-field">
                                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                                <button type="button" class="password-toggle" id="passwordToggle" aria-label="Show password">
                                    <i class="far fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="remember-forgot">
                            <div class="remember-me">
                                <input type="checkbox" id="remember" class="remember-checkbox">
                                <label for="remember" class="remember-label">Remember me</label>
                            </div>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>
                        <div class="login-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                                Login
                            </button>
                        </div>
                    </form>
                </div>
                <div class="login-footer">
                    <p class="login-footer-text">Need a tenant account? <a href="login.html" class="login-footer-link">Tenant Login</a></p>
                    <p class="login-footer-text">Are you an agent? <a href="agent-login.html" class="login-footer-link">Agent Login</a></p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <a href="index.html"><span>Tenant</span>Portal <span class="admin-highlight">Admin</span></a>
                </div>
                <div class="footer-links">
                    <a href="#" class="footer-link">About</a>
                    <a href="#" class="footer-link">Contact</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> TenantPortal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Add form validation
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
    });
    </script>
</body>
</html>