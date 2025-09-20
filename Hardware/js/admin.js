document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const adminWrapper = document.querySelector('.admin-wrapper');
    
    sidebarToggle.addEventListener('click', function() {
        adminWrapper.classList.toggle('collapsed');
        
        // Save state in localStorage
        if (adminWrapper.classList.contains('collapsed')) {
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    });
    
    // Check localStorage for sidebar state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        adminWrapper.classList.add('collapsed');
    }
    
    // Mobile sidebar toggle
    const mobileSidebarToggle = document.createElement('button');
    mobileSidebarToggle.className = 'mobile-sidebar-toggle';
    mobileSidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.admin-header .header-left').prepend(mobileSidebarToggle);
    
    mobileSidebarToggle.addEventListener('click', function() {
        adminWrapper.classList.toggle('show-sidebar');
    });
    
    // Initialize Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue',
                data: [120000, 150000, 180000, 140000, 160000, 190000, 210000, 200000, 220000, 240000, 230000, 250000],
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    // Notification dropdown toggle
    const notificationBtn = document.querySelector('.notification-btn');
    const notificationDropdown = document.querySelector('.notification-dropdown');
    
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
    });
    
    // Profile dropdown toggle
    const profileDropdown = document.querySelector('.profile-dropdown');
    const profileMenu = document.querySelector('.profile-menu');
    
    profileDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        notificationDropdown.style.display = 'none';
        profileMenu.style.display = 'none';
    });
    
    // Tab functionality for product details
    const tabNavItems = document.querySelectorAll('.tab-nav li');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabNavItems.length > 0) {
        tabNavItems.forEach(item => {
            item.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all tabs
                tabNavItems.forEach(navItem => {
                    navItem.classList.remove('active');
                });
                
                // Add active class to current tab
                this.classList.add('active');
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show current tab content
                document.getElementById(tabId).classList.add('active');
            });
        });
    }
    
    // Color selection for product variations
    const colorOptions = document.querySelectorAll('.color-options input');
    
    colorOptions.forEach(option => {
        option.addEventListener('change', function() {
            document.querySelector('.color-options .selected')?.classList.remove('selected');
            this.nextElementSibling.classList.add('selected');
        });
    });
    
    // Quantity selector
    const quantityMinus = document.querySelectorAll('.quantity-btn.minus');
    const quantityPlus = document.querySelectorAll('.quantity-btn.plus');
    const quantityInputs = document.querySelectorAll('.quantity-selector input');
    
    quantityMinus.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.nextElementSibling;
            if (parseInt(input.value) > parseInt(input.min)) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
    
    quantityPlus.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.previousElementSibling;
            input.value = parseInt(input.value) + 1;
        });
    });
    
    // Create account checkbox
    const createAccountCheckbox = document.getElementById('create-account');
    const accountFields = document.querySelector('.account-fields');
    
    if (createAccountCheckbox) {
        createAccountCheckbox.addEventListener('change', function() {
            if (this.checked) {
                accountFields.style.display = 'block';
            } else {
                accountFields.style.display = 'none';
            }
        });
    }
    
    // Product image thumbnail click
    const thumbnails = document.querySelectorAll('.thumbnail-images .thumbnail');
    const mainImage = document.getElementById('main-product-image');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            document.querySelector('.thumbnail-images .thumbnail.active')?.classList.remove('active');
            this.classList.add('active');
            mainImage.src = this.querySelector('img').src;
        });
    });
    
    // Add to wishlist
    const wishlistBtn = document.querySelector('.add-to-wishlist');
    
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function() {
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
    
    // Additional styles for dynamic elements
    const style = document.createElement('style');
    style.textContent = `
        .mobile-sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--gray-color);
            cursor: pointer;
            margin-right: 15px;
        }
        
        .color-options label {
            display: inline-block;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
        }
        
        .color-options label.selected {
            border-color: var(--secondary-color);
        }
        
        .color-options label::after {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border: 1px solid var(--border-color);
            border-radius: 50%;
        }
        
        .color-options input {
            display: none;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
        }
        
        .quantity-selector input {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid var(--border-color);
            border-left: none;
            border-right: none;
            -moz-appearance: textfield;
        }
        
        .quantity-selector input::-webkit-outer-spin-button,
        .quantity-selector input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            background-color: var(--light-gray);
            border: 1px solid var(--border-color);
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .quantity-btn.minus {
            border-radius: 4px 0 0 4px;
        }
        
        .quantity-btn.plus {
            border-radius: 0 4px 4px 0;
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
        
        @media (max-width: 768px) {
            .mobile-sidebar-toggle {
                display: block;
            }
            
            .admin-wrapper.collapsed .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-wrapper.collapsed .admin-content {
                margin-left: 0;
            }
            
            .admin-wrapper.collapsed .admin-header {
                left: 0;
            }
        }
    `;
    document.head.appendChild(style);
});