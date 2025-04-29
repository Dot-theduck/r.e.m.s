// Users Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeUsersPage();
    setupEventListeners();
});

// Initialize the users page
function initializeUsersPage() {
    loadTenants();
    updatePagination();
}

// Set up event listeners
function setupEventListeners() {
    // Search functionality
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }

    // Filter functionality
    const filterSelects = document.querySelectorAll('.filter-group select');
    filterSelects.forEach(select => {
        select.addEventListener('change', handleFilters);
    });

    // Add tenant button
    const addTenantBtn = document.querySelector('.add-tenant-btn');
    if (addTenantBtn) {
        addTenantBtn.addEventListener('click', showAddTenantModal);
    }

    // Export tenants button
    const exportTenantsBtn = document.querySelector('.export-tenants-btn');
    if (exportTenantsBtn) {
        exportTenantsBtn.addEventListener('click', exportTenants);
    }

    // Pagination buttons
    const paginationBtns = document.querySelectorAll('.pagination-btn');
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', handlePagination);
    });
}

// Load tenants data
function loadTenants(page = 1) {
    // This would typically be an API call
    // For now, we'll use mock data
    const mockTenants = [
        {
            id: 'T001',
            name: 'John Doe',
            property: 'Sunset Apartments #101',
            contact: '+1 234-567-8900',
            leaseStart: '2023-01-01',
            leaseEnd: '2024-01-01',
            status: 'active',
            avatar: 'path/to/avatar1.jpg'
        },
        // Add more mock tenants as needed
    ];

    displayTenants(mockTenants);
}

// Display tenants in the table
function displayTenants(tenants) {
    const tableBody = document.querySelector('.tenants-table tbody');
    if (!tableBody) return;

    tableBody.innerHTML = '';
    tenants.forEach(tenant => {
        const row = createTenantRow(tenant);
        tableBody.appendChild(row);
    });
}

// Create a tenant row
function createTenantRow(tenant) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <div class="tenant-info-cell">
                <img src="${tenant.avatar}" alt="${tenant.name}" class="tenant-avatar">
                <div>
                    <div class="tenant-name">${tenant.name}</div>
                    <div class="tenant-id">ID: ${tenant.id}</div>
                </div>
            </div>
        </td>
        <td>${tenant.property}</td>
        <td>${tenant.contact}</td>
        <td>${formatDate(tenant.leaseStart)} - ${formatDate(tenant.leaseEnd)}</td>
        <td><span class="tenant-status ${tenant.status}">${capitalizeFirst(tenant.status)}</span></td>
        <td>
            <div class="action-buttons">
                <button class="view-tenant" data-id="${tenant.id}">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="edit-tenant" data-id="${tenant.id}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete-tenant" data-id="${tenant.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;

    // Add event listeners to action buttons
    const viewBtn = row.querySelector('.view-tenant');
    const editBtn = row.querySelector('.edit-tenant');
    const deleteBtn = row.querySelector('.delete-tenant');

    viewBtn.addEventListener('click', () => viewTenantDetails(tenant.id));
    editBtn.addEventListener('click', () => editTenant(tenant.id));
    deleteBtn.addEventListener('click', () => deleteTenant(tenant.id));

    return row;
}

// Handle search functionality
function handleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    // Implement search logic here
    // This would typically filter the tenants list based on the search term
}

// Handle filters
function handleFilters() {
    const statusFilter = document.querySelector('select[name="status"]').value;
    const propertyFilter = document.querySelector('select[name="property"]').value;
    const sortBy = document.querySelector('select[name="sort"]').value;

    // Implement filter logic here
    // This would typically filter and sort the tenants list based on the selected filters
}

// Show add tenant modal
function showAddTenantModal() {
    const modal = document.getElementById('addTenantModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

// Close modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// View tenant details
function viewTenantDetails(tenantId) {
    // This would typically fetch tenant details from an API
    // For now, we'll use mock data
    const mockTenantDetails = {
        id: tenantId,
        name: 'John Doe',
        property: 'Sunset Apartments #101',
        contact: '+1 234-567-8900',
        email: 'john.doe@example.com',
        leaseStart: '2023-01-01',
        leaseEnd: '2024-01-01',
        status: 'active',
        avatar: 'path/to/avatar1.jpg',
        // Add more details as needed
    };

    displayTenantDetails(mockTenantDetails);
}

// Display tenant details in modal
function displayTenantDetails(tenant) {
    const modal = document.getElementById('tenantDetailsModal');
    if (!modal) return;

    // Update modal content with tenant details
    const content = modal.querySelector('.tenant-details-content');
    content.innerHTML = `
        <div class="tenant-profile">
            <div class="tenant-avatar-large">
                <img src="${tenant.avatar}" alt="${tenant.name}">
            </div>
            <div class="tenant-info">
                <h3>${tenant.name}</h3>
                <div class="tenant-id">ID: ${tenant.id}</div>
                <span class="tenant-status ${tenant.status}">${capitalizeFirst(tenant.status)}</span>
            </div>
        </div>
        <div class="tenant-details-grid">
            <div class="tenant-detail-section">
                <h4>Contact Information</h4>
                <div class="tenant-detail-item">
                    <i class="fas fa-phone"></i>
                    <span>${tenant.contact}</span>
                </div>
                <div class="tenant-detail-item">
                    <i class="fas fa-envelope"></i>
                    <span>${tenant.email}</span>
                </div>
            </div>
            <div class="tenant-detail-section">
                <h4>Property Information</h4>
                <div class="tenant-detail-item">
                    <i class="fas fa-home"></i>
                    <span>${tenant.property}</span>
                </div>
                <div class="tenant-detail-item">
                    <i class="fas fa-calendar"></i>
                    <span>${formatDate(tenant.leaseStart)} - ${formatDate(tenant.leaseEnd)}</span>
                </div>
            </div>
        </div>
    `;

    modal.style.display = 'block';
}

// Edit tenant
function editTenant(tenantId) {
    // Implement edit tenant functionality
    console.log('Edit tenant:', tenantId);
}

// Delete tenant
function deleteTenant(tenantId) {
    if (confirm('Are you sure you want to delete this tenant?')) {
        // Implement delete tenant functionality
        console.log('Delete tenant:', tenantId);
    }
}

// Export tenants
function exportTenants() {
    // Implement export functionality
    console.log('Exporting tenants...');
}

// Handle pagination
function handlePagination(event) {
    const page = event.target.dataset.page;
    if (page) {
        loadTenants(parseInt(page));
        updatePagination();
    }
}

// Update pagination controls
function updatePagination() {
    // Implement pagination update logic
    // This would typically update the pagination buttons based on the current page and total pages
}

// Utility functions
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
} 