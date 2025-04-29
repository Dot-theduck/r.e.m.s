<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | REMS</title>
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
        }

        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
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

        .nav-list {
            display: flex;
            gap: 2rem;
        }

        .nav-item a {
            padding: 0.5rem 0;
            font-weight: 500;
            color: var(--gray-600);
            position: relative;
            transition: color 0.2s;
        }

        .nav-item a:hover {
            color: var(--primary);
        }

        .nav-item.active a {
            color: var(--primary);
        }

        .nav-item.active a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary);
            border-radius: 2px;
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
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.html"><i class="fas fa-building"></i><span>REMS</span></a>
            </div>
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php">Home</a></li>
                    <li class="nav-item"><a href="#">Properties</a></li>
                    <li class="nav-item active"><a href="aboutus.php">About Us</a></li>
                    <li class="nav-item"><a href="contactus.php">Contact</a></li>
                </ul>
            </nav>

            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>About REMS</h1>
            <p>Revolutionizing property management with innovative solutions and exceptional service.</p>
        </div>
    </section>

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
                        <a href="index.html"><i class="fas fa-building"></i><span>REMS</span></a>
                    </div>
                    <p>Comprehensive property management solutions for landlords and property managers.</p>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="#">Properties</a></li>
                        <li><a href="aboutus.html">About Us</a></li>
                        <li><a href="contactus.html">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Resources</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-location-dot"></i> 123 Business Ave, Suite 500<br>New York, NY 10001</li>
                        <li><i class="fas fa-phone"></i> (555) 123-4567</li>
                        <li><i class="fas fa-envelope"></i> info@rems.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 REMS. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 