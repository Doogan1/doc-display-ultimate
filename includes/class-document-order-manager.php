<?php
/**
 * Document Order Manager Class
 * 
 * Handles document ordering functionality with drag-and-drop interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FileBird_FD_Document_Order_Manager {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'addOrderManagerMenu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueOrderManagerScripts'));
        add_action('wp_ajax_filebird_fd_get_documents_for_ordering', array($this, 'ajaxGetDocumentsForOrdering'));
        add_action('wp_ajax_filebird_fd_update_document_order', array($this, 'ajaxUpdateDocumentOrder'));
        add_action('wp_ajax_filebird_fd_get_folder_documents', array($this, 'ajaxGetFolderDocuments'));
        add_action('wp_ajax_filebird_fd_get_folders_order_manager', array($this, 'ajaxGetFoldersOrderManager'));
    }
    
    /**
     * Add order manager menu
     */
    public function addOrderManagerMenu() {
        add_submenu_page(
            'upload.php', // Parent slug (Media menu)
            __('Document Order Manager', 'filebird-frontend-docs'),
            __('Document Order', 'filebird-frontend-docs'),
            'manage_options',
            'filebird-document-order',
            array($this, 'orderManagerPage')
        );
    }
    
    /**
     * Enqueue scripts and styles for order manager
     */
    public function enqueueOrderManagerScripts($hook) {
        if ($hook !== 'media_page_filebird-document-order') {
            return;
        }
        
        wp_enqueue_style('dashicons');
        wp_enqueue_style('wp-jquery-ui-dialog');
        
        wp_enqueue_style(
            'filebird-frontend-docs-order-manager',
            FB_FD_PLUGIN_URL . 'assets/css/order-manager.css',
            array(),
            FB_FD_VERSION
        );
        
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-dialog');
        
        wp_enqueue_script(
            'filebird-frontend-docs-order-manager',
            FB_FD_PLUGIN_URL . 'assets/js/order-manager.js',
            array('jquery', 'jquery-ui-sortable', 'jquery-ui-dialog'),
            FB_FD_VERSION,
            true
        );
        
        wp_localize_script(
            'filebird-frontend-docs-order-manager',
            'filebird_fd_order',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('filebird_fd_order_nonce'),
                'strings' => array(
                    'saving' => __('Saving order...', 'filebird-frontend-docs'),
                    'saved' => __('Order saved successfully!', 'filebird-frontend-docs'),
                    'error' => __('Error saving order. Please try again.', 'filebird-frontend-docs'),
                    'confirm_reset' => __('Are you sure you want to reset the order? This cannot be undone.', 'filebird-frontend-docs')
                )
            )
        );
        
        // Also localize the admin script data for folder loading
        wp_localize_script(
            'filebird-frontend-docs-order-manager',
            'filebird_fd_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('filebird_fd_admin_nonce')
            )
        );
    }
    
    /**
     * AJAX handler for getting documents for ordering
     */
    public function ajaxGetDocumentsForOrdering() {
        try {
            // Verify nonce and permissions
            if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_order_nonce') || 
                !current_user_can('manage_options')) {
                wp_die(__('Security check failed.', 'filebird-frontend-docs'));
            }
            
            // Get folder IDs - can be single folder or comma-separated list
            $folder_ids_input = sanitize_text_field($_POST['folder_ids']);
            $include_subfolders = filter_var($_POST['include_subfolders'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $exclude_folders = array();
            
            // Debug logging
            error_log('FileBird FD Debug - ajaxGetDocumentsForOrdering called');
            error_log('FileBird FD Debug - folder_ids_input: ' . $folder_ids_input);
            error_log('FileBird FD Debug - include_subfolders: ' . ($include_subfolders ? 'true' : 'false'));
            
            // Parse exclude folders if provided
            if (!empty($_POST['exclude_folders'])) {
                $exclude_folders = array_map('intval', explode(',', sanitize_text_field($_POST['exclude_folders'])));
            }
            error_log('FileBird FD Debug - exclude_folders: ' . print_r($exclude_folders, true));
            
            if (empty($folder_ids_input)) {
                error_log('FileBird FD Debug - No folders specified');
                wp_send_json_error(__('No folders specified.', 'filebird-frontend-docs'));
            }
            
            // Parse folder IDs
            $folder_ids = array_map('intval', explode(',', $folder_ids_input));
            $folder_ids = array_filter($folder_ids); // Remove empty values
            error_log('FileBird FD Debug - parsed folder_ids: ' . print_r($folder_ids, true));
            
            if (empty($folder_ids)) {
                error_log('FileBird FD Debug - No valid folder IDs found');
                wp_send_json_error(__('Invalid folder IDs provided.', 'filebird-frontend-docs'));
            }
            
            // Get documents from all specified folders
            $all_documents = array();
            $folder_info = array();
            
            foreach ($folder_ids as $folder_id) {
                error_log('FileBird FD Debug - Processing folder ID: ' . $folder_id);
                
                if (!FileBird_FD_Helper::folderExists($folder_id)) {
                    error_log('FileBird FD Debug - Folder does not exist: ' . $folder_id);
                    continue; // Skip non-existent folders
                }
                
                // Get documents from this folder (and subfolders if enabled)
                $args = array(
                    'orderby' => 'menu_order',
                    'order' => 'ASC',
                    'limit' => -1,
                    'include_subfolders' => $include_subfolders,
                    'exclude_folders' => $exclude_folders
                );
                error_log('FileBird FD Debug - Getting documents for folder ' . $folder_id . ' with args: ' . print_r($args, true));
                
                $documents = FileBird_FD_Helper::getAttachmentsByFolderIdRecursive($folder_id, $args);
                error_log('FileBird FD Debug - Found ' . count($documents) . ' documents in folder ' . $folder_id);
                
                // Add folder information to each document
                foreach ($documents as $doc) {
                    $doc->source_folder_id = $folder_id;
                    
                    // Debug folder lookup
                    $folder_object = FileBird_FD_Helper::getFolderById($folder_id);
                    error_log('FileBird FD Debug - getFolderById(' . $folder_id . ') returned: ' . print_r($folder_object, true));
                    
                    if ($folder_object && isset($folder_object->name)) {
                        $doc->source_folder_name = $folder_object->name;
                        error_log('FileBird FD Debug - Using folder name: ' . $folder_object->name);
                    } else {
                        $doc->source_folder_name = 'Unknown Folder (ID: ' . $folder_id . ')';
                        error_log('FileBird FD Debug - Folder object is null or missing name property');
                    }
                    
                    $all_documents[] = $doc;
                }
                
                // Store folder info for display
                $folder_object = FileBird_FD_Helper::getFolderById($folder_id);
                $folder_name = ($folder_object && isset($folder_object->name)) ? $folder_object->name : 'Unknown Folder (ID: ' . $folder_id . ')';
                
                $folder_info[$folder_id] = array(
                    'id' => $folder_id,
                    'name' => $folder_name,
                    'count' => count($documents)
                );
            }
            
            error_log('FileBird FD Debug - Total documents collected: ' . count($all_documents));
            error_log('FileBird FD Debug - Folder info: ' . print_r($folder_info, true));
            
            // Sort all documents by menu_order across all folders
            usort($all_documents, function($a, $b) {
                return $a->menu_order - $b->menu_order;
            });
            
            // Format documents for frontend
            $formatted_documents = array();
            foreach ($all_documents as $doc) {
                $formatted_documents[] = array(
                    'id' => $doc->ID,
                    'title' => $doc->post_title,
                    'filename' => basename($doc->file_path),
                    'file_type' => $doc->file_type,
                    'file_size' => $doc->file_size,
                    'menu_order' => $doc->menu_order,
                    'thumbnail' => $doc->thumbnail_url,
                    'url' => $doc->file_url,
                    'source_folder_id' => $doc->source_folder_id,
                    'source_folder_name' => $doc->source_folder_name
                );
            }
            
            $response_data = array(
                'documents' => $formatted_documents,
                'folder_info' => $folder_info,
                'total_folders' => count($folder_info),
                'total_documents' => count($formatted_documents)
            );
            
            error_log('FileBird FD Debug - Final response data: ' . print_r($response_data, true));
            
            wp_send_json_success($response_data);
            
        } catch (Exception $e) {
            error_log('FileBird FD Error - ajaxGetDocumentsForOrdering exception: ' . $e->getMessage());
            error_log('FileBird FD Error - Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Server error: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('FileBird FD Error - ajaxGetDocumentsForOrdering fatal error: ' . $e->getMessage());
            error_log('FileBird FD Error - Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Server error: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX handler for updating document order
     */
    public function ajaxUpdateDocumentOrder() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_order_nonce') || 
            !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'filebird-frontend-docs'));
        }
        
        $document_order = $_POST['document_order'];
        
        if (!is_array($document_order)) {
            wp_send_json_error(__('Invalid data provided.', 'filebird-frontend-docs'));
        }
        
        // Update the order across all folders
        $result = $this->updateDocumentOrderMultiFolder($document_order);
        
        if ($result) {
            wp_send_json_success(__('Order updated successfully across all folders.', 'filebird-frontend-docs'));
        } else {
            wp_send_json_error(__('Error updating order.', 'filebird-frontend-docs'));
        }
    }
    
    /**
     * AJAX handler for getting folders in order manager
     */
    public function ajaxGetFoldersOrderManager() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_admin_nonce') || 
            !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'filebird-frontend-docs'));
        }
        
        $folders_tree = FileBird_FD_Helper::getFolderTree();
        
        wp_send_json_success($folders_tree);
    }
    
    /**
     * AJAX handler for getting folder documents
     */
    public function ajaxGetFolderDocuments() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_order_nonce') || 
            !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'filebird-frontend-docs'));
        }
        
        $folder_id = intval($_POST['folder_id']);
        
        if (!$folder_id) {
            wp_send_json_error(__('Invalid folder ID.', 'filebird-frontend-docs'));
        }
        
        // Get documents for the folder
        $documents = FileBird_FD_Helper::getAttachmentsByFolderId($folder_id, array(
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'limit' => -1
        ));
        
        $formatted_documents = array();
        foreach ($documents as $doc) {
            $formatted_documents[] = array(
                'id' => $doc->ID,
                'title' => $doc->post_title,
                'filename' => basename($doc->file_path),
                'file_type' => $doc->file_type,
                'file_size' => $doc->file_size,
                'menu_order' => $doc->menu_order,
                'thumbnail' => $doc->thumbnail_url
            );
        }
        
        wp_send_json_success($formatted_documents);
    }
    
    /**
     * Get documents with their current order
     */
    private function getDocumentsWithOrder($folder_id) {
        $documents = FileBird_FD_Helper::getAttachmentsByFolderId($folder_id, array(
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'limit' => -1
        ));
        
        $formatted_documents = array();
        foreach ($documents as $doc) {
            $formatted_documents[] = array(
                'id' => $doc->ID,
                'title' => $doc->post_title,
                'filename' => basename($doc->file_path),
                'file_type' => $doc->file_type,
                'file_size' => $doc->file_size,
                'menu_order' => $doc->menu_order,
                'thumbnail' => $doc->thumbnail_url,
                'url' => $doc->file_url
            );
        }
        
        return $formatted_documents;
    }
    
    /**
     * Update document order in database
     */
    private function updateDocumentOrder($folder_id, $document_order) {
        global $wpdb;
        
        try {
            // Start transaction
            $wpdb->query('START TRANSACTION');
            
            foreach ($document_order as $index => $document_id) {
                $document_id = intval($document_id);
                $menu_order = intval($index) + 1;
                
                $result = $wpdb->update(
                    $wpdb->posts,
                    array('menu_order' => $menu_order),
                    array('ID' => $document_id, 'post_type' => 'attachment'),
                    array('%d'),
                    array('%d', '%s')
                );
                
                if ($result === false) {
                    throw new Exception('Failed to update document order');
                }
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Clear any caches
            clean_post_cache($folder_id);
            
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            error_log('FileBird FD: Error updating document order - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update document order in database for multiple folders
     */
    private function updateDocumentOrderMultiFolder($document_order) {
        global $wpdb;
        
        try {
            // Start transaction
            $wpdb->query('START TRANSACTION');
            
            // Update menu_order for all documents in the combined order
            foreach ($document_order as $index => $document_id) {
                $document_id = intval($document_id);
                $menu_order = intval($index) + 1;
                
                $result = $wpdb->update(
                    $wpdb->posts,
                    array('menu_order' => $menu_order),
                    array('ID' => $document_id, 'post_type' => 'attachment'),
                    array('%d'),
                    array('%d', '%s')
                );
                
                if ($result === false) {
                    throw new Exception('Failed to update document order for document ID: ' . $document_id);
                }
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            error_log('FileBird FD: Error updating document order across multiple folders - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Order manager page content
     */
    public function orderManagerPage() {
        ?>
        <div class="wrap">
            <h1><?php _e('Document Order Manager', 'filebird-frontend-docs'); ?></h1>
            
            <?php if (!FileBird_FD_Helper::isFileBirdAvailable()): ?>
                <div class="notice notice-error">
                    <p><?php _e('FileBird plugin is not active. Please install and activate FileBird first.', 'filebird-frontend-docs'); ?></p>
                </div>
            <?php else: ?>
                
                <div class="filebird-fd-order-manager-container">
                    <div class="filebird-fd-order-manager-section">
                        <h2><?php _e('Select Folder to Reorder Documents', 'filebird-frontend-docs'); ?></h2>
                        <p><?php _e('Choose a folder to reorder the documents within it. Drag and drop documents to change their order.', 'filebird-frontend-docs'); ?></p>
                        
                        <div class="filebird-fd-folder-selector">
                            <div class="filebird-fd-folder-tree-container">
                                <div class="filebird-fd-folder-tree-header">
                                    <input type="text" id="folder-search" placeholder="<?php _e('Search folders...', 'filebird-frontend-docs'); ?>" class="regular-text">
                                    <button type="button" id="expand-all-folders" class="button button-small">
                                        <?php _e('Expand All', 'filebird-frontend-docs'); ?>
                                    </button>
                                    <button type="button" id="collapse-all-folders" class="button button-small">
                                        <?php _e('Collapse All', 'filebird-frontend-docs'); ?>
                                    </button>
                                </div>
                                <div id="folder-tree" class="filebird-fd-folder-tree">
                                    <div class="filebird-fd-loading">
                                        <span class="spinner is-active"></span>
                                        <?php _e('Loading folders...', 'filebird-frontend-docs'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="filebird-fd-selected-folder">
                                <label><?php _e('Selected Folder:', 'filebird-frontend-docs'); ?></label>
                                <div id="selected-folder-display">
                                    <span class="no-folder-selected"><?php _e('No folder selected', 'filebird-frontend-docs'); ?></span>
                                </div>
                                <input type="hidden" id="selected-folder-id" value="">
                            </div>
                        </div>
                    </div>
                    
                    <div class="filebird-fd-order-manager-section" id="document-order-section" style="display: none;">
                        <h2><?php _e('Document Order', 'filebird-frontend-docs'); ?></h2>
                        <p><?php _e('Drag and drop documents to reorder them. The order will be saved automatically.', 'filebird-frontend-docs'); ?></p>
                        
                        <div class="filebird-fd-order-controls">
                            <button type="button" id="save-order" class="button button-primary">
                                <?php _e('Save Order', 'filebird-frontend-docs'); ?>
                            </button>
                            <button type="button" id="reset-order" class="button">
                                <?php _e('Reset to Default', 'filebird-frontend-docs'); ?>
                            </button>
                            <button type="button" id="preview-order" class="button">
                                <?php _e('Preview Order', 'filebird-frontend-docs'); ?>
                            </button>
                        </div>
                        
                        <div id="document-list" class="filebird-fd-document-list">
                            <!-- Documents will be loaded here -->
                        </div>
                        
                        <div id="order-status" class="filebird-fd-order-status" style="display: none;">
                            <span class="spinner is-active"></span>
                            <span class="status-text"></span>
                        </div>
                    </div>
                </div>
                
            <?php endif; ?>
        </div>
        <?php
    }
} 