<?php
// Include the database connection
require_once '../../tenant/config.php'; // adjust path if necessary

// Fetch properties where landlord_id = 2
$sql = "SELECT * FROM properties WHERE landlord_id = 2";
$result = $mysqli->query($sql);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties | Agent Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Base Styles */
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --secondary: #64748b;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
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
            --agent-primary: #6366f1;
            --agent-primary-dark: #4f46e5;
            --agent-primary-light: #a5b4fc;
            --agent-secondary: #8b5cf6;
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

        .agent-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background-color: white;
            border-right: 1px solid var(--gray-200);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 50;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--agent-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .sidebar-nav-item {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-600);
            text-decoration: none;
            transition: all 0.2s;
            border-radius: 0.25rem;
            margin: 0 0.5rem;
        }

        .sidebar-nav-link:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
        }

        .sidebar-nav-link.active {
            background-color: var(--agent-primary-light);
            color: var(--agent-primary-dark);
            font-weight: 500;
        }

        .sidebar-nav-icon {
            margin-right: 0.75rem;
            width: 1.25rem;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.625rem 1.25rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            font-size: 0.875rem;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--agent-primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--agent-primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--gray-300);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            background-color: var(--gray-100);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        /* Properties Grid */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .property-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .property-content {
            padding: 1.25rem;
        }

        .property-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .property-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-right: 0.5rem;
        }

        .property-status {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-available {
            background-color: var(--success-light);
            color: var(--success);
        }

        .status-occupied {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .status-maintenance {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .property-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .property-detail {
            display: flex;
            align-items: center;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .property-detail i {
            margin-right: 0.5rem;
            color: var(--agent-primary);
        }

        .property-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--agent-primary);
            margin-bottom: 1rem;
        }

        .property-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background-color: var(--agent-primary);
            color: white;
            align-items: center;
            justify-content: center;
            z-index: 100;
            box-shadow: var(--shadow-md);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
            }
            
            .properties-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .empty-state-icon {
            font-size: 3rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .empty-state-description {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="agent-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-building"></i>
                    <span>REMS</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-nav-item">
                    <a href="dashboard.php" class="sidebar-nav-link active">
                        <i class="fas fa-home sidebar-nav-icon"></i>
                        Dashboard
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="properties.php" class="sidebar-nav-link">
                        <i class="fas fa-building sidebar-nav-icon"></i>
                        Properties
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="payment_report.php" class="sidebar-nav-link">
                        <i class="fas fa-money-bill-wave sidebar-nav-icon"></i>
                        Payments
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="users.php" class="sidebar-nav-link">
                        <i class="fas fa-users sidebar-nav-icon"></i>
                        Tenants
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="maintenance.php" class="sidebar-nav-link">
                        <i class="fas fa-tools sidebar-nav-icon"></i>
                        Maintenance
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="report.php" class="sidebar-nav-link">
                        <i class="fas fa-chart-bar sidebar-nav-icon"></i>
                        Reports
                    </a>
                </div>

                <div class="sidebar-nav-item">
                    <a href="../../index.php" class="sidebar-nav-link">
                        <i class="fas fa-sign-out-alt sidebar-nav-icon"></i>
                        Logout
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Properties</h1>
                <div class="header-actions">
                    <a href="add_property.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Property
                    </a>
                </div>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="properties-grid">
                    <?php while ($property = $result->fetch_assoc()): 
                        // Set status class
                        $statusClass = '';
                        if ($property['status'] === 'available') {
                            $statusClass = 'status-available';
                        } elseif ($property['status'] === 'occupied') {
                            $statusClass = 'status-occupied';
                        } elseif ($property['status'] === 'maintenance') {
                            $statusClass = 'status-maintenance';
                        }
                    ?>
                        <div class="property-card">
                            <img src="<?php echo htmlspecialchars($property['featured_image'] ?: '/placeholder.svg?height=200&width=400'); ?>" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                 class="property-image"
                                 onerror="this.src='/placeholder.svg?height=200&width=400'">
                            <div class="property-content">
                                <div class="property-header">
                                    <h2 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h2>
                                    <span class="property-status <?php echo $statusClass; ?>"><?php echo ucfirst($property['status']); ?></span>
                                </div>
                                <div class="property-details">
                                    <div class="property-detail">
                                        <i class="fas fa-bed"></i>
                                        <?php echo htmlspecialchars($property['bedrooms']); ?> Beds
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-bath"></i>
                                        <?php echo htmlspecialchars($property['bathrooms']); ?> Baths
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <?php echo number_format($property['square_footage']); ?> sqft
                                    </div>
                                    <div class="property-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($property['city']); ?>
                                    </div>
                                </div>
                                <div class="property-price">$<?php echo number_format($property['monthly_rent'], 2); ?>/month</div>
                                <div class="property-actions">
                                    <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="view_property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="empty-state-title">No properties found</h3>
                    <p class="empty-state-description">You haven't added any properties yet. Get started by adding your first property.</p>
                    <a href="add_property.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Property
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Mobile menu toggle -->
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

    <script>
        // Mobile menu toggle functionality
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$mysqli->close();
?>