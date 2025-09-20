// Offers Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Countdown timer for offers
    function updateCountdown() {
        // Set the date we're counting down to (3 days from now)
        const countDownDate = new Date();
        countDownDate.setDate(countDownDate.getDate() + 3);
        
        // Update the countdown every 1 second
        const countdownFunction = setInterval(function() {
            // Get today's date and time
            const now = new Date().getTime();
            
            // Find the distance between now and the count down date
            const distance = countDownDate - now;
            
            // Time calculations for days, hours, minutes and seconds
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Display the result
            document.getElementById("days").textContent = days.toString().padStart(2, '0');
            document.getElementById("hours").textContent = hours.toString().padStart(2, '0');
            document.getElementById("minutes").textContent = minutes.toString().padStart(2, '0');
            document.getElementById("seconds").textContent = seconds.toString().padStart(2, '0');
            
            // If the count down is finished, reset it
            if (distance < 0) {
                clearInterval(countdownFunction);
                // Reset to 3 days
                countDownDate.setDate(countDownDate.getDate() + 3);
            }
        }, 1000);
    }
    
    updateCountdown();
    
    // Add to cart from offers
    document.querySelectorAll('.offer-card .btn').forEach(button => {
        button.addEventListener('click', function(e) {
            // For demo purposes, we'll prevent navigation and show a toast
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                const offerTitle = this.closest('.offer-card').querySelector('h3').textContent;
                showToast(`${offerTitle} added to cart`);
                updateCartCount(1);
            }
        });
    });
    
    // Toast notification function
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    // Update cart count
    function updateCartCount(change) {
        const cartCount = document.querySelector('.cart-count');
        let count = parseInt(cartCount.textContent) || 0;
        count += change;
        cartCount.textContent = count;
        
        // Add animation
        cartCount.classList.add('pulse');
        setTimeout(() => {
            cartCount.classList.remove('pulse');
        }, 300);
        
        // Store in localStorage for persistence
        localStorage.setItem('cartCount', count);
    }
    
    // Newsletter form submission
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            if (email && isValidEmail(email)) {
                showToast('Thank you for subscribing to our newsletter!');
                this.reset();
            } else {
                showToast('Please enter a valid email address.');
            }
        });
    }
    
    // Simple email validation
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Initialize cart count from localStorage if available
    if (localStorage.getItem('cartCount')) {
        document.querySelector('.cart-count').textContent = localStorage.getItem('cartCount');
    }
});