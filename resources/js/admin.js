/**
 * SiedeStudios - Admin Panel JavaScript File
 * Entry point for Vite
 */

// Import Bootstrap
import './bootstrap.js';

// Import modules (which will now have access to global jQuery)
import './theme-toggle.js';
import './animations.js';

// Sidebar Module
const Sidebar = {
    init: function() {
        const $sidebarToggle = $('#sidebar-toggle');
        const $mobileSidebarToggle = $('#mobile-sidebar-toggle');
        const $adminLayout = $('#admin-layout');
        
        // Check sidebar state from localStorage
        if (localStorage.getItem('siedaae-sidebar-collapsed') === 'true') {
            $adminLayout.addClass('sidebar-collapsed');
        }
        
        // Sidebar toggle for desktop
        $sidebarToggle.on('click', function() {
            $adminLayout.toggleClass('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', $adminLayout.hasClass('sidebar-collapsed'));
        });
        
        // Sidebar toggle for mobile
        $mobileSidebarToggle.on('click', function() {
            $adminLayout.toggleClass('sidebar-active');
        });

        // Close mobile sidebar when clicking outside
        $(document).on('click', function(e) {
            if ($adminLayout.hasClass('sidebar-active') && 
                !$(e.target).closest('.admin-sidebar').length && 
                !$(e.target).closest('#mobile-sidebar-toggle').length) {
                $adminLayout.removeClass('sidebar-active');
            }
        });
    }
};

// Alerts Module
const Alerts = {
    init: function() {
        // Auto-dismiss alerts after 5 seconds
        const $alerts = $('.alert');
        
        $alerts.each(function() {
            const $alert = $(this);
            setTimeout(function() {
                $alert.addClass('alert-fade-out');
                setTimeout(function() {
                    $alert.slideUp(300, function() {
                        $(this).remove();
                    });
                }, 500);
            }, 5000);
        });
        
        // Manual close button functionality
        $('.alert-close').on('click', function() {
            $(this).closest('.alert').addClass('alert-fade-out');
            setTimeout(() => {
                $(this).closest('.alert').slideUp(300, function() {
                    $(this).remove();
                });
            }, 300);
        });
    }
};

// Tabs Module
const Tabs = {
    init: function() {
        const $tabs = $('.admin-tab');
        const $tabContents = $('.tab-content');
        
        $tabs.on('click', function(e) {
            e.preventDefault();
            
            const targetTab = $(this).data('tab');
            
            // Update active tab
            $tabs.removeClass('active');
            $(this).addClass('active');
            
            // Show corresponding content
            $tabContents.removeClass('active');
            $(`[data-tab-content="${targetTab}"]`).addClass('active');
            
            // Update URL hash
            window.location.hash = targetTab;
        });
        
        // Check URL hash on page load
        const hash = window.location.hash.substring(1);
        if (hash) {
            const $activeTab = $(`.admin-tab[data-tab="${hash}"]`);
            if ($activeTab.length) {
                $activeTab.click();
            }
        }
    }
};

// File Inputs Module
const FileInputs = {
    init: function() {
        // Logo file input
        $('#logo').on('change', function() {
            const $fileInput = $(this);
            const $label = $('#logo-name');
            const $container = $fileInput.closest('.file-input');
            
            if (this.files.length > 0) {
                $label.text(this.files[0].name);
                $container.addClass('has-file');
            } else {
                $label.text('Choose a logo file');
                $container.removeClass('has-file');
            }
        });
        
        // Favicon file input
        $('#favicon').on('change', function() {
            const $fileInput = $(this);
            const $label = $('#favicon-name');
            const $container = $fileInput.closest('.file-input');
            
            if (this.files.length > 0) {
                $label.text(this.files[0].name);
                $container.addClass('has-file');
            } else {
                $label.text('Choose a favicon file');
                $container.removeClass('has-file');
            }
        });

        // Generic file input handler for other file inputs
        $('.file-input input[type="file"]').each(function() {
            const $input = $(this);
            const $container = $input.closest('.file-input');
            const $label = $container.find('.file-input-text');
            const originalText = $label.text();
            
            $input.on('change', function() {
                if (this.files.length > 0) {
                    $label.text(this.files[0].name);
                    $container.addClass('has-file');
                } else {
                    $label.text(originalText);
                    $container.removeClass('has-file');
                }
            });
        });
    }
};

