// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.createElement('button');
    mobileMenuBtn.className = 'mobile-menu-btn';
    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.header-content').appendChild(mobileMenuBtn);
    
    mobileMenuBtn.addEventListener('click', function() {
        document.querySelector('.nav-menu').classList.toggle('active');
    });
    
    // Hero Slider
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dots .dot');
    let currentSlide = 0;
    
    function showSlide(n) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    // Auto slide change
    let slideInterval = setInterval(nextSlide, 5000);
    
    // Manual controls
    document.querySelector('.next-slide').addEventListener('click', function() {
        clearInterval(slideInterval);
        nextSlide();
        slideInterval = setInterval(nextSlide, 5000);
    });
    
    document.querySelector('.prev-slide').addEventListener('click', function() {
        clearInterval(slideInterval);
        prevSlide();
        slideInterval = setInterval(nextSlide, 5000);
    });
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            clearInterval(slideInterval);
            showSlide(index);
            slideInterval = setInterval(nextSlide, 5000);
        });
    });
    
    // Testimonial Slider
    const testimonials = document.querySelectorAll('.testimonial');
    let currentTestimonial = 0;
    
    function showTestimonial(n) {
        testimonials.forEach(testimonial => testimonial.classList.remove('active'));
        
        currentTestimonial = (n + testimonials.length) % testimonials.length;
        testimonials[currentTestimonial].classList.add('active');
    }
    
    document.querySelector('.next-testimonial').addEventListener('click', function() {
        showTestimonial(currentTestimonial + 1);
    });
    
    document.querySelector('.prev-testimonial').addEventListener('click', function() {
        showTestimonial(currentTestimonial - 1);
    });
    
    // Wishlist toggle
    document.querySelectorAll('.product-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (this.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showToast('Product added to wishlist');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                showToast('Product removed from wishlist');
            }
        });
    });
    
    // Add to cart
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('h3 a').textContent;
            updateCartCount(1);
            showToast(`${productName} added to cart`);
        });
    });
    
    // Quick view (would normally open a modal with more product details)
    document.querySelectorAll('.quick-view').forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('h3 a').textContent;
            showToast(`Quick view: ${productName}`);
        });
    });
    
    // Toast notification
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
    }
    
    // Initialize cart count from localStorage if available
    if (localStorage.getItem('cartCount')) {
        document.querySelector('.cart-count').textContent = localStorage.getItem('cartCount');
    }
});

// Additional styles for dynamic elements
const style = document.createElement('style');
style.textContent = `
    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--dark-color);
        cursor: pointer;
        padding: 10px;
    }
    
    .toast {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--primary-color);
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1000;
    }
    
    .toast.show {
        opacity: 1;
    }
    
    .pulse {
        animation: pulse 0.3s ease;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1); }
    }
    
    @media (max-width: 768px) {
        .mobile-menu-btn {
            display: block;
        }
        
        .nav-menu {
            display: none;
            width: 100%;
            background-color: var(--primary-color);
            padding: 10px 0;
        }
        
        .nav-menu.active {
            display: block;
        }
        
        .nav-menu > li {
            width: 100%;
        }
        
        .nav-menu > li > a {
            padding: 12px 20px;
        }
        
        .dropdown-menu {
            position: static;
            width: 100%;
            box-shadow: none;
            padding: 10px;
        }
    }
`;
document.head.appendChild(style);