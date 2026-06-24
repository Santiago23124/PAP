// Parallax effect
document.addEventListener('DOMContentLoaded', function() {
    const heroSection = document.querySelector('.hero-section');
    const menuHero = document.querySelector('.menu-hero');
    const mainNavigation = document.querySelector('.main-navigation');
    
    // Navbar scroll effect
    if (mainNavigation) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                mainNavigation.classList.add('scrolled');
            } else {
                mainNavigation.classList.remove('scrolled');
            }
        });
        // Check initial state
        if (window.scrollY > 50) {
            mainNavigation.classList.add('scrolled');
        }
    }
    
    if (heroSection) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallaxBg = document.querySelector('.hero-section .parallax-bg');
            if (parallaxBg) {
                parallaxBg.style.transform = `translateZ(-1px) scale(1.5) translateY(${scrolled * 0.3}px)`;
            }
        });
    }
    
    if (menuHero) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallaxBg = document.querySelector('.menu-hero .parallax-bg');
            if (parallaxBg) {
                parallaxBg.style.transform = `translateZ(-1px) scale(1.5) translateY(${scrolled * 0.3}px)`;
            }
        });
    }
    
    // Scroll reveal animations - IntersectionObserver with staggered delay
    (function() {
        const revealSelectors = [
            '.hero-content > *',
            '.intro-section', '.intro-content', '.feature-card', '.quote-section', '.menu-preview-section', '.info-section',
            '.menu-item', '.menu-section', '.story-paragraph', 
            '.caracteristica-item', '.destaque-restaurante', 
            '.story-text', '.final-section', '.final-content',
            '.menu-items-grid > div', '.caracteristicas > div',
            '.intro-text-side', '.intro-images', '.intro-feature',
            '.menu-preview-item', '.info-card', '.feature-number'
        ];
        
        const elements = document.querySelectorAll(revealSelectors.join(', '));
        
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        entry.target.style.transition = 'opacity 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            elements.forEach(function(el, index) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(40px)';
                el.style.transitionDelay = (index * 0.05) + 's';
                observer.observe(el);
            });
        } else {
            elements.forEach(function(el) {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            });
        }
    })();
});

// Menu Hambúrguer para Mobile
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const dropdowns = document.querySelectorAll('.dropdown');
    
    // Toggle menu hambúrguer
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
        });
    }
    
    // Fechar menu ao clicar em um link (mobile)
    if (navMenu) {
        const navLinks = navMenu.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    navMenu.classList.remove('active');
                    if (menuToggle) {
                        menuToggle.classList.remove('active');
                    }
                    document.body.style.overflow = '';
                }
            });
        });
    }
    
    // Dropdown toggle em mobile
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        }
    });
    
    // Fechar menu ao redimensionar para desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            if (navMenu) {
                navMenu.classList.remove('active');
            }
            if (menuToggle) {
                menuToggle.classList.remove('active');
            }
            document.body.style.overflow = '';
        }
    });
    
    // Fechar menu ao clicar fora (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && navMenu && navMenu.classList.contains('active')) {
            if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                if (menuToggle) {
                    menuToggle.classList.remove('active');
                }
                document.body.style.overflow = '';
            }
        }
    });
});