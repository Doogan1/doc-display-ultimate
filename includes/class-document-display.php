<?php
/**
 * Document Display Class
 * 
 * Handles document rendering and display logic
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FileBird_FD_Document_Display {
    
    public function __construct() {
        add_action('wp_ajax_filebird_fd_get_folders', array($this, 'ajaxGetFolders'));
        add_action('wp_ajax_nopriv_filebird_fd_get_folders', array($this, 'ajaxGetFolders'));
        add_action('wp_ajax_filebird_fd_get_documents', array($this, 'ajaxGetDocuments'));
        add_action('wp_ajax_nopriv_filebird_fd_get_documents', array($this, 'ajaxGetDocuments'));
        
        // Document editing AJAX handlers
        add_action('wp_ajax_filebird_fd_replace_document', array($this, 'ajaxReplaceDocument'));
        add_action('wp_ajax_filebird_fd_rename_document', array($this, 'ajaxRenameDocument'));
    }
    
    /**
     * AJAX handler for getting folders
     */
    public function ajaxGetFolders() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_nonce')) {
            wp_die(__('Security check failed.', 'filebird-frontend-docs'));
        }
        
        $folders = FileBird_FD_Helper::getAllFolders();
        $options = array();
        
        foreach ($folders as $folder) {
            // Check if required properties exist
            if (!isset($folder->id) || !isset($folder->name)) {
                continue; // Skip folders with missing properties
            }
            
            $options[] = array(
                'id' => $folder->id,
                'name' => $folder->name,
                'count' => FileBird_FD_Helper::getAttachmentCountByFolderId($folder->id)
            );
        }
        
        wp_send_json_success($options);
    }
    
    /**
     * AJAX handler for getting documents
     */
    public function ajaxGetDocuments() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_nonce')) {
            wp_die(__('Security check failed.', 'filebird-frontend-docs'));
        }
        
        $folder_id = intval($_POST['folder_id']);
        $args = array(
            'orderby' => sanitize_text_field($_POST['orderby'] ?? 'date'),
            'order' => sanitize_text_field($_POST['order'] ?? 'DESC'),
            'limit' => intval($_POST['limit'] ?? -1),
            'include_subfolders' => filter_var($_POST['include_subfolders'] ?? false, FILTER_VALIDATE_BOOLEAN)
        );
        
        $attachments = FileBird_FD_Helper::getAttachmentsByFolderIdRecursive($folder_id, $args);
        $documents = array();
        
        foreach ($attachments as $attachment) {
            $documents[] = array(
                'id' => $attachment->ID,
                'title' => $attachment->post_title,
                'url' => $attachment->file_url,
                'type' => $attachment->file_type,
                'size' => $attachment->file_size,
                'date' => get_the_date('', $attachment->ID),
                'thumbnail' => $attachment->thumbnail_url,
                'medium' => $attachment->medium_url
            );
        }
        
        wp_send_json_success($documents);
    }
    
    /**
     * Render document grid
     */
    public static function renderGrid($attachments, $atts) {
        $columns = intval($atts['columns']);
        $column_class = 'filebird-docs-col-' . $columns;
        ?>
        <div class="filebird-docs-grid <?php echo esc_attr($column_class); ?>">
            <?php foreach ($attachments as $attachment): ?>
                <div class="filebird-docs-grid-item">
                    <div class="filebird-docs-card">
                        <?php if ($atts['show_thumbnail'] && $attachment->thumbnail_url): ?>
                            <div class="filebird-docs-card-image">
                                <img src="<?php echo esc_url($attachment->thumbnail_url); ?>" 
                                     alt="<?php echo esc_attr($attachment->post_title); ?>"
                                     class="filebird-docs-thumbnail">
                            </div>
                        <?php endif; ?>
                        
                        <div class="filebird-docs-card-content">
                            <?php if ($atts['show_title']): ?>
                                <h4 class="filebird-docs-card-title">
                                    <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                       target="_blank" 
                                       class="filebird-docs-link">
                                        <?php echo esc_html($attachment->post_title); ?>
                                    </a>
                                </h4>
                            <?php endif; ?>
                            
                            <div class="filebird-docs-card-meta">
                                <?php if ($atts['show_size'] && $attachment->file_size): ?>
                                    <span class="filebird-docs-size">
                                        <i class="filebird-docs-icon filebird-docs-icon-size"></i>
                                        <?php echo esc_html($attachment->file_size); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($atts['show_date']): ?>
                                    <span class="filebird-docs-date">
                                        <i class="filebird-docs-icon filebird-docs-icon-date"></i>
                                        <?php echo esc_html(get_the_date('', $attachment->ID)); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="filebird-docs-card-actions">
                                <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                   target="_blank" 
                                   class="filebird-docs-download-btn">
                                    <?php _e('Download', 'filebird-frontend-docs'); ?>
                                </a>
                                <?php echo self::getEditButton($attachment->ID, $attachment->post_title); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Render document list
     */
    public static function renderList($attachments, $atts) {
        ?>
        <div class="filebird-docs-list">
            <?php foreach ($attachments as $attachment): ?>
                <div class="filebird-docs-list-item">
                    <div class="filebird-docs-list-content">
                        <?php if ($atts['show_thumbnail'] && $attachment->thumbnail_url): ?>
                            <div class="filebird-docs-list-thumbnail">
                                <img src="<?php echo esc_url($attachment->thumbnail_url); ?>" 
                                     alt="<?php echo esc_attr($attachment->post_title); ?>"
                                     class="filebird-docs-thumbnail">
                            </div>
                        <?php endif; ?>
                        
                        <div class="filebird-docs-list-details">
                            <?php if ($atts['show_title']): ?>
                                <h4 class="filebird-docs-list-title">
                                    <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                       target="_blank" 
                                       class="filebird-docs-link">
                                        <?php echo esc_html($attachment->post_title); ?>
                                    </a>
                                </h4>
                            <?php endif; ?>
                            
                            <div class="filebird-docs-list-meta">
                                <?php if ($atts['show_size'] && $attachment->file_size): ?>
                                    <span class="filebird-docs-size">
                                        <i class="filebird-docs-icon filebird-docs-icon-size"></i>
                                        <?php echo esc_html($attachment->file_size); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($atts['show_date']): ?>
                                    <span class="filebird-docs-date">
                                        <i class="filebird-docs-icon filebird-docs-icon-date"></i>
                                        <?php echo esc_html(get_the_date('', $attachment->ID)); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="filebird-docs-list-actions">
                            <a href="<?php echo esc_url($attachment->file_url); ?>" 
                               target="_blank" 
                               class="filebird-docs-download-btn">
                                <?php _e('Download', 'filebird-frontend-docs'); ?>
                            </a>
                            <?php echo self::getEditButton($attachment->ID, $attachment->post_title); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Render document table
     */
    public static function renderTable($attachments, $atts) {
        ?>
        <div class="filebird-docs-table-wrapper">
            <table class="filebird-docs-table">
                <thead>
                    <tr>
                        <?php if ($atts['show_thumbnail']): ?>
                            <th class="filebird-docs-table-thumbnail"><?php _e('Preview', 'filebird-frontend-docs'); ?></th>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_title']): ?>
                            <th class="filebird-docs-table-title"><?php _e('Title', 'filebird-frontend-docs'); ?></th>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_size']): ?>
                            <th class="filebird-docs-table-size"><?php _e('Size', 'filebird-frontend-docs'); ?></th>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_date']): ?>
                            <th class="filebird-docs-table-date"><?php _e('Date', 'filebird-frontend-docs'); ?></th>
                        <?php endif; ?>
                        
                        <th class="filebird-docs-table-actions"><?php _e('Actions', 'filebird-frontend-docs'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attachments as $attachment): ?>
                        <tr class="filebird-docs-table-row">
                            <?php if ($atts['show_thumbnail']): ?>
                                <td class="filebird-docs-table-thumbnail">
                                    <?php if ($attachment->thumbnail_url): ?>
                                        <img src="<?php echo esc_url($attachment->thumbnail_url); ?>" 
                                             alt="<?php echo esc_attr($attachment->post_title); ?>"
                                             class="filebird-docs-thumbnail">
                                    <?php else: ?>
                                        <span class="filebird-docs-no-thumbnail">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            
                            <?php if ($atts['show_title']): ?>
                                <td class="filebird-docs-table-title">
                                    <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                       target="_blank" 
                                       class="filebird-docs-link">
                                        <?php echo esc_html($attachment->post_title); ?>
                                    </a>
                                </td>
                            <?php endif; ?>
                            
                            <?php if ($atts['show_size']): ?>
                                <td class="filebird-docs-table-size">
                                    <?php echo esc_html($attachment->file_size); ?>
                                </td>
                            <?php endif; ?>
                            
                            <?php if ($atts['show_date']): ?>
                                <td class="filebird-docs-table-date">
                                    <?php echo esc_html(get_the_date('', $attachment->ID)); ?>
                                </td>
                            <?php endif; ?>
                            
                            <td class="filebird-docs-table-actions">
                                <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                   target="_blank" 
                                   class="filebird-docs-download-btn">
                                    <?php _e('Download', 'filebird-frontend-docs'); ?>
                                </a>
                                <?php echo self::getEditButton($attachment->ID, $attachment->post_title); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Get file type icon class
     */
    public static function getFileTypeIcon($file_type) {
        $icon_map = array(
            'application/pdf' => 'filebird-docs-icon-pdf',
            'application/msword' => 'filebird-docs-icon-doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'filebird-docs-icon-docx',
            'application/vnd.ms-excel' => 'filebird-docs-icon-xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'filebird-docs-icon-xlsx',
            'application/vnd.ms-powerpoint' => 'filebird-docs-icon-ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'filebird-docs-icon-pptx',
            'text/plain' => 'filebird-docs-icon-txt',
            'image/jpeg' => 'filebird-docs-icon-image',
            'image/png' => 'filebird-docs-icon-image',
            'image/gif' => 'filebird-docs-icon-image',
            'image/webp' => 'filebird-docs-icon-image'
        );
        
        return isset($icon_map[$file_type]) ? $icon_map[$file_type] : 'filebird-docs-icon-file';
    }
    
    /**
     * AJAX handler for replacing documents
     */
    public function ajaxReplaceDocument() {
        $logger = FileBird_FD_Logger::getInstance();
        
        // Check nonce for security
        if (!wp_verify_nonce($_POST['filebird_fd_nonce'], 'filebird_fd_edit_nonce')) {
            $logger->error('Security check failed for document replacement', array('user_id' => get_current_user_id()));
            wp_send_json_error('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            $logger->error('Insufficient permissions for document replacement', array('user_id' => get_current_user_id()));
            wp_send_json_error('Insufficient permissions');
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            $logger->error('No file uploaded or upload error', array(
                'user_id' => get_current_user_id(),
                'files' => isset($_FILES) ? array_keys($_FILES) : array(),
                'error' => isset($_FILES['document_file']) ? $_FILES['document_file']['error'] : 'no_file',
                'post_data' => array_keys($_POST)
            ));
            wp_send_json_error('No file uploaded or upload error');
        }
        
        $file = $_FILES['document_file'];
        $attachment_id = intval($_POST['attachment_id']);
        $new_title = sanitize_text_field($_POST['document_title']);
        
        // Validate attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            $logger->error('Invalid attachment', array('attachment_id' => $attachment_id));
            wp_send_json_error('Invalid attachment');
        }
        
        // Validate file type (allow common document types)
        $allowed_types = array(
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
        );
        
        $file_type = wp_check_filetype($file['name']);
        
        if (!in_array($file_type['type'], $allowed_types)) {
            $logger->error('File type not allowed', array('file_type' => $file_type['type']));
            wp_send_json_error('File type not allowed');
        }
        
        // Validate file size (10MB limit)
        if ($file['size'] > 10 * 1024 * 1024) {
            $logger->error('File size too large', array('file_size' => $file['size']));
            wp_send_json_error('File size must be less than 10MB');
        }
        
        // Upload the new file
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            $logger->error('Upload failed', array('error' => $upload['error']));
            wp_send_json_error('Upload failed: ' . $upload['error']);
        }
        
        // Get the old file path for deletion
        $old_file_path = get_attached_file($attachment_id);
        
        // Update attachment with new file
        $attachment_data = array(
            'ID' => $attachment_id,
            'post_title' => $new_title ?: basename($upload['file']),
            'post_mime_type' => $upload['type']
        );
        
        $update_result = wp_update_post($attachment_data);
        
        if (is_wp_error($update_result)) {
            $logger->error('Failed to update attachment', array('error' => $update_result->get_error_message()));
            wp_send_json_error('Failed to update attachment');
        }
        
        // Update attachment metadata
        update_attached_file($attachment_id, $upload['file']);
        $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_metadata);
        
        // Delete the old file
        if ($old_file_path && file_exists($old_file_path)) {
            unlink($old_file_path);
        }
        
        // Get updated attachment info for potential future dynamic updates
        $updated_attachment = get_post($attachment_id);
        $thumbnail_url = wp_get_attachment_image_src($attachment_id, 'thumbnail');
        $medium_url = wp_get_attachment_image_src($attachment_id, 'medium');
        
        wp_send_json_success(array(
            'message' => 'Document replaced successfully',
            'attachment_id' => $attachment_id,
            'new_title' => $new_title,
            'thumbnail_url' => $thumbnail_url ? $thumbnail_url[0] : '',
            'medium_url' => $medium_url ? $medium_url[0] : '',
            'file_url' => wp_get_attachment_url($attachment_id)
        ));
    }
    
    /**
     * AJAX handler for renaming documents
     */
    public function ajaxRenameDocument() {
        $logger = FileBird_FD_Logger::getInstance();
        
        // Check nonce for security
        if (!wp_verify_nonce($_POST['filebird_fd_nonce'], 'filebird_fd_edit_nonce')) {
            $logger->error('Security check failed for document rename', array('user_id' => get_current_user_id()));
            wp_send_json_error('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts')) {
            $logger->error('Insufficient permissions for document rename', array('user_id' => get_current_user_id()));
            wp_send_json_error('Insufficient permissions');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $new_title = sanitize_text_field($_POST['document_title']);
        
        if (empty($new_title)) {
            $logger->error('Title cannot be empty');
            wp_send_json_error('Title cannot be empty');
        }
        
        // Validate attachment exists
        $attachment = get_post($attachment_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            $logger->error('Invalid attachment for rename', array('attachment_id' => $attachment_id));
            wp_send_json_error('Invalid attachment');
        }
        
        // Update attachment title
        $update_result = wp_update_post(array(
            'ID' => $attachment_id,
            'post_title' => $new_title
        ));
        
        if (is_wp_error($update_result)) {
            $logger->error('Failed to update document title', array('error' => $update_result->get_error_message()));
            wp_send_json_error('Failed to update document title');
        }
        
        wp_send_json_success('Document renamed successfully');
    }
    
    /**
     * Check if user can edit documents
     */
    public static function canEditDocuments() {
        return current_user_can('edit_posts');
    }
    
    /**
     * Get edit button HTML for a document
     */
    public static function getEditButton($attachment_id, $document_title) {
        if (!self::canEditDocuments()) {
            return '';
        }
        
        $nonce = wp_create_nonce('filebird_fd_edit_nonce');
        
        return sprintf(
            '<button type="button" class="filebird-docs-edit-btn" data-attachment-id="%d" data-document-title="%s" data-nonce="%s" title="%s">
                <i class="filebird-docs-icon filebird-docs-icon-edit"></i>
                <span class="filebird-docs-edit-text">%s</span>
            </button>',
            esc_attr($attachment_id),
            esc_attr($document_title),
            esc_attr($nonce),
            esc_attr__('Edit Document', 'filebird-frontend-docs'),
            esc_html__('Edit', 'filebird-frontend-docs')
        );
    }
} 