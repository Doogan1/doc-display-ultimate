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
        init: function() {
            this.loadFolders();
            this.bindEvents();
            this.updateShortcode();
        },

        bindEvents: function() {
            // Shortcode generator events
            $('#folder-select, #layout-select, #columns-input, #orderby-select, #order-select, #limit-input').on('change', function() {
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
            var $folderSelect = $('#folder-select');
            var $foldersList = $('#folders-list');

            // Show loading state
            $folderSelect.html('<option value="">Loading folders...</option>');
            $foldersList.html('<p class="filebird-fd-loading">Loading folders...</p>');

            $.ajax({
                url: filebird_fd_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'filebird_fd_get_folders_admin',
                    nonce: filebird_fd_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FileBirdFDAdmin.Admin.populateFolders(response.data, $folderSelect, $foldersList);
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

        populateFolders: function(folders, $folderSelect, $foldersList) {
            // Populate select dropdown
            $folderSelect.html('<option value="">Select a folder...</option>');
            
            folders.forEach(function(folder) {
                $folderSelect.append(
                    '<option value="' + folder.id + '">' + 
                    folder.name + ' (' + folder.count + ' documents)' +
                    '</option>'
                );
            });

            // Populate folders list with hierarchical display
            var foldersHtml = '<div class="filebird-fd-folders-list">';
            
            if (folders.length === 0) {
                foldersHtml += '<p>No folders found. Please create folders in FileBird first.</p>';
            } else {
                folders.forEach(function(folder) {
                    // Check if this is a subfolder (has dashes in the name)
                    var isSubfolder = folder.name.indexOf('—') !== -1;
                    var folderClass = isSubfolder ? 'filebird-fd-folder-hierarchical' : '';
                    
                    foldersHtml += 
                        '<div class="filebird-fd-folder-item ' + folderClass + '">' +
                            '<div class="filebird-fd-folder-info">' +
                                '<span class="filebird-fd-folder-name">' + folder.name + '</span>' +
                                '<span class="filebird-fd-folder-count">' + folder.count + ' documents</span>' +
                            '</div>' +
                            '<span class="filebird-fd-folder-id">ID: ' + folder.id + '</span>' +
                        '</div>';
                });
            }
            
            foldersHtml += '</div>';
            $foldersList.html(foldersHtml);
        },

        updateShortcode: function() {
            var folder = $('#folder-select').val();
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
            var $foldersList = $('#folders-list');
            $foldersList.html('<div class="filebird-fd-notice error">' + message + '</div>');
        },

        showSuccess: function(message) {
            var $foldersList = $('#folders-list');
            $foldersList.html('<div class="filebird-fd-notice success">' + message + '</div>');
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        FileBirdFDAdmin.Admin.init();
    });

    // Export for global access
    window.FileBirdFDAdmin = FileBirdFDAdmin;

})(jQuery); 