<?php
require_once 'config.php';

$email = $new_password = $confirm_password = "";
$email_err = $new_password_err = $confirm_password_err = $reset_err = $reset_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your registered email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";     
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // Process the form
    if (empty($email_err) && empty($new_password_err) && empty($confirm_password_err)) {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    // Email exists, update password
                    $update_sql = "UPDATE users SET password = ? WHERE email = ?";
                    if ($update_stmt = $mysqli->prepare($update_sql)) {
                        $update_stmt->bind_param("ss", $param_password, $param_email_update);
                        
                        $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $param_email_update = $email;

                        if ($update_stmt->execute()) {
                            $reset_success = "Password reset successfully. You can now <a href='login.php' class='text-blue-600 hover:text-blue-800 font-medium'>login</a>.";
                        } else {
                            $reset_err = "Something went wrong. Please try again later.";
                        }
                        $update_stmt->close();
                    }
                } else {
                    $reset_err = "No account found with that email.";
                }
            } else {
                $reset_err = "Oops! Something went wrong. Please try again later.";
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
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 py-6 px-6">
            <h2 class="text-2xl font-bold text-white text-center">Reset Password</h2>
            <p class="text-purple-100 text-center mt-2">Enter your email and create a new password</p>
        </div>
        
        <div class="p-6 sm:p-8">
            <?php if (!empty($reset_err)): ?>
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                    <p class="font-medium"><i class="fas fa-exclamation-circle mr-2"></i> Error</p>
                    <p><?php echo $reset_err; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($reset_success)): ?>
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                    <p class="font-medium"><i class="fas fa-check-circle mr-2"></i> Success</p>
                    <p><?php echo $reset_success; ?></p>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" 
                               class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 border <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?>" 
                               placeholder="your@email.com">
                    </div>
                    <?php if (!empty($email_err)): ?>
                        <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?php echo $email_err; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="new_password" id="new_password" 
                               class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 border <?php echo (!empty($new_password_err)) ? 'border-red-500' : ''; ?>" 
                               placeholder="••••••••">
                    </div>
                    <?php if (!empty($new_password_err)): ?>
                        <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?php echo $new_password_err; ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-gray-500">Password must be at least 6 characters</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="confirm_password" id="confirm_password" 
                               class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3 border <?php echo (!empty($confirm_password_err)) ? 'border-red-500' : ''; ?>" 
                               placeholder="••••••••">
                    </div>
                    <?php if (!empty($confirm_password_err)): ?>
                        <p class="mt-1 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?php echo $confirm_password_err; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        <i class="fas fa-key mr-2"></i> Reset Password
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Remember your password? 
                    <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-150">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>