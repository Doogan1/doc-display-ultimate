/**
 * FileBird Frontend Documents - Admin JavaScript
 * Version: 0.1.0
 */

(function($) {
    'use strict';

    // Admin namespace
    window.FileBirdFDAdmin = window.FileBirdFDAdmin || {};

    // Main admin class
    FileBirdFDAdmin.Admin = {
        selectedFolderId: null,
        selectedFolderName: null,
        folderTree: null,

        init: function() {
            this.loadFolders();
            this.bindEvents();
            this.updateShortcode();
        },

        bindEvents: function() {
            // Folder tree events
            $(document).on('click', '.filebird-fd-folder-item', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Prevent bubbling to parent folders
                FileBirdFDAdmin.Admin.selectFolder($(this));
            });

            $(document).on('click', '.filebird-fd-folder-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                FileBirdFDAdmin.Admin.toggleFolder($(this));
            });

            // Clear selection button
            $(document).on('click', '.filebird-fd-clear-selection', function(e) {
                e.preventDefault();
                FileBirdFDAdmin.Admin.clearSelection();
            });

            // Search functionality
            $('#folder-search').on('input', function() {
                FileBirdFDAdmin.Admin.filterFolders($(this).val());
            });

            // Expand/Collapse all buttons
            $('#expand-all-folders').on('click', function() {
                FileBirdFDAdmin.Admin.expandAllFolders();
            });

            $('#collapse-all-folders').on('click', function() {
                FileBirdFDAdmin.Admin.collapseAllFolders();
            });

            // Shortcode generator events
            $('#layout-select, #columns-input, #orderby-select, #order-select, #limit-input').on('change', function() {
                FileBirdFDAdmin.Admin.updateShortcode();
            });

            $('#show-title, #show-size, #show-date, #show-thumbnail, #include-subfolders, #group-by-folder, #accordion-default').on('change', function() {
                FileBirdFDAdmin.Admin.updateShortcode();
            });

            // Copy shortcode button
            $('#copy-shortcode').on('click', function() {
                FileBirdFDAdmin.Admin.copyShortcode();
            });
        },

        loadFolders: function() {
            var $folderTree = $('#folder-tree');

            // Show loading state
            $folderTree.html('<div class="filebird-fd-loading"><span class="spinner is-active"></span>Loading folders...</div>');

            $.ajax({
                url: filebird_fd_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'filebird_fd_get_folders_admin',
                    nonce: filebird_fd_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FileBirdFDAdmin.Admin.folderTree = response.data;
                        FileBirdFDAdmin.Admin.renderFolderTree(response.data);
                    } else {
                        FileBirdFDAdmin.Admin.showError('Failed to load folders.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading folders:', error);
                    FileBirdFDAdmin.Admin.showError('Error loading folders. Please try again.');
                }
            });
        },

        renderFolderTree: function(folders) {
            var $folderTree = $('#folder-tree');
            var html = '';

            if (folders.length === 0) {
                html = '<div class="filebird-fd-no-folders">No folders found. Please create folders in FileBird first.</div>';
            } else {
                html = this.buildFolderTreeHtml(folders);
            }

            $folderTree.html(html);
        },

        buildFolderTreeHtml: function(folders, level = 0) {
            var html = '<ul class="filebird-fd-folder-list' + (level > 0 ? ' filebird-fd-folder-children' : '') + '">';
            
            folders.forEach(function(folder) {
                var hasChildren = folder.children && folder.children.length > 0;
                var folderClass = 'filebird-fd-folder-item';
                var toggleClass = hasChildren ? 'filebird-fd-folder-toggle' : 'filebird-fd-folder-toggle-empty';
                var toggleIcon = hasChildren ? 'dashicons-arrow-right-alt2' : 'dashicons-arrow-right-alt2';
                
                html += '<li class="' + folderClass + '" data-folder-id="' + folder.id + '" data-folder-name="' + this.escapeHtml(folder.name) + '">';
                html += '<div class="filebird-fd-folder-content">';
                html += '<span class="' + toggleClass + '"><span class="dashicons ' + toggleIcon + '"></span></span>';
                html += '<span class="filebird-fd-folder-name">' + this.escapeHtml(folder.name) + '</span>';
                html += '<span class="filebird-fd-folder-count">(' + folder.count + ')</span>';
                html += '</div>';
                
                if (hasChildren) {
                    html += this.buildFolderTreeHtml(folder.children, level + 1);
                }
                
                html += '</li>';
            }.bind(this));
            
            html += '</ul>';
            return html;
        },

        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        },

        selectFolder: function($folderItem) {
            // Check if clicking the same folder (deselect)
            if ($folderItem.hasClass('selected')) {
                this.clearSelection();
                return;
            }
            
            // Remove previous selection
            $('.filebird-fd-folder-item').removeClass('selected');
            
            // Add selection to current item
            $folderItem.addClass('selected');
            
            // Get folder data
            var folderId = $folderItem.data('folder-id');
            var folderName = $folderItem.data('folder-name');
            
            // Update selected folder display
            this.selectedFolderId = folderId;
            this.selectedFolderName = folderName;
            
            $('#selected-folder-id').val(folderId);
            $('#selected-folder-display').html('<span class="selected-folder-name">' + this.escapeHtml(folderName) + '</span><button type="button" class="filebird-fd-clear-selection button button-small">Clear</button>');
            
            // Update shortcode
            this.updateShortcode();
        },

        toggleFolder: function($toggle) {
            var $folderItem = $toggle.closest('.filebird-fd-folder-item');
            var $children = $folderItem.find('> .filebird-fd-folder-children');
            var $icon = $toggle.find('.dashicons');
            
            if ($children.length > 0) {
                if ($children.is(':visible')) {
                    $children.slideUp(200);
                    $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-right-alt2');
                } else {
                    $children.slideDown(200);
                    $icon.removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
                }
            }
        },

        filterFolders: function(searchTerm) {
            if (!searchTerm) {
                // Show all folders
                $('.filebird-fd-folder-item').show();
                return;
            }
            
            searchTerm = searchTerm.toLowerCase();
            
            $('.filebird-fd-folder-item').each(function() {
                var $item = $(this);
                var folderName = $item.data('folder-name').toLowerCase();
                
                if (folderName.indexOf(searchTerm) !== -1) {
                    $item.show();
                    // Show parent folders
                    $item.parents('.filebird-fd-folder-item').show();
                } else {
                    $item.hide();
                }
            });
        },

        expandAllFolders: function() {
            $('.filebird-fd-folder-children').slideDown(200);
            $('.filebird-fd-folder-toggle .dashicons').removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
        },

        collapseAllFolders: function() {
            $('.filebird-fd-folder-children').slideUp(200);
            $('.filebird-fd-folder-toggle .dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-right-alt2');
        },

        updateShortcode: function() {
            var folder = this.selectedFolderId;
            var layout = $('#layout-select').val();
            var columns = $('#columns-input').val();
            var orderby = $('#orderby-select').val();
            var order = $('#order-select').val();
            var limit = $('#limit-input').val();
            var showTitle = $('#show-title').is(':checked');
            var showSize = $('#show-size').is(':checked');
            var showDate = $('#show-date').is(':checked');
            var showThumbnail = $('#show-thumbnail').is(':checked');
            var includeSubfolders = $('#include-subfolders').is(':checked');
            var groupByFolder = $('#group-by-folder').is(':checked');
            var accordionDefault = $('#accordion-default').val();

            var shortcode = '[filebird_docs';

            // Required parameter
            if (folder) {
                shortcode += ' folder="' + folder + '"';
            }

            // Optional parameters
            if (layout && layout !== 'grid') {
                shortcode += ' layout="' + layout + '"';
            }

            if (columns && columns !== '3' && layout === 'grid') {
                shortcode += ' columns="' + columns + '"';
            }

            if (orderby && orderby !== 'date') {
                shortcode += ' orderby="' + orderby + '"';
            }

            if (order && order !== 'DESC') {
                shortcode += ' order="' + order + '"';
            }

            if (limit && limit !== '-1') {
                shortcode += ' limit="' + limit + '"';
            }

            if (!showTitle) {
                shortcode += ' show_title="false"';
            }

            if (showSize) {
                shortcode += ' show_size="true"';
            }

            if (showDate) {
                shortcode += ' show_date="true"';
            }

            if (!showThumbnail) {
                shortcode += ' show_thumbnail="false"';
            }

            if (includeSubfolders) {
                shortcode += ' include_subfolders="true"';
            }

            if (groupByFolder) {
                shortcode += ' group_by_folder="true"';
            }

            if (accordionDefault && accordionDefault !== 'closed') {
                shortcode += ' accordion_default="' + accordionDefault + '"';
            }

            shortcode += ']';

            $('#shortcode-output').text(shortcode);
        },

        copyShortcode: function() {
            var shortcode = $('#shortcode-output').text();
            var $button = $('#copy-shortcode');
            var originalText = $button.text();

            // Create temporary textarea to copy text
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(shortcode).select();
            document.execCommand('copy');
            $temp.remove();

            // Show success feedback
            $button.text('Copied!').addClass('button-primary');
            
            setTimeout(function() {
                $button.text(originalText).removeClass('button-primary');
            }, 2000);
        },

        showError: function(message) {
            var $folderTree = $('#folder-tree');
            $folderTree.html('<div class="filebird-fd-notice error">' + message + '</div>');
        },

        showSuccess: function(message) {
            var $folderTree = $('#folder-tree');
            $folderTree.html('<div class="filebird-fd-notice success">' + message + '</div>');
        },

        clearSelection: function() {
            $('.filebird-fd-folder-item').removeClass('selected');
            this.selectedFolderId = null;
            this.selectedFolderName = null;
            $('#selected-folder-id').val('');
            $('#selected-folder-display').html('<span class="no-folder-selected">No folder selected</span>');
            this.updateShortcode();
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        FileBirdFDAdmin.Admin.init();
    });

    // Export for global access
    window.FileBirdFDAdmin = FileBirdFDAdmin;

})(jQuery); 