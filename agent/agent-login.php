<?php
// Start PHP session
session_start();

// Define page title
$pageTitle = "Agent Login | REMS";

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    if ($email === "johndoe@rems.com" && $password === "John123") {
        $_SESSION['agent_email'] = $email;

        if ($remember) {
            setcookie('agent_remember', 'johndoe', time() + (86400 * 30), "/");
        }

        header("Location: agent-johndoe/dashboard.php");
        exit();
    } elseif ($email === "maryjane@rems.com" && $password === "mary123") {
        $_SESSION['agent_email'] = $email;

        if ($remember) {
            setcookie('agent_remember', 'maryjane', time() + (86400 * 30), "/");
        }

        header("Location: agent-maryjane/dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
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
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }

        /* Header Styles */
        .header {
            background-color: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }

        .logo a {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo span {
            color: var(--primary);
        }

        /* Login Styles */
        .login-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
            width: 100%;
            min-height: calc(100vh - 70px);
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin: 0 auto;
            position: relative;
            left: 0;
            right: 0;
        }

        .login-header {
            background-color: var(--primary-light);
            padding: 2rem;
            text-align: center;
            color: var(--primary-dark);
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            font-size: 0.875rem;
            color: var(--primary-dark);
        }

        .login-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: var(--shadow);
        }

        .login-avatar i {
            font-size: 2.5rem;
            color: var(--primary);
        }

        .login-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input.is-invalid {
            border-color: var(--danger);
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--gray-700);
        }

        .error-message {
            display: <?php echo isset($error) ? 'block' : 'none'; ?>;
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 0.5rem;
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
            gap: 0.5rem;
        }

        .remember-checkbox {
            width: 1rem;
            height: 1rem;
            border-radius: 0.25rem;
            border: 1px solid var(--gray-400);
            appearance: none;
            cursor: pointer;
            position: relative;
        }

        .remember-checkbox:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .remember-checkbox:checked::after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 0.75rem;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .forgot-password {
            color: var(--primary);
            font-size: 0.875rem;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            width: 100%;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background-color: var(--gray-300);
        }

        .login-footer {
            text-align: center;
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .login-footer p {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .login-footer a {
            color: var(--primary);
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Styles */
        @media (max-width: 640px) {
            .login-container {
                margin: 1rem;
            }

            .login-header {
                padding: 1.5rem;
            }

            .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="../index.php"><i class="fas fa-building"></i><span>REMS</span></a>
            </div>
        </div>
    </header>

    <!-- Login Section -->
    <section class="login-section">
        <div class="login-container">
            <div class="login-header">
                <div class="login-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h1>Agent Login</h1>
                <p>Access your agent dashboard</p>
            </div>
            <div class="login-body">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-input" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="remember-forgot">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" class="remember-checkbox">
                            <span>Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
            <div class="login-footer">
                <p>Don't have an account? <a href="../register.php">Register as an Agent</a></p>
            </div>
        </div>
    </section>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.classList.remove('fa-eye');
                toggleButton.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleButton.classList.remove('fa-eye-slash');
                toggleButton.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html> 