// Table Module
const Tables = {
    init: function() {
        // Confirm delete actions
        $('.action-delete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
                return false;
            }
        });

        // Handle bulk actions
        $('#select-all-items').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.item-checkbox').prop('checked', isChecked);
            this.updateBulkActionsVisibility();
        });
        
        $('.item-checkbox').on('change', function() {
            this.updateBulkActionsVisibility();
        });
    },
    
    updateBulkActionsVisibility: function() {
        const $bulkActions = $('.bulk-actions');
        const hasCheckedItems = $('.item-checkbox:checked').length > 0;
        
        if (hasCheckedItems) {
            $bulkActions.slideDown(200);
        } else {
            $bulkActions.slideUp(200);
        }
    }
};

// Status Toggles
const StatusToggles = {
    init: function() {
        // Status dropdown toggle
        $('.status-dropdown .dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            $(this).closest('.status-dropdown').toggleClass('active');
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.status-dropdown').length) {
                $('.status-dropdown').removeClass('active');
            }
        });
        
        // Status toggle switch
        $('.status-toggle-switch').on('change', function() {
            const $form = $(this).closest('form');
            $form.submit();
        });
    }
};

// Dashboard specific functionality
const Dashboard = {
    init: function() {
        // Any dashboard specific initialization can go here
        console.log('Dashboard initialized');
        
        // Add chart initialization here if needed
    }
};

// Animations Module
const Animations = {
    init: function() {
        // Add entrance animations to elements
        $('.stats-grid .stat-card').each(function(index) {
            $(this).css({
                'animation-delay': `${index * 0.1}s`,
                'animation-name': 'fadeInUp',
                'animation-duration': '0.5s',
                'animation-fill-mode': 'both'
            });
        });
        
        $('.quick-actions-grid .quick-action-item').each(function(index) {
            $(this).css({
                'animation-delay': `${0.3 + index * 0.1}s`,
                'animation-name': 'fadeInUp',
                'animation-duration': '0.5s',
                'animation-fill-mode': 'both'
            });
        });
        
        // Smooth scrolling for anchor links
        $('a[href^="#"]:not([data-tab])').on('click', function(e) {
            e.preventDefault();
            const target = $(this.hash);
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 500);
            }
        });
    }
};

// Search Filter Module
const SearchFilter = {
    init: function() {
        // Filter dropdown toggle
        $('.filter-dropdown-toggle').on('click', function() {
            $(this).next('.filter-dropdown-menu').toggleClass('show');
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.filter-dropdown').length) {
                $('.filter-dropdown-menu').removeClass('show');
            }
        });
        
        // Clear search form
        $('.clear-search-btn').on('click', function() {
            const $form = $(this).closest('form');
            $form.find('input[type="search"]').val('');
            $form.find('select').prop('selectedIndex', 0);
            $form.submit();
        });
    }
};

// Sortable Tables (requires jQuery UI)
const SortableTables = {
    init: function() {
        if ($.fn.sortable) {
            $('.sortable-table tbody').sortable({
                handle: '.sort-handle',
                axis: 'y',
                helper: function(e, tr) {
                    const $originals = tr.children();
                    const $helper = tr.clone();
                    
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    
                    return $helper;
                },
                update: function(event, ui) {
                    const itemIds = [];
                    $(this).find('tr').each(function() {
                        itemIds.push($(this).data('id'));
                    });
                    
                    // Send AJAX request to update order
                    if (itemIds.length > 0) {
                        $.ajax({
                            url: $(this).data('url'),
                            method: 'POST',
                            data: {
                                items: itemIds,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Show success notification
                                    showNotification('Order updated successfully', 'success');
                                }
                            },
                            error: function() {
                                showNotification('Failed to update order', 'error');
                                // Revert the sort
                                $(this).sortable('cancel');
                            }
                        });
                    }
                }
            });
        }
    }
};

