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
            if (!selectedFolders) {
                $documentList.html('<div class="empty">No folders selected. Please select folders in the document library settings above.</div>');
                return;
            }
            
            // Show loading state
            $documentList.html('<div class="loading"><span class="spinner is-active"></span>Loading documents from selected folders...</div>');
            
            // For now, we'll load documents from the first selected folder
            // In the future, we could enhance this to load from multiple folders
            var folderIds = selectedFolders.split(',').filter(function(id) { return id.trim() !== ''; });
            var firstFolderId = folderIds[0];
            
            $.ajax({
                url: filebird_fd_order.ajax_url,
                type: 'POST',
                data: {
                    action: 'filebird_fd_get_documents_for_ordering',
                    folder_id: firstFolderId,
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

        renderDocuments: function(documents) {
            var $documentList = $('#document-list');
            
            if (documents.length === 0) {
                $documentList.html('<div class="empty">No documents found in this folder.</div>');
                return;
            }
            
            var html = '';
            documents.forEach(function(doc, index) {
                html += this.buildDocumentItemHtml(doc, index + 1);
            }.bind(this));
            
            $documentList.html(html);
        },

        buildDocumentItemHtml: function(doc, order) {
            var fileIcon = this.getFileIcon(doc.file_type);
            var thumbnail = doc.thumbnail ? '<img src="' + doc.thumbnail + '" alt="' + doc.title + '">' : '<div class="file-icon ' + fileIcon + '"></div>';
            
            return '<div class="filebird-fd-document-item" data-document-id="' + doc.id + '">' +
                '<div class="filebird-fd-document-drag-handle" tabindex="0"></div>' +
                '<div class="filebird-fd-document-thumbnail">' + thumbnail + '</div>' +
                '<div class="filebird-fd-document-info">' +
                    '<div class="filebird-fd-document-title">' + this.escapeHtml(doc.title) + '</div>' +
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