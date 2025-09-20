// About Page Specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Team member hover effect for touch devices
    const teamMembers = document.querySelectorAll('.team-member');
    
    teamMembers.forEach(member => {
        member.addEventListener('touchstart', function() {
            this.classList.add('hover');
        }, {passive: true});
        
        member.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('hover');
            }, 500);
        }, {passive: true});
    });
    
    // Stats counter animation
    const statItems = document.querySelectorAll('.stat-item h3');
    const statsSection = document.querySelector('.stats-grid');
    let counted = false;
    
    function startCounters() {
        if (counted) return;
        
        statItems.forEach(stat => {
            const target = parseInt(stat.textContent);
            let count = 0;
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 20);
            
            const timer = setInterval(() => {
                count += increment;
                if (count >= target) {
                    count = target;
                    clearInterval(timer);
                }
                stat.textContent = Math.floor(count) + '+';
            }, 20);
        });
        
        counted = true;
    }
    
    // Intersection Observer for stats counter
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startCounters();
            }
        });
    }, { threshold: 0.5 });
    
    if (statsSection) {
        observer.observe(statsSection);
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