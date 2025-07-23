document.addEventListener('DOMContentLoaded', function() {
    // Quantity selectors
    const quantityMinus = document.querySelectorAll('.quantity-btn.minus');
    const quantityPlus = document.querySelectorAll('.quantity-btn.plus');
    const quantityInputs = document.querySelectorAll('.quantity-selector input');
    
    quantityMinus.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.nextElementSibling;
            if (parseInt(input.value) > parseInt(input.min)) {
                input.value = parseInt(input.value) - 1;
                updateCartItem(this.closest('tr'));
            }
        });
    });
    
    quantityPlus.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.previousElementSibling;
            input.value = parseInt(input.value) + 1;
            updateCartItem(this.closest('tr'));
        });
    });
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (parseInt(this.value) < parseInt(this.min)) {
                this.value = this.min;
            }
            updateCartItem(this.closest('tr'));
        });
    });
    
    // Remove item buttons
    const removeBtns = document.querySelectorAll('.remove-btn');
    removeBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            row.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                row.remove();
                updateCartTotals();
                updateCartCount();
            }, 300);
        });
    });
    
    // Shipping method selection
    const shippingMethods = document.querySelectorAll('input[name="shipping"]');
    shippingMethods.forEach(method => {
        method.addEventListener('change', function() {
            updateCartTotals();
        });
    });
    
    // Update cart item totals
    function updateCartItem(row) {
        const price = parseFloat(row.querySelector('.product-price').textContent.replace('Rs. ', '').replace(',', ''));
        const quantity = parseInt(row.querySelector('.quantity-selector input').value);
        const total = price * quantity;
        
        row.querySelector('.product-total').textContent = 'Rs. ' + total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        updateCartTotals();
        updateCartCount();
    }
    
    // Update cart totals
    function updateCartTotals() {
        let subtotal = 0;
        const rows = document.querySelectorAll('.cart-table tbody tr');
        
        rows.forEach(row => {
            const total = parseFloat(row.querySelector('.product-total').textContent.replace('Rs. ', '').replace(',', ''));
            subtotal += total;
        });
        
        // Calculate shipping
        let shipping = 0;
        const selectedShipping = document.querySelector('input[name="shipping"]:checked');
        
        if (selectedShipping) {
            const shippingText = selectedShipping.nextElementSibling.nextElementSibling.textContent;
            if (shippingText !== 'Free Shipping' && shippingText !== 'Rs. 0.00') {
                shipping = parseFloat(shippingText.replace('Rs. ', '').replace(',', ''));
            }
        }
        
        // Update totals
        document.querySelector('.cart-summary td:nth-child(2)').textContent = 'Rs. ' + subtotal.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        document.querySelector('.total-price').textContent = 'Rs. ' + (subtotal + shipping).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Update cart count in header
    function updateCartCount() {
        let totalItems = 0;
        const quantityInputs = document.querySelectorAll('.quantity-selector input');
        
        quantityInputs.forEach(input => {
            totalItems += parseInt(input.value);
        });
        
        // Update cart count in header
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = totalItems;
            
            // Add animation
            cartCount.classList.add('pulse');
            setTimeout(() => {
                cartCount.classList.remove('pulse');
            }, 300);
        }
        
        // Save to localStorage
        localStorage.setItem('cartCount', totalItems);
    }
    
    // Apply coupon code
    const applyCouponBtn = document.querySelector('.coupon-code button');
    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const couponInput = this.previousElementSibling;
            if (couponInput.value.trim() === '') {
                showToast('Please enter a coupon code');
                return;
            }
            
            // In a real app, you would validate the coupon with the server
            showToast('Coupon applied successfully');
            couponInput.value = '';
        });
    }
    
    // Proceed to checkout
    const checkoutBtn = document.querySelector('.proceed-checkout');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const cartItems = document.querySelectorAll('.cart-table tbody tr');
            if (cartItems.length === 0) {
                showToast('Your cart is empty');
                return;
            }
            
            window.location.href = '../checkout.html';
        });
    }
    
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
    
    // Initialize cart
    updateCartTotals();
    updateCartCount();
    
    // Additional styles for dynamic elements
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(20px); }
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
    `;
    document.head.appendChild(style);
});