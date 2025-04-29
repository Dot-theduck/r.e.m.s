<?php
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | REMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen, Ubuntu, Cantarell, \'Open Sans\', \'Helvetica Neue\', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header Styles */
        .header {
            background-color: white;
            box-shadow: var(--shadow);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        .header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo a {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo span {
            color: var(--primary);
        }

        /* Content Styles */
        .content {
            margin-top: 5rem;
            padding: 2rem 0;
        }

        .content-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            padding: 2rem;
        }

        .content-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .content-header h1 {
            font-size: 2rem;
            color: var(--gray-800);
            margin-bottom: 1rem;
        }

        .content-header p {
            color: var(--gray-600);
        }

        .section {
            margin-bottom: 2rem;
        }

        .section h2 {
            font-size: 1.5rem;
            color: var(--gray-800);
            margin-bottom: 1rem;
        }

        .section p {
            margin-bottom: 1rem;
            color: var(--gray-700);
        }

        .section ul {
            list-style-position: inside;
            margin-bottom: 1rem;
            padding-left: 1rem;
        }

        .section li {
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }

        /* Footer Styles */
        .footer {
            background-color: var(--gray-800);
            color: white;
            padding: 2rem 0;
            text-align: center;
        }

        .footer p {
            margin: 0;
        }

        /* Responsive Styles */
        @media (max-width: 640px) {
            .content-card {
                padding: 1.5rem;
            }

            .content-header h1 {
                font-size: 1.75rem;
            }

            .section h2 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.html"><i class="fas fa-building"></i><span>REMS</span></a>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="content">
        <div class="container">
            <div class="content-card">
                <div class="content-header">
                    <h1>Privacy Policy</h1>
                    <p>Last updated: January 1, 2023</p>
                </div>

                <div class="section">
                    <h2>1. Introduction</h2>
                    <p>REMS (Real Estate Management System) is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.</p>
                </div>

                <div class="section">
                    <h2>2. Information We Collect</h2>
                    <p>We collect several types of information from and about users of our service, including:</p>
                    <ul>
                        <li>Personal identification information (name, email address, phone number)</li>
                        <li>Property-related information</li>
                        <li>Payment information</li>
                        <li>Usage data and cookies</li>
                        <li>Communication preferences</li>
                    </ul>
                </div>

                <div class="section">
                    <h2>3. How We Use Your Information</h2>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide and maintain our service</li>
                        <li>Process your transactions</li>
                        <li>Send you important updates and notifications</li>
                        <li>Improve our service and user experience</li>
                        <li>Comply with legal obligations</li>
                    </ul>
                </div>

                <div class="section">
                    <h2>4. Information Sharing</h2>
                    <p>We may share your information with:</p>
                    <ul>
                        <li>Property owners and managers (as necessary for property management)</li>
                        <li>Service providers who assist in our operations</li>
                        <li>Legal authorities when required by law</li>
                    </ul>
                    <p>We do not sell your personal information to third parties.</p>
                </div>

                <div class="section">
                    <h2>5. Data Security</h2>
                    <p>We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure, and we cannot guarantee absolute security.</p>
                </div>

                <div class="section">
                    <h2>6. Your Rights</h2>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access your personal information</li>
                        <li>Correct inaccurate information</li>
                        <li>Request deletion of your information</li>
                        <li>Opt-out of marketing communications</li>
                        <li>Withdraw consent for data processing</li>
                    </ul>
                </div>

                <div class="section">
                    <h2>7. Cookies and Tracking</h2>
                    <p>We use cookies and similar tracking technologies to track activity on our service and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>
                </div>

                <div class="section">
                    <h2>8. Children\'s Privacy</h2>
                    <p>Our service is not intended for use by children under the age of 18. We do not knowingly collect personal information from children under 18.</p>
                </div>

                <div class="section">
                    <h2>9. Changes to This Policy</h2>
                    <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
                </div>

                <div class="section">
                    <h2>10. Contact Us</h2>
                    <p>If you have any questions about this Privacy Policy, please contact us at:</p>
                    <ul>
                        <li>Email: privacy@rems.com</li>
                        <li>Phone: (555) 123-4567</li>
                        <li>Address: 123 Business Street, Suite 100, City, State 12345</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2023 REMS. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 
';
?>