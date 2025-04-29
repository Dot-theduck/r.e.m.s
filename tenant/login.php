<?php
session_start();
require_once 'config.php';

$email = $password = "";
$email_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id, first_name, last_name, email, password, user_type FROM users WHERE email = ?";

        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                // Check if email exists
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password, $user_type);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["first_name"] = $first_name;
                            $_SESSION["last_name"] = $last_name;
                            $_SESSION["email"] = $email;
                            $_SESSION["user_type"] = $user_type;

                            // Redirect user to dashboard
                            header("location: dashboard.php");
                            exit;
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    $login_err = "No account found with that email.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
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
    <title>Tenant Login | REMS</title>
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

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 450px;
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

        .login-header {
            background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-dark));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .login-header::after {
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

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            font-size: 1rem;
            opacity: 0.9;
        }

        .login-logo {
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

        .login-form {
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

        .login-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 4px solid var(--danger);
        }

        .form-hint {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input {
            width: 1rem;
            height: 1rem;
        }

        .forgot-password {
            color: var(--tenant-primary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
            color: var(--tenant-primary-dark);
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

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray-600);
        }

        .register-link a {
            color: var(--tenant-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
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

        /* Responsive Styles */
        @media (max-width: 576px) {
            .login-container {
                margin: 1rem;
                max-width: none;
            }

            .login-header {
                padding: 1.5rem;
            }

            .login-form {
                padding: 1.5rem;
            }

            .form-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <div class="logo-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to access your tenant portal</p>
        </div>
        
        <div class="login-form">
            <?php if (!empty($login_err)): ?>
                <div class="login-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $login_err; ?></span>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                        <input type="password" id="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'error' : ''; ?>" placeholder="Enter your password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($password_err)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $password_err; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-footer">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </div>

                <div class="register-link">
                    Don't have an account? <a href="register.php">Register here</a>
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
    </script>
</body>
</html>