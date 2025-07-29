<?php
/**
 * Document Library Custom Post Type Class
 * 
 * Handles the registration and management of the document_library custom post type
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FileBird_FD_Document_Library_CPT {
    
    public function __construct() {
        add_action('init', array($this, 'registerPostType'));
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_action('save_post', array($this, 'saveMetaBoxes'));
        add_action('init', array($this, 'registerShortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueEditorStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
        add_action('wp_ajax_filebird_fd_get_folders_order_manager', array($this, 'ajaxGetFoldersOrderManager'));
        add_action('wp_ajax_filebird_fd_get_documents_for_ordering', array($this, 'ajaxGetDocumentsForOrdering'));
        add_action('wp_ajax_filebird_fd_update_document_order', array($this, 'ajaxUpdateDocumentOrder'));
    }
    
    /**
     * Register the document_library custom post type
     */
    public function registerPostType() {
        $labels = array(
            'name'                  => _x('Document Libraries', 'Post type general name', 'filebird-frontend-docs'),
            'singular_name'         => _x('Document Library', 'Post type singular name', 'filebird-frontend-docs'),
            'menu_name'             => _x('Document Libraries', 'Admin Menu text', 'filebird-frontend-docs'),
            'name_admin_bar'        => _x('Document Library', 'Add New on Toolbar', 'filebird-frontend-docs'),
            'add_new'               => __('Add New', 'filebird-frontend-docs'),
            'add_new_item'          => __('Add New Document Library', 'filebird-frontend-docs'),
            'new_item'              => __('New Document Library', 'filebird-frontend-docs'),
            'edit_item'             => __('Edit Document Library', 'filebird-frontend-docs'),
            'view_item'             => __('View Document Library', 'filebird-frontend-docs'),
            'all_items'             => __('All Document Libraries', 'filebird-frontend-docs'),
            'search_items'          => __('Search Document Libraries', 'filebird-frontend-docs'),
            'parent_item_colon'     => __('Parent Document Libraries:', 'filebird-frontend-docs'),
            'not_found'             => __('No document libraries found.', 'filebird-frontend-docs'),
            'not_found_in_trash'    => __('No document libraries found in Trash.', 'filebird-frontend-docs'),
            'featured_image'        => _x('Document Library Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'filebird-frontend-docs'),
            'set_featured_image'    => _x('Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'filebird-frontend-docs'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the "Remove featured image" phrase for this post type.', 'filebird-frontend-docs'),
            'use_featured_image'    => _x('Use as cover image', 'Overrides the "Use as featured image" phrase for this post type.', 'filebird-frontend-docs'),
            'archives'              => _x('Document library archives', 'The post type archive label used in nav menus.', 'filebird-frontend-docs'),
            'insert_into_item'      => _x('Insert into document library', 'Overrides the "Insert into post" phrase.', 'filebird-frontend-docs'),
            'uploaded_to_this_item' => _x('Uploaded to this document library', 'Overrides the "Uploaded to this post" phrase.', 'filebird-frontend-docs'),
            'filter_items_list'     => _x('Filter document libraries list', 'Screen reader text for the filter links.', 'filebird-frontend-docs'),
            'items_list_navigation' => _x('Document libraries list navigation', 'Screen reader text for the pagination.', 'filebird-frontend-docs'),
            'items_list'            => _x('Document libraries list', 'Screen reader text for the items list.', 'filebird-frontend-docs'),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'document-library'),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-media-document',
            'supports'           => array('title'),
            'show_in_rest'       => false,
        );
        
        register_post_type('document_library', $args);
    }
    
    /**
     * Add meta boxes for the custom post type
     */
    public function addMetaBoxes() {
        add_meta_box(
            'document_library_settings',
            __('Document Library Settings', 'filebird-frontend-docs'),
            array($this, 'renderSettingsMetaBox'),
            'document_library',
            'normal',
            'high'
        );
        
        add_meta_box(
            'document_library_shortcode',
            __('Shortcode', 'filebird-frontend-docs'),
            array($this, 'renderShortcodeMetaBox'),
            'document_library',
            'side',
            'high'
        );
    }
    
    /**
     * Render the settings meta box
     */
    public function renderSettingsMetaBox($post) {
        // Add nonce for security
        wp_nonce_field('document_library_meta_box', 'document_library_meta_box_nonce');
        
        // Get saved values
        $folders = get_post_meta($post->ID, '_document_library_folders', true);
        $layout = get_post_meta($post->ID, '_document_library_layout', true) ?: 'grid';
        $columns = get_post_meta($post->ID, '_document_library_columns', true) ?: 3;
        $orderby = get_post_meta($post->ID, '_document_library_orderby', true) ?: 'date';
        $order = get_post_meta($post->ID, '_document_library_order', true) ?: 'DESC';
        $limit = get_post_meta($post->ID, '_document_library_limit', true) ?: -1;
        $show_title = get_post_meta($post->ID, '_document_library_show_title', true) !== 'false';
        $show_size = get_post_meta($post->ID, '_document_library_show_size', true) === 'true';
        $show_date = get_post_meta($post->ID, '_document_library_show_date', true) === 'true';
        $show_thumbnail = get_post_meta($post->ID, '_document_library_show_thumbnail', true) !== 'false';
        $include_subfolders = get_post_meta($post->ID, '_document_library_include_subfolders', true) === 'true';
        $group_by_folder = get_post_meta($post->ID, '_document_library_group_by_folder', true) === 'true';
        $accordion_default = get_post_meta($post->ID, '_document_library_accordion_default', true) ?: 'closed';
        $exclude_folders = get_post_meta($post->ID, '_document_library_exclude_folders', true);
        $custom_class = get_post_meta($post->ID, '_document_library_custom_class', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="folder-selector"><?php _e('Select Folders', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
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
                            <label><?php _e('Selected Folders:', 'filebird-frontend-docs'); ?></label>
                            <div id="selected-folder-display">
                                <span class="no-folder-selected"><?php _e('No folders selected', 'filebird-frontend-docs'); ?></span>
                            </div>
                            <input type="hidden" id="document_library_folders" name="document_library_folders" value="<?php echo esc_attr($folders); ?>" />
                            
                            <div id="subfolder-controls" style="display: none;">
                                <label><?php _e('Subfolder Selection:', 'filebird-frontend-docs'); ?></label>
                                <p class="description"><?php _e('Uncheck folders you want to exclude from the display. The selected parent folders are always included.', 'filebird-frontend-docs'); ?></p>
                                <div id="subfolder-list">
                                    <!-- Subfolders will be populated here -->
                                </div>
                                <div class="filebird-fd-subfolder-actions">
                                    <button type="button" id="check-all-subfolders" class="button button-small">
                                        <?php _e('Check All', 'filebird-frontend-docs'); ?>
                                    </button>
                                    <button type="button" id="uncheck-all-subfolders" class="button button-small">
                                        <?php _e('Uncheck All', 'filebird-frontend-docs'); ?>
                                    </button>
                                    <button type="button" id="expand-all-subfolders" class="button button-small">
                                        <?php _e('Expand All', 'filebird-frontend-docs'); ?>
                                    </button>
                                    <button type="button" id="collapse-all-subfolders" class="button button-small">
                                        <?php _e('Collapse All', 'filebird-frontend-docs'); ?>
                                    </button>
                                </div>
                            </div>
                            
                            <div id="accordion-state-controls" style="display: none;">
                                <label><?php _e('Accordion State Control:', 'filebird-frontend-docs'); ?></label>
                                <p class="description"><?php _e('Select which folders should be open or closed by default when grouping is enabled. This provides granular control over the accordion behavior.', 'filebird-frontend-docs'); ?></p>
                                <div id="accordion-state-list">
                                    <!-- Accordion state controls will be populated here -->
                                </div>
                                <div class="filebird-fd-accordion-state-actions">
                                    <button type="button" id="open-all-accordions" class="button button-small">
                                        <?php _e('Open All', 'filebird-frontend-docs'); ?>
                                    </button>
                                    <button type="button" id="close-all-accordions" class="button button-small">
                                        <?php _e('Close All', 'filebird-frontend-docs'); ?>
                                    </button>
                                    <button type="button" id="expand-all-accordion-states" class="button button-small">
                                        <?php _e('Expand All', 'filebird-frontend-docs'); ?>
                                    </button>
                                    <button type="button" id="collapse-all-accordion-states" class="button button-small">
                                        <?php _e('Collapse All', 'filebird-frontend-docs'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="description"><?php _e('Click on folders to select them for the document library. You can select multiple folders.', 'filebird-frontend-docs'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="document_library_layout"><?php _e('Layout', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
                    <select id="document_library_layout" name="document_library_layout">
                        <option value="grid" <?php selected($layout, 'grid'); ?>><?php _e('Grid', 'filebird-frontend-docs'); ?></option>
                        <option value="list" <?php selected($layout, 'list'); ?>><?php _e('List', 'filebird-frontend-docs'); ?></option>
                        <option value="table" <?php selected($layout, 'table'); ?>><?php _e('Table', 'filebird-frontend-docs'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="document_library_columns"><?php _e('Columns (Grid only)', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
                    <input type="number" id="document_library_columns" name="document_library_columns" value="<?php echo esc_attr($columns); ?>" min="1" max="6" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="document_library_orderby"><?php _e('Order By', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
                    <select id="document_library_orderby" name="document_library_orderby">
                        <option value="date" <?php selected($orderby, 'date'); ?>><?php _e('Date', 'filebird-frontend-docs'); ?></option>
                        <option value="title" <?php selected($orderby, 'title'); ?>><?php _e('Title', 'filebird-frontend-docs'); ?></option>
                        <option value="menu_order" <?php selected($orderby, 'menu_order'); ?>><?php _e('Menu Order', 'filebird-frontend-docs'); ?></option>
                        <option value="ID" <?php selected($orderby, 'ID'); ?>><?php _e('ID', 'filebird-frontend-docs'); ?></option>
                    </select>
                </td>
            </tr>
            
            <!-- Document Order Manager (shown when menu_order is selected) -->
            <tr id="document-order-manager-row" style="display: none;">
                <th scope="row">
                    <label><?php _e('Document Order', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
                    <div class="filebird-fd-document-order-manager">
                        <p><?php _e('Drag and drop documents to reorder them. This order will be used when "Menu Order" is selected.', 'filebird-frontend-docs'); ?></p>
                        
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
                            <div class="empty">
                                <?php _e('Select folders above to load documents for ordering.', 'filebird-frontend-docs'); ?>
                            </div>
                        </div>
                        
                        <div id="order-status" class="filebird-fd-order-status" style="display: none;">
                            <span class="spinner is-active"></span>
                            <span class="status-text"></span>
                        </div>
                    </div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="document_library_order"><?php _e('Order', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
                    <select id="document_library_order" name="document_library_order">
                        <option value="ASC" <?php selected($order, 'ASC'); ?>><?php _e('Ascending', 'filebird-frontend-docs'); ?></option>
                        <option value="DESC" <?php selected($order, 'DESC'); ?>><?php _e('Descending', 'filebird-frontend-docs'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="document_library_limit"><?php _e('Limit', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
                    <input type="number" id="document_library_limit" name="document_library_limit" value="<?php echo esc_attr($limit); ?>" min="-1" />
                    <p class="description"><?php _e('Use -1 for all documents', 'filebird-frontend-docs'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Display Options', 'filebird-frontend-docs'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="document_library_show_title" value="true" <?php checked($show_title); ?> />
                        <?php _e('Show Title', 'filebird-frontend-docs'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="document_library_show_size" value="true" <?php checked($show_size); ?> />
                        <?php _e('Show File Size', 'filebird-frontend-docs'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="document_library_show_date" value="true" <?php checked($show_date); ?> />
                        <?php _e('Show Date', 'filebird-frontend-docs'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="document_library_show_thumbnail" value="true" <?php checked($show_thumbnail); ?> />
                        <?php _e('Show Thumbnail', 'filebird-frontend-docs'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Subfolder Options', 'filebird-frontend-docs'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="document_library_include_subfolders" value="true" <?php checked($include_subfolders); ?> />
                        <?php _e('Include Subfolders', 'filebird-frontend-docs'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="document_library_group_by_folder" value="true" <?php checked($group_by_folder); ?> />
                        <?php _e('Group by Folder', 'filebird-frontend-docs'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="document_library_accordion_default" value="open" <?php checked($accordion_default, 'open'); ?> />
                        <?php _e('Folders Open by Default', 'filebird-frontend-docs'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="document_library_exclude_folders"><?php _e('Exclude Folders', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
                    <input type="text" id="document_library_exclude_folders" name="document_library_exclude_folders" value="<?php echo esc_attr($exclude_folders); ?>" class="regular-text" />
                    <p class="description"><?php _e('Comma-separated folder IDs to exclude', 'filebird-frontend-docs'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="document_library_custom_class"><?php _e('Custom CSS Class', 'filebird-frontend-docs'); ?></label>
                </th>
                <td>
                    <input type="text" id="document_library_custom_class" name="document_library_custom_class" value="<?php echo esc_attr($custom_class); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        
        <script>
        jQuery(document).ready(function($) {
            // Initialize the admin interface for document library
            if (typeof FileBirdFDAdmin !== 'undefined' && FileBirdFDAdmin.Admin) {
                // Override the updateShortcode function to update our hidden field instead
                var originalUpdateShortcode = FileBirdFDAdmin.Admin.updateShortcode;
                FileBirdFDAdmin.Admin.updateShortcode = function() {
                    // Call the original function
                    originalUpdateShortcode.call(this);
                    
                    // Also update our document library folders field
                    var selectedFolderId = this.selectedFolderId;
                    if (selectedFolderId) {
                        $('#document_library_folders').val(selectedFolderId);
                    }
                };
                
                // Override the selectFolder function to handle our specific needs
                var originalSelectFolder = FileBirdFDAdmin.Admin.selectFolder;
                FileBirdFDAdmin.Admin.selectFolder = function($folderItem) {
                    // Call the original function
                    originalSelectFolder.call(this, $folderItem);
                    
                    // Update our hidden field
                    var selectedFolderId = this.selectedFolderId;
                    if (selectedFolderId) {
                        $('#document_library_folders').val(selectedFolderId);
                    }
                    
                    // Load documents for ordering if menu_order is selected
                    if ($('#document_library_orderby').val() === 'menu_order') {
                        FileBirdFDOrder.OrderManager.loadDocumentsForLibrary();
                    }
                };
                
                // Initialize with existing selected folders
                var existingFolders = $('#document_library_folders').val();
                if (existingFolders) {
                    // Set the selected folder display
                    var folderIds = existingFolders.split(',').filter(function(id) { return id.trim() !== ''; });
                    if (folderIds.length > 0) {
                        // For now, we'll use the first folder as the primary selection
                        // In the future, we could enhance this to support multiple folder selection
                        var firstFolderId = folderIds[0];
                        
                        // Wait for the folder tree to load, then select the folder
                        var checkFolderExists = function() {
                            var $folderItem = $('.filebird-fd-folder-item[data-folder-id="' + firstFolderId + '"]');
                            if ($folderItem.length > 0) {
                                $folderItem.addClass('selected');
                                
                                // Update the selected folder display
                                var folderName = $folderItem.data('folder-name');
                                if (folderName) {
                                    $('#selected-folder-display').html('<span class="selected-folder">' + folderName + '</span>');
                                }
                                
                                // Update the admin's selected folder state
                                FileBirdFDAdmin.Admin.selectedFolderId = firstFolderId;
                                FileBirdFDAdmin.Admin.selectedFolderName = folderName;
                            } else {
                                // Try again in a moment
                                setTimeout(checkFolderExists, 100);
                            }
                        };
                        
                        // Start checking for the folder
                        setTimeout(checkFolderExists, 500);
                    }
                }
            }
            
            // Handle orderby change to show/hide document order manager
            $('#document_library_orderby').on('change', function() {
                var orderby = $(this).val();
                if (orderby === 'menu_order') {
                    $('#document-order-manager-row').show();
                    // Load documents if folders are selected
                    var selectedFolders = $('#document_library_folders').val();
                    if (selectedFolders && typeof FileBirdFDOrder !== 'undefined' && FileBirdFDOrder.OrderManager) {
                        FileBirdFDOrder.OrderManager.loadDocumentsForLibrary();
                    }
                } else {
                    $('#document-order-manager-row').hide();
                }
            });
            
            // Initialize order manager if menu_order is selected
            if ($('#document_library_orderby').val() === 'menu_order') {
                $('#document-order-manager-row').show();
                
                // Load documents if folders are already selected (for existing libraries)
                var selectedFolders = $('#document_library_folders').val();
                if (selectedFolders && typeof FileBirdFDOrder !== 'undefined' && FileBirdFDOrder.OrderManager) {
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
        </script>
        <?php
    }
    
    /**
     * Render the shortcode meta box
     */
    public function renderShortcodeMetaBox($post) {
        $shortcode = '[render_document_library id="' . $post->ID . '"]';
        ?>
        <p><?php _e('Use this shortcode to display this document library:', 'filebird-frontend-docs'); ?></p>
        <input type="text" value="<?php echo esc_attr($shortcode); ?>" readonly style="width: 100%; font-family: monospace;" onclick="this.select();" />
        <p class="description"><?php _e('Copy and paste this shortcode into any post or page.', 'filebird-frontend-docs'); ?></p>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function saveMetaBoxes($post_id) {
        // Security checks
        if (!isset($_POST['document_library_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['document_library_meta_box_nonce'], 'document_library_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save meta fields
        $fields = array(
            'document_library_folders',
            'document_library_layout',
            'document_library_columns',
            'document_library_orderby',
            'document_library_order',
            'document_library_limit',
            'document_library_show_title',
            'document_library_show_size',
            'document_library_show_date',
            'document_library_show_thumbnail',
            'document_library_include_subfolders',
            'document_library_group_by_folder',
            'document_library_accordion_default',
            'document_library_exclude_folders',
            'document_library_custom_class'
        );
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field($_POST[$field]);
                update_post_meta($post_id, '_' . $field, $value);
            } else {
                // Handle unchecked checkboxes
                if (in_array($field, array(
                    'document_library_show_title',
                    'document_library_show_size',
                    'document_library_show_date',
                    'document_library_show_thumbnail',
                    'document_library_include_subfolders',
                    'document_library_group_by_folder'
                ))) {
                    update_post_meta($post_id, '_' . $field, 'false');
                }
            }
        }
    }
    
    /**
     * Register the new shortcode
     */
    public function registerShortcode() {
        add_shortcode('render_document_library', array($this, 'renderDocumentLibrary'));
    }
    
    /**
     * Render the document library shortcode
     */
    public function renderDocumentLibrary($atts) {
        $atts = shortcode_atts(array(
            'id' => null,
        ), $atts, 'render_document_library');
        
        $post_id = intval($atts['id']);
        if (!$post_id || get_post_type($post_id) !== 'document_library') {
            return '<div class="doc-library-error">' . __('Invalid library ID.', 'filebird-frontend-docs') . '</div>';
        }
        
        // Get library settings
        $folders = get_post_meta($post_id, '_document_library_folders', true);
        $layout = get_post_meta($post_id, '_document_library_layout', true) ?: 'grid';
        $columns = get_post_meta($post_id, '_document_library_columns', true) ?: 3;
        $orderby = get_post_meta($post_id, '_document_library_orderby', true) ?: 'date';
        $order = get_post_meta($post_id, '_document_library_order', true) ?: 'DESC';
        $limit = get_post_meta($post_id, '_document_library_limit', true) ?: -1;
        $show_title = get_post_meta($post_id, '_document_library_show_title', true) !== 'false';
        $show_size = get_post_meta($post_id, '_document_library_show_size', true) === 'true';
        $show_date = get_post_meta($post_id, '_document_library_show_date', true) === 'true';
        $show_thumbnail = get_post_meta($post_id, '_document_library_show_thumbnail', true) !== 'false';
        $include_subfolders = get_post_meta($post_id, '_document_library_include_subfolders', true) === 'true';
        $group_by_folder = get_post_meta($post_id, '_document_library_group_by_folder', true) === 'true';
        $accordion_default = get_post_meta($post_id, '_document_library_accordion_default', true) ?: 'closed';
        $exclude_folders = get_post_meta($post_id, '_document_library_exclude_folders', true);
        $custom_class = get_post_meta($post_id, '_document_library_custom_class', true);
        
        // Convert to shortcode attributes
        $shortcode_atts = array(
            'folder' => $folders, // This can be comma-separated folder IDs
            'layout' => $layout,
            'columns' => $columns,
            'orderby' => $orderby,
            'order' => $order,
            'limit' => $limit,
            'show_title' => $show_title ? 'true' : 'false',
            'show_size' => $show_size ? 'true' : 'false',
            'show_date' => $show_date ? 'true' : 'false',
            'show_thumbnail' => $show_thumbnail ? 'true' : 'false',
            'include_subfolders' => $include_subfolders ? 'true' : 'false',
            'group_by_folder' => $group_by_folder ? 'true' : 'false',
            'accordion_default' => $accordion_default,
            'exclude_folders' => $exclude_folders,
            'class' => $custom_class
        );
        
        // Build shortcode string
        $shortcode_parts = array();
        foreach ($shortcode_atts as $key => $value) {
            if ($value !== '' && $value !== null) {
                $shortcode_parts[] = $key . '="' . esc_attr($value) . '"';
            }
        }
        
        $shortcode = '[filebird_docs ' . implode(' ', $shortcode_parts) . ']';
        
        // Start building the output with a cohesive container
        $output = '';
        
        // Check if user can edit this library
        $can_edit = current_user_can('edit_post', $post_id);
        
        if ($can_edit) {
            $edit_url = get_edit_post_link($post_id);
            $output .= '<div class="doc-library-container doc-library-editable">';
            $output .= '<div class="doc-library-header">';
            $output .= '<a href="' . esc_url($edit_url) . '" class="doc-library-edit-button">' . __('Edit Library', 'filebird-frontend-docs') . '</a>';
            $output .= '</div>';
            $output .= '<div class="doc-library-content">';
        } else {
            $output .= '<div class="doc-library-container">';
            $output .= '<div class="doc-library-content">';
        }
        
        // Render the shortcode
        $output .= do_shortcode($shortcode);
        
        // Close the content and container divs
        $output .= '</div>'; // .doc-library-content
        $output .= '</div>'; // .doc-library-container
        
        return $output;
    }
    
    /**
     * Enqueue admin scripts and styles for document library post type
     */
    public function enqueueAdminScripts($hook) {
        global $post_type;
        
        // Only load on document_library post type pages
        if ($post_type !== 'document_library') {
            return;
        }
        
        // Debug: Log that we're loading scripts for document_library
        error_log('FileBird FD: Loading admin scripts for document_library post type');
        
        // Enqueue WordPress dashicons for folder tree icons
        wp_enqueue_style('dashicons');
        wp_enqueue_style('wp-jquery-ui-dialog');
        
        wp_enqueue_style(
            'filebird-frontend-docs-admin',
            FB_FD_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FB_FD_VERSION
        );
        
        wp_enqueue_style(
            'filebird-frontend-docs-order-manager',
            FB_FD_PLUGIN_URL . 'assets/css/order-manager.css',
            array(),
            FB_FD_VERSION
        );
        
        wp_enqueue_script(
            'filebird-frontend-docs-admin',
            FB_FD_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            FB_FD_VERSION,
            true
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
            'filebird-frontend-docs-admin',
            'filebird_fd_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('filebird_fd_admin_nonce')
            )
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
        
        // Debug: Log that scripts have been enqueued
        error_log('FileBird FD: Admin scripts enqueued for document_library');
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
     * AJAX handler for getting documents for ordering
     */
    public function ajaxGetDocumentsForOrdering() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_order_nonce') || 
            !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'filebird-frontend-docs'));
        }
        
        $folder_id = intval($_POST['folder_id']);
        
        if (!$folder_id) {
            wp_send_json_error(__('Invalid folder ID.', 'filebird-frontend-docs'));
        }
        
        // Get documents with current order
        $documents = $this->getDocumentsWithOrder($folder_id);
        
        wp_send_json_success($documents);
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
        
        $folder_id = intval($_POST['folder_id']);
        $document_order = $_POST['document_order'];
        
        if (!$folder_id || !is_array($document_order)) {
            wp_send_json_error(__('Invalid data provided.', 'filebird-frontend-docs'));
        }
        
        // Update the order
        $result = $this->updateDocumentOrder($folder_id, $document_order);
        
        if ($result) {
            wp_send_json_success(__('Order updated successfully.', 'filebird-frontend-docs'));
        } else {
            wp_send_json_error(__('Error updating order.', 'filebird-frontend-docs'));
        }
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
     * Enqueue styles for editor buttons
     */
    public function enqueueEditorStyles() {
        wp_enqueue_style(
            'filebird-frontend-docs-editor',
            FB_FD_PLUGIN_URL . 'assets/css/editor.css',
            array(),
            FB_FD_VERSION
        );
    }
} 