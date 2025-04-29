<?php
require_once './tenant/config.php'; // Adjust path if needed

// Handle filtering and sorting
$where_conditions = [];
$order_by = "created_at DESC"; // Default sorting

// Filter by status if provided
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $mysqli->real_escape_string($_GET['status']);
    $where_conditions[] = "status = '$status'";
}

// Filter by bedrooms if provided
if (isset($_GET['min_beds']) && is_numeric($_GET['min_beds'])) {
    $min_beds = (int)$_GET['min_beds'];
    $where_conditions[] = "bedrooms >= $min_beds";
}

// Filter by price range
if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $min_price = (int)$_GET['min_price'];
    $where_conditions[] = "monthly_rent >= $min_price";
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $max_price = (int)$_GET['max_price'];
    $where_conditions[] = "monthly_rent <= $max_price";
}

// Search by location
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $location = $mysqli->real_escape_string($_GET['location']);
    $where_conditions[] = "(address LIKE '%$location%' OR city LIKE '%$location%' OR zip_code LIKE '%$location%')";
}

// Handle sorting
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_low':
            $order_by = "monthly_rent ASC";
            break;
        case 'price_high':
            $order_by = "monthly_rent DESC";
            break;
        case 'newest':
            $order_by = "created_at DESC";
            break;
        case 'bedrooms':
            $order_by = "bedrooms DESC";
            break;
    }
}

// Build the query
$query = "SELECT * FROM properties";
if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}
$query .= " ORDER BY $order_by";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9; // Number of properties per page
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_query = str_replace("SELECT *", "SELECT COUNT(*)", $query);
$count_result = $mysqli->query($count_query);
$total_rows = $count_result->fetch_row()[0];
$total_pages = ceil($total_rows / $per_page);

// Add pagination to the main query
$query .= " LIMIT $offset, $per_page";

// Execute the query
$result = $mysqli->query($query);

