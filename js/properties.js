// Properties Page JavaScript

// Initialize properties page
document.addEventListener('DOMContentLoaded', function() {
    loadProperties();
    setupEventListeners();
});

// Load properties data
function loadProperties() {
    // In a real application, this would fetch data from an API
    const properties = [
        {
            id: 1,
            title: 'Luxury Apartment',
            type: 'apartment',
            location: 'Downtown, New York',
            price: 2500,
            status: 'occupied',
            image: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267',
            bedrooms: 2,
            bathrooms: 2,
            area: 1200,
            description: 'A luxurious apartment in the heart of downtown with stunning city views. Features modern appliances, hardwood floors, and a private balcony.',
            amenities: ['Parking', 'Gym', 'Pool', 'Security', 'Elevator', 'Central AC'],
            tenant: {
                name: 'John Smith',
                avatar: 'https://randomuser.me/api/portraits/men/32.jpg',
                email: 'john.smith@example.com',
                phone: '+1 (555) 123-4567',
                leaseStart: '2023-01-15',
                leaseEnd: '2024-01-14'
            }
        },
        {
            id: 2,
            title: 'Modern Villa',
            type: 'house',
            location: 'Beverly Hills, CA',
            price: 5000,
            status: 'vacant',
            image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9',
            bedrooms: 4,
            bathrooms: 3,
            area: 2800,
            description: 'A stunning modern villa with panoramic views of the city. Features high-end finishes, a gourmet kitchen, and a spacious backyard with a pool.',
            amenities: ['Pool', 'Garden', 'Garage', 'Security System', 'Smart Home', 'Wine Cellar'],
            tenant: null
        },
        {
            id: 3,
            title: 'Office Space',
            type: 'office',
            location: 'Financial District, Chicago',
            price: 3800,
            status: 'occupied',
            image: 'https://images.unsplash.com/photo-1605276374104-dee2a0ed3cd6',
            bedrooms: 0,
            bathrooms: 2,
            area: 1500,
            description: 'Modern office space in a prime location. Features open floor plan, high-speed internet, meeting rooms, and 24/7 access.',
            amenities: ['High-Speed Internet', 'Meeting Rooms', 'Kitchen', 'Security', 'Parking', 'Reception'],
            tenant: {
                name: 'Tech Solutions Inc.',
                avatar: 'https://randomuser.me/api/portraits/men/67.jpg',
                email: 'contact@techsolutions.com',
                phone: '+1 (555) 987-6543',
                leaseStart: '2022-06-01',
                leaseEnd: '2025-05-31'
            }
        },
        {
            id: 4,
            title: 'Cozy Condo',
            type: 'condo',
            location: 'Seattle, WA',
            price: 1800,
            status: 'maintenance',
            image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750',
            bedrooms: 1,
            bathrooms: 1,
            area: 800,
            description: 'A cozy condo in a quiet neighborhood. Perfect for singles or couples. Features updated kitchen and bathroom.',
            amenities: ['Parking', 'Storage', 'Security', 'Elevator', 'Bike Storage'],
            tenant: {
                name: 'Sarah Johnson',
                avatar: 'https://randomuser.me/api/portraits/women/44.jpg',
                email: 'sarah.j@example.com',
                phone: '+1 (555) 234-5678',
                leaseStart: '2022-09-01',
                leaseEnd: '2023-08-31'
            }
        },
        {
            id: 5,
            title: 'Retail Space',
            type: 'commercial',
            location: 'Shopping Mall, Miami',
            price: 4200,
            status: 'vacant',
            image: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c',
            bedrooms: 0,
            bathrooms: 1,
            area: 1200,
            description: 'Prime retail space in a high-traffic shopping mall. Features storefront windows, storage room, and updated electrical.',
            amenities: ['Storefront Windows', 'Storage Room', 'Security', 'Parking', 'Loading Dock'],
            tenant: null
        },
        {
            id: 6,
            title: 'Family Home',
            type: 'house',
            location: 'Suburbs, Boston',
            price: 3200,
            status: 'occupied',
            image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c',
            bedrooms: 3,
            bathrooms: 2,
            area: 2000,
            description: 'Spacious family home in a quiet suburban neighborhood. Features a large backyard, updated kitchen, and finished basement.',
            amenities: ['Backyard', 'Garage', 'Basement', 'Security System', 'Fireplace', 'Deck'],
            tenant: {
                name: 'Michael Chen',
                avatar: 'https://randomuser.me/api/portraits/men/67.jpg',
                email: 'michael.chen@example.com',
                phone: '+1 (555) 876-5432',
                leaseStart: '2021-12-01',
                leaseEnd: '2023-11-30'
            }
        }
    ];

    // Store properties in localStorage for demo purposes
    localStorage.setItem('properties', JSON.stringify(properties));
    
    // Display properties
    displayProperties(properties);
}

