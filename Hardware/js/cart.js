// Cart Page Specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Quantity buttons functionality
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    
    quantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const input = document.querySelector(`.quantity-input[data-id="${productId}"]`);
            let quantity = parseInt(input.value);
            
            if (this.classList.contains('plus')) {
                quantity++;
            } else if (this.classList.contains('minus') && quantity > 1) {
                quantity--;
            }
            
            input.value = quantity;
            
            // Auto-submit the form when quantity changes
            const form = input.closest('form');
            if (form && quantity !== parseInt(input.defaultValue)) {
                form.submit();
            }
        });
    });
    
    // Quantity input validation
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            let quantity = parseInt(this.value);
            
            if (isNaN(quantity) || quantity < 1) {
                this.value = 1;
                quantity = 1;
            }
            
            // Auto-submit the form when quantity changes
            const form = this.closest('form');
            if (form && quantity !== parseInt(this.defaultValue)) {
                form.submit();
            }
        });
    });
    
    // Add to cart buttons in related products
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('h3 a').textContent;
            
            // Send AJAX request to add to cart
            addToCart(productId, 1, function(response) {
                if (response.success) {
                    // Show toast notification
                    showToast(`${productName} added to cart`);
                    
                    // Update cart count
                    updateCartCount(response.itemCount);
                } else {
                    alert('Error: ' + response.message);
                }
            });
        });
    });
    
    // Toast notification
    function showToast(message) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        
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
    function updateCartCount(count) {
        const cartCount = document.querySelector('.cart-count');
        cartCount.textContent = count;
        
        // Add animation
        cartCount.classList.add('pulse');
        setTimeout(() => {
            cartCount.classList.remove('pulse');
        }, 300);
        
        // Update localStorage if available
        if (typeof(Storage) !== "undefined") {
            localStorage.setItem('cartCount', count);
        }
    }
    
    // AJAX function to add to cart
    function addToCart(productId, quantity, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'add-to-cart.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        callback(response);
                    } catch (e) {
                        callback({success: false, message: 'Invalid response from server'});
                    }
                } else {
                    callback({success: false, message: 'Server error: ' + xhr.status});
                }
            }
        };
        xhr.send('product_id=' + productId + '&quantity=' + quantity);
    }
    
    // Initialize cart count from localStorage if available
    if (typeof(Storage) !== "undefined" && localStorage.getItem('cartCount')) {
        document.querySelector('.cart-count').textContent = localStorage.getItem('cartCount');
    }
});