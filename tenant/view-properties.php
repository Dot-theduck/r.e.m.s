<?php
// Start a PHP session for user authentication
session_start();

// You can add database connection here
// $conn = mysqli_connect("localhost", "username", "password", "database");

// Example of user data that could be fetched from database
$user = [
    'name' => 'John Doe',
    'role' => 'Tenant'
];

// Example of properties data that could be fetched from database
$properties = [
    [
        'id' => 1,
        'title' => 'Modern apartment with balcony',
        'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
        'price' => '$1,850/month',
        'address' => '123 Main Street, Apt 4B, Anytown, CA 90210',
        'beds' => '2 Beds',
        'baths' => '2 Baths',
        'sqft' => '1,200 sqft',
        'status' => 'available',
        'description' => 'Modern apartment with open floor plan, stainless steel appliances, and private balcony. Building amenities include gym and rooftop terrace.',
        'agent_name' => 'Sarah Johnson',
        'agent_image' => 'https://randomuser.me/api/portraits/women/65.jpg',
        'date_listed' => 'Listed 2 days ago'
    ],
    [
        'id' => 2,
        'title' => 'Luxury townhouse with garden',
        'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
        'price' => '$2,400/month',
        'address' => '456 Oak Avenue, Townhouse 7, Anytown, CA 90211',
        'beds' => '3 Beds',
        'baths' => '2.5 Baths',
        'sqft' => '1,800 sqft',
        'status' => 'available',
        'description' => 'Spacious townhouse with hardwood floors, updated kitchen, and private backyard. Includes attached garage and full basement for storage.',
        'agent_name' => 'Michael Rodriguez',
        'agent_image' => 'https://randomuser.me/api/portraits/men/32.jpg',
        'date_listed' => 'Listed 1 week ago'
    ],
    [
        'id' => 3,
        'title' => 'Studio apartment in downtown',
        'image' => 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
        'price' => '$1,200/month',
        'address' => '789 Pine Street, Unit 12C, Anytown, CA 90212',
        'beds' => 'Studio',
        'baths' => '1 Bath',
        'sqft' => '550 sqft',
        'status' => 'pending',
        'description' => 'Cozy studio apartment in the heart of downtown. Features modern finishes, built-in storage solutions, and city views. Walk to restaurants and shops.',
        'agent_name' => 'Sarah Johnson',
        'agent_image' => 'https://randomuser.me/api/portraits/women/65.jpg',
        'date_listed' => 'Listed 3 days ago'
    ],
    [
        'id' => 4,
        'title' => 'Single family home with yard',
        'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
        'price' => '$3,200/month',
        'address' => '101 Cedar Lane, Anytown, CA 90213',
        'beds' => '4 Beds',
        'baths' => '3 Baths',
        'sqft' => '2,400 sqft',
        'status' => 'available',
        'description' => 'Beautiful single-family home in a quiet neighborhood. Features large kitchen, family room with fireplace, and fenced backyard with patio.',
        'agent_name' => 'Michael Rodriguez',
        'agent_image' => 'https://randomuser.me/api/portraits/men/32.jpg',
        'date_listed' => 'Listed 5 days ago'
    ],
    [
        'id' => 5,
        'title' => 'Modern loft apartment',
        'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
        'price' => '$1,950/month',
        'address' => '222 Maple Drive, Loft 3A, Anytown, CA 90214',
        'beds' => '1 Bed',
        'baths' => '1 Bath',
        'sqft' => '850 sqft',
        'status' => 'available',
        'description' => 'Stylish loft apartment with high ceilings, exposed brick, and large windows. Industrial-chic design with modern amenities and in-unit laundry.',
        'agent_name' => 'Jennifer Lee',
        'agent_image' => 'https://randomuser.me/api/portraits/women/42.jpg',
        'date_listed' => 'Listed today'
    ],
    [
        'id' => 6,
        'title' => 'Luxury condo with view',
        'image' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
        'price' => '$2,800/month',
        'address' => '333 Birch Street, Unit 15F, Anytown, CA 90215',
        'beds' => '2 Beds',
        'baths' => '2 Baths',
        'sqft' => '1,400 sqft',
        'status' => 'leased',
        'description' => 'Luxury condo with panoramic city views, gourmet kitchen, and spa-like bathrooms. Building includes concierge, pool, and fitness center.',
        'agent_name' => 'Michael Rodriguez',
        'agent_image' => 'https://randomuser.me/api/portraits/men/32.jpg',
        'date_listed' => 'Listed 2 weeks ago'
    ]
];

// Get property count
$propertyCount = count($properties);

