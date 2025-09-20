// main.js - JavaScript functionality for the entire site

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Global Site Functionality ---

    // Mobile menu toggle
    const mobileMenuBtn = document.createElement('button');
    mobileMenuBtn.className = 'mobile-menu-btn';
    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
    const headerContent = document.querySelector('.header-content');
    if (headerContent) {
        headerContent.appendChild(mobileMenuBtn);
    }
    mobileMenuBtn.addEventListener('click', () => {
        const navMenu = document.querySelector('.nav-menu');
        navMenu?.classList.toggle('active');
    });
    
    // Dropdown menu functionality
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.addEventListener('mouseenter', () => dropdown.classList.add('active'));
        dropdown.addEventListener('mouseleave', () => dropdown.classList.remove('active'));
    });

    // Quick view functionality (redirects to product detail page)
    document.querySelectorAll('.quick-view').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            if (productId) {
                window.location.href = `product-detail.php?id=${productId}`;
            }
        });
    });

    // Newsletter form submission
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (re.test(email)) {
                showToast('Thank you for subscribing!');
                emailInput.value = '';
            } else {
                showToast('Please enter a valid email address.', true);
            }
        });
    }

    // --- Homepage Specific Sliders ---

    // Hero slider functionality
    const sliderContainer = document.querySelector('.slider-container');
    if (sliderContainer) {
        const slides = sliderContainer.querySelectorAll('.slide');
        const dotsContainer = sliderContainer.querySelector('.slider-dots');
        const prevBtn = sliderContainer.querySelector('.prev-slide');
        const nextBtn = sliderContainer.querySelector('.next-slide');
        let currentSlide = 0;
        
        if (dotsContainer && slides.length > 1) {
            slides.forEach((_, i) => {
                const dot = document.createElement('button');
                dot.className = i === 0 ? 'dot active' : 'dot';
                dot.addEventListener('click', () => goToSlide(i));
                dotsContainer.appendChild(dot);
            });
        }
        
        const goToSlide = (n) => {
            if (slides.length === 0) return;
            slides[currentSlide].classList.remove('active');
            const dots = dotsContainer?.querySelectorAll('.dot');
            dots?.[currentSlide]?.classList.remove('active');
            
            currentSlide = (n + slides.length) % slides.length;
            
            slides[currentSlide].classList.add('active');
            dots?.[currentSlide]?.classList.add('active');
        };
        
        prevBtn?.addEventListener('click', () => goToSlide(currentSlide - 1));
        nextBtn?.addEventListener('click', () => goToSlide(currentSlide + 1));
        if (slides.length > 1) setInterval(() => goToSlide(currentSlide + 1), 5000);
    }
    
    // Testimonial slider functionality
    const testimonialSlider = document.querySelector('.testimonial-slider');
    if (testimonialSlider) {
        const testimonials = testimonialSlider.querySelectorAll('.testimonial');
        const prevBtn = testimonialSlider.querySelector('.prev-testimonial');
        const nextBtn = testimonialSlider.querySelector('.next-testimonial');
        let currentTestimonial = 0;
        
        const showTestimonial = (n) => {
            if (testimonials.length === 0) return;
            testimonials.forEach(t => t.classList.remove('active'));
            currentTestimonial = (n + testimonials.length) % testimonials.length;
            testimonials[currentTestimonial].classList.add('active');
        };
        
        prevBtn?.addEventListener('click', () => showTestimonial(currentTestimonial - 1));
        nextBtn?.addEventListener('click', () => showTestimonial(currentTestimonial + 1));
        if (testimonials.length > 1) setInterval(() => showTestimonial(currentTestimonial + 1), 7000);
    }

    // --- AJAX Functionality for Cart & Wishlist ---

    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;
            
            const productId = this.getAttribute('data-id');
            const productCard = this.closest('.product-card');
            const productName = productCard?.querySelector('h3 a')?.textContent || 'Product';
            const originalButtonHTML = this.innerHTML; // Store original HTML

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; // Show loading spinner
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'add-to-cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    // Reset button state
                    button.disabled = false;
                    button.innerHTML = originalButtonHTML;
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Show toast notification
                                showToast(`${productName} added to cart`);
                                
                                // Update cart count
                                updateCartCount(response.itemCount);
                            } else {
                                showToast('Error: ' + response.message, true);
                            }
                        } catch (e) {
                            showToast('Error adding product to cart', true);
                        }
                    } else {
                        showToast('Server error. Please try again.', true);
                    }
                }
            };
            xhr.send('product_id=' + productId + '&quantity=1');
        });
    });
    
    // Wishlist toggle functionality
    document.querySelectorAll('.product-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const isInWishlist = this.getAttribute('data-in-wishlist') === 'true';
            const action = isInWishlist ? 'remove' : 'add';
            const heartIcon = this.querySelector('i');
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'toggle-wishlist.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Update UI
                            if (response.action === 'added') {
                                heartIcon.classList.remove('far');
                                heartIcon.classList.add('fas');
                                button.setAttribute('data-in-wishlist', 'true');
                                button.classList.add('in-wishlist');
                                
                                // Show success message
                                showToast('Product added to wishlist');
                            } else {
                                heartIcon.classList.remove('fas');
                                heartIcon.classList.add('far');
                                button.setAttribute('data-in-wishlist', 'false');
                                button.classList.remove('in-wishlist');
                                
                                // Show success message
                                showToast('Product removed from wishlist');
                            }
                            
                            // Update wishlist count
                            updateWishlistCount(response.wishlistCount);
                        } else {
                            if (response.message.includes('login')) {
                                // Redirect to login for guests
                                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                            } else {
                                showToast('Error: ' + response.message, true);
                            }
                        }
                    } catch (e) {
                        showToast('Error updating wishlist', true);
                    }
                }
            };
            xhr.send('product_id=' + productId + '&action=' + action);
        });
    });

    // --- Helper Functions ---

    // Shows a toast notification at the bottom-right of the screen
    function showToast(message, isError = false) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = isError ? 'toast error' : 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Updates the cart count in the header with a pulse animation
    function updateCartCount(count) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = count;
            cartCount.classList.add('pulse');
            setTimeout(() => {
                cartCount.classList.remove('pulse');
            }, 500);
        }
    }
    
    // Updates the wishlist count in the header with a pulse animation
    function updateWishlistCount(count) {
        const wishlistCount = document.querySelector('.wishlist-count');
        if (wishlistCount) {
            wishlistCount.textContent = count;
            wishlistCount.classList.add('pulse');
            setTimeout(() => {
                wishlistCount.classList.remove('pulse');
            }, 500);
        }
    }
    
    // --- Responsive Navigation ---
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        const navMenu = document.querySelector('.nav-menu');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        
        if (navMenu?.classList.contains('active') && 
            !navMenu.contains(e.target) && 
            !mobileMenuBtn?.contains(e.target)) {
            navMenu.classList.remove('active');
        }
    });
    
    // Prevent dropdown from closing when clicking inside
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // --- Image Error Handling ---
    
    // Handle broken images
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function() {
            if (this.src.includes('products/')) {
                this.src = 'images/products/default-product.jpg';
            }
        });
    });
});

