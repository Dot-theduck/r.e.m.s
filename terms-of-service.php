<?php
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | REMS</title>
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
                    <h1>Terms of Service</h1>
                    <p>Last updated: January 1, 2023</p>
                </div>

                <div class="section">
                    <h2>1. Agreement to Terms</h2>
                    <p>By accessing and using REMS (Real Estate Management System), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using or accessing this site.</p>
                </div>

                <div class="section">
                    <h2>2. Use License</h2>
                    <p>Permission is granted to temporarily access the materials on REMS\'s website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                    <ul>
                        <li>Modify or copy the materials</li>
                        <li>Use the materials for any commercial purpose</li>
                        <li>Attempt to decompile or reverse engineer any software contained on REMS\'s website</li>
                        <li>Remove any copyright or other proprietary notations from the materials</li>
                        <li>Transfer the materials to another person or "mirror" the materials on any other server</li>
                    </ul>
                </div>

                <div class="section">
                    <h2>3. User Accounts</h2>
                    <p>To access certain features of REMS, you may be required to create an account. You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account.</p>
                </div>

                <div class="section">
                    <h2>4. Property Management</h2>
                    <p>REMS provides tools for property management, including but not limited to:</p>
                    <ul>
                        <li>Property listing and management</li>
                        <li>Tenant communication</li>
                        <li>Rent collection</li>
                        <li>Maintenance request tracking</li>
                    </ul>
                    <p>Users are responsible for the accuracy of all information provided and must comply with all applicable laws and regulations.</p>
                </div>

                <div class="section">
                    <h2>5. Privacy</h2>
                    <p>Your use of REMS is also governed by our Privacy Policy. Please review our Privacy Policy, which also governs the Site and informs users of our data collection practices.</p>
                </div>

                <div class="section">
                    <h2>6. Disclaimer</h2>
                    <p>The materials on REMS\'s website are provided on an \'as is\' basis. REMS makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                </div>

                <div class="section">
                    <h2>7. Limitations</h2>
                    <p>In no event shall REMS or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on REMS\'s website.</p>
                </div>

                <div class="section">
                    <h2>8. Revisions and Errata</h2>
                    <p>The materials appearing on REMS\'s website could include technical, typographical, or photographic errors. REMS does not warrant that any of the materials on its website are accurate, complete, or current. REMS may make changes to the materials contained on its website at any time without notice.</p>
                </div>

                <div class="section">
                    <h2>9. Governing Law</h2>
                    <p>These terms and conditions are governed by and construed in accordance with the laws of the United States and you irrevocably submit to the exclusive jurisdiction of the courts in that location.</p>
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