// Get current year for copyright
$currentYear = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Properties | Tenant Portal</title>
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

        .user-profile {
            position: relative;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            color: var(--gray-600);
            font-weight: 500;
            padding: 0.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .profile-btn:hover {
            background-color: var(--gray-100);
        }

        .dropdown-content {
            position: absolute;
            top: 100%;
            right: 0;
            width: 200px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            display: none;
            z-index: 10;
        }

        .profile-dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--gray-600);
            transition: background-color 0.2s, color 0.2s;
        }

        .dropdown-content a:hover {
            background-color: var(--gray-100);
            color: var(--primary);
        }

        .dropdown-content a.logout {
            border-top: 1px solid var(--gray-200);
            margin-top: 0.5rem;
            color: var(--danger);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            flex-direction: column;
            gap: 5px;
            padding: 0.5rem;
            cursor: pointer;
        }

        .mobile-menu-btn span {
            display: block;
            width: 24px;
            height: 2px;
            background-color: var(--gray-600);
            transition: transform 0.3s, opacity 0.3s;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--gray-500);
        }

        /* Properties Page Specific Styles */
        .properties-layout {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Filter Sidebar */
        .filter-section {
            margin-bottom: 1.5rem;
        }

        .filter-title {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.75rem;
        }

        .filter-group {
            margin-bottom: 1rem;
        }

        .filter-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .filter-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filter-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: white;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filter-checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-checkbox {
            width: 1rem;
            height: 1rem;
            border-radius: 0.25rem;
            border: 1px solid var(--gray-400);
            appearance: none;
            background-color: white;
            cursor: pointer;
            position: relative;
        }

        .filter-checkbox:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .filter-checkbox:checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0.5rem;
            height: 0.5rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }

        .filter-checkbox:focus {
            outline: 2px solid var(--primary-light);
            outline-offset: 2px;
        }

        .filter-checkbox-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            cursor: pointer;
        }

        .price-range {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .price-range-input {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
        }

        .price-range-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .price-range-separator {
            color: var(--gray-500);
        }

        .filter-slider {
            width: 100%;
            margin: 1rem 0;
        }

        .filter-slider-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: white;
            color: var(--gray-600);
            border: 1px solid var(--gray-300);
        }

        .btn-secondary:hover {
            background-color: var(--gray-100);
        }

        .btn-block {
            width: 100%;
        }

        .filter-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .filter-actions .btn {
            flex: 1;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Properties List */
        .properties-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .properties-count {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .properties-sort {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sort-label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .sort-select {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            background-color: white;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px;
        }

        .sort-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .view-toggle {
            display: flex;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .view-toggle-btn {
            padding: 0.5rem 0.75rem;
            background-color: white;
            border: none;
            cursor: pointer;
            color: var(--gray-500);
            transition: background-color 0.2s, color 0.2s;
        }

        .view-toggle-btn:hover {
            background-color: var(--gray-100);
            color: var(--gray-700);
        }

        .view-toggle-btn.active {
            background-color: var(--primary);
            color: white;
        }

        /* Properties Grid */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1rem;
        }

        /* Property Card */
        .property-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .property-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .property-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .property-card:hover .property-image img {
            transform: scale(1.05);
        }

        .property-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .property-badge-available {
            background-color: var(--success);
            color: white;
        }

        .property-badge-pending {
            background-color: var(--warning);
            color: white;
        }

        .property-badge-leased {
            background-color: var(--danger);
            color: white;
        }

        .property-favorite {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .property-favorite:hover {
            background-color: white;
        }

        .property-favorite i {
            color: var(--gray-400);
            transition: color 0.2s;
        }

        .property-favorite.active i {
            color: var(--danger);
        }

        .property-content {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .property-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .property-address {
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.75rem;
        }

        .property-features {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .property-feature {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .property-feature i {
            color: var(--primary);
        }

        .property-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
            flex: 1;
        }

        .property-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .property-agent {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .property-agent-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            overflow: hidden;
        }

        .property-agent-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .property-agent-name {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .property-date {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* List View */
        .properties-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .property-list-item {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
        }

        .property-list-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .property-list-image {
            width: 240px;
            height: 180px;
            position: relative;
            flex-shrink: 0;
        }

        .property-list-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .property-list-content {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .property-list-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .property-list-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .property-list-address {
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.75rem;
        }

        .property-list-features {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .property-list-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
            flex: 1;
        }

        .property-list-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        .pagination-list {
            display: flex;
            gap: 0.25rem;
        }

        .pagination-item {
            display: flex;
        }

        .pagination-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            color: var(--gray-600);
            transition: background-color 0.2s, color 0.2s;
        }

        .pagination-link:hover {
            background-color: var(--gray-200);
            color: var(--gray-800);
        }

        .pagination-link.active {
            background-color: var(--primary);
            color: white;
        }

        .pagination-link.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Mobile Filter Toggle */
        .mobile-filter-toggle {
            display: none;
            margin-bottom: 1rem;
        }

        /* Footer Styles */
        .footer {
            background-color: white;
            padding: 1.5rem 0;
            border-top: 1px solid var(--gray-200);
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .footer-logo span {
            color: var(--primary);
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-link {
            color: var(--gray-600);
            transition: color 0.2s;
            font-size: 0.875rem;
        }

        .footer-link:hover {
            color: var(--primary);
        }

        .footer-bottom {
            margin-top: 1rem;
            text-align: center;
            color: var(--gray-500);
            font-size: 0.75rem;
        }

        /* Property Details Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .modal.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(20px);
            transition: transform 0.3s;
        }

        .modal.active .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--gray-500);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: var(--gray-800);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .property-gallery {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .property-gallery-main {
            height: 400px;
            border-radius: var(--border-radius);
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .property-gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .property-gallery-thumbs {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .property-gallery-thumb {
            width: 80px;
            height: 60px;
            border-radius: 0.25rem;
            overflow: hidden;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .property-gallery-thumb:hover,
        .property-gallery-thumb.active {
            opacity: 1;
        }

        .property-gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .property-gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
            z-index: 1;
        }

        .property-gallery-nav:hover {
            background-color: white;
        }

        .property-gallery-prev {
            left: 1rem;
        }

        .property-gallery-next {
            right: 1rem;
        }

        .property-details {
            margin-bottom: 1.5rem;
        }

        .property-details-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .property-details-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .property-details-address {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 1rem;
        }

        .property-details-features {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .property-details-feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            color: var(--gray-700);
        }

        .property-details-feature i {
            color: var(--primary);
            font-size: 1.25rem;
        }

        .property-details-description {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .property-details-description h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.75rem;
        }

        .property-details-description p {
            color: var(--gray-600);
            line-height: 1.6;
        }

        .property-details-amenities {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .property-details-amenities h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.75rem;
        }

        .property-details-amenities-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .property-details-amenity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .property-details-amenity i {
            color: var(--success);
        }

        .property-details-location {
            margin-bottom: 1.5rem;
        }

        .property-details-location h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.75rem;
        }

        .property-map {
            height: 300px;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .property-details-agent {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
        }

        .property-details-agent-avatar {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            overflow: hidden;
        }

        .property-details-agent-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .property-details-agent-info {
            flex: 1;
        }

        .property-details-agent-name {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .property-details-agent-title {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .property-details-agent-contact {
            display: flex;
            gap: 1rem;
        }

        .property-details-agent-contact a {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            color: var(--primary);
        }

        .property-details-agent-contact a:hover {
            text-decoration: underline;
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .properties-layout {
                grid-template-columns: 1fr;
            }

            .filter-sidebar {
                display: none;
            }

            .filter-sidebar.active {
                display: block;
            }

            .mobile-filter-toggle {
                display: flex;
            }

            .property-details-amenities-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .nav-list {
                display: none;
            }

            .mobile-menu-btn {
                display: flex;
            }

            .mobile-menu-active .nav-list {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 70px;
                left: 0;
                width: 100%;
                background-color: white;
                box-shadow: var(--shadow);
                padding: 1rem 0;
                z-index: 100;
            }

            .mobile-menu-active .nav-item {
                width: 100%;
            }

            .mobile-menu-active .nav-item a {
                display: block;
                padding: 0.75rem 1.5rem;
            }

            .mobile-menu-active .nav-item.active a::after {
                display: none;
            }

            .mobile-menu-active .nav-item.active a {
                background-color: var(--gray-100);
            }

            .properties-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .properties-sort {
                width: 100%;
                justify-content: space-between;
            }

            .property-list-item {
                flex-direction: column;
            }

            .property-list-image {
                width: 100%;
                height: 200px;
            }

            .footer-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .footer-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .property-details-amenities-list {
                grid-template-columns: 1fr;
            }

            .property-details-agent {
                flex-direction: column;
                text-align: center;
            }

            .property-details-agent-contact {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="dashboard.php">
                    <i class="fas fa-building" aria-hidden="true"></i>
                    <span>Tenant</span>Portal
                </a>
            </div>
            <nav class="main-nav" aria-label="Main navigation">
                <ul class="nav-list">
                    <li class="nav-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a href="my-leases.php">My Lease</a></li>
                    <li class="nav-item active"><a href="view-properties.php" aria-current="page">Properties</a></li>
                    <li class="nav-item"><a href="contact-agent.php">Contact Agent</a></li>
                </ul>
            </nav>
            <div class="user-profile">
                <div class="profile-dropdown">
                    <button class="profile-btn" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                        <span><?php echo $user['name']; ?></span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-content" role="menu">
                        <a href="#" role="menuitem"><i class="fas fa-user" aria-hidden="true"></i> My Profile</a>
                        <a href="#" role="menuitem"><i class="fas fa-cog" aria-hidden="true"></i> Settings</a>
                        <a href="#" role="menuitem"><i class="fas fa-bell" aria-hidden="true"></i> Notifications</a>
                        <a href="login.php" class="logout" role="menuitem"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout</a>
                    </div>
                </div>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Available Properties</h1>
                <p>Browse our selection of available properties for rent or lease.</p>
            </div>

            <!-- Mobile Filter Toggle -->
            <div class="mobile-filter-toggle">
                <button class="btn btn-secondary" id="mobileFilterBtn">
                    <i class="fas fa-filter" aria-hidden="true"></i>
                    Filter Properties
                </button>
            </div>

            <!-- Properties Layout -->
            <div class="properties-layout">
                <!-- Filter Sidebar -->
                <div class="filter-sidebar" id="filterSidebar">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Filter Properties</h2>
                        </div>
                        <div class="card-body">
                            <form id="filterForm">
                                <div class="filter-section">
                                    <div class="filter-group">
                                        <label for="searchInput" class="filter-label">Search</label>
                                        <input type="text" id="searchInput" class="filter-input" placeholder="Search by address, city, or zip code">
                                    </div>
                                </div>

                                <div class="filter-section">
                                    <div class="filter-title">Property Type</div>
                                    <div class="filter-checkbox-group">
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="typeApartment" class="filter-checkbox" checked>
                                            <label for="typeApartment" class="filter-checkbox-label">Apartment</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="typeHouse" class="filter-checkbox" checked>
                                            <label for="typeHouse" class="filter-checkbox-label">House</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="typeCondo" class="filter-checkbox" checked>
                                            <label for="typeCondo" class="filter-checkbox-label">Condo</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="typeTownhouse" class="filter-checkbox" checked>
                                            <label for="typeTownhouse" class="filter-checkbox-label">Townhouse</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter-section">
                                    <div class="filter-title">Price Range</div>
                                    <div class="price-range">
                                        <input type="number" id="priceMin" class="price-range-input" placeholder="Min" min="0" value="500">
                                        <span class="price-range-separator">-</span>
                                        <input type="number" id="priceMax" class="price-range-input" placeholder="Max" min="0" value="5000">
                                    </div>
                                    <input type="range" id="priceSlider" class="filter-slider" min="0" max="10000" step="100" value="5000">
                                    <div class="filter-slider-labels">
                                        <span>$0</span>
                                        <span>$10,000+</span>
                                    </div>
                                </div>

                                <div class="filter-section">
                                    <div class="filter-title">Bedrooms</div>
                                    <div class="filter-checkbox-group">
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="bedStudio" class="filter-checkbox" checked>
                                            <label for="bedStudio" class="filter-checkbox-label">Studio</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="bed1" class="filter-checkbox" checked>
                                            <label for="bed1" class="filter-checkbox-label">1 Bedroom</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="bed2" class="filter-checkbox" checked>
                                            <label for="bed2" class="filter-checkbox-label">2 Bedrooms</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="bed3" class="filter-checkbox" checked>
                                            <label for="bed3" class="filter-checkbox-label">3 Bedrooms</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="bed4plus" class="filter-checkbox" checked>
                                            <label for="bed4plus" class="filter-checkbox-label">4+ Bedrooms</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter-section">
                                    <div class="filter-title">Bathrooms</div>
                                    <div class="filter-checkbox-group">
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="bath1" class="filter-checkbox" checked>
                                            <label for="bath1" class="filter-checkbox-label">1 Bathroom</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="bath2" class="filter-checkbox" checked>
                                            <label for="bath2" class="filter-checkbox-label">2 Bathrooms</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="bath3plus" class="filter-checkbox" checked>
                                            <label for="bath3plus" class="filter-checkbox-label">3+ Bathrooms</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter-section">
                                    <div class="filter-title">Amenities</div>
                                    <div class="filter-checkbox-group">
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="amenParking" class="filter-checkbox">
                                            <label for="amenParking" class="filter-checkbox-label">Parking</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="amenPets" class="filter-checkbox">
                                            <label for="amenPets" class="filter-checkbox-label">Pet Friendly</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="amenPool" class="filter-checkbox">
                                            <label for="amenPool" class="filter-checkbox-label">Pool</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="amenGym" class="filter-checkbox">
                                            <label for="amenGym" class="filter-checkbox-label">Gym</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="amenWasher" class="filter-checkbox">
                                            <label for="amenWasher" class="filter-checkbox-label">Washer/Dryer</label>
                                        </div>
                                        <div class="filter-checkbox-item">
                                            <input type="checkbox" id="amenAc" class="filter-checkbox">
                                            <label for="amenAc" class="filter-checkbox-label">Air Conditioning</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="filter-actions">
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Properties Content -->
                <div class="properties-content">
                    <div class="card">
                        <div class="card-header">
                            <div class="properties-header">
                                <div class="properties-count">
                                    Showing <span id="propertiesCount"><?php echo $propertyCount; ?></span> properties
                                </div>
                                <div class="properties-sort">
                                    <label for="sortSelect" class="sort-label">Sort by:</label>
                                    <select id="sortSelect" class="sort-select">
                                        <option value="newest">Newest</option>
                                        <option value="price-asc">Price: Low to High</option>
                                        <option value="price-desc">Price: High to Low</option>
                                        <option value="size-asc">Size: Small to Large</option>
                                        <option value="size-desc">Size: Large to Small</option>
                                    </select>
                                    <div class="view-toggle">
                                        <button type="button" class="view-toggle-btn active" id="gridViewBtn" aria-label="Grid view">
                                            <i class="fas fa-th" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="view-toggle-btn" id="listViewBtn" aria-label="List view">
                                            <i class="fas fa-list" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Grid View (Default) -->
                            <div class="properties-grid" id="propertiesGrid">
                                <?php foreach ($properties as $property): ?>
                                <!-- Property Card -->
                                <div class="property-card" data-id="<?php echo $property['id']; ?>">
                                    <div class="property-image">
                                        <img src="<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>">
                                        <div class="property-badge property-badge-<?php echo $property['status']; ?>"><?php echo ucfirst($property['status']); ?></div>
                                        <div class="property-favorite" aria-label="Add to favorites">
                                            <i class="far fa-heart" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                    <div class="property-content">
                                        <div class="property-price"><?php echo $property['price']; ?></div>
                                        <div class="property-address"><?php echo $property['address']; ?></div>
                                        <div class="property-features">
                                            <div class="property-feature">
                                                <i class="fas fa-bed" aria-hidden="true"></i>
                                                <span><?php echo $property['beds']; ?></span>
                                            </div>
                                            <div class="property-feature">
                                                <i class="fas fa-bath" aria-hidden="true"></i>
                                                <span><?php echo $property['baths']; ?></span>
                                            </div>
                                            <div class="property-feature">
                                                <i class="fas fa-ruler-combined" aria-hidden="true"></i>
                                                <span><?php echo $property['sqft']; ?></span>
                                            </div>
                                        </div>
                                        <div class="property-description">
                                            <?php echo $property['description']; ?>
                                        </div>
                                        <div class="property-footer">
                                            <div class="property-agent">
                                                <div class="property-agent-avatar">
                                                    <img src="<?php echo $property['agent_image']; ?>" alt="<?php echo $property['agent_name']; ?>">
                                                </div>
                                                <div class="property-agent-name"><?php echo $property['agent_name']; ?></div>
                                            </div>
                                            <div class="property-date"><?php echo $property['date_listed']; ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- List View (Hidden by default) -->
                            <div class="properties-list" id="propertiesList" style="display: none;">
                                <?php foreach ($properties as $property): ?>
                                <!-- Property List Item -->
                                <div class="property-list-item" data-id="<?php echo $property['id']; ?>">
                                    <div class="property-list-image">
                                        <img src="<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>">
                                        <div class="property-badge property-badge-<?php echo $property['status']; ?>"><?php echo ucfirst($property['status']); ?></div>
                                    </div>
                                    <div class="property-list-content">
                                        <div class="property-list-header">
                                            <div class="property-list-price"><?php echo $property['price']; ?></div>
                                            <div class="property-favorite" aria-label="Add to favorites">
                                                <i class="far fa-heart" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                        <div class="property-list-address"><?php echo $property['address']; ?></div>
                                        <div class="property-list-features">
                                            <div class="property-feature">
                                                <i class="fas fa-bed" aria-hidden="true"></i>
                                                <span><?php echo $property['beds']; ?></span>
                                            </div>
                                            <div class="property-feature">
                                                <i class="fas fa-bath" aria-hidden="true"></i>
                                                <span><?php echo $property['baths']; ?></span>
                                            </div>
                                            <div class="property-feature">
                                                <i class="fas fa-ruler-combined" aria-hidden="true"></i>
                                                <span><?php echo $property['sqft']; ?></span>
                                            </div>
                                        </div>
                                        <div class="property-list-description">
                                            <?php echo $property['description']; ?>
                                        </div>
                                        <div class="property-list-footer">
                                            <div class="property-agent">
                                                <div class="property-agent-avatar">
                                                    <img src="<?php echo $property['agent_image']; ?>" alt="<?php echo $property['agent_name']; ?>">
                                                </div>
                                                <div class="property-agent-name"><?php echo $property['agent_name']; ?></div>
                                            </div>
                                            <div class="property-date"><?php echo $property['date_listed']; ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination">
                                <ul class="pagination-list" aria-label="Pagination">
                                    <li class="pagination-item">
                                        <a href="#" class="pagination-link disabled" aria-label="Previous page" aria-disabled="true">
                                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                    <li class="pagination-item">
                                        <a href="#" class="pagination-link active" aria-label="Page 1" aria-current="page">1</a>
                                    </li>
                                    <li class="pagination-item">
                                        <a href="#" class="pagination-link" aria-label="Page 2">2</a>
                                    </li>
                                    <li class="pagination-item">
                                        <a href="#" class="pagination-link" aria-label="Page 3">3</a>
                                    </li>
                                    <li class="pagination-item">
                                        <a href="#" class="pagination-link" aria-label="Next page">
                                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Property Details Modal -->
    <div class="modal" id="propertyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Property Details</h2>
                <button type="button" class="modal-close" id="modalClose" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be dynamically loaded -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modalCloseBtn">Close</button>
                <button type="button" class="btn btn-primary" id="scheduleViewingBtn">Proceed to Payment</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <a href="dashboard.php"><span>Tenant</span>Portal</a>
                </div>
                <div class="footer-links">
                    <a href="#" class="footer-link">About</a>
                    <a href="#" class="footer-link">Contact</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo $currentYear; ?> TenantPortal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const header = document.querySelector('.header');

            mobileMenuBtn.addEventListener('click', function() {
                header.classList.toggle('mobile-menu-active');
                const isExpanded = header.classList.contains('mobile-menu-active');
                mobileMenuBtn.setAttribute('aria-expanded', isExpanded);
            });

            // Mobile filter toggle
            const mobileFilterBtn = document.getElementById('mobileFilterBtn');
            const filterSidebar = document.getElementById('filterSidebar');

            mobileFilterBtn.addEventListener('click', function() {
                filterSidebar.classList.toggle('active');
                const isExpanded = filterSidebar.classList.contains('active');
                mobileFilterBtn.setAttribute('aria-expanded', isExpanded);
                if (isExpanded) {
                    mobileFilterBtn.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i> Close Filters';
                } else {
                    mobileFilterBtn.innerHTML = '<i class="fas fa-filter" aria-hidden="true"></i> Filter Properties';
                }
            });

            // View toggle
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listViewBtn = document.getElementById('listViewBtn');
            const propertiesGrid = document.getElementById('propertiesGrid');
            const propertiesList = document.getElementById('propertiesList');

            gridViewBtn.addEventListener('click', function() {
                gridViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
                propertiesGrid.style.display = 'grid';
                propertiesList.style.display = 'none';
            });

            listViewBtn.addEventListener('click', function() {
                listViewBtn.classList.add('active');
                gridViewBtn.classList.remove('active');
                propertiesList.style.display = 'flex';
                propertiesGrid.style.display = 'none';
            });

            // Favorite toggle
            const favoriteButtons = document.querySelectorAll('.property-favorite');
            
            favoriteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.toggle('active');
                    const icon = this.querySelector('i');
                    if (this.classList.contains('active')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        this.setAttribute('aria-label', 'Remove from favorites');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.setAttribute('aria-label', 'Add to favorites');
                    }
                });
            });

            // Property cards click
            const propertyCards = document.querySelectorAll('.property-card, .property-list-item');
            const propertyModal = document.getElementById('propertyModal');
            const modalClose = document.getElementById('modalClose');
            const modalCloseBtn = document.getElementById('modalCloseBtn');
            const modalBody = document.getElementById('modalBody');

            propertyCards.forEach(card => {
                card.addEventListener('click', function() {
                    const propertyId = this.getAttribute('data-id');
                    openPropertyModal(propertyId);
                });
            });

            modalClose.addEventListener('click', closePropertyModal);
            modalCloseBtn.addEventListener('click', closePropertyModal);
            propertyModal.addEventListener('click', function(e) {
                if (e.target === propertyModal) {
                    closePropertyModal();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && propertyModal.classList.contains('active')) {
                    closePropertyModal();
                }
            });

            function openPropertyModal(propertyId) {
                // In a real application, you would fetch property details from an API
                // For demo purposes, we'll use hardcoded content
                const propertyDetails = getPropertyDetails(propertyId);
                
                modalBody.innerHTML = `
                    <div class="property-gallery">
                        <div class="property-gallery-main">
                            <img src="${propertyDetails.image}" alt="${propertyDetails.title}">
                        </div>
                        <div class="property-gallery-thumbs">
                            <div class="property-gallery-thumb active">
                                <img src="${propertyDetails.image}" alt="Main view">
                            </div>
                            <div class="property-gallery-thumb">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Kitchen view">
                            </div>
                            <div class="property-gallery-thumb">
                                <img src="https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Bedroom view">
                            </div>
                            <div class="property-gallery-thumb">
                                <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Bathroom view">
                            </div>
                        </div>
                        <div class="property-gallery-nav property-gallery-prev" aria-label="Previous image">
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </div>
                        <div class="property-gallery-nav property-gallery-next" aria-label="Next image">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="property-details">
                        <div class="property-details-header">
                            <div class="property-details-price">${propertyDetails.price}</div>
                            <div class="property-favorite ${propertyDetails.isFavorite ? 'active' : ''}" aria-label="${propertyDetails.isFavorite ? 'Remove from favorites' : 'Add to favorites'}">
                                <i class="${propertyDetails.isFavorite ? 'fas' : 'far'} fa-heart" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="property-details-address">${propertyDetails.address}</div>
                        <div class="property-details-features">
                            <div class="property-details-feature">
                                <i class="fas fa-bed" aria-hidden="true"></i>
                                <span>${propertyDetails.beds} ${propertyDetails.beds > 1 ? 'Beds' : 'Bed'}</span>
                            </div>
                            <div class="property-details-feature">
                                <i class="fas fa-bath" aria-hidden="true"></i>
                                <span>${propertyDetails.baths} ${propertyDetails.baths > 1 ? 'Baths' : 'Bath'}</span>
                            </div>
                            <div class="property-details-feature">
                                <i class="fas fa-ruler-combined" aria-hidden="true"></i>
                                <span>${propertyDetails.sqft} sqft</span>
                            </div>
                            <div class="property-details-feature">
                                <i class="fas fa-car" aria-hidden="true"></i>
                                <span>${propertyDetails.parking}</span>
                            </div>
                            <div class="property-details-feature">
                                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                <span>Available ${propertyDetails.availableDate}</span>
                            </div>
                        </div>
                        <div class="property-details-description">
                            <h3>Description</h3>
                            <p>${propertyDetails.description}</p>
                        </div>
                        <div class="property-details-amenities">
                            <h3>Amenities</h3>
                            <div class="property-details-amenities-list">
                                ${propertyDetails.amenities.map(amenity => `
                                    <div class="property-details-amenity">
                                        <i class="fas fa-check" aria-hidden="true"></i>
                                        <span>${amenity}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        <div class="property-details-location">
                            <h3>Location</h3>
                            <div class="property-map" id="propertyMap"></div>
                        </div>
                        <div class="property-details-agent">
                            <div class="property-details-agent-avatar">
                                <img src="${propertyDetails.agent.avatar}" alt="${propertyDetails.agent.name}">
                            </div>
                            <div class="property-details-agent-info">
                                <div class="property-details-agent-name">${propertyDetails.agent.name}</div>
                                <div class="property-details-agent-title">${propertyDetails.agent.title}</div>
                                <div class="property-details-agent-contact">
                                    <a href="tel:${propertyDetails.agent.phone}"><i class="fas fa-phone" aria-hidden="true"></i> Call</a>
                                    <a href="mailto:${propertyDetails.agent.email}"><i class="fas fa-envelope" aria-hidden="true"></i> Email</a>
                                    <a href="contact-agent.php"><i class="fas fa-comment" aria-hidden="true"></i> Message</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                propertyModal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Initialize map
                setTimeout(initPropertyMap, 100);
                
                // Gallery navigation
                const galleryThumbs = modalBody.querySelectorAll('.property-gallery-thumb');
                const galleryMain = modalBody.querySelector('.property-gallery-main img');
                const galleryPrev = modalBody.querySelector('.property-gallery-prev');
                const galleryNext = modalBody.querySelector('.property-gallery-next');
                
                galleryThumbs.forEach(thumb => {
                    thumb.addEventListener('click', function() {
                        galleryThumbs.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        galleryMain.src = this.querySelector('img').src;
                    });
                });
                
                let currentIndex = 0;
                
                galleryPrev.addEventListener('click', function() {
                    currentIndex = (currentIndex - 1 + galleryThumbs.length) % galleryThumbs.length;
                    updateGallery();
                });
                
                galleryNext.addEventListener('click', function() {
                    currentIndex = (currentIndex + 1) % galleryThumbs.length;
                    updateGallery();
                });
                
                function updateGallery() {
                    galleryThumbs.forEach(t => t.classList.remove('active'));
                    galleryThumbs[currentIndex].classList.add('active');
                    galleryMain.src = galleryThumbs[currentIndex].querySelector('img').src;
                }
                
                // Favorite toggle in modal
                const modalFavorite = modalBody.querySelector('.property-favorite');
                modalFavorite.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.toggle('active');
                    const icon = this.querySelector('i');
                    if (this.classList.contains('active')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        this.setAttribute('aria-label', 'Remove from favorites');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.setAttribute('aria-label', 'Add to favorites');
                    }
                });
            }

            function closePropertyModal() {
                propertyModal.classList.remove('active');
                document.body.style.overflow = '';
            }

            function initPropertyMap() {
                const propertyMap = document.getElementById('propertyMap');
                if (propertyMap) {
                    const propertyLocation = { lat: 34.0736, lng: -118.4004 }; // Example coordinates
                    
                    const map = new google.maps.Map(propertyMap, {
                        center: propertyLocation,
                        zoom: 15,
                        mapTypeControl: false,
                        fullscreenControl: false
                    });
                    
                    const marker = new google.maps.Marker({
                        position: propertyLocation,
                        map: map,
                        title: 'Property Location'
                    });
                }
            }

            function getPropertyDetails(propertyId) {
                // In a real application, this would be fetched from an API
                const properties = {
                    '1': {
                        title: 'Modern apartment with balcony',
                        image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
                        price: '$1,850/month',
                        address: '123 Main Street, Apt 4B, Anytown, CA 90210',
                        beds: 2,
                        baths: 2,
                        sqft: 1200,
                        parking: '1 Covered Spot',
                        availableDate: 'Now',
                        description: 'This modern apartment features an open floor plan with abundant natural light, stainless steel appliances, and a private balcony with city views. The building offers a fitness center, rooftop terrace, and secure entry. Located in a vibrant neighborhood with easy access to public transportation, restaurants, and shopping.',
                        amenities: ['In-unit Washer/Dryer', 'Central Air Conditioning', 'Dishwasher', 'Hardwood Floors', 'Walk-in Closet', 'Fitness Center', 'Rooftop Terrace', 'Elevator', 'Controlled Access', 'Pet Friendly'],
                        isFavorite: false,
                        agent: {
                            name: 'Sarah Johnson',
                            title: 'Senior Property Manager',
                            avatar: 'https://randomuser.me/api/portraits/women/65.jpg',
                            phone: '+15551234567',
                            email: 'sarah.johnson@propmanage.com'
                        }
                    },
                    '2': {
                        title: 'Luxury townhouse with garden',
                        image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
                        price: '$2,400/month',
                        address: '456 Oak Avenue, Townhouse 7, Anytown, CA 90211',
                        beds: 3,
                        baths: 2.5,
                        sqft: 1800,
                        parking: '2 Covered Spots',
                        availableDate: 'Now',
                        description: 'Spacious townhouse with hardwood floors, updated kitchen, and private backyard. Includes attached garage and full basement for storage.',
                        amenities: ['Hardwood Floors', 'Updated Kitchen', 'Private Backyard', 'Attached Garage', 'Full Basement', 'Central Air', 'Washer/Dryer', 'Pet Friendly'],
                        isFavorite: false,
                        agent: {
                            name: 'Michael Rodriguez',
                            title: 'Property Manager',
                            avatar: 'https://randomuser.me/api/portraits/men/32.jpg',
                            phone: '+15551234568',
                            email: 'michael.rodriguez@propmanage.com'
                        }
                    },
                    '3': {
                        title: 'Studio apartment in downtown',
                        image: 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
                        price: '$1,200/month',
                        address: '789 Pine Street, Unit 12C, Anytown, CA 90212',
                        beds: 1,
                        baths: 1,
                        sqft: 550,
                        parking: 'Street Parking',
                        availableDate: 'Now',
                        description: 'Cozy studio apartment in the heart of downtown. Features modern finishes, built-in storage solutions, and city views. Walk to restaurants and shops.',
                        amenities: ['Modern Finishes', 'Built-in Storage', 'City Views', 'Walk to Downtown', 'Pet Friendly'],
                        isFavorite: false,
                        agent: {
                            name: 'Sarah Johnson',
                            title: 'Senior Property Manager',
                            avatar: 'https://randomuser.me/api/portraits/women/65.jpg',
                            phone: '+15551234567',
                            email: 'sarah.johnson@propmanage.com'
                        }
                    },
                    '4': {
                        title: 'Single family home with yard',
                        image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
                        price: '$3,200/month',
                        address: '101 Cedar Lane, Anytown, CA 90213',
                        beds: 4,
                        baths: 3,
                        sqft: 2400,
                        parking: '2 Car Garage',
                        availableDate: 'Now',
                        description: 'Beautiful single-family home in a quiet neighborhood. Features large kitchen, family room with fireplace, and fenced backyard with patio.',
                        amenities: ['Large Kitchen', 'Family Room', 'Fireplace', 'Fenced Backyard', 'Patio', '2 Car Garage', 'Central Air', 'Washer/Dryer'],
                        isFavorite: false,
                        agent: {
                            name: 'Michael Rodriguez',
                            title: 'Property Manager',
                            avatar: 'https://randomuser.me/api/portraits/men/32.jpg',
                            phone: '+15551234568',
                            email: 'michael.rodriguez@propmanage.com'
                        }
                    },
                    '5': {
                        title: 'Modern loft apartment',
                        image: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
                        price: '$1,950/month',
                        address: '222 Maple Drive, Loft 3A, Anytown, CA 90214',
                        beds: 1,
                        baths: 1,
                        sqft: 850,
                        parking: '1 Covered Spot',
                        availableDate: 'Now',
                        description: 'Stylish loft apartment with high ceilings, exposed brick, and large windows. Industrial-chic design with modern amenities and in-unit laundry.',
                        amenities: ['High Ceilings', 'Exposed Brick', 'Large Windows', 'In-unit Laundry', 'Modern Kitchen', 'Pet Friendly'],
                        isFavorite: false,
                        agent: {
                            name: 'Jennifer Lee',
                            title: 'Property Manager',
                            avatar: 'https://randomuser.me/api/portraits/women/42.jpg',
                            phone: '+15551234569',
                            email: 'jennifer.lee@propmanage.com'
                        }
                    },
                    '6': {
                        title: 'Luxury condo with view',
                        image: 'https://images.unsplash.com/photo-1484154218962-a197022b5858?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80',
                        price: '$2,800/month',
                        address: '333 Birch Street, Unit 15F, Anytown, CA 90215',
                        beds: 2,
                        baths: 2,
                        sqft: 1400,
                        parking: '1 Covered Spot',
                        availableDate: 'Now',
                        description: 'Luxury condo with panoramic city views, gourmet kitchen, and spa-like bathrooms. Building includes concierge, pool, and fitness center.',
                        amenities: ['Panoramic Views', 'Gourmet Kitchen', 'Spa-like Bathrooms', 'Concierge', 'Pool', 'Fitness Center', 'Pet Friendly'],
                        isFavorite: false,
                        agent: {
                            name: 'Michael Rodriguez',
                            title: 'Property Manager',
                            avatar: 'https://randomuser.me/api/portraits/men/32.jpg',
                            phone: '+15551234568',
                            email: 'michael.rodriguez@propmanage.com'
                        }
                    }
                };
                
                return properties[propertyId] || properties['1']; // Default to first property if ID not found
            }

            // Price slider functionality
            const priceSlider = document.getElementById('priceSlider');
            const priceMin = document.getElementById('priceMin');
            const priceMax = document.getElementById('priceMax');
            
            priceSlider.addEventListener('input', function() {
                priceMax.value = this.value;
            });
            
            priceMin.addEventListener('input', function() {
                if (parseInt(this.value) > parseInt(priceMax.value)) {
                    priceMax.value = this.value;
                }
            });
            
            priceMax.addEventListener('input', function() {
                if (parseInt(this.value) < parseInt(priceMin.value)) {
                    priceMin.value = this.value;
                }
                priceSlider.value = this.value;
            });

            // Filter form submission
            const filterForm = document.getElementById('filterForm');
            
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // In a real application, this would filter the properties
                alert('Filters applied! This would filter the properties in a real application.');
            });
            
            filterForm.addEventListener('reset', function() {
                setTimeout(() => {
                    priceMin.value = '500';
                    priceMax.value = '5000';
                    priceSlider.value = '5000';
                }, 0);
            });
        });

        // Google Maps initialization for property map
        function initMap() {
            // This function is called by the Google Maps API
            // The actual map initialization happens when a property modal is opened
        }
    </script>
</body>
</html>
