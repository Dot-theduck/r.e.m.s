<?php
require_once 'config.php';

$first_name = $last_name = $email = $password = $confirm_password = "";
$first_name_err = $last_name_err = $email_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm your password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }

    // Check input errors before inserting into database
    if (empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (first_name, last_name, email, password, user_type) VALUES (?, ?, ?, ?, 'tenant')";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssss", $param_first_name, $param_last_name, $param_email, $param_password);

            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Secure password hashing

            if ($stmt->execute()) {
                // Redirect to login page
                header("location: login.php");
                exit;
            } else {
                echo "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }

    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Registration | REMS</title>
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
            --tenant-primary: #8b5cf6;
            --tenant-primary-dark: #7c3aed;
            --tenant-primary-light: #c4b5fd;
            --border-radius: 0.5rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            color: var(--gray-800);
            background-color: var(--gray-100);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-image: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.1)), 
                              url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Registration Container */
        .registration-container {
            width: 100%;
            max-width: 500px;
            margin: 2rem auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .registration-header {
            background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-dark));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .registration-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 5px;
            background: white;
            border-radius: 5px;
        }

        .registration-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .registration-subtitle {
            font-size: 1rem;
            opacity: 0.9;
        }

        .registration-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1rem;
        }

        .logo-icon {
            width: 3.5rem;
            height: 3.5rem;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .registration-form {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 1rem;
            color: var(--gray-800);
            transition: all 0.3s ease;
            background-color: var(--gray-50);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--tenant-primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            background-color: white;
        }

        .form-control.error {
            border-color: var(--danger);
        }

        .error-message {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .form-hint {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        .password-strength {
            height: 4px;
            background-color: var(--gray-200);
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }

        .strength-weak {
            width: 33%;
            background-color: var(--danger);
        }

        .strength-medium {
            width: 66%;
            background-color: var(--warning);
        }

        .strength-strong {
            width: 100%;
            background-color: var(--success);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-dark));
            color: white;
            box-shadow: 0 2px 4px rgba(139, 92, 246, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--tenant-primary-dark), var(--tenant-primary-dark));
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(139, 92, 246, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray-600);
        }

        .login-link a {
            color: var(--tenant-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
            color: var(--tenant-primary-dark);
        }

        .form-divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--gray-500);
        }

        .form-divider::before,
        .form-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: var(--gray-300);
        }

        .form-divider span {
            padding: 0 1rem;
            font-size: 0.875rem;
        }

        /* Responsive Styles */
        @media (max-width: 576px) {
            .registration-container {
                margin: 1rem;
                max-width: none;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .registration-header {
                padding: 1.5rem;
            }

            .registration-form {
                padding: 1.5rem;
            }
        }

        /* Password visibility toggle */
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
            color: var(--gray-500);
            cursor: pointer;
            font-size: 1rem;
        }

        .password-toggle:hover {
            color: var(--tenant-primary);
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <div class="registration-logo">
                <div class="logo-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            <h1 class="registration-title">Tenant Registration</h1>
            <p class="registration-subtitle">Create your account to access the tenant portal</p>
        </div>
        
        <div class="registration-form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>" placeholder="Enter your first name">
                        <?php if (!empty($first_name_err)): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $first_name_err; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>" placeholder="Enter your last name">
                        <?php if (!empty($last_name_err)): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $last_name_err; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email address">
                    <?php if (!empty($email_err)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $email_err; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($password); ?>" placeholder="Create a secure password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($password_err)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $password_err; ?>
                        </div>
                    <?php else: ?>
                        <div class="form-hint">Password must be at least 6 characters long</div>
                        <div class="password-strength">
                            <div class="password-strength-meter" id="passwordStrength"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($confirm_password); ?>" placeholder="Confirm your password">
                        <button type="button" class="password-toggle" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($confirm_password_err)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $confirm_password_err; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </div>

                <div class="login-link">
                    Already have an account? <a href="login.php">Log in here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('passwordStrength');
            
            // Remove all classes
            strengthMeter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
            
            if (password.length === 0) {
                strengthMeter.style.width = '0';
                return;
            }
            
            // Simple password strength calculation
            let strength = 0;
            
            // Length check
            if (password.length >= 6) strength += 1;
            if (password.length >= 10) strength += 1;
            
            // Character variety check
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Set the appropriate class based on strength
            if (strength <= 2) {
                strengthMeter.classList.add('strength-weak');
            } else if (strength <= 4) {
                strengthMeter.classList.add('strength-medium');
            } else {
                strengthMeter.classList.add('strength-strong');
            }
        });
    </script>
</body>
</html>