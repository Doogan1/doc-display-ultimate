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

            // File input validation
            $(document).on('change', '#filebird-docs-file-input', function() {
                var file = this.files[0];
                if (file) {
                    // Check file size (10MB limit)
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File size must be less than 10MB');
                        this.value = '';
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
                        return;
                    }

                    // Update title if empty
                    var titleInput = $('#filebird-docs-title-input-replace');
                    if (!titleInput.val().trim()) {
                        titleInput.val(file.name.replace(/\.[^/.]+$/, ''));
                    }
                }
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
                
                var files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    var fileInput = $('#filebird-docs-file-input');
                    fileInput[0].files = files;
                    fileInput.trigger('change');
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
                                            <input type="file" id="filebird-docs-file-input" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.webp">
                                        </div>
                                    </div>
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
            
            // Reset form
            $('#filebird-docs-edit-form')[0].reset();
            $('#filebird-docs-tab-rename').addClass('active');
            $('#filebird-docs-tab-replace').removeClass('active');
            $('.filebird-docs-tab-btn').removeClass('active');
            $('.filebird-docs-tab-btn[data-tab="rename"]').addClass('active');
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
                var renameTitle = $('#filebird-docs-title-input').val();
                formData.set('document_title', renameTitle);
            } else if (activeTab === 'replace') {
                // For replace, use the replace tab input
                var replaceTitle = $('#filebird-docs-title-input-replace').val();
                formData.set('document_title', replaceTitle);
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
                        alert('Document updated successfully!');
                        FileBirdFD.DocumentEditor.closeModal();
                        // Reload the page to show the updated document
                        window.location.reload();
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
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        FileBirdFD.DocumentEditor.init();
    });

})(jQuery); 