// Display properties in the grid
function displayProperties(properties) {
    const propertiesGrid = document.getElementById('propertiesGrid');
    if (!propertiesGrid) return;
    
    // Clear existing content
    propertiesGrid.innerHTML = '';
    
    // Add property cards
    properties.forEach(property => {
        const propertyCard = createPropertyCard(property);
        propertiesGrid.appendChild(propertyCard);
    });
}

// Create a property card element
function createPropertyCard(property) {
    const card = document.createElement('div');
    card.className = 'property-card';
    card.dataset.id = property.id;
    
    // Status class
    const statusClass = property.status === 'occupied' ? 'occupied' : 
                        property.status === 'vacant' ? 'vacant' : 'maintenance';
    
    // Format price
    const formattedPrice = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(property.price);
    
    card.innerHTML = `
        <div class="property-card-image">
            <img src="${property.image}" alt="${property.title}">
            <span class="property-card-status ${statusClass}">${property.status.charAt(0).toUpperCase() + property.status.slice(1)}</span>
        </div>
        <div class="property-card-content">
            <h3 class="property-card-title">${property.title}</h3>
            <p class="property-card-location"><i class="fa-solid fa-location-dot"></i> ${property.location}</p>
            <p class="property-card-price">${formattedPrice}/month</p>
            <div class="property-card-features">
                <div class="property-card-feature">
                    <i class="fa-solid fa-bed feature-icon"></i>
                    <span class="feature-value">${property.bedrooms} Beds</span>
                </div>
                <div class="property-card-feature">
                    <i class="fa-solid fa-bath feature-icon"></i>
                    <span class="feature-value">${property.bathrooms} Baths</span>
                </div>
                <div class="property-card-feature">
                    <i class="fa-solid fa-ruler-combined feature-icon"></i>
                    <span class="feature-value">${property.area} sq ft</span>
                </div>
            </div>
            <div class="property-card-actions">
                <button class="btn btn-secondary" onclick="viewPropertyDetails(${property.id})">View Details</button>
                <button class="btn btn-primary" onclick="editProperty(${property.id})">Edit</button>
            </div>
        </div>
    `;
    
    return card;
}

// View property details
function viewPropertyDetails(propertyId) {
    // Get property data
    const properties = JSON.parse(localStorage.getItem('properties') || '[]');
    const property = properties.find(p => p.id === propertyId);
    
    if (!property) return;
    
    // Format price
    const formattedPrice = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(property.price);
    
    // Update modal content
    document.getElementById('detailPropertyImage').src = property.image;
    document.getElementById('detailPropertyTitle').textContent = property.title;
    document.getElementById('detailPropertyLocation').innerHTML = `<i class="fa-solid fa-location-dot"></i> ${property.location}`;
    document.getElementById('detailPropertyPrice').textContent = `${formattedPrice}/month`;
    document.getElementById('detailPropertyDescription').textContent = property.description;
    
    // Status badge
    const statusClass = property.status === 'occupied' ? 'occupied' : 
                        property.status === 'vacant' ? 'vacant' : 'maintenance';
    const statusText = property.status.charAt(0).toUpperCase() + property.status.slice(1);
    document.getElementById('detailPropertyStatus').className = `property-status ${statusClass}`;
    document.getElementById('detailPropertyStatus').textContent = statusText;
    
    // Amenities
    const amenitiesList = document.getElementById('detailPropertyAmenities');
    amenitiesList.innerHTML = '';
    property.amenities.forEach(amenity => {
        const li = document.createElement('li');
        li.innerHTML = `<i class="fa-solid fa-check"></i> ${amenity}`;
        amenitiesList.appendChild(li);
    });
    
    // Tenant info
    const tenantInfo = document.getElementById('detailPropertyTenant');
    if (property.tenant) {
        const leaseStart = new Date(property.tenant.leaseStart).toLocaleDateString();
        const leaseEnd = new Date(property.tenant.leaseEnd).toLocaleDateString();
        
        tenantInfo.innerHTML = `
            <div class="property-tenant-info">
                <div class="tenant-avatar">
                    <img src="${property.tenant.avatar}" alt="${property.tenant.name}">
                </div>
                <div class="tenant-details">
                    <h5>${property.tenant.name}</h5>
                    <p>Lease: ${leaseStart} - ${leaseEnd}</p>
                </div>
            </div>
            <div class="tenant-contact">
                <a href="mailto:${property.tenant.email}"><i class="fa-solid fa-envelope"></i> Email</a>
                <a href="tel:${property.tenant.phone}"><i class="fa-solid fa-phone"></i> Call</a>
            </div>
        `;
    } else {
        tenantInfo.innerHTML = '<p>No tenant assigned</p>';
    }
    
    // Show modal
    const modal = document.getElementById('propertyDetailsModal');
    modal.style.display = 'block';
    
    // Set up edit button
    document.getElementById('editPropertyBtn').onclick = function() {
        editProperty(propertyId);
    };
    
    // Set up maintenance history button
    document.getElementById('viewMaintenanceBtn').onclick = function() {
        viewMaintenanceHistory(propertyId);
    };
}

