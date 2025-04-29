// Agent Dashboard JavaScript

// Initialize dashboard data
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupEventListeners();
    initializeRevenueChart();
});

// Dashboard initialization
function initializeDashboard() {
    updateDashboardStats();
    loadRecentProperties();
    loadRecentMessages();
    loadMaintenanceRequests();
}

// Event listeners setup
function setupEventListeners() {
    // Add Property Modal
    const addPropertyBtn = document.querySelector('.btn-primary');
    const modal = document.getElementById('addPropertyModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const addPropertyForm = document.getElementById('addPropertyForm');

    if (addPropertyBtn) {
        addPropertyBtn.addEventListener('click', showAddPropertyModal);
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    if (addPropertyForm) {
        addPropertyForm.addEventListener('submit', handleAddProperty);
    }

    // Message actions
    document.querySelectorAll('.message-actions .btn-icon').forEach(button => {
        button.addEventListener('click', handleMessageAction);
    });

    // Maintenance actions
    document.querySelectorAll('.maintenance-actions .btn-icon').forEach(button => {
        button.addEventListener('click', handleMaintenanceAction);
    });
}

// Dashboard Stats
function updateDashboardStats() {
    // In a real application, this would fetch data from an API
    const stats = {
        totalProperties: 24,
        occupiedUnits: 18,
        vacantUnits: 6,
        monthlyRevenue: 24500
    };

    document.getElementById('total-properties').textContent = stats.totalProperties;
    document.getElementById('occupied-units').textContent = stats.occupiedUnits;
    document.getElementById('vacant-units').textContent = stats.vacantUnits;
    document.getElementById('monthly-revenue').textContent = `$${stats.monthlyRevenue.toLocaleString()}`;
}

// Property Management
function loadRecentProperties() {
    // In a real application, this would fetch data from an API
    const properties = [
        {
            id: 1,
            title: 'Luxury Apartment',
            location: 'Downtown, New York',
            price: 2500,
            status: 'occupied',
            image: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267'
        },
        {
            id: 2,
            title: 'Modern Villa',
            location: 'Beverly Hills, CA',
            price: 5000,
            status: 'vacant',
            image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9'
        },
        {
            id: 3,
            title: 'Office Space',
            location: 'Financial District, Chicago',
            price: 3800,
            status: 'occupied',
            image: 'https://images.unsplash.com/photo-1605276374104-dee2a0ed3cd6'
        }
    ];

    // Update property list in the UI
    const propertyList = document.querySelector('.property-list');
    if (propertyList) {
        propertyList.innerHTML = properties.map(property => `
            <div class="property-item">
                <div class="property-image">
                    <img src="${property.image}" alt="${property.title}">
                    <span class="property-status ${property.status}">${property.status.charAt(0).toUpperCase() + property.status.slice(1)}</span>
                </div>
                <div class="property-details">
                    <h3>${property.title}</h3>
                    <p class="property-location"><i class="fa-solid fa-location-dot"></i> ${property.location}</p>
                    <p class="property-price">$${property.price}/month</p>
                </div>
                <div class="property-actions">
                    <button class="btn-icon" title="Edit" onclick="editProperty(${property.id})"><i class="fa-solid fa-edit"></i></button>
                    <button class="btn-icon" title="View Details" onclick="viewPropertyDetails(${property.id})"><i class="fa-solid fa-eye"></i></button>
                </div>
            </div>
        `).join('');
    }
}

// Message Management
function loadRecentMessages() {
    // In a real application, this would fetch data from an API
    const messages = [
        {
            id: 1,
            sender: 'John Smith',
            avatar: 'https://randomuser.me/api/portraits/men/32.jpg',
            preview: 'I\'m interested in the Modern Villa property. Is it still available?',
            time: '2 hours ago',
            unread: true
        },
        {
            id: 2,
            sender: 'Sarah Johnson',
            avatar: 'https://randomuser.me/api/portraits/women/44.jpg',
            preview: 'The maintenance request for the Luxury Apartment has been completed.',
            time: 'Yesterday',
            unread: false
        },
        {
            id: 3,
            sender: 'Michael Chen',
            avatar: 'https://randomuser.me/api/portraits/men/67.jpg',
            preview: 'Thank you for helping me find the perfect office space!',
            time: '3 days ago',
            unread: false
        }
    ];

    // Update message list in the UI
    const messageList = document.querySelector('.message-list');
    if (messageList) {
        messageList.innerHTML = messages.map(message => `
            <div class="message-item ${message.unread ? 'unread' : ''}">
                <div class="message-avatar">
                    <img src="${message.avatar}" alt="${message.sender}">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <h4>${message.sender}</h4>
                        <span class="message-time">${message.time}</span>
                    </div>
                    <p class="message-preview">${message.preview}</p>
                </div>
                <div class="message-actions">
                    ${message.unread ? `
                        <button class="btn-icon" title="Mark as Read" onclick="markMessageAsRead(${message.id})">
                            <i class="fa-solid fa-envelope-open"></i>
                        </button>
                    ` : ''}
                    <button class="btn-icon" title="Reply" onclick="replyToMessage(${message.id})">
                        <i class="fa-solid fa-reply"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
}

// Maintenance Requests
function loadMaintenanceRequests() {
    // In a real application, this would fetch data from an API
    const requests = [
        {
            id: 1,
            title: 'Plumbing Issue',
            property: 'Luxury Apartment - Unit 304',
            dueDate: 'Today',
            priority: 'high',
            status: 'pending',
            icon: 'fa-wrench'
        },
        {
            id: 2,
            title: 'Electrical Repair',
            property: 'Office Space - Suite 502',
            dueDate: 'Tomorrow',
            priority: 'medium',
            status: 'assigned',
            icon: 'fa-bolt'
        },
        {
            id: 3,
            title: 'Painting',
            property: 'Modern Villa',
            dueDate: 'Next Week',
            priority: 'low',
            status: 'completed',
            icon: 'fa-paint-roller'
        }
    ];

    // Update maintenance list in the UI
    const maintenanceList = document.querySelector('.maintenance-list');
    if (maintenanceList) {
        maintenanceList.innerHTML = requests.map(request => `
            <div class="maintenance-item ${request.priority}-priority">
                <div class="maintenance-icon">
                    <i class="fa-solid ${request.icon}"></i>
                </div>
                <div class="maintenance-details">
                    <h4>${request.title}</h4>
                    <p>${request.property}</p>
                    <span class="maintenance-date">Due: ${request.dueDate}</span>
                </div>
                <div class="maintenance-status">
                    <span class="status-badge ${request.status}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span>
                </div>
                <div class="maintenance-actions">
                    ${request.status === 'pending' ? `
                        <button class="btn-icon" title="Assign" onclick="assignMaintenance(${request.id})">
                            <i class="fa-solid fa-user-plus"></i>
                        </button>
                    ` : request.status === 'assigned' ? `
                        <button class="btn-icon" title="Update Status" onclick="updateMaintenanceStatus(${request.id})">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    ` : ''}
                    <button class="btn-icon" title="View Details" onclick="viewMaintenanceDetails(${request.id})">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
}

// Modal Functions
function showAddPropertyModal() {
    const modal = document.getElementById('addPropertyModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal() {
    const modal = document.getElementById('addPropertyModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Property Form Handler
function handleAddProperty(event) {
    event.preventDefault();
    
    const formData = {
        title: document.getElementById('propertyTitle').value,
        type: document.getElementById('propertyType').value,
        location: document.getElementById('propertyLocation').value,
        price: document.getElementById('propertyPrice').value,
        status: document.getElementById('propertyStatus').value,
        description: document.getElementById('propertyDescription').value,
        image: document.getElementById('propertyImage').value
    };

    // In a real application, this would send data to an API
    console.log('Adding new property:', formData);
    
    // Close modal and refresh properties
    closeModal();
    loadRecentProperties();
}

// Message Actions
function handleMessageAction(event) {
    const action = event.currentTarget.getAttribute('title');
    const messageId = event.currentTarget.closest('.message-item').dataset.id;

    switch (action) {
        case 'Mark as Read':
            markMessageAsRead(messageId);
            break;
        case 'Reply':
            replyToMessage(messageId);
            break;
        case 'View Details':
            viewMessageDetails(messageId);
            break;
    }
}

function markMessageAsRead(messageId) {
    // In a real application, this would update the message status via API
    console.log('Marking message as read:', messageId);
    loadRecentMessages();
}

function replyToMessage(messageId) {
    // In a real application, this would open a reply form
    console.log('Replying to message:', messageId);
}

function viewMessageDetails(messageId) {
    // In a real application, this would show message details
    console.log('Viewing message details:', messageId);
}

// Maintenance Actions
function handleMaintenanceAction(event) {
    const action = event.currentTarget.getAttribute('title');
    const requestId = event.currentTarget.closest('.maintenance-item').dataset.id;

    switch (action) {
        case 'Assign':
            assignMaintenance(requestId);
            break;
        case 'Update Status':
            updateMaintenanceStatus(requestId);
            break;
        case 'View Details':
            viewMaintenanceDetails(requestId);
            break;
    }
}

function assignMaintenance(requestId) {
    // In a real application, this would assign the maintenance request
    console.log('Assigning maintenance request:', requestId);
    loadMaintenanceRequests();
}

function updateMaintenanceStatus(requestId) {
    // In a real application, this would update the maintenance status
    console.log('Updating maintenance status:', requestId);
    loadMaintenanceRequests();
}

function viewMaintenanceDetails(requestId) {
    // In a real application, this would show maintenance details
    console.log('Viewing maintenance details:', requestId);
}

// Property Actions
function editProperty(propertyId) {
    // In a real application, this would open the edit property form
    console.log('Editing property:', propertyId);
}

function viewPropertyDetails(propertyId) {
    // In a real application, this would show property details
    console.log('Viewing property details:', propertyId);
}

// Revenue Chart
function initializeRevenueChart() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    // In a real application, this would fetch data from an API
    const data = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Monthly Revenue',
            data: [21000, 19500, 22000, 23800, 24500, 24000],
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            tension: 0.4
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    };

    new Chart(ctx, config);
}

// Dashboard Refresh
function refreshDashboard() {
    initializeDashboard();
}
