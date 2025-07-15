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
} 