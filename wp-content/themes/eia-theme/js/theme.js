/**
 * EIA Theme JavaScript
 */

(function() {
    'use strict';

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {

        // Mobile menu functionality
        initMobileMenu();

        // Smooth scrolling for anchor links
        initSmoothScrolling();

        // Initialize animations on scroll
        initScrollAnimations();

        // Search functionality
        initSearch();
    });

    /**
     * Initialize mobile menu
     */
    function initMobileMenu() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function(e) {
                e.preventDefault();
                toggleMobileMenu();
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                    closeMobileMenu();
                }
            });

            // Close mobile menu on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    closeMobileMenu();
                }
                handleResponsiveMenu();
            });

            // Initial responsive menu setup
            handleResponsiveMenu();
        }
    }

    /**
     * Toggle mobile menu visibility
     */
    function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu) {
            if (mobileMenu.style.display === 'none' || mobileMenu.style.display === '') {
                mobileMenu.style.display = 'block';
                document.body.style.overflow = 'hidden'; // Prevent scroll when menu is open
            } else {
                mobileMenu.style.display = 'none';
                document.body.style.overflow = ''; // Restore scroll
            }
        }
    }

    /**
     * Close mobile menu
     */
    function closeMobileMenu() {
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu) {
            mobileMenu.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    /**
     * Handle responsive menu display
     */
    function handleResponsiveMenu() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mainNavigation = document.querySelector('.main-navigation');
        const searchForm = document.querySelector('.site-header form');
        const mobileMenu = document.getElementById('mobile-menu');

        if (window.innerWidth <= 1024) {
            // Mobile view
            if (mobileMenuButton) mobileMenuButton.style.display = 'block';
            if (mainNavigation) mainNavigation.style.display = 'none';
            if (searchForm) searchForm.style.display = 'none';
        } else {
            // Desktop view
            if (mobileMenuButton) mobileMenuButton.style.display = 'none';
            if (mainNavigation) mainNavigation.style.display = 'flex';
            if (searchForm) searchForm.style.display = 'flex';
            if (mobileMenu) mobileMenu.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    /**
     * Initialize smooth scrolling for anchor links
     */
    function initSmoothScrolling() {
        const anchorLinks = document.querySelectorAll('a[href^="#"]');

        anchorLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');

                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    e.preventDefault();

                    // Close mobile menu if open
                    closeMobileMenu();

                    // Smooth scroll to target
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * Initialize scroll animations
     */
    function initScrollAnimations() {
        // Add fade-in animation for elements when they come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Add initial styles and observe elements
        const animatedElements = document.querySelectorAll('.hero-section, .about-section, .news-section');

        animatedElements.forEach(function(element) {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(element);
        });

        // Animate cards on scroll
        const cards = document.querySelectorAll('[style*="background: white"][style*="border-radius"]');
        cards.forEach(function(card, index) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });
    }

    /**
     * Initialize search functionality
     */
    function initSearch() {
        const searchForms = document.querySelectorAll('form[role="search"]');

        searchForms.forEach(function(form) {
            const searchInput = form.querySelector('input[type="search"]');

            if (searchInput) {
                // Add search suggestions (if needed)
                searchInput.addEventListener('focus', function() {
                    this.style.borderColor = 'var(--eia-blue)';
                    this.style.boxShadow = '0 0 0 2px rgba(45, 79, 179, 0.1)';
                });

                searchInput.addEventListener('blur', function() {
                    this.style.borderColor = '#d1d5db';
                    this.style.boxShadow = 'none';
                });

                // Submit form on Enter key
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        form.submit();
                    }
                });
            }
        });
    }

    /**
     * Add hover effects to buttons and cards
     */
    function initHoverEffects() {
        // Add hover effects to buttons
        const buttons = document.querySelectorAll('.btn, button');

        buttons.forEach(function(button) {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });

        // Add hover effects to cards
        const cards = document.querySelectorAll('[style*="box-shadow"]');

        cards.forEach(function(card) {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            });
        });
    }

    // Initialize hover effects after DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initHoverEffects, 100);
    });

    /**
     * Utility function to debounce function calls
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = function() {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Debounced resize handler
    window.addEventListener('resize', debounce(handleResponsiveMenu, 250));

})();