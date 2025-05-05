<?php
require_once './tenant/config.php'; // Loads $mysqli

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST["name"]);
    $email   = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);

    // Insert into database using $mysqli
    $stmt = $mysqli->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $name, $email, $message);
        $stmt->execute();
        $stmt->close();
        $success = "Thank you! Your message has been sent.";
    } else {
        $error = "Error: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4ade80;
            --border: #e9ecef;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f6f8fb 0%, #e9f1f9 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 550px;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            color: var(--dark);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #6c757d;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
        }

        input, textarea {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: var(--light);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 10px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.2);
        }

        button:active {
            transform: translateY(0);
        }

        .message {
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .success {
            background-color: rgba(74, 222, 128, 0.15);
            color: #0a7a3f;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }

        .error {
            background-color: rgba(248, 113, 113, 0.15);
            color: #b91c1c;
            border: 1px solid rgba(248, 113, 113, 0.3);
        }

        .required {
            color: #e63946;
            margin-left: 4px;
        }

        @media (max-width: 576px) {
            .container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Contact Us</h2>
            <p class="subtitle">We'd love to hear from you. Send us a message!</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Your Name <span class="required">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" id="name" name="name" placeholder="Enter your name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Your Email <span class="required">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="message">Your Message <span class="required">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-comment-alt" style="top: 24px; transform: none;"></i>
                    <textarea id="message" name="message" placeholder="Type your message here..." required></textarea>
                </div>
            </div>

            <button type="submit">
                <i class="fas fa-paper-plane"></i>
                Send Message
            </button>
        </form>
    </div>
</body>
</html>