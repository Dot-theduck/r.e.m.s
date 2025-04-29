// Main JavaScript functionality

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenuBtn.classList.toggle('active');
            mainNav.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.mobile-menu-btn') && !event.target.closest('.main-nav') && mainNav.classList.contains('active')) {
            mobileMenuBtn.classList.remove('active');
            mainNav.classList.remove('active');
            document.body.classList.remove('no-scroll');
        }
    });
    
    // User Profile Dropdown (for mobile)
    const profileBtn = document.querySelector('.profile-btn');
    const dropdownContent = document.querySelector('.dropdown-content');
    
    if (profileBtn && dropdownContent && window.innerWidth < 768) {
        profileBtn.addEventListener('click', function(event) {
            event.preventDefault();
            dropdownContent.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.profile-dropdown') && dropdownContent.classList.contains('show')) {
                dropdownContent.classList.remove('show');
            }
        });
    }
    
    // Refresh Statistics Button
    const refreshBtn = document.getElementById('refresh-stats');
    
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Add loading state
            refreshBtn.classList.add('loading');
            refreshBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
            
            // Simulate data loading
            setTimeout(function() {
                // Update statistics with new random values
                updateStatistics();
                
                // Remove loading state
                refreshBtn.classList.remove('loading');
                refreshBtn.innerHTML = '<i class="fa-solid fa-sync-alt"></i> Refresh';
            }, 1500);
        });
    }
    
    // Function to update statistics with random values
    function updateStatistics() {
        // Total Properties: Random number between 100 and 150
        const totalProperties = document.getElementById('total-properties');
        if (totalProperties) {
            const newTotal = Math.floor(Math.random() * 51) + 100;
            animateValue(totalProperties, parseInt(totalProperties.textContent), newTotal, 1000);
            
            // Update bar chart height
            const barChart = document.querySelector('.chart-bar');
            if (barChart) {
                barChart.style.height = `${(newTotal / 150) * 100}%`;
            }
        }
        
        // Occupancy Rate: Random number between 80 and 98
        const occupancyRate = document.getElementById('occupancy-rate');
        if (occupancyRate) {
            const newRate = Math.floor(Math.random() * 19) + 80;
            animateValue(occupancyRate, parseInt(occupancyRate.textContent), newRate, 1000, '%');
            
            // Update circle chart
            const circleChart = document.querySelector('.circle');
            if (circleChart) {
                circleChart.setAttribute('stroke-dasharray', `${newRate}, 100`);
            }
        }
        
        // Monthly Revenue: Random number between 200,000 and 300,000
        const monthlyRevenue = document.getElementById('monthly-revenue');
        if (monthlyRevenue) {
            const currentRevenue = parseInt(monthlyRevenue.textContent.replace(/[^0-9]/g, ''));
            const newRevenue = Math.floor(Math.random() * 100001) + 200000;
            animateValue(monthlyRevenue, currentRevenue, newRevenue, 1000, '$', true);
            
            // Update line chart (simplified)
            const lineChart = document.querySelector('.chart-line polyline');
            if (lineChart) {
                const points = [];
                for (let i = 0; i < 6; i++) {
                    const x = i * 20;
                    const y = Math.floor(Math.random() * 20) + 5;
                    points.push(`${x},${y}`);
                }
                lineChart.setAttribute('points', points.join(' '));
                
                // Reset animation
                lineChart.style.animation = 'none';
                setTimeout(() => {
                    lineChart.style.animation = 'dash 2s ease-in-out forwards';
                }, 10);
            }
        }
    }
    
    // Function to animate value changes
    function animateValue(element, start, end, duration, suffix = '', isCurrency = false) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            let value = Math.floor(progress * (end - start) + start);
            
            if (isCurrency) {
                element.textContent = '$' + value.toLocaleString();
            } else {
                element.textContent = value + suffix;
            }
            
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
});