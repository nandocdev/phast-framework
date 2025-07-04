// Phast Framework - Main JavaScript

(function() {
    'use strict';

    // Initialize app when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeApp();
    });

    function initializeApp() {
        console.log('Phast Framework initialized');
        
        // Initialize components
        initializeNavigation();
        initializeModals();
        initializeForms();
        initializeTooltips();
        initializeAnimations();
        
        // Setup CSRF token for AJAX requests
        setupCSRFToken();
    }

    function initializeNavigation() {
        // Mobile menu toggle
        const navToggle = document.querySelector('.navbar-toggler');
        if (navToggle) {
            navToggle.addEventListener('click', function() {
                console.log('Mobile menu toggled');
            });
        }

        // Active menu highlighting
        highlightActiveMenuItem();
    }

    function highlightActiveMenuItem() {
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.nav-link');
        
        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.startsWith(href) && href !== '/') {
                item.classList.add('active');
            } else if (href === '/' && currentPath === '/') {
                item.classList.add('active');
            }
        });
    }

    function initializeModals() {
        // Handle modal events
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                const firstInput = modal.querySelector('input, textarea, select');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });
    }

    function initializeForms() {
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        // Auto-resize textareas
        const textareas = document.querySelectorAll('textarea[data-auto-resize]');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    }

    function initializeTooltips() {
        // Initialize Bootstrap tooltips
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    function initializeAnimations() {
        // Fade in animation for cards
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
            card.classList.add('fade-in');
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('slide-in');
                }
            });
        }, observerOptions);

        const animateElements = document.querySelectorAll('[data-animate]');
        animateElements.forEach(el => observer.observe(el));
    }

    function setupCSRFToken() {
        // Get CSRF token from meta tag or generate one
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!token) {
            // Generate a simple token for demo purposes
            token = 'demo-csrf-token-' + Math.random().toString(36).substr(2, 9);
        }

        // Set up AJAX defaults
        if (typeof jQuery !== 'undefined') {
            jQuery.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        }

        // Store token for vanilla JS AJAX calls
        window.csrfToken = token;
    }

    // Utility functions
    window.PhastUtils = {
        // Show loading spinner
        showLoading: function(element) {
            if (element) {
                element.innerHTML = '<div class="spinner-phast mx-auto"></div>';
            }
        },

        // Hide loading spinner
        hideLoading: function(element, originalContent) {
            if (element) {
                element.innerHTML = originalContent || '';
            }
        },

        // Show toast notification
        showToast: function(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        },

        // Format date
        formatDate: function(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            return new Date(date).toLocaleDateString('es-ES', {...defaultOptions, ...options});
        },

        // Make AJAX request
        ajax: function(url, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken || ''
                }
            };

            const config = {...defaults, ...options};
            
            if (config.data && typeof config.data === 'object') {
                config.body = JSON.stringify(config.data);
            }

            return fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    this.showToast('Error en la petición: ' + error.message, 'danger');
                    throw error;
                });
        },

        // Debounce function
        debounce: function(func, wait, immediate) {
            let timeout;
            return function executedFunction() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };

    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.error);
        // You could send this to a logging service
    });

    // Log framework info
    console.log('%cPhast Framework', 'color: #007bff; font-size: 18px; font-weight: bold;');
    console.log('Version: 1.0.0');
    console.log('PHP Version:', '<?= PHP_VERSION ?>' || 'Unknown');
    
})();
