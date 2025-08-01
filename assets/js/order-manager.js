/**
 * FileBird Frontend Documents - Order Manager JavaScript
 * Version: 0.1.0
 */

(function($) {
    'use strict';

    // Order Manager namespace
    window.FileBirdFDOrder = window.FileBirdFDOrder || {};

    // Main order manager class
    FileBirdFDOrder.OrderManager = {
        selectedFolderId: null,
        selectedFolderName: null,
        documents: [],
        originalOrder: [],
        isDirty: false,

        init: function() {
            // Only load folders if we're in standalone mode
            // In document library CPT, folders are loaded by admin.js
            if (!$('#document_library_folders').length) {
                this.loadFolders();
            }
            this.bindEvents();
            
            // Mark as initialized
            this.isInitialized = true;
        },

        bindEvents: function() {
            // Only bind folder tree events if we're in standalone mode (not in document library CPT)
            // In the document library CPT, folder selection is handled by admin.js
            if (!$('#document_library_folders').length) {
                // Folder tree events
                $(document).on('click', '.filebird-fd-folder-item', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    FileBirdFDOrder.OrderManager.selectFolder($(this));
                });

                $(document).on('click', '.filebird-fd-folder-toggle', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    FileBirdFDOrder.OrderManager.toggleFolder($(this));
                });
            }

            // Order control events
            $('#save-order').on('click', function() {
                FileBirdFDOrder.OrderManager.saveOrder();
            });

            $('#reset-order').on('click', function() {
                FileBirdFDOrder.OrderManager.resetOrder();
            });

            $('#preview-order').on('click', function() {
                FileBirdFDOrder.OrderManager.previewOrder();
            });

            // Search functionality
            $('#folder-search').on('input', function() {
                FileBirdFDOrder.OrderManager.searchFolders($(this).val());
            });

            // Expand/Collapse buttons
            $('#expand-all-folders').on('click', function() {
                FileBirdFDOrder.OrderManager.expandAllFolders();
            });

            $('#collapse-all-folders').on('click', function() {
                FileBirdFDOrder.OrderManager.collapseAllFolders();
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
                    action: 'filebird_fd_get_folders_order_manager',
                    nonce: filebird_fd_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FileBirdFDOrder.OrderManager.renderFolderTree(response.data);
                    } else {
                        FileBirdFDOrder.OrderManager.showError('Failed to load folders.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading folders:', error);
                    FileBirdFDOrder.OrderManager.showError('Error loading folders. Please try again.');
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
            // Remove previous selection
            $('.filebird-fd-folder-item').removeClass('selected');
            
            // Add selection to current item
            $folderItem.addClass('selected');
            
            // Get folder data
            var folderId = $folderItem.data('folder-id');
            var folderName = $folderItem.data('folder-name');
            
            // Update selected folder display
            $('#selected-folder-display').html('<span class="selected-folder">' + folderName + '</span>');
            $('#selected-folder-id').val(folderId);
            
            // Store selected folder
            this.selectedFolderId = folderId;
            this.selectedFolderName = folderName;
            
            // Load documents for this folder
            this.loadDocuments(folderId);
        },

        toggleFolder: function($toggle) {
            var $folderItem = $toggle.closest('.filebird-fd-folder-item');
            var $children = $folderItem.find('> .filebird-fd-folder-children');
            
            if ($children.length > 0) {
                $children.slideToggle(200);
                $toggle.find('.dashicons').toggleClass('dashicons-arrow-right-alt2 dashicons-arrow-down-alt2');
            }
        },

        loadDocuments: function(folderId) {
            var $documentList = $('#document-list');
            
            // Show loading state
            $documentList.html('<div class="loading"><span class="spinner is-active"></span>Loading documents...</div>');
            $('#document-order-section').show();
            
            $.ajax({
                url: filebird_fd_order.ajax_url,
                type: 'POST',
                data: {
                    action: 'filebird_fd_get_documents_for_ordering',
                    folder_id: folderId,
                    nonce: filebird_fd_order.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FileBirdFDOrder.OrderManager.documents = response.data;
                        FileBirdFDOrder.OrderManager.originalOrder = response.data.map(function(doc) { return doc.id; });
                        FileBirdFDOrder.OrderManager.renderDocuments(response.data);
                        FileBirdFDOrder.OrderManager.initializeSortable();
                    } else {
                        FileBirdFDOrder.OrderManager.showError('Failed to load documents.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading documents:', error);
                    FileBirdFDOrder.OrderManager.showError('Error loading documents. Please try again.');
                }
            });
        },

        loadDocumentsForLibrary: function() {
            var $documentList = $('#document-list');
            
            // Get all selected folders from the document library settings
            var selectedFolders = $('#document_library_folders').val();
            console.log('Selected folders:', selectedFolders);
            
            if (!selectedFolders) {
                $documentList.html('<div class="empty">No folders selected. Please select folders in the document library settings above.</div>');
                return;
            }
            
            // Get subfolder settings from the document library
            var $includeSubfoldersCheckbox = $('#document_library_include_subfolders');
            console.log('Include subfolders checkbox element:', $includeSubfoldersCheckbox);
            console.log('Include subfolders checkbox length:', $includeSubfoldersCheckbox.length);
            console.log('Include subfolders checkbox checked:', $includeSubfoldersCheckbox.is(':checked'));
            console.log('Include subfolders checkbox prop checked:', $includeSubfoldersCheckbox.prop('checked'));
            
            var includeSubfolders = $includeSubfoldersCheckbox.is(':checked');
            var excludeFolders = $('#document_library_exclude_folders').val() || '';
            console.log('Include subfolders:', includeSubfolders);
            console.log('Exclude folders:', excludeFolders);
            
            // Show loading state
            $documentList.html('<div class="loading"><span class="spinner is-active"></span>Loading documents from selected folders...</div>');
            
            // Load documents from all selected folders
            var ajaxData = {
                action: 'filebird_fd_get_documents_for_ordering',
                folder_ids: selectedFolders,
                include_subfolders: includeSubfolders,
                exclude_folders: excludeFolders,
                nonce: filebird_fd_order.nonce
            };
            console.log('AJAX request data:', ajaxData);
            
            $.ajax({
                url: filebird_fd_order.ajax_url,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    console.log('AJAX response:', response);
                    if (response.success) {
                        console.log('Documents loaded:', response.data.documents);
                        console.log('Folder info:', response.data.folder_info);
                        console.log('Total folders:', response.data.total_folders);
                        console.log('Total documents:', response.data.total_documents);
                        
                        FileBirdFDOrder.OrderManager.documents = response.data.documents;
                        FileBirdFDOrder.OrderManager.originalOrder = response.data.documents.map(function(doc) { return doc.id; });
                        FileBirdFDOrder.OrderManager.folderInfo = response.data.folder_info;
                        FileBirdFDOrder.OrderManager.renderDocuments(response.data.documents);
                        FileBirdFDOrder.OrderManager.renderFolderSummary(response.data);
                        FileBirdFDOrder.OrderManager.initializeSortable();
                    } else {
                        console.error('AJAX error response:', response);
                        FileBirdFDOrder.OrderManager.showError('Failed to load documents.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {xhr: xhr, status: status, error: error});
                    console.error('Response text:', xhr.responseText);
                    FileBirdFDOrder.OrderManager.showError('Error loading documents. Please try again.');
                }
            });
        },

        renderDocuments: function(documents) {
            var $documentList = $('#document-list');
            
            if (documents.length === 0) {
                $documentList.html('<div class="empty">No documents found in this folder.</div>');
                return;
            }
            
            // Group documents by source folder
            var groupedDocuments = {};
            documents.forEach(function(doc) {
                var folderName = doc.source_folder_name || 'Unknown Folder';
                if (!groupedDocuments[folderName]) {
                    groupedDocuments[folderName] = [];
                }
                groupedDocuments[folderName].push(doc);
            });
            
            var html = '';
            var globalOrder = 1;
            var self = this; // Store reference to 'this'
            
            // Render each folder group
            Object.keys(groupedDocuments).forEach(function(folderName) {
                var folderDocs = groupedDocuments[folderName];
                
                // Add folder header
                html += '<div class="filebird-fd-folder-group">';
                html += '<div class="filebird-fd-folder-header">';
                html += '<h4 class="filebird-fd-folder-title">' + self.escapeHtml(folderName) + '</h4>';
                html += '<span class="filebird-fd-folder-count">' + folderDocs.length + ' document(s)</span>';
                html += '</div>';
                html += '<div class="filebird-fd-folder-documents">';
                
                // Render documents in this folder with fresh ordering (1, 2, 3...)
                folderDocs.forEach(function(doc, index) {
                    html += self.buildDocumentItemHtml(doc, index + 1); // Start fresh at 1 for each folder
                });
                
                html += '</div>'; // Close folder-documents
                html += '</div>'; // Close folder-group
            });
            
            $documentList.html(html);
        },

        buildDocumentItemHtml: function(doc, order) {
            var fileIcon = this.getFileIcon(doc.file_type);
            var thumbnail = doc.thumbnail ? '<img src="' + doc.thumbnail + '" alt="' + doc.title + '">' : '<div class="file-icon ' + fileIcon + '"></div>';
            
            // Add folder indicator if document has source folder info
            var folderIndicator = '';
            if (doc.source_folder_name) {
                folderIndicator = '<div class="filebird-fd-document-folder">' + this.escapeHtml(doc.source_folder_name) + '</div>';
            }
            
            return '<div class="filebird-fd-document-item" data-document-id="' + doc.id + '">' +
                '<div class="filebird-fd-document-drag-handle" tabindex="0"></div>' +
                '<div class="filebird-fd-document-thumbnail">' + thumbnail + '</div>' +
                '<div class="filebird-fd-document-info">' +
                    '<div class="filebird-fd-document-title">' + this.escapeHtml(doc.title) + '</div>' +
                    folderIndicator +
                    '<div class="filebird-fd-document-meta">' +
                        '<span class="filebird-fd-document-filename">' + this.escapeHtml(doc.filename) + '</span>' +
                        '<span class="filebird-fd-document-size">' + doc.file_size + '</span>' +
                        '<span class="filebird-fd-document-type">' + doc.file_type + '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="filebird-fd-document-order">' + order + '</div>' +
                '</div>';
        },

        getFileIcon: function(fileType) {
            if (fileType.includes('pdf')) return 'file-icon-pdf';
            if (fileType.includes('word') || fileType.includes('document')) return 'file-icon-doc';
            if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'file-icon-xls';
            if (fileType.includes('powerpoint') || fileType.includes('presentation')) return 'file-icon-ppt';
            if (fileType.includes('image')) return 'file-icon-img';
            if (fileType.includes('video')) return 'file-icon-video';
            if (fileType.includes('audio')) return 'file-icon-audio';
            if (fileType.includes('zip') || fileType.includes('rar') || fileType.includes('tar')) return 'file-icon-archive';
            return 'file-icon-other';
        },

        initializeSortable: function() {
            var self = this;
            
            $('#document-list').sortable({
                handle: '.filebird-fd-document-drag-handle',
                placeholder: 'filebird-fd-document-item ui-sortable-placeholder',
                helper: function(e, item) {
                    return item.clone().addClass('ui-sortable-helper');
                },
                start: function(e, ui) {
                    ui.item.addClass('dragging');
                },
                stop: function(e, ui) {
                    ui.item.removeClass('dragging');
                    self.updateOrderNumbers();
                    self.markAsDirty();
                }
            });
        },

        updateOrderNumbers: function() {
            $('.filebird-fd-document-item').each(function(index) {
                $(this).find('.filebird-fd-document-order').text(index + 1);
            });
        },

        markAsDirty: function() {
            this.isDirty = true;
            $('#save-order').prop('disabled', false).addClass('button-primary');
        },

        saveOrder: function() {
            // Get the selected folders from the document library settings
            var selectedFolders = $('#document_library_folders').val();
            if (!selectedFolders) {
                this.showError('No folders selected in the document library.');
                return;
            }

            var documentOrder = [];
            $('.filebird-fd-document-item').each(function() {
                documentOrder.push($(this).data('document-id'));
            });

            this.showStatus('saving', filebird_fd_order.strings.saving);

            // For now, we'll save the order for the first folder
            // In the future, we could enhance this to handle multiple folders
            var folderIds = selectedFolders.split(',').filter(function(id) { return id.trim() !== ''; });
            var firstFolderId = folderIds[0];

            $.ajax({
                url: filebird_fd_order.ajax_url,
                type: 'POST',
                data: {
                    action: 'filebird_fd_update_document_order',
                    folder_id: firstFolderId,
                    document_order: documentOrder,
                    nonce: filebird_fd_order.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FileBirdFDOrder.OrderManager.showStatus('success', filebird_fd_order.strings.saved);
                        FileBirdFDOrder.OrderManager.isDirty = false;
                        FileBirdFDOrder.OrderManager.originalOrder = documentOrder;
                        $('#save-order').prop('disabled', true).removeClass('button-primary');
                    } else {
                        FileBirdFDOrder.OrderManager.showStatus('error', response.data || filebird_fd_order.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving order:', error);
                    FileBirdFDOrder.OrderManager.showStatus('error', filebird_fd_order.strings.error);
                }
            });
        },

        resetOrder: function() {
            if (!confirm(filebird_fd_order.strings.confirm_reset)) {
                return;
            }

            // Reload documents in original order
            this.loadDocuments(this.selectedFolderId);
            this.isDirty = false;
            $('#save-order').prop('disabled', true).removeClass('button-primary');
        },

        previewOrder: function() {
            // Create a preview dialog showing the current order
            var previewHtml = '<div class="filebird-fd-preview-dialog">';
            previewHtml += '<h3>Document Order Preview</h3>';
            previewHtml += '<div class="filebird-fd-preview-list">';
            
            $('.filebird-fd-document-item').each(function(index) {
                var title = $(this).find('.filebird-fd-document-title').text();
                previewHtml += '<div class="filebird-fd-preview-item">';
                previewHtml += '<span class="filebird-fd-preview-number">' + (index + 1) + '</span>';
                previewHtml += '<span class="filebird-fd-preview-title">' + title + '</span>';
                previewHtml += '</div>';
            });
            
            previewHtml += '</div></div>';

            // Create dialog
            var $dialog = $('<div id="preview-dialog" title="Document Order Preview">' + previewHtml + '</div>');
            $('body').append($dialog);
            
            $dialog.dialog({
                modal: true,
                width: 500,
                close: function() {
                    $(this).dialog('destroy').remove();
                }
            });
        },

        showStatus: function(type, message) {
            var $status = $('#order-status');
            $status.removeClass('saving success error').addClass(type);
            $status.find('.status-text').text(message);
            $status.show();
            
            if (type === 'success') {
                setTimeout(function() {
                    $status.fadeOut();
                }, 3000);
            }
        },

        showError: function(message) {
            console.error('FileBird FD Order Manager Error:', message);
        },

        searchFolders: function(query) {
            if (!query) {
                $('.filebird-fd-folder-item').show();
                return;
            }
            
            query = query.toLowerCase();
            $('.filebird-fd-folder-item').each(function() {
                var folderName = $(this).data('folder-name').toLowerCase();
                if (folderName.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        },

        expandAllFolders: function() {
            $('.filebird-fd-folder-children').show();
            $('.filebird-fd-folder-toggle .dashicons').removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
        },

        collapseAllFolders: function() {
            $('.filebird-fd-folder-children').hide();
            $('.filebird-fd-folder-toggle .dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-right-alt2');
        },

        renderFolderSummary: function(data) {
            var $summaryContainer = $('#folder-summary');
            if ($summaryContainer.length === 0) {
                // Create summary container if it doesn't exist
                $('#document-order-section').prepend('<div id="folder-summary" class="filebird-fd-folder-summary"></div>');
                $summaryContainer = $('#folder-summary');
            }
            
            var html = '<div class="filebird-fd-summary-header">';
            html += '<h3>Document Order Summary</h3>';
            html += '<div class="filebird-fd-summary-stats">';
            html += '<span class="filebird-fd-stat">' + data.total_folders + ' folder(s)</span>';
            html += '<span class="filebird-fd-stat">' + data.total_documents + ' document(s)</span>';
            html += '</div>';
            html += '</div>';
            
            if (data.folder_info && Object.keys(data.folder_info).length > 0) {
                html += '<div class="filebird-fd-folder-breakdown">';
                html += '<h4>Folders Included:</h4>';
                html += '<ul class="filebird-fd-folder-list">';
                
                Object.values(data.folder_info).forEach(function(folder) {
                    html += '<li class="filebird-fd-folder-item-summary">';
                    html += '<span class="filebird-fd-folder-name">' + this.escapeHtml(folder.name) + '</span>';
                    html += '<span class="filebird-fd-folder-count">' + folder.count + ' document(s)</span>';
                    html += '</li>';
                }.bind(this));
                
                html += '</ul>';
                html += '</div>';
            }
            
            $summaryContainer.html(html);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        FileBirdFDOrder.OrderManager.init();
        
        // Check if we need to load documents for an existing document library
        if ($('#document_library_orderby').val() === 'menu_order') {
            var selectedFolders = $('#document_library_folders').val();
            if (selectedFolders) {
                // Wait for the order manager to be fully initialized
                var checkInitialized = function() {
                    if (FileBirdFDOrder.OrderManager.isInitialized) {
                        FileBirdFDOrder.OrderManager.loadDocumentsForLibrary();
                    } else {
                        setTimeout(checkInitialized, 100);
                    }
                };
                setTimeout(checkInitialized, 500);
            }
        }
    });

})(jQuery); 