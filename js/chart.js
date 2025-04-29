// Charts and Statistics Functionality

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts with animation
    initializeCharts();
    
    function initializeCharts() {
        // Bar Chart Animation
        const barChart = document.querySelector('.chart-bar');
        if (barChart) {
            setTimeout(() => {
                barChart.style.height = '80%';
            }, 500);
        }
        
        // Circle Chart Animation
        const circleChart = document.querySelector('.circle');
        if (circleChart) {
            setTimeout(() => {
                circleChart.setAttribute('stroke-dasharray', '92, 100');
            }, 500);
        }
        
        // Line Chart Animation is handled by CSS animation
    }
    
    // Add window scroll event to animate charts when they come into view
    const statSection = document.querySelector('.statistics');
    if (statSection) {
        window.addEventListener('scroll', function() {
            const sectionPosition = statSection.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (sectionPosition < screenPosition) {
                initializeCharts();
                // Remove event listener after animation is triggered
                window.removeEventListener('scroll', this);
            }
        });
    }
});