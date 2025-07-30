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
        excludedSubfolders: [],
        accordionStates: {},

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

            // Subfolder checkbox events
            $(document).on('change', '.subfolder-checkbox', function() {
                FileBirdFDAdmin.Admin.updateExcludedSubfolders();
            });

            // Subfolder toggle events
            $(document).on('click', '.subfolder-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                FileBirdFDAdmin.Admin.toggleSubfolder($(this));
            });

            // Accordion state toggle events
            $(document).on('click', '.accordion-state-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                FileBirdFDAdmin.Admin.toggleAccordionState($(this));
            });

            // Check/Uncheck all subfolders
            $('#check-all-subfolders').on('click', function() {
                FileBirdFDAdmin.Admin.checkAllSubfolders();
            });

            $('#uncheck-all-subfolders').on('click', function() {
                FileBirdFDAdmin.Admin.uncheckAllSubfolders();
            });

            // Expand/Collapse all subfolders
            $('#expand-all-subfolders').on('click', function() {
                FileBirdFDAdmin.Admin.expandAllSubfolders();
            });

            $('#collapse-all-subfolders').on('click', function() {
                FileBirdFDAdmin.Admin.collapseAllSubfolders();
            });

            // Accordion state control events
            $(document).on('change', '.accordion-state-radio', function() {
                FileBirdFDAdmin.Admin.updateAccordionStates();
            });

            // Open/Close all accordions
            $('#open-all-accordions').on('click', function() {
                FileBirdFDAdmin.Admin.openAllAccordions();
            });

            $('#close-all-accordions').on('click', function() {
                FileBirdFDAdmin.Admin.closeAllAccordions();
            });

            // Expand/Collapse all accordion state controls
            $('#expand-all-accordion-states').on('click', function() {
                FileBirdFDAdmin.Admin.expandAllAccordionStates();
            });

            $('#collapse-all-accordion-states').on('click', function() {
                FileBirdFDAdmin.Admin.collapseAllAccordionStates();
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

            // Show/hide accordion state controls based on group-by-folder setting
            $('#group-by-folder').on('change', function() {
                if ($(this).is(':checked') && FileBirdFDAdmin.Admin.selectedFolderId) {
                    $('#accordion-state-controls').show();
                } else {
                    $('#accordion-state-controls').hide();
                }
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
            
            // Populate subfolders if they exist
            this.populateSubfolders(folderId);
            
            // Show accordion state controls if group-by-folder is enabled
            if ($('#group-by-folder').is(':checked')) {
                $('#accordion-state-controls').show();
            }
            
            // Update shortcode
            this.updateShortcode();
        },

        populateSubfolders: function(folderId) {
            var subfolders = this.findSubfoldersHierarchical(folderId);
            
            if (subfolders.length > 0) {
                var html = this.buildNestedSubfolderHtml(subfolders);
                
                $('#subfolder-list').html(html);
                $('#subfolder-controls').show();
                
                // Initialize excluded subfolders
                this.updateExcludedSubfolders();
                
                // Also populate accordion state controls
                this.populateAccordionStateControls(folderId);
            } else {
                $('#subfolder-controls').hide();
                $('#accordion-state-controls').hide();
                this.excludedSubfolders = [];
                this.accordionStates = {};
            }
        },

        populateAccordionStateControls: function(folderId) {
            var subfolders = this.findSubfoldersHierarchical(folderId);
            
            if (subfolders.length > 0) {
                var html = this.buildNestedAccordionStateHtml(subfolders);
                
                $('#accordion-state-list').html(html);
                $('#accordion-state-controls').show();
                
                // Initialize accordion states
                this.updateAccordionStates();
            } else {
                $('#accordion-state-controls').hide();
                this.accordionStates = {};
            }
        },

        findSubfoldersHierarchical: function(folderId) {
            var hierarchicalSubfolders = [];
            this.findSubfoldersHierarchicalRecursive(this.folderTree, folderId, hierarchicalSubfolders);
            return hierarchicalSubfolders;
        },

        findSubfoldersHierarchicalRecursive: function(folders, parentId, hierarchicalSubfolders) {
            folders.forEach(function(folder) {
                if (folder.id == parentId && folder.children) {
                    folder.children.forEach(function(child) {
                        var subfolderData = {
                            id: child.id,
                            name: child.name,
                            children: []
                        };
                        
                        // Recursively get nested subfolders
                        if (child.children && child.children.length > 0) {
                            this.findSubfoldersHierarchicalRecursive([child], child.id, subfolderData.children);
                        }
                        
                        hierarchicalSubfolders.push(subfolderData);
                    }.bind(this));
                } else if (folder.children) {
                    this.findSubfoldersHierarchicalRecursive(folder.children, parentId, hierarchicalSubfolders);
                }
            }.bind(this));
        },

        buildNestedSubfolderHtml: function(subfolders, level = 0) {
            var html = '';
            
            // Get excluded folders from the document library settings
            var excludedFolders = [];
            var excludeInput = $('#document_library_exclude_folders');
            if (excludeInput.length > 0) {
                var excludeValue = excludeInput.val();
                if (excludeValue) {
                    excludedFolders = excludeValue.split(',').map(function(id) { return id.trim(); });
                }
            }
            
            subfolders.forEach(function(subfolder) {
                var hasChildren = subfolder.children && subfolder.children.length > 0;
                var folderClass = 'subfolder-item';
                var toggleClass = hasChildren ? 'subfolder-toggle' : 'subfolder-toggle-empty';
                var toggleIcon = hasChildren ? 'dashicons-arrow-right-alt2' : 'dashicons-arrow-right-alt2';
                
                // Check if this subfolder is in the excluded list
                var isExcluded = excludedFolders.indexOf(subfolder.id.toString()) !== -1;
                var checkedAttr = isExcluded ? '' : ' checked';
                
                html += '<div class="' + folderClass + '" data-level="' + level + '">';
                html += '<div class="subfolder-content">';
                html += '<span class="' + toggleClass + '"><span class="dashicons ' + toggleIcon + '"></span></span>';
                html += '<label><input type="checkbox" class="subfolder-checkbox" value="' + subfolder.id + '"' + checkedAttr + '> ' + this.escapeHtml(subfolder.name) + '</label>';
                html += '</div>';
                
                if (hasChildren) {
                    html += '<div class="subfolder-children" style="display: none;">';
                    html += this.buildNestedSubfolderHtml(subfolder.children, level + 1);
                    html += '</div>';
                }
                
                html += '</div>';
            }.bind(this));
            
            return html;
        },

        buildNestedAccordionStateHtml: function(subfolders, level = 0) {
            var html = '';
            
            // Get excluded folders from the document library settings
            var excludedFolders = [];
            var excludeInput = $('#document_library_exclude_folders');
            if (excludeInput.length > 0) {
                var excludeValue = excludeInput.val();
                if (excludeValue) {
                    excludedFolders = excludeValue.split(',').map(function(id) { return id.trim(); });
                }
            }
            
            subfolders.forEach(function(subfolder) {
                // Skip excluded folders and their entire subtree
                if (excludedFolders.indexOf(subfolder.id.toString()) !== -1) {
                    // Skip this folder and all its children completely
                    return;
                }
                
                var hasChildren = subfolder.children && subfolder.children.length > 0;
                var folderClass = 'accordion-state-item';
                var toggleClass = hasChildren ? 'accordion-state-toggle' : 'accordion-state-toggle-empty';
                var toggleIcon = hasChildren ? 'dashicons-arrow-right-alt2' : 'dashicons-arrow-right-alt2';
                
                html += '<div class="' + folderClass + '" data-level="' + level + '">';
                html += '<div class="accordion-state-content">';
                html += '<span class="' + toggleClass + '"><span class="dashicons ' + toggleIcon + '"></span></span>';
                html += '<div class="accordion-state-controls">';
                html += '<label><input type="radio" class="accordion-state-radio" name="accordion_state_' + subfolder.id + '" value="' + subfolder.id + '_open" checked> ' + this.escapeHtml(subfolder.name) + ' - Open</label>';
                html += '<label><input type="radio" class="accordion-state-radio" name="accordion_state_' + subfolder.id + '" value="' + subfolder.id + '_closed"> ' + this.escapeHtml(subfolder.name) + ' - Closed</label>';
                html += '</div>';
                html += '</div>';
                
                if (hasChildren) {
                    html += '<div class="accordion-state-children" style="display: none;">';
                    html += this.buildNestedAccordionStateHtml(subfolder.children, level + 1);
                    html += '</div>';
                }
                
                html += '</div>';
            }.bind(this));
            
            return html;
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

        toggleSubfolder: function($toggle) {
            var $subfolderItem = $toggle.closest('.subfolder-item');
            var $children = $subfolderItem.find('.subfolder-children');
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

        toggleAccordionState: function($toggle) {
            var $accordionStateItem = $toggle.closest('.accordion-state-item');
            var $children = $accordionStateItem.find('.accordion-state-children');
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

            // Add exclude_folders attribute if there are excluded subfolders
            if (this.excludedSubfolders.length > 0) {
                shortcode += ' exclude_folders="' + this.excludedSubfolders.join(',') + '"';
            }

            // Add accordion_states attribute if there are custom accordion states
            if (Object.keys(this.accordionStates).length > 0) {
                var accordionStatesArray = [];
                for (var folderId in this.accordionStates) {
                    if (this.accordionStates.hasOwnProperty(folderId)) {
                        var state = this.accordionStates[folderId] ? 'open' : 'closed';
                        accordionStatesArray.push(folderId + ':' + state);
                    }
                }
                if (accordionStatesArray.length > 0) {
                    shortcode += ' accordion_states="' + accordionStatesArray.join(',') + '"';
                }
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
            this.excludedSubfolders = [];
            this.accordionStates = {};
            $('#selected-folder-id').val('');
            $('#selected-folder-display').html('<span class="no-folder-selected">No folder selected</span>');
            $('#subfolder-controls').hide();
            $('#accordion-state-controls').hide();
            this.updateShortcode();
        },

        updateExcludedSubfolders: function() {
            this.excludedSubfolders = [];
            $('.subfolder-checkbox:not(:checked)').each(function() {
                FileBirdFDAdmin.Admin.excludedSubfolders.push($(this).val());
            });
            
            // Update the document library exclude folders field
            var excludeInput = $('#document_library_exclude_folders');
            if (excludeInput.length > 0) {
                excludeInput.val(this.excludedSubfolders.join(','));
            }
            
            this.updateShortcode();
        },

        checkAllSubfolders: function() {
            $('.subfolder-checkbox').prop('checked', true);
            this.updateExcludedSubfolders();
        },

        uncheckAllSubfolders: function() {
            $('.subfolder-checkbox').prop('checked', false);
            this.updateExcludedSubfolders();
        },

        expandAllSubfolders: function() {
            $('.subfolder-children').slideDown(200);
            $('.subfolder-toggle .dashicons').removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
        },

        collapseAllSubfolders: function() {
            $('.subfolder-children').slideUp(200);
            $('.subfolder-toggle .dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-right-alt2');
        },

        updateAccordionStates: function() {
            this.accordionStates = {};
            $('.accordion-state-radio:checked').each(function() {
                var value = $(this).val();
                var parts = value.split('_');
                if (parts.length === 2) {
                    var folderId = parts[0];
                    var state = parts[1];
                    FileBirdFDAdmin.Admin.accordionStates[folderId] = (state === 'open');
                }
            });
            this.updateShortcode();
        },

        openAllAccordions: function() {
            $('.accordion-state-radio').prop('checked', true);
            this.updateAccordionStates();
        },

        closeAllAccordions: function() {
            $('.accordion-state-radio').prop('checked', false);
            this.updateAccordionStates();
        },

        expandAllAccordionStates: function() {
            $('.accordion-state-radio').prop('checked', true);
            this.updateAccordionStates();
        },

        collapseAllAccordionStates: function() {
            $('.accordion-state-radio').prop('checked', false);
            this.updateAccordionStates();
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        FileBirdFDAdmin.Admin.init();
    });

    // Export for global access
    window.FileBirdFDAdmin = FileBirdFDAdmin;

})(jQuery); 