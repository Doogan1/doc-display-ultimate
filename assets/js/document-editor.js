/**
 * FileBird Frontend Documents - Document Editor JavaScript
 * Handles the modal functionality and AJAX file upload for editing documents
 */

(function($) {
    'use strict';

    // Plugin namespace
    window.FileBirdFD = window.FileBirdFD || {};

    // Document Editor class
    FileBirdFD.DocumentEditor = {
        init: function() {
            this.bindEvents();
            this.createModal();
            
            // Initialize flags
            this.processingFile = false;
        },

        bindEvents: function() {
            // Edit button click
            $(document).on('click', '.filebird-docs-edit-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var attachmentId = $btn.data('attachment-id');
                var documentTitle = $btn.data('document-title');
                var nonce = $btn.data('nonce');
                
                FileBirdFD.DocumentEditor.openModal(attachmentId, documentTitle, nonce);
            });

            // Close modal events
            $(document).on('click', '.filebird-docs-modal-close', function() {
                FileBirdFD.DocumentEditor.closeModal();
            });

            $(document).on('click', '.filebird-docs-modal-cancel', function() {
                FileBirdFD.DocumentEditor.closeModal();
            });

            // Close modal when clicking outside
            $(document).on('click', '.filebird-docs-modal-overlay', function(e) {
                if (e.target === this) {
                    FileBirdFD.DocumentEditor.closeModal();
                }
            });

            // Close modal with Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('.filebird-docs-modal-overlay').is(':visible')) {
                    FileBirdFD.DocumentEditor.closeModal();
                }
            });

            // File input change event handler
            $(document).on('change', '#filebird-docs-file-input', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Prevent multiple processing of the same file
                if (FileBirdFD.DocumentEditor.processingFile) {
                    return;
                }
                FileBirdFD.DocumentEditor.processingFile = true;
                
                var file = this.files[0];
                
                if (file) {
                    // Check file size (10MB limit)
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File size must be less than 10MB');
                        this.value = '';
                        FileBirdFD.DocumentEditor.resetDropZone();
                        FileBirdFD.DocumentEditor.processingFile = false;
                        return;
                    }

                    // Check file type
                    var allowedTypes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain',
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/webp'
                    ];

                    if (allowedTypes.indexOf(file.type) === -1) {
                        alert('Please select a valid document file');
                        this.value = '';
                        FileBirdFD.DocumentEditor.resetDropZone();
                        FileBirdFD.DocumentEditor.processingFile = false;
                        return;
                    }
                    
                    // Show file info in drop zone
                    FileBirdFD.DocumentEditor.showFileInfo(file);
                    
                    // Update title if empty
                    var titleInput = $('#filebird-docs-title-input-replace');
                    if (!titleInput.val().trim()) {
                        titleInput.val(file.name.replace(/\.[^/.]+$/, ''));
                    }
                }
                
                // Reset processing flag
                FileBirdFD.DocumentEditor.processingFile = false;
            });

            // Form submission
            $(document).on('submit', '#filebird-docs-edit-form', function(e) {
                e.preventDefault();
                FileBirdFD.DocumentEditor.submitForm();
            });

            // Tab switching
            $(document).on('click', '.filebird-docs-tab-btn', function(e) {
                e.preventDefault();
                var tab = $(this).data('tab');
                FileBirdFD.DocumentEditor.switchTab(tab);
            });

            // Drag and drop functionality
            $(document).on('dragover', '.filebird-docs-drop-zone', function(e) {
                e.preventDefault();
                $(this).addClass('filebird-docs-drop-zone-active');
            });

            $(document).on('dragleave', '.filebird-docs-drop-zone', function(e) {
                e.preventDefault();
                $(this).removeClass('filebird-docs-drop-zone-active');
            });

            $(document).on('drop', '.filebird-docs-drop-zone', function(e) {
                e.preventDefault();
                $(this).removeClass('filebird-docs-drop-zone-active');
                
                // Prevent multiple processing
                if (FileBirdFD.DocumentEditor.processingFile) {
                    return;
                }
                FileBirdFD.DocumentEditor.processingFile = true;
                
                var files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    var fileInput = $('#filebird-docs-file-input')[0];
                    fileInput.files = files;
                    // Manually call the change handler instead of triggering
                    if (fileInput.files[0]) {
                        var file = fileInput.files[0];
                        
                        // Check file size (10MB limit)
                        if (file.size > 10 * 1024 * 1024) {
                            alert('File size must be less than 10MB');
                            fileInput.value = '';
                            FileBirdFD.DocumentEditor.resetDropZone();
                            FileBirdFD.DocumentEditor.processingFile = false;
                            return;
                        }

                        // Check file type
                        var allowedTypes = [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'text/plain',
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ];

                        if (allowedTypes.indexOf(file.type) === -1) {
                            alert('Please select a valid document file');
                            fileInput.value = '';
                            FileBirdFD.DocumentEditor.resetDropZone();
                            FileBirdFD.DocumentEditor.processingFile = false;
                            return;
                        }

                        // Show file info in drop zone
                        FileBirdFD.DocumentEditor.showFileInfo(file);
                        
                        // Update title if empty
                        var titleInput = $('#filebird-docs-title-input-replace');
                        if (!titleInput.val().trim()) {
                            titleInput.val(file.name.replace(/\.[^/.]+$/, ''));
                        }
                    }
                }
                
                // Reset processing flag
                FileBirdFD.DocumentEditor.processingFile = false;
            });
            
            // Click to browse functionality
            $(document).on('click', '.filebird-docs-drop-zone', function(e) {
                // Don't trigger if clicking on the remove button or if we're in success state
                if ($(e.target).closest('.filebird-docs-remove-file').length > 0 || $(this).hasClass('filebird-docs-drop-zone-success')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                
                // Don't trigger if we're currently processing a file
                if (FileBirdFD.DocumentEditor.processingFile) {
                    return false;
                }
                
                // Only trigger file input if we're not in success state and no file is currently selected
                if (!$(this).hasClass('filebird-docs-drop-zone-success') && !$(this).find('.filebird-docs-file-info').length) {
                    // Use the existing file input
                    var fileInput = $('#filebird-docs-file-input')[0];
                    if (fileInput) {
                        fileInput.click();
                    }
                }
            });
        },

        createModal: function() {
            var modalHtml = `
                <div id="filebird-docs-modal" class="filebird-docs-modal-overlay hidden">
                    <div class="filebird-docs-modal">
                        <div class="filebird-docs-modal-header">
                            <h3 class="filebird-docs-modal-title">Edit Document</h3>
                            <button type="button" class="filebird-docs-modal-close" aria-label="Close">
                                <i class="filebird-docs-icon filebird-docs-icon-close"></i>
                            </button>
                        </div>
                        
                        <div class="filebird-docs-modal-tabs">
                            <button type="button" class="filebird-docs-tab-btn active" data-tab="rename">
                                <i class="filebird-docs-icon filebird-docs-icon-edit"></i>
                                Rename
                            </button>
                            <button type="button" class="filebird-docs-tab-btn" data-tab="replace">
                                <i class="filebird-docs-icon filebird-docs-icon-upload"></i>
                                Replace File
                            </button>
                        </div>
                        
                        <form id="filebird-docs-edit-form" class="filebird-docs-modal-form">
                            <input type="hidden" id="filebird-docs-attachment-id" name="attachment_id">
                            <input type="hidden" id="filebird-docs-nonce" name="filebird_fd_nonce">
                            
                            <div id="filebird-docs-tab-rename" class="filebird-docs-tab-content active">
                                <div class="filebird-docs-form-group">
                                    <label for="filebird-docs-title-input">Document Title</label>
                                    <input type="text" id="filebird-docs-title-input" name="document_title" required>
                                </div>
                            </div>
                            
                            <div id="filebird-docs-tab-replace" class="filebird-docs-tab-content">
                                <div class="filebird-docs-form-group">
                                    <label for="filebird-docs-title-input-replace">Document Title</label>
                                    <input type="text" id="filebird-docs-title-input-replace" name="document_title" required>
                                </div>
                                
                                <div class="filebird-docs-form-group">
                                    <label for="filebird-docs-file-input">New File</label>
                                    <div class="filebird-docs-drop-zone">
                                        <div class="filebird-docs-drop-zone-content">
                                            <i class="filebird-docs-icon filebird-docs-icon-upload"></i>
                                            <p>Drag and drop a file here or click to browse</p>
                                        </div>
                                    </div>
                                    <input type="file" id="filebird-docs-file-input" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.webp" style="display: none;">
                                </div>
                            </div>
                            
                            <div class="filebird-docs-modal-actions">
                                <button type="button" class="filebird-docs-modal-cancel">Cancel</button>
                                <button type="submit" class="filebird-docs-modal-submit">
                                    <span class="submit-text">Save Changes</span>
                                    <span class="loading-text" style="display: none;">Processing...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
        },

        openModal: function(attachmentId, documentTitle, nonce) {
            $('#filebird-docs-attachment-id').val(attachmentId);
            $('#filebird-docs-nonce').val(nonce);
            $('#filebird-docs-title-input').val(documentTitle);
            $('#filebird-docs-title-input-replace').val(documentTitle);
            
            $('#filebird-docs-modal').removeClass('hidden');
            $('body').css('overflow', 'hidden');
            
            // Focus on title input
            $('#filebird-docs-title-input').focus();
        },

        closeModal: function() {
            $('#filebird-docs-modal').addClass('hidden');
            $('body').css('overflow', '');
            
            // Manually clear form fields instead of reset
            $('#filebird-docs-title-input').val('');
            $('#filebird-docs-title-input-replace').val('');
            $('#filebird-docs-attachment-id').val('');
            $('#filebird-docs-nonce').val('');
            
            $('#filebird-docs-tab-rename').addClass('active');
            $('#filebird-docs-tab-replace').removeClass('active');
            $('.filebird-docs-tab-btn').removeClass('active');
            $('.filebird-docs-tab-btn[data-tab="rename"]').addClass('active');
            
            // Reset drop zone
            this.resetDropZone();
            
            // Reset flags
            this.processingFile = false;
        },

        switchTab: function(tab) {
            // Update tab buttons
            $('.filebird-docs-tab-btn').removeClass('active');
            $('.filebird-docs-tab-btn[data-tab="' + tab + '"]').addClass('active');
            
            // Update tab content
            $('.filebird-docs-tab-content').removeClass('active');
            $('#filebird-docs-tab-' + tab).addClass('active');
            
            // Sync title inputs
            var titleValue = $('#filebird-docs-title-input').val();
            $('#filebird-docs-title-input-replace').val(titleValue);
        },

        submitForm: function() {
            var $form = $('#filebird-docs-edit-form');
            var $submitBtn = $form.find('.filebird-docs-modal-submit');
            var $submitText = $submitBtn.find('.submit-text');
            var $loadingText = $submitBtn.find('.loading-text');
            
            // Get attachment ID and new title for UI update
            var attachmentId = $('#filebird-docs-attachment-id').val();
            var newTitle = '';
            
            // Determine which tab is active
            var activeTab = $('.filebird-docs-tab-btn.active').data('tab');
            var action = activeTab === 'replace' ? 'filebird_fd_replace_document' : 'filebird_fd_rename_document';
            
            // Show loading state
            $submitBtn.prop('disabled', true);
            $submitText.hide();
            $loadingText.show();
            
            // Prepare form data
            var formData = new FormData($form[0]);
            formData.append('action', action);
            
            // Ensure we're using the correct title input based on active tab
            if (activeTab === 'rename') {
                // For rename, use the rename tab input
                newTitle = $('#filebird-docs-title-input').val();
                formData.set('document_title', newTitle);
            } else if (activeTab === 'replace') {
                // For replace, use the replace tab input
                newTitle = $('#filebird-docs-title-input-replace').val();
                formData.set('document_title', newTitle);
                
                // Check if file is attached
                var fileInput = $('#filebird-docs-file-input')[0];
                if (!fileInput.files || fileInput.files.length === 0) {
                    alert('Please select a file to upload');
                    return;
                }
                
                // Manually append the file to FormData to ensure it's included
                formData.append('document_file', fileInput.files[0]);
            }
            
            // AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        if (activeTab === 'replace') {
                            // For file replacement, we need to reload the page to show new file and thumbnail
                            // This is necessary because:
                            // 1. The file URL changes
                            // 2. Thumbnail generation takes time
                            // 3. File metadata (size, type) may change
                            FileBirdFD.DocumentEditor.showSuccessMessage('Document replaced successfully!');
                            FileBirdFD.DocumentEditor.closeModal();
                            // Reload the page to show the new file and thumbnail
                            window.location.reload();
                        } else {
                            // For rename, update the UI without page reload
                            FileBirdFD.DocumentEditor.updateDocumentUI(attachmentId, newTitle);
                            FileBirdFD.DocumentEditor.showSuccessMessage('Document renamed successfully!');
                            FileBirdFD.DocumentEditor.closeModal();
                        }
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error occurred'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while updating the document. Please try again.');
                },
                complete: function() {
                    // Restore button state
                    $submitBtn.prop('disabled', false);
                    $submitText.show();
                    $loadingText.hide();
                }
            });
        },
        
        updateDocumentUI: function(attachmentId, newTitle) {
            // Update document title in all places where it appears
            var $documentElements = $('[data-attachment-id="' + attachmentId + '"]').closest('.filebird-docs-card, .filebird-docs-list-item, .filebird-docs-table-row');
            
            $documentElements.each(function() {
                var $element = $(this);
                
                // Update card title
                $element.find('.filebird-docs-card-title a, .filebird-docs-list-title a, .filebird-docs-table-title a').text(newTitle);
                
                // Update link titles
                $element.find('.filebird-docs-link').attr('title', newTitle);
                
                // Update edit button data
                $element.find('.filebird-docs-edit-btn').attr('data-document-title', newTitle);
            });
            
            // Update modal title if it's open for this document
            if ($('#filebird-docs-attachment-id').val() === attachmentId) {
                $('#filebird-docs-title-input').val(newTitle);
                $('#filebird-docs-title-input-replace').val(newTitle);
            }
        },
        
        updateDocumentThumbnail: function(attachmentId, thumbnailUrl, mediumUrl, fileUrl) {
            // Update document thumbnails and file URLs in all places where they appear
            var $documentElements = $('[data-attachment-id="' + attachmentId + '"]').closest('.filebird-docs-card, .filebird-docs-list-item, .filebird-docs-table-row');
            
            $documentElements.each(function() {
                var $element = $(this);
                
                // Update thumbnail images
                $element.find('.filebird-docs-thumbnail').attr('src', thumbnailUrl);
                
                // Update file links
                $element.find('.filebird-docs-link').attr('href', fileUrl);
                $element.find('.filebird-docs-download-btn').attr('href', fileUrl);
                
                // Update medium images if they exist
                if (mediumUrl) {
                    $element.find('.filebird-docs-medium').attr('src', mediumUrl);
                }
            });
        },
        
        showSuccessMessage: function(message) {
            // Create a subtle success notification
            var $notification = $('<div class="filebird-docs-notification filebird-docs-notification-success">' + message + '</div>');
            $('body').append($notification);
            
            // Show the notification
            setTimeout(function() {
                $notification.addClass('show');
            }, 100);
            
            // Hide after 3 seconds
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);
        },
        
        showFileInfo: function(file) {
            var $dropZone = $('.filebird-docs-drop-zone');
            var $content = $dropZone.find('.filebird-docs-drop-zone-content');
            
            // Format file size
            var fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            
            // Get file type icon
            var fileType = this.getFileTypeIcon(file.type);
            
            // Update drop zone content
            $content.html(`
                <div class="filebird-docs-file-info">
                    <i class="filebird-docs-icon ${fileType}"></i>
                    <div class="filebird-docs-file-details">
                        <div class="filebird-docs-file-name">${file.name}</div>
                        <div class="filebird-docs-file-size">${fileSize}</div>
                    </div>
                    <button type="button" class="filebird-docs-remove-file" title="Remove file">
                        <i class="filebird-docs-icon filebird-docs-icon-close"></i>
                    </button>
                </div>
            `);
            
            // Add remove file functionality
            $content.find('.filebird-docs-remove-file').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                $('#filebird-docs-file-input').val('');
                FileBirdFD.DocumentEditor.resetDropZone();
                return false;
            });
            
            // Also handle remove button clicks via event delegation
            $(document).on('click', '.filebird-docs-remove-file', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                $('#filebird-docs-file-input').val('');
                FileBirdFD.DocumentEditor.resetDropZone();
                return false;
            });
            
            // Add success styling
            $dropZone.addClass('filebird-docs-drop-zone-success');
        },
        
        resetDropZone: function() {
            var $dropZone = $('.filebird-docs-drop-zone');
            var $content = $dropZone.find('.filebird-docs-drop-zone-content');
            
            // Reset to original content
            $content.html(`
                <i class="filebird-docs-icon filebird-docs-icon-upload"></i>
                <p>Drag and drop a file here or click to browse</p>
            `);
            
            // Remove success styling
            $dropZone.removeClass('filebird-docs-drop-zone-success');
        },
        
        getFileTypeIcon: function(mimeType) {
            var iconMap = {
                'application/pdf': 'filebird-docs-icon-pdf',
                'application/msword': 'filebird-docs-icon-doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'filebird-docs-icon-docx',
                'application/vnd.ms-excel': 'filebird-docs-icon-xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'filebird-docs-icon-xlsx',
                'application/vnd.ms-powerpoint': 'filebird-docs-icon-ppt',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'filebird-docs-icon-pptx',
                'text/plain': 'filebird-docs-icon-txt',
                'image/jpeg': 'filebird-docs-icon-image',
                'image/png': 'filebird-docs-icon-image',
                'image/gif': 'filebird-docs-icon-image',
                'image/webp': 'filebird-docs-icon-image'
            };
            
            return iconMap[mimeType] || 'filebird-docs-icon-file';
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        FileBirdFD.DocumentEditor.init();
    });

})(jQuery); 