// --- Global AJAX Functions ---

// Function to add item to cart via AJAX (used in product detail pages)
function addToCart(productId, quantity = 1) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'add-to-cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Update cart count
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = response.itemCount;
                        cartCount.classList.add('pulse');
                        setTimeout(() => {
                            cartCount.classList.remove('pulse');
                        }, 500);
                    }
                    
                    // Show success message
                    showToast('Product added to cart');
                } else {
                    showToast('Error: ' + response.message, true);
                }
            } catch (e) {
                showToast('Error adding product to cart', true);
            }
        }
    };
    xhr.send('product_id=' + productId + '&quantity=' + quantity);
}

// Function to toggle wishlist via AJAX (used in product detail pages)
function toggleWishlist(productId, buttonElement) {
    const isInWishlist = buttonElement.getAttribute('data-in-wishlist') === 'true';
    const action = isInWishlist ? 'remove' : 'add';
    const heartIcon = buttonElement.querySelector('i');
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'toggle-wishlist.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Update UI
                    if (response.action === 'added') {
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas');
                        buttonElement.setAttribute('data-in-wishlist', 'true');
                        buttonElement.classList.add('in-wishlist');
                        
                        // Show success message
                        showToast('Product added to wishlist');
                    } else {
                        heartIcon.classList.remove('fas');
                        heartIcon.classList.add('far');
                        buttonElement.setAttribute('data-in-wishlist', 'false');
                        buttonElement.classList.remove('in-wishlist');
                        
                        // Show success message
                        showToast('Product removed from wishlist');
                    }
                    
                    // Update wishlist count
                    const wishlistCount = document.querySelector('.wishlist-count');
                    if (wishlistCount) {
                        wishlistCount.textContent = response.wishlistCount;
                        wishlistCount.classList.add('pulse');
                        setTimeout(() => {
                            wishlistCount.classList.remove('pulse');
                        }, 500);
                    }
                } else {
                    if (response.message.includes('login')) {
                        // Redirect to login for guests
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                    } else {
                        showToast('Error: ' + response.message, true);
                    }
                }
            } catch (e) {
                showToast('Error updating wishlist', true);
            }
        }
    };
    xhr.send('product_id=' + productId + '&action=' + action);
}

// Global toast function
function showToast(message, isError = false) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = isError ? 'toast error' : 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}