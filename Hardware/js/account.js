// Account Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart count from localStorage
    function initCartCount() {
        const cartCount = localStorage.getItem('cartCount');
        if (cartCount) {
            document.querySelector('.cart-count').textContent = cartCount;
        }
    }

    initCartCount();

    // Add active class to current page in navigation
    function setActiveNav() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-menu a');

        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.php')) {
                link.parentElement.classList.add('active');
            } else {
                link.parentElement.classList.remove('active');
            }
        });
    }

    setActiveNav();

    // Tab switching functionality
    const menuLinks = document.querySelectorAll('.account-menu a');
    const accountTabs = document.querySelectorAll('.account-tab');

    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);

            // Remove active class from all links and tabs
            menuLinks.forEach(item => item.classList.remove('active'));
            accountTabs.forEach(tab => tab.classList.remove('active'));

            // Add active class to the clicked link and corresponding tab
            this.classList.add('active');
            document.getElementById(targetId).classList.add('active');
        });
    });

    // Handle initial tab display based on URL hash
    function handleHashChange() {
        const hash = window.location.hash;
        if (hash) {
            const targetLink = document.querySelector(`.account-menu a[href="${hash}"]`);
            if (targetLink) {
                menuLinks.forEach(item => item.classList.remove('active'));
                accountTabs.forEach(tab => tab.classList.remove('active'));
                targetLink.classList.add('active');
                document.querySelector(hash).classList.add('active');
            }
        } else {
            // Default to dashboard if no hash is present
            document.getElementById('dashboard').classList.add('active');
            document.querySelector('.account-menu a[href="#dashboard"]').classList.add('active');
        }
    }

    handleHashChange();
    window.addEventListener('hashchange', handleHashChange);

    // Order status tooltips
    const statusBadges = document.querySelectorAll('.status-badge');
    statusBadges.forEach(badge => {
        let tooltip = null;

        function getStatusDescription(status) {
            const descriptions = {
                'pending': 'Your order is being processed',
                'processing': 'Your order is being prepared for shipment',
                'completed': 'Your order has been delivered',
                'cancelled': 'Your order has been cancelled'
            };
            return descriptions[status.toLowerCase()] || 'Status information';
        }

        function createTooltip(description) {
            tooltip = document.createElement('div');
            tooltip.className = 'status-tooltip';
            tooltip.textContent = description;
            document.body.appendChild(tooltip);
        }

        function positionTooltip(targetElement) {
            if (!tooltip) return;
            const rect = targetElement.getBoundingClientRect();
            const scrollX = window.scrollX || window.pageXOffset;
            const scrollY = window.scrollY || window.pageYOffset;
            const top = rect.top + scrollY - tooltip.offsetHeight - 10;
            const left = rect.left + scrollX + (rect.width / 2) - (tooltip.offsetWidth / 2);
            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
        }

        badge.addEventListener('mouseenter', function(e) {
            const status = this.textContent.trim();
            const description = getStatusDescription(status);
            createTooltip(description);
            positionTooltip(this);
        });

        badge.addEventListener('mouseleave', function() {
            if (tooltip) {
                tooltip.remove();
                tooltip = null;
            }
        });

        // Reposition tooltip on scroll or resize
        window.addEventListener('scroll', () => {
            if (tooltip) positionTooltip(badge);
        });
        window.addEventListener('resize', () => {
            if (tooltip) positionTooltip(badge);
        });
    });

    // Quick actions for overview cards
    const overviewCards = document.querySelectorAll('.overview-card');
    overviewCards.forEach(card => {
        card.addEventListener('click', function(e) {
            const link = this.querySelector('a.view-all');
            if (link && e.target !== link && !link.contains(e.target)) {
                window.location.href = link.href;
            }
        });
    });
});