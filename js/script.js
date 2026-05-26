document.addEventListener('DOMContentLoaded', function() {
    const heroSection = document.querySelector('.hero-section');
    const menuHero = document.querySelector('.menu-hero');
    
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
    
    // Scroll reveal animations - Inline styles for guaranteed effect
    (function() {
        const revealSelectors = [
            '.menu-item', '.menu-section', '.story-paragraph', 
            '.caracteristica-item', '.destaque-restaurante', 
            '.story-text', '.final-section', '.final-content',
            '.menu-items-grid > div', '.caracteristicas > div'
        ];
        
        const elements = document.querySelectorAll(revealSelectors.join(', '));
        
        elements.forEach(function(el) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(40px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        });
        
        const checkVisibility = function() {
            const windowHeight = window.innerHeight;
            
            elements.forEach(function(el) {
                const rect = el.getBoundingClientRect();
                if (rect.top < windowHeight - 50) {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }
            });
        };
        
        window.addEventListener('scroll', checkVisibility);
        checkVisibility();
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

// script.js - Funcionalidades gerais do site (sem tradução)
// A tradução foi movida para traducao.js