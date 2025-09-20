// Product Detail Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Image thumbnail navigation
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainProductImage');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const imageUrl = this.getAttribute('data-image');
            mainImage.src = imageUrl;
            
            // Update active thumbnail
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Quantity selector
    const quantityInput = document.getElementById('productQuantity');
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    
    if (minusBtn && plusBtn && quantityInput) {
        minusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            const max = parseInt(quantityInput.getAttribute('max')) || 100;
            if (value < max) {
                quantityInput.value = value + 1;
            }
        });
    }
    
    // Add to cart
    const addToCartBtn = document.querySelector('.btn-add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantity = parseInt(quantityInput.value);
            const productName = document.querySelector('.product-info h1').textContent;
            
            addToCart(productId, productName, quantity);
        });
    }
    
    // Wishlist toggle
    const wishlistBtn = document.querySelector('.btn-wishlist');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            const productName = document.querySelector('.product-info h1').textContent;
            
            if (this.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                showToast(`${productName} added to wishlist`);
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                showToast(`${productName} removed from wishlist`);
            }
        });
    }
    
    // Product tabs
    const tabNavs = document.querySelectorAll('.tabs-nav li');
    const tabPanels = document.querySelectorAll('.tab-panel');
    
    tabNavs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update active tab
            tabNavs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding panel
            tabPanels.forEach(panel => {
                panel.classList.remove('active');
                if (panel.id === tabId) {
                    panel.classList.add('active');
                }
            });
        });
    });
    
    // Review rating stars
    const ratingStars = document.querySelectorAll('.rating-input i');
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            
            // Update stars
            ratingStars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('active');
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('active');
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });
    });
    
    // Review form submission
    const reviewForm = document.querySelector('.review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // In a real application, this would submit the review to the server
            const title = document.getElementById('reviewTitle').value;
            
            showToast('Thank you for your review! It will be published after moderation.');
            this.reset();
            
            // Reset stars
            ratingStars.forEach(star => {
                star.classList.remove('active');
                star.classList.remove('fas');
                star.classList.add('far');
            });
        });
    }
    
    // Share buttons
    const shareButtons = document.querySelectorAll('.share-buttons a');
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const platform = this.querySelector('i').classList[1].replace('fa-', '');
            const productName = document.querySelector('.product-info h1').textContent;
            const productUrl = window.location.href;
            
            let shareUrl = '';
            
            switch (platform) {
                case 'facebook-f':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(productUrl)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(productName)}&url=${encodeURIComponent(productUrl)}`;
                    break;
                case 'pinterest':
                    shareUrl = `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(productUrl)}&description=${encodeURIComponent(productName)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(productName + ' ' + productUrl)}`;
                    break;
                case 'envelope':
                    shareUrl = `mailto:?subject=${encodeURIComponent(productName)}&body=${encodeURIComponent('Check out this product: ' + productUrl)}`;
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank');
            }
        });
    });
});

// Add to cart function
function addToCart(productId, productName, quantity = 1) {
    // In a real application, this would make an AJAX request to add to cart
    // For now, we'll simulate it
    
    // Update cart count
    updateCartCount(quantity);
    
    // Show success message
    showToast(`${quantity} x ${productName} added to cart`);
    
    // In a real application, you would:
    // fetch('cart.php?action=add&id=' + productId, { 
    //   method: 'POST',
    //   body: JSON.stringify({ quantity: quantity })
    // })
    //   .then(response => response.json())
    //   .then(data => {
    //       updateCartCount(data.itemCount);
    //       showToast('Product added to cart');
    //   });
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