// Form Validation
const FormValidation = {
    init: function() {
        $('form.validate-form').each(function() {
            const $form = $(this);
            
            $form.on('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                $form.find('.form-error').remove();
                $form.find('.is-invalid').removeClass('is-invalid');
                
                // Check required fields
                $form.find('[required]').each(function() {
                    const $field = $(this);
                    if (!$field.val().trim()) {
                        isValid = false;
                        $field.addClass('is-invalid');
                        $field.after('<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> This field is required</div>');
                    }
                });
                
                // Check email fields
                $form.find('input[type="email"]').each(function() {
                    const $field = $(this);
                    const val = $field.val().trim();
                    
                    if (val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                        isValid = false;
                        $field.addClass('is-invalid');
                        $field.after('<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> Please enter a valid email address</div>');
                    }
                });
                
                // Check URL fields
                $form.find('input[type="url"]').each(function() {
                    const $field = $(this);
                    const val = $field.val().trim();
                    
                    if (val && !/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(val)) {
                        isValid = false;
                        $field.addClass('is-invalid');
                        $field.after('<div class="form-error"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> Please enter a valid URL</div>');
                    }
                });
                
                return isValid;
            });
        });
    }
};

// Notification function
function showNotification(message, type = 'info', duration = 5000) {
    const $notification = $(`
        <div class="admin-notification notification-${type}">
            <div class="notification-content">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="notification-icon">
                    ${type === 'success' ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>' : ''}
                    ${type === 'error' ? '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>' : ''}
                    ${type === 'info' ? '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>' : ''}
                    ${type === 'warning' ? '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>' : ''}
                </svg>
                <span>${message}</span>
            </div>
            <button class="notification-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    `);
    
    // Add notification to container or create it
    let $container = $('.notifications-container');
    if (!$container.length) {
        $container = $('<div class="notifications-container"></div>');
        $('body').append($container);
    }
    
    $container.append($notification);
    
    // Animate in
    setTimeout(() => {
        $notification.addClass('show');
    }, 10);
    
    // Auto-dismiss
    const dismissTimeout = setTimeout(() => {
        $notification.removeClass('show');
        setTimeout(() => {
            $notification.remove();
        }, 300);
    }, duration);
    
    // Close button
    $notification.find('.notification-close').on('click', function() {
        clearTimeout(dismissTimeout);
        $notification.removeClass('show');
        setTimeout(() => {
            $notification.remove();
        }, 300);
    });
}

// Initialize all modules
$(document).ready(function() {
    Sidebar.init();
    Alerts.init();
    
    // Initialize optional modules based on page elements
    if ($('.admin-tab').length) {
        Tabs.init();
    }
    
    if ($('.file-input').length) {
        FileInputs.init();
    }
    
    if ($('.admin-table').length) {
        Tables.init();
    }
    
    if ($('.status-dropdown').length || $('.status-toggle-switch').length) {
        StatusToggles.init();
    }
    
    if ($('.stats-grid').length || $('.quick-actions-grid').length) {
        Dashboard.init();
        Animations.init();
    }
    
    if ($('.search-filter-container').length || $('.filter-dropdown').length) {
        SearchFilter.init();
    }
    
    if ($('.sortable-table').length) {
        SortableTables.init();
    }
    
    if ($('form.validate-form').length) {
        FormValidation.init();
    }
    
    // Custom CSS for printing
    if (window.matchMedia) {
        const mediaQueryList = window.matchMedia('print');
        mediaQueryList.addEventListener('change', function(mql) {
            if (mql.matches) {
                // Before print
                $('body').addClass('is-printing');
            } else {
                // After print
                $('body').removeClass('is-printing');
            }
        });
    }
    
    // Add CSS animations
    const style = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .notifications-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .admin-notification {
            background-color: var(--color-background-alt);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            max-width: 380px;
            transform: translateX(100%);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            border-left: 4px solid var(--color-primary);
        }
        
        .admin-notification.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .admin-notification .notification-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .admin-notification .notification-icon {
            color: var(--color-primary);
        }
        
        .admin-notification.notification-success {
            border-left-color: var(--color-success);
        }
        
        .admin-notification.notification-success .notification-icon {
            color: var(--color-success);
        }
        
        .admin-notification.notification-error {
            border-left-color: var(--color-error);
        }
        
        .admin-notification.notification-error .notification-icon {
            color: var(--color-error);
        }
        
        .admin-notification.notification-warning {
            border-left-color: var(--color-warning);
        }
        
        .admin-notification.notification-warning .notification-icon {
            color: var(--color-warning);
        }
        
        .admin-notification .notification-close {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-text-muted);
            padding: 4px;
            border-radius: var(--border-radius-sm);
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        
        .admin-notification .notification-close:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--color-text);
        }
    `;
    
    $('<style>')
        .prop('type', 'text/css')
        .html(style)
        .appendTo('head');

    // Make global
    window.showNotification = showNotification;
});

// Export any modules that might need to be accessed externally
export { showNotification };