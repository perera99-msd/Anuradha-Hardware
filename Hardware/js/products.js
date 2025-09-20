// products.js - JavaScript functionality for products page

document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality for products page
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;
            
            const productId = this.getAttribute('data-id');
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('h3 a').textContent;
            const originalButtonHTML = this.innerHTML;
            
            // Show loading state
            this.disabled = true;
            this.innerHTML = '<span class="loading"></span>';
            
            // Send AJAX request to add to cart
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
    
    // Wishlist functionality
    const wishlistButtons = document.querySelectorAll('.product-wishlist');
    
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const isInWishlist = this.getAttribute('data-in-wishlist') === 'true';
            const heartIcon = this.querySelector('i');
            
            // Toggle wishlist for logged-in users
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
            xhr.send('product_id=' + productId + '&action=' + (isInWishlist ? 'remove' : 'add'));
        });
    });
    
    // Sorting functionality
    const sortSelect = document.getElementById('sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', this.value);
            // Reset to page 1 when sorting changes
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        });
    }
    
    // Filter functionality
    const applyFiltersBtn = document.getElementById('applyFilters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            const url = new URL(window.location.href);
            
            // Apply price filter
            const priceRange = document.getElementById('priceRange');
            if (priceRange && priceRange.value !== "100000") {
                url.searchParams.set('max_price', priceRange.value);
            } else {
                url.searchParams.delete('max_price');
            }
            
            // Apply brand filters
            const selectedBrands = Array.from(document.querySelectorAll('input[name="brand"]:checked'))
                .map(brand => brand.value);
            
            if (selectedBrands.length > 0) {
                url.searchParams.set('brands', selectedBrands.join(','));
            } else {
                url.searchParams.delete('brands');
            }
            
            // Apply stock filters
            const inStock = document.querySelector('input[name="stock"][value="in-stock"]').checked;
            const outOfStock = document.querySelector('input[name="stock"][value="out-of-stock"]').checked;
            
            if (inStock && !outOfStock) {
                url.searchParams.set('stock', 'in');
            } else if (!inStock && outOfStock) {
                url.searchParams.set('stock', 'out');
            } else {
                url.searchParams.delete('stock');
            }
            
            // Reset to page 1 when filters change
            url.searchParams.set('page', '1');
            
            window.location.href = url.toString();
        });
    }
    
    // Price range display
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        // Set initial value from URL if exists
        const urlParams = new URLSearchParams(window.location.search);
        const maxPrice = urlParams.get('max_price');
        if (maxPrice) {
            priceRange.value = maxPrice;
        }
        updatePriceRangeDisplay();
        
        priceRange.addEventListener('input', updatePriceRangeDisplay);
    }
    
    function updatePriceRangeDisplay() {
        const priceRange = document.getElementById('priceRange');
        const priceValues = document.querySelector('.price-values');
        if (priceRange && priceValues) {
            const currentValue = parseInt(priceRange.value);
            priceValues.innerHTML = `<span>Rs. 0</span><span>Rs. ${currentValue.toLocaleString()}</span>`;
        }
    }
    
    // Toast notification function
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
    
    // Update cart count
    function updateCartCount(count) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = count;
            
            // Add animation
            cartCount.classList.add('pulse');
            setTimeout(() => {
                cartCount.classList.remove('pulse');
            }, 300);
        }
    }
});