// Edit property
function editProperty(propertyId) {
    // In a real application, this would open an edit form
    console.log('Editing property:', propertyId);
    alert('Edit functionality would be implemented here');
}

// View maintenance history
function viewMaintenanceHistory(propertyId) {
    // In a real application, this would show maintenance history
    console.log('Viewing maintenance history for property:', propertyId);
    alert('Maintenance history would be displayed here');
}

// Show add property modal
function showAddPropertyModal() {
    const modal = document.getElementById('addPropertyModal');
    modal.style.display = 'block';
}

// Close modal
function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
}

// Export properties
function exportProperties() {
    // In a real application, this would export properties to CSV or PDF
    console.log('Exporting properties');
    alert('Properties would be exported here');
}

// Setup event listeners
function setupEventListeners() {
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Close modals when clicking close button
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });
    
    // Add property form submission
    const addPropertyForm = document.getElementById('addPropertyForm');
    if (addPropertyForm) {
        addPropertyForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Get form data
            const formData = {
                title: document.getElementById('propertyTitle').value,
                type: document.getElementById('propertyType').value,
                location: document.getElementById('propertyLocation').value,
                price: parseFloat(document.getElementById('propertyPrice').value),
                status: document.getElementById('propertyStatus').value,
                description: document.getElementById('propertyDescription').value,
                image: document.getElementById('propertyImage').value,
                // Default values for demo
                bedrooms: 2,
                bathrooms: 1,
                area: 1000,
                amenities: ['Parking', 'Security'],
                tenant: null
            };
            
            // In a real application, this would send data to an API
            console.log('Adding new property:', formData);
            
            // Add to local storage for demo
            const properties = JSON.parse(localStorage.getItem('properties') || '[]');
            formData.id = properties.length > 0 ? Math.max(...properties.map(p => p.id)) + 1 : 1;
            properties.push(formData);
            localStorage.setItem('properties', JSON.stringify(properties));
            
            // Refresh properties list
            displayProperties(properties);
            
            // Close modal and reset form
            closeModal();
            addPropertyForm.reset();
        });
    }
    
    // Filter properties
    const filterInputs = document.querySelectorAll('.filter-group select, #propertySearch');
    filterInputs.forEach(input => {
        input.addEventListener('change', filterProperties);
    });
    
    // Search input
    const searchInput = document.getElementById('propertySearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterProperties);
    }
    
    // Pagination
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    
    if (prevPageBtn && nextPageBtn) {
        prevPageBtn.addEventListener('click', function() {
            // In a real application, this would load the previous page
            console.log('Loading previous page');
        });
        
        nextPageBtn.addEventListener('click', function() {
            // In a real application, this would load the next page
            console.log('Loading next page');
        });
    }
}

// Filter properties
function filterProperties() {
    const properties = JSON.parse(localStorage.getItem('properties') || '[]');
    const searchTerm = document.getElementById('propertySearch').value.toLowerCase();
    const typeFilter = document.getElementById('propertyType').value;
    const statusFilter = document.getElementById('propertyStatus').value;
    const locationFilter = document.getElementById('propertyLocation').value;
    const priceFilter = document.getElementById('propertyPrice').value;
    
    // Filter properties
    const filteredProperties = properties.filter(property => {
        // Search term
        const matchesSearch = property.title.toLowerCase().includes(searchTerm) || 
                             property.location.toLowerCase().includes(searchTerm);
        
        // Type filter
        const matchesType = !typeFilter || property.type === typeFilter;
        
        // Status filter
        const matchesStatus = !statusFilter || property.status === statusFilter;
        
        // Location filter
        const matchesLocation = !locationFilter || property.location.toLowerCase().includes(locationFilter);
        
        // Price filter
        let matchesPrice = true;
        if (priceFilter) {
            const [min, max] = priceFilter.split('-').map(val => val === '+' ? Infinity : parseInt(val));
            matchesPrice = property.price >= min && (max === Infinity || property.price <= max);
        }
        
        return matchesSearch && matchesType && matchesStatus && matchesLocation && matchesPrice;
    });
    
    // Display filtered properties
    displayProperties(filteredProperties);
} 