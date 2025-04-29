// Testimonial Carousel Functionality

document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('testimonial-track');
    const dots = document.querySelectorAll('.nav-dot');
    let currentSlide = 0;
    let autoplayInterval;
    
    if (!track || !dots.length) return;
    
    // Initialize carousel
    updateCarousel();
    startAutoplay();
    
    // Add click event listeners to navigation dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            updateCarousel();
            resetAutoplay();
        });
    });
    
    // Function to update carousel position
    function updateCarousel() {
        // Update track position
        track.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        // Update active dot
        dots.forEach((dot, index) => {
            if (index === currentSlide) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
    
    // Function to start autoplay
    function startAutoplay() {
        autoplayInterval = setInterval(() => {
            currentSlide = (currentSlide + 1) % dots.length;
            updateCarousel();
        }, 5000); // Change slide every 5 seconds
    }
    
    // Function to reset autoplay
    function resetAutoplay() {
        clearInterval(autoplayInterval);
        startAutoplay();
    }
    
    // Pause autoplay when user hovers over carousel
    track.addEventListener('mouseenter', () => {
        clearInterval(autoplayInterval);
    });
    
    // Resume autoplay when user leaves carousel
    track.addEventListener('mouseleave', () => {
        startAutoplay();
    });
    
    // Touch events for mobile swipe
    let touchStartX = 0;
    let touchEndX = 0;
    
    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        clearInterval(autoplayInterval);
    }, { passive: true });
    
    track.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        startAutoplay();
    }, { passive: true });
    
    function handleSwipe() {
        const swipeThreshold = 50; // Minimum distance for a swipe
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) < swipeThreshold) return;
        
        if (diff > 0) {
            // Swipe left - next slide
            currentSlide = Math.min(currentSlide + 1, dots.length - 1);
        } else {
            // Swipe right - previous slide
            currentSlide = Math.max(currentSlide - 1, 0);
        }
        
        updateCarousel();
    }
});