// Get all statuses for filter dropdown
$status_query = "SELECT DISTINCT status FROM properties";
$status_result = $mysqli->query($status_query);
$statuses = [];
if ($status_result) {
    while ($status_row = $status_result->fetch_assoc()) {
        $statuses[] = $status_row['status'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }
        
        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .property-card {
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            border: none;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .property-img-container {
            position: relative;
            height: 220px;
            overflow: hidden;
        }
        
        .property-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .property-card:hover .property-img {
            transform: scale(1.05);
        }
        
        .status-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 6px 12px;
            border-radius: 30px;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            z-index: 2;
        }
        
        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .address {
            color: var(--dark-gray);
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .amenities {
            display: flex;
            justify-content: space-between;
            margin: 1rem 0;
            color: var(--dark-gray);
        }
        
        .amenity-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .amenity-item i {
            margin-right: 5px;
            color: var(--secondary-color);
        }
        
        .card-footer {
            background-color: white;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .btn-view {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 8px 20px;
            transition: background-color 0.3s;
        }
        
        .btn-view:hover {
            background-color: #2980b9;
            color: white;
        }
        
        .filter-section {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }
        
        .page-link {
            color: var(--secondary-color);
            border-radius: 5px;
            margin: 0 3px;
        }
        
        .page-item.active .page-link {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .no-properties {
            background-color: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .search-container {
            position: relative;
        }
        
        .search-container .form-control {
            padding-left: 40px;
            border-radius: 30px;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 10px;
            color: #adb5bd;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        @media (max-width: 768px) {
            .filter-section {
                margin-bottom: 1rem;
            }
            
            .property-img-container {
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.php"><i class="fas fa-building"></i><span>REMS</span></a>
            </div>
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item active"><a href="index.php">Home</a></li>
                    <li class="nav-item dropdown">
                        <a href="properties.php" class="dropdown-toggle">Properties <i class="fas fa-chevron-down"></i></a>
                     <!-- <div class="dropdown-menu">
                            <a href="properties.php"><i class="fas fa-building"></i> All Properties</a>
                            <a href="properties.php"><i class="fas fa-home"></i> Residential</a>
                            <a href="properties.php"><i class="fas fa-briefcase"></i> Commercial</a>
                            <a href="properties.php"><i class="fas fa-building-user"></i> Mixed Use</a>
                        </div> -->
                    </li>
                    <li class="nav-item"><a href="aboutus.php">About Us</a></li>
                    <li class="nav-item"><a href="contactus.php">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-user"></i> Dashboard
                    </a>
                <?php else: ?>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="tenant/register.php" class="btn signup-btn" style="background: linear-gradient(90deg, #2563eb, #0f172a); color: #fff; border-radius: 2rem; padding: 0.5rem 1.5rem; font-weight: 600; box-shadow: 0 2px 8px rgba(37,99,235,0.08); display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.2s;">
                            <i class="fas fa-user-plus"></i> 
                            <span>Sign Up</span>
                        </a>
                        <button class="login-btn" onclick="showCustomLoginModal()" style="background: #fff; color: #2563eb; border: 2px solid #2563eb; border-radius: 2rem; padding: 0.5rem 1.5rem; font-weight: 600; box-shadow: 0 2px 8px rgba(37,99,235,0.05); display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.2s, color 0.2s, border 0.2s;">
                            <i class="fas fa-sign-in-alt"></i> 
                            <span>Login</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-btn" aria-label="Toggle navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <h1 class="mb-2"><i class="fas fa-home me-2"></i>Property Listings</h1>
            <p class="lead">Find your perfect home from our curated selection of properties</p>
        </div>
    </header>

    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <h3 class="filter-title">Find Your Perfect Property</h3>
            <form id="filterForm" method="GET" action="">
                <div class="row g-3">
                    <!-- Search by Location -->
                    <div class="col-md-4">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="form-control" name="location" placeholder="Search by city, address or zip" value="<?= isset($_GET['location']) ? htmlspecialchars($_GET['location']) : '' ?>">
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status ?>" <?= (isset($_GET['status']) && $_GET['status'] === $status) ? 'selected' : '' ?>>
                                    <?= ucfirst($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Bedrooms Filter -->
                    <div class="col-md-2">
                        <select class="form-select" name="min_beds">
                            <option value="">Any Beds</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($_GET['min_beds']) && $_GET['min_beds'] == $i) ? 'selected' : '' ?>>
                                    <?= $i ?>+ Beds
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- Price Range Filter -->
                    <div class="col-md-2">
                        <select class="form-select" name="min_price">
                            <option value="">Min Price</option>
                            <?php foreach ([500, 1000, 1500, 2000, 2500, 3000] as $price): ?>
                                <option value="<?= $price ?>" <?= (isset($_GET['min_price']) && $_GET['min_price'] == $price) ? 'selected' : '' ?>>
                                    $<?= number_format($price) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <select class="form-select" name="max_price">
                            <option value="">Max Price</option>
                            <?php foreach ([1500, 2000, 3000, 4000, 5000, 10000] as $price): ?>
                                <option value="<?= $price ?>" <?= (isset($_GET['max_price']) && $_GET['max_price'] == $price) ? 'selected' : '' ?>>
                                    $<?= number_format($price) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Apply Filters Button -->
                    <div class="col-md-12 d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                            <a href="property_listing.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                        
                        <!-- Sort Options -->
                        <div class="d-flex align-items-center">
                            <label class="me-2">Sort by:</label>
                            <select class="form-select form-select-sm" name="sort" onchange="this.form.submit()" style="width: auto;">
                                <option value="newest" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'newest') ? 'selected' : '' ?>>Newest</option>
                                <option value="price_low" <?= (isset($_GET['sort']) && $_GET['sort'] === 'price_low') ? 'selected' : '' ?>>Price (Low to High)</option>
                                <option value="price_high" <?= (isset($_GET['sort']) && $_GET['sort'] === 'price_high') ? 'selected' : '' ?>>Price (High to Low)</option>
                                <option value="bedrooms" <?= (isset($_GET['sort']) && $_GET['sort'] === 'bedrooms') ? 'selected' : '' ?>>Most Bedrooms</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Results Count -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="mb-0">
                <strong><?= $total_rows ?></strong> properties found
                <?php if (!empty($where_conditions)): ?>
                    <span class="text-muted">with applied filters</span>
                <?php endif; ?>
            </p>
            
            <!-- View Toggle (could be implemented with JavaScript) -->
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary active">
                    <i class="fas fa-th-large"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <!-- Property Listings -->
        <div class="row g-4">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="property-card">
                            <div class="property-img-container">
                                <span class="status-badge bg-<?= $row['status'] === 'available' ? 'success' : ($row['status'] === 'occupied' ? 'warning' : 'secondary') ?>">
                                    <?= strtoupper($row['status']) ?>
                                </span>
                                <img src="<?= htmlspecialchars($row['featured_image']) ?>" class="property-img" alt="<?= htmlspecialchars($row['address']) ?>">
                            </div>
                            <div class="card-body">
                                <div class="price mb-2">$<?= number_format($row['monthly_rent'], 0) ?><span class="text-muted" style="font-size: 1rem;"> /month</span></div>
                                <p class="address">
                                    <i class="fas fa-map-marker-alt me-2 text-secondary"></i>
                                    <?= htmlspecialchars($row['address']) ?>, <?= htmlspecialchars($row['city']) ?>, <?= htmlspecialchars($row['zip_code']) ?>
                                </p>
                                
                                <div class="amenities">
                                    <div class="amenity-item">
                                        <i class="fas fa-bed"></i>
                                        <span><?= $row['bedrooms'] ?> Beds</span>
                                    </div>
                                    <div class="amenity-item">
                                        <i class="fas fa-bath"></i>
                                        <span><?= $row['bathrooms'] ?> Baths</span>
                                    </div>
                                    <div class="amenity-item">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span><?= number_format($row['square_footage']) ?> sqft</span>
                                    </div>
                                </div>
                                
                                <p class="text-muted description">
                                    <?= substr(htmlspecialchars($row['description']), 0, 100) ?>...
                                </p>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    Added <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                </small>
                                <a href="property_detail.php?id=<?= $row['id'] ?>" class="btn btn-view">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="no-properties">
                        <i class="fas fa-home fa-4x mb-3 text-secondary"></i>
                        <h3>No properties found</h3>
                        <p class="text-muted">Try adjusting your search filters or check back later for new listings.</p>
                        <a href="property_listing.php" class="btn btn-primary mt-3">
                            <i class="fas fa-redo me-2"></i>Reset Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Property Listings</h5>
                    <p>Find your perfect home from our curated selection of properties.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Home</a></li>
                        <li><a href="#" class="text-white">Properties</a></li>
                        <li><a href="#" class="text-white">About Us</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <address class="mb-0">
                        <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> 123 Main St, City, State</p>
                        <p class="mb-1"><i class="fas fa-phone me-2"></i> (123) 456-7890</p>
                        <p class="mb-0"><i class="fas fa-envelope me-2"></i> info@example.com</p>
                    </address>
                </div>
            </div>
            <hr class="my-3 bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> Property Listings. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show loading overlay when form is submitted or links are clicked
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            if (filterForm) {
                filterForm.addEventListener('submit', function() {
                    loadingOverlay.style.display = 'flex';
                });
            }
            
            // Add loading for pagination links
            const paginationLinks = document.querySelectorAll('.pagination .page-link');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function() {
                    loadingOverlay.style.display = 'flex';
                });
            });
            
            // Add loading for property detail links
            const propertyLinks = document.querySelectorAll('.btn-view');
            propertyLinks.forEach(link => {
                link.addEventListener('click', function() {
                    loadingOverlay.style.display = 'flex';
                });
            });
        });
    </script>
</body>
</html>