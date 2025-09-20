// Show toast notification
function showToast(message, isError = false) {
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
            toast.remove();
        }, 300);
    }, 3000);
}

// Update wishlist count
function updateWishlistCount(count) {
    const wishlistCount = document.querySelector('.wishlist-count');
    const wishlistCountText = document.getElementById('wishlist-count');
    
    if (wishlistCount) {
        wishlistCount.textContent = count;
        wishlistCount.classList.add('pulse');
        setTimeout(() => {
            wishlistCount.classList.remove('pulse');
        }, 300);
    }
    
    if (wishlistCountText) {
        wishlistCountText.textContent = count;
    }
}

// Update cart count
function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        cartCount.classList.add('pulse');
        setTimeout(() => {
            cartCount.classList.remove('pulse');
        }, 300);
    }
}

// Show/hide loading
function setLoading(loading) {
    const loadingElement = document.getElementById('wishlist-loading');
    if (loadingElement) {
        loadingElement.style.display = loading ? 'block' : 'none';
    }
}

// Handle wishlist actions
function handleWishlistAction(action, productId, productName = '') {
    setLoading(true);
    
    const formData = new FormData();
    formData.append('action', action);
    if (productId) {
        formData.append('product_id', productId);
    }
    
    fetch('wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        setLoading(false);
        
        if (data.success) {
            showToast(data.message);
            
            // Update counts
            if (data.wishlistCount !== undefined) {
                updateWishlistCount(data.wishlistCount);
            }
            
            if (data.cartCount !== undefined) {
                updateCartCount(data.cartCount);
            }
            
            // Handle different actions
            if (action === 'remove') {
                // Remove the item from the DOM
                const itemElement = document.querySelector(`.wishlist-item[data-id="${productId}"]`);
                if (itemElement) {
                    itemElement.remove();
                }
                
                // If no items left, show empty state
                if (data.wishlistCount === 0) {
                    document.querySelector('.wishlist-container').innerHTML = `
                        <div class="empty-wishlist">
                            <div class="empty-wishlist-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h2>Your wishlist is empty</h2>
                            <p>You haven't added any items to your wishlist yet.</p>
                            <a href="products.php" class="btn">Start Shopping</a>
                        </div>
                    `;
                }
            } else if (action === 'move_to_cart') {
                // Remove the item from the DOM
                const itemElement = document.querySelector(`.wishlist-item[data-id="${productId}"]`);
                if (itemElement) {
                    itemElement.remove();
                }
                
                // If no items left, show empty state
                if (data.wishlistCount === 0) {
                    document.querySelector('.wishlist-container').innerHTML = `
                        <div class="empty-wishlist">
                            <div class="empty-wishlist-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h2>Your wishlist is empty</h2>
                            <p>You haven't added any items to your wishlist yet.</p>
                            <a href="products.php" class="btn">Start Shopping</a>
                        </div>
                    `;
                }
            } else if (action === 'clear') {
                // Clear all items from the DOM
                document.querySelector('.wishlist-container').innerHTML = `
                    <div class="empty-wishlist">
                        <div class="empty-wishlist-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h2>Your wishlist is empty</h2>
                        <p>You haven't added any items to your wishlist yet.</p>
                        <a href="products.php" class="btn">Start Shopping</a>
                    </div>
                `;
            }
        } else {
            showToast(data.message, true);
        }
    })
    .catch(error => {
        setLoading(false);
        showToast('An error occurred. Please try again.', true);
        console.error('Error:', error);
    });
}

// Initialize wishlist functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for remove buttons
    document.querySelectorAll('.btn-remove').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const productName = this.closest('.wishlist-item').querySelector('h3 a').textContent;
            
            if (confirm(`Are you sure you want to remove "${productName}" from your wishlist?`)) {
                handleWishlistAction('remove', productId, productName);
            }
        });
    });
    
    // Add event listeners for move to cart buttons
    document.querySelectorAll('.btn-move-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const productName = this.closest('.wishlist-item').querySelector('h3 a').textContent;
            
            handleWishlistAction('move_to_cart', productId, productName);
        });
    });
    
    // Add event listener for clear wishlist button
    const clearWishlistBtn = document.getElementById('clear-wishlist');
    if (clearWishlistBtn) {
        clearWishlistBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to clear your entire wishlist?')) {
                handleWishlistAction('clear');
            }
        });
    }
});