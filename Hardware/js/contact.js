// Contact Page Specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // FAQ accordion functionality
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            // Close all other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active');
        });
    });
    
    // Form validation
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let valid = true;
            const inputs = this.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    highlightError(input);
                } else {
                    removeErrorHighlight(input);
                    
                    // Email validation
                    if (input.type === 'email' && !isValidEmail(input.value)) {
                        valid = false;
                        highlightError(input, 'Please enter a valid email address.');
                    }
                }
            });
            
            if (!valid) {
                e.preventDefault();
                
                // Scroll to first error
                const firstError = this.querySelector('.error-highlight');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Remove non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Format based on length
            if (value.length > 0) {
                value = '+' + value;
                
                if (value.length > 3) {
                    value = value.substring(0, 3) + ' ' + value.substring(3);
                }
                if (value.length > 7) {
                    value = value.substring(0, 7) + ' ' + value.substring(7);
                }
                if (value.length > 12) {
                    value = value.substring(0, 12);
                }
            }
            
            this.value = value;
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Helper functions
    function highlightError(input, message = 'This field is required.') {
        input.classList.add('error-highlight');
        
        // Remove existing error message
        const existingError = input.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#e74c3c';
        errorDiv.style.fontSize = '0.8rem';
        errorDiv.style.marginTop = '5px';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        input.parentElement.appendChild(errorDiv);
    }
    
    function removeErrorHighlight(input) {
        input.classList.remove('error-highlight');
        
        // Remove error message
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Add styles for error highlighting
    const style = document.createElement('style');
    style.textContent = `
        .error-highlight {
            border-color: #e74c3c !important;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1) !important;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-right: 5px;
            font-size: 0.9rem;
        }
    `;
    document.head.appendChild(style);
});