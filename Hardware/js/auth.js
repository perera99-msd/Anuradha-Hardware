// Authentication Pages Specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Password strength indicator (for register page)
    const passwordInput = document.getElementById('password');
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.querySelector('.strength-text');
    
    if (passwordInput && strengthBar && strengthText) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            // Update strength bar
            strengthBar.style.width = strength.percentage + '%';
            strengthBar.style.backgroundColor = strength.color;
            
            // Update strength text
            strengthText.textContent = strength.text;
            strengthText.style.color = strength.color;
        });
    }
    
    // Password confirmation validation (for register page)
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordMatch = document.getElementById('password-match');
    
    if (passwordInput && confirmPasswordInput && passwordMatch) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword === '') {
                passwordMatch.textContent = '';
                passwordMatch.className = 'password-feedback';
            } else if (password === confirmPassword) {
                passwordMatch.textContent = 'Passwords match!';
                passwordMatch.className = 'password-feedback success';
            } else {
                passwordMatch.textContent = 'Passwords do not match.';
                passwordMatch.className = 'password-feedback error';
            }
        });
    }
    
    // Form validation
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                confirmPassword.focus();
                
                // Show error message
                if (passwordMatch) {
                    passwordMatch.textContent = 'Passwords do not match.';
                    passwordMatch.className = 'password-feedback error';
                }
            }
        });
    }
    
    // Calculate password strength
    function calculatePasswordStrength(password) {
        let strength = 0;
        let feedback = '';
        
        if (password.length > 0) {
            // Length check
            if (password.length > 7) strength += 25;
            
            // Lowercase check
            if (password.match(/[a-z]/)) strength += 25;
            
            // Uppercase check
            if (password.match(/[A-Z]/)) strength += 25;
            
            // Number/special char check
            if (password.match(/[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/)) strength += 25;
        }
        
        // Determine strength level
        let level, color;
        
        if (strength === 0) {
            level = '';
            color = 'transparent';
            feedback = 'Password strength';
        } else if (strength < 50) {
            level = 'Weak';
            color = '#e74c3c';
            feedback = 'Password is weak';
        } else if (strength < 75) {
            level = 'Fair';
            color = '#f39c12';
            feedback = 'Password is fair';
        } else if (strength < 100) {
            level = 'Good';
            color = '#3498db';
            feedback = 'Password is good';
        } else {
            level = 'Strong';
            color = '#27ae60';
            feedback = 'Password is strong';
        }
        
        return {
            percentage: strength,
            color: color,
            text: feedback
        };
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
});