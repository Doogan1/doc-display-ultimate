/**
 * FileBird Frontend Documents - Frontend JavaScript
 * Version: 0.1.0
 */

(function($) {
    'use strict';

    // Plugin namespace
    window.FileBirdFD = window.FileBirdFD || {};

    // Main plugin class
    FileBirdFD.Frontend = {
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initLazyLoading();
            this.initAccordionStates();
        },

        bindEvents: function() {
            // Accordion functionality
            $(document).on('click', '.filebird-docs-accordion-toggle', function(e) {
                e.preventDefault();
                var $toggle = $(this);
                var $section = $toggle.closest('.filebird-docs-accordion-section');
                var $content = $section.find('.filebird-docs-accordion-content');
                var isExpanded = $toggle.attr('aria-expanded') === 'true';
                
                // Toggle aria-expanded
                $toggle.attr('aria-expanded', !isExpanded);
                
                // Toggle content visibility
                if (isExpanded) {
                    // Closing the folder - hide content and all nested folders
                    $content.removeClass('filebird-docs-accordion-open').hide();
                    
                    // Close all nested accordions within this section
                    $content.find('.filebird-docs-accordion-toggle').attr('aria-expanded', 'false');
                    $content.find('.filebird-docs-accordion-content').removeClass('filebird-docs-accordion-open').hide();
                } else {
                    // Opening the folder - show content but keep nested accordions in their current state
                    $content.addClass('filebird-docs-accordion-open').show();
                    
                    // Ensure nested accordions maintain their proper state
                    $content.find('.filebird-docs-accordion-content').each(function() {
                        var $nestedContent = $(this);
                        var $nestedToggle = $nestedContent.siblings('.filebird-docs-accordion-header').find('.filebird-docs-accordion-toggle');
                        var nestedIsExpanded = $nestedToggle.attr('aria-expanded') === 'true';
                        
                        if (nestedIsExpanded) {
                            $nestedContent.addClass('filebird-docs-accordion-open').show();
                        } else {
                            $nestedContent.removeClass('filebird-docs-accordion-open').hide();
                        }
                    });
                }
            });

            // Download button enhancements
            $(document).on('click', '.filebird-docs-download-btn', function(e) {
                // Add download tracking if needed
                var $btn = $(this);
                var originalText = $btn.text();
                
                $btn.text('Downloading...').prop('disabled', true);
                
                // Reset button after a short delay
                setTimeout(function() {
                    $btn.text(originalText).prop('disabled', false);
                }, 2000);
            });

            // Card hover effects
            $(document).on('mouseenter', '.filebird-docs-card', function() {
                $(this).addClass('hover');
            }).on('mouseleave', '.filebird-docs-card', function() {
                $(this).removeClass('hover');
            });

            // List item hover effects
            $(document).on('mouseenter', '.filebird-docs-list-item', function() {
                $(this).addClass('hover');
            }).on('mouseleave', '.filebird-docs-list-item', function() {
                $(this).removeClass('hover');
            });

            // Table row hover effects
            $(document).on('mouseenter', '.filebird-docs-table-row', function() {
                $(this).addClass('hover');
            }).on('mouseleave', '.filebird-docs-table-row', function() {
                $(this).removeClass('hover');
            });

            // Keyboard navigation
            $(document).on('keydown', '.filebird-docs-link', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                }
            });

            // Accordion keyboard navigation
            $(document).on('keydown', '.filebird-docs-accordion-toggle', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                }
            });

            // Accessibility improvements
            $(document).on('focus', '.filebird-docs-card, .filebird-docs-list-item', function() {
                $(this).addClass('focused');
            }).on('blur', '.filebird-docs-card, .filebird-docs-list-item', function() {
                $(this).removeClass('focused');
            });
        },

        initTooltips: function() {
            // Initialize tooltips for truncated titles
            $('.filebird-docs-card-title a, .filebird-docs-list-title a').each(function() {
                var $link = $(this);
                var title = $link.attr('title');
                
                if (!title) {
                    title = $link.text();
                    $link.attr('title', title);
                }
            });
        },

        initLazyLoading: function() {
            // Lazy load images if Intersection Observer is supported
            if ('IntersectionObserver' in window) {
                var imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                $('.filebird-docs-thumbnail[data-src]').each(function() {
                    imageObserver.observe(this);
                });
            }
        },

        initAccordionStates: function() {
            // Ensure all accordions start in the correct state based on their aria-expanded attributes
            $('.filebird-docs-accordion-toggle').each(function() {
                var $toggle = $(this);
                var $content = $toggle.closest('.filebird-docs-accordion-section').find('.filebird-docs-accordion-content');
                var isExpanded = $toggle.attr('aria-expanded') === 'true';
                
                if (isExpanded) {
                    $content.addClass('filebird-docs-accordion-open').show();
                } else {
                    $content.removeClass('filebird-docs-accordion-open').hide();
                }
            });
        },

        // AJAX methods for dynamic loading
        loadFolders: function(callback) {
            $.ajax({
                url: filebird_fd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filebird_fd_get_folders',
                    nonce: filebird_fd_ajax.nonce
                },
                success: function(response) {
                    if (response.success && callback) {
                        callback(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading folders:', error);
                }
            });
        },

        loadDocuments: function(folderId, options, callback) {
            var data = {
                action: 'filebird_fd_get_documents',
                nonce: filebird_fd_ajax.nonce,
                folder_id: folderId
            };

            if (options) {
                $.extend(data, options);
            }

            $.ajax({
                url: filebird_fd_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success && callback) {
                        callback(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading documents:', error);
                }
            });
        },

        // Utility methods
        showLoading: function($container) {
            $container.html('<div class="filebird-docs-loading">Loading...</div>');
        },

        showError: function($container, message) {
            $container.html('<div class="filebird-docs-error">' + message + '</div>');
        },

        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        formatDate: function(dateString) {
            var date = new Date(dateString);
            return date.toLocaleDateString();
        },

        // Search functionality
        filterDocuments: function(searchTerm, $container) {
            var $items = $container.find('.filebird-docs-grid-item, .filebird-docs-list-item, .filebird-docs-table-row');
            
            $items.each(function() {
                var $item = $(this);
                var title = $item.find('.filebird-docs-card-title, .filebird-docs-list-title, .filebird-docs-table-title').text().toLowerCase();
                
                if (title.indexOf(searchTerm.toLowerCase()) > -1) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
        },

        // Sort functionality
        sortDocuments: function(sortBy, sortOrder, $container) {
            var $items = $container.find('.filebird-docs-grid-item, .filebird-docs-list-item, .filebird-docs-table-row').toArray();
            
            $items.sort(function(a, b) {
                var $a = $(a);
                var $b = $(b);
                var aValue, bValue;
                
                switch(sortBy) {
                    case 'title':
                        aValue = $a.find('.filebird-docs-card-title, .filebird-docs-list-title, .filebird-docs-table-title').text();
                        bValue = $b.find('.filebird-docs-card-title, .filebird-docs-list-title, .filebird-docs-table-title').text();
                        break;
                    case 'size':
                        aValue = parseInt($a.find('.filebird-docs-size').text().replace(/[^\d]/g, '')) || 0;
                        bValue = parseInt($b.find('.filebird-docs-size').text().replace(/[^\d]/g, '')) || 0;
                        break;
                    case 'date':
                        aValue = new Date($a.find('.filebird-docs-date').text());
                        bValue = new Date($b.find('.filebird-docs-date').text());
                        break;
                    default:
                        return 0;
                }
                
                if (sortOrder === 'desc') {
                    return aValue < bValue ? 1 : -1;
                } else {
                    return aValue > bValue ? 1 : -1;
                }
            });
            
            $container.find('.filebird-docs-grid, .filebird-docs-list, .filebird-docs-table tbody').append($items);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        FileBirdFD.Frontend.init();
    });

    // Export for global access
    window.FileBirdFD = FileBirdFD;

})(jQuery); 