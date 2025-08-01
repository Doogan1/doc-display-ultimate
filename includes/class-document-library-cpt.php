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
        add_action('wp_ajax_filebird_fd_scan_library_usage', array($this, 'ajaxScanLibraryUsage'));
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
        
        add_meta_box(
            'document_library_usage',
            __('Library Usage', 'filebird-frontend-docs'),
            array($this, 'renderUsageMetaBox'),
            'document_library',
            'side',
            'default'
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
        $exclude_folders = get_post_meta($post->ID, '_document_library_exclude_folders', true);
        $custom_class = get_post_meta($post->ID, '_document_library_custom_class', true);
        $accordion_states = get_post_meta($post->ID, '_document_library_accordion_states', true) ?: '{}';
        
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
                            <input type="hidden" id="document_library_accordion_states" name="document_library_accordion_states" value="<?php echo esc_attr($accordion_states); ?>" />
                            
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
                        <input type="checkbox" id="document_library_include_subfolders" name="document_library_include_subfolders" value="true" <?php checked($include_subfolders); ?> />
                        <?php _e('Include Subfolders', 'filebird-frontend-docs'); ?>
                    </label><br>
                    
                    <label>
                        <input type="checkbox" name="document_library_group_by_folder" value="true" <?php checked($group_by_folder); ?> />
                        <?php _e('Group by Folder', 'filebird-frontend-docs'); ?>
                    </label><br>
                    

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
                    var folderIds = existingFolders.split(',').filter(function(id) { return id.trim() !== ''; });
                    if (folderIds.length > 0) {
                        // Wait for the folder tree to load, then expand and select folders
                        var initializeFolders = function() {
                            var allFoldersFound = true;
                            var selectedFolderNames = [];
                            var highestLevelFolderId = null;
                            var highestLevel = -1;
                            
                            // Check if all folders exist and find the highest level folder
                            folderIds.forEach(function(folderId) {
                                var $folderItem = $('.filebird-fd-folder-item[data-folder-id="' + folderId + '"]');
                                if ($folderItem.length > 0) {
                                    // Get folder level (count parent folders)
                                    var level = $folderItem.parents('.filebird-fd-folder-item').length;
                                    if (level > highestLevel) {
                                        highestLevel = level;
                                        highestLevelFolderId = folderId;
                                    }
                                    
                                    // Collect folder names for display
                                    var folderName = $folderItem.data('folder-name');
                                    if (folderName) {
                                        selectedFolderNames.push(folderName);
                                    }
                                } else {
                                    allFoldersFound = false;
                                }
                            });
                            
                            if (allFoldersFound) {
                                // Expand all parent folders to show selected folders
                                folderIds.forEach(function(folderId) {
                                    var $folderItem = $('.filebird-fd-folder-item[data-folder-id="' + folderId + '"]');
                                    if ($folderItem.length > 0) {
                                        // Expand all parent folders
                                        $folderItem.parents('.filebird-fd-folder-item').each(function() {
                                            var $parent = $(this);
                                            var $toggle = $parent.find('.filebird-fd-folder-toggle');
                                            var $children = $parent.find('> .filebird-fd-folder-children');
                                            
                                            if ($children.length > 0 && !$children.is(':visible')) {
                                                $toggle.removeClass('collapsed').addClass('expanded');
                                                $children.show();
                                            }
                                        });
                                        
                                        // Mark folder as selected
                                        $folderItem.addClass('selected');
                                    }
                                });
                                
                                // Update the selected folder display with all folder names
                                if (selectedFolderNames.length > 0) {
                                    var displayHtml = '';
                                    selectedFolderNames.forEach(function(name, index) {
                                        if (index > 0) displayHtml += ', ';
                                        displayHtml += '<span class="selected-folder">' + name + '</span>';
                                    });
                                    $('#selected-folder-display').html(displayHtml);
                                }
                                
                                // Update the admin's selected folder state (use the highest level folder as primary)
                                if (highestLevelFolderId) {
                                    var $primaryFolder = $('.filebird-fd-folder-item[data-folder-id="' + highestLevelFolderId + '"]');
                                    var primaryFolderName = $primaryFolder.data('folder-name');
                                    
                                    FileBirdFDAdmin.Admin.selectedFolderId = highestLevelFolderId;
                                    FileBirdFDAdmin.Admin.selectedFolderName = primaryFolderName;
                                    
                                    // Trigger the folder selection logic to populate subfolders
                                    // This simulates what happens when a user clicks on a folder
                                    if (typeof FileBirdFDAdmin.Admin.populateSubfolders === 'function') {
                                        FileBirdFDAdmin.Admin.populateSubfolders(highestLevelFolderId);
                                    }
                                    
                                    // Show accordion state controls if group-by-folder is enabled
                                    if ($('#group-by-folder').is(':checked')) {
                                        $('#accordion-state-controls').show();
                                    }
                                    
                                    // Update the shortcode
                                    if (typeof FileBirdFDAdmin.Admin.updateShortcode === 'function') {
                                        FileBirdFDAdmin.Admin.updateShortcode();
                                    }
                                }
                            } else {
                                // Try again in a moment
                                setTimeout(initializeFolders, 100);
                            }
                        };
                        
                        // Start the initialization process
                        setTimeout(initializeFolders, 500);
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
     * Render the usage meta box
     */
    public function renderUsageMetaBox($post) {
        $usage = get_post_meta($post->ID, '_document_library_usage', true);
        $last_scan = get_post_meta($post->ID, '_document_library_usage_last_scan', true);
        
        ?>
        <div class="document-library-usage">
            <p><?php _e('This library is currently used on the following pages:', 'filebird-frontend-docs'); ?></p>
            
            <div id="usage-list">
                <?php if (!empty($usage)): ?>
                    <ul class="usage-list">
                        <?php foreach ($usage as $item): ?>
                            <li>
                                <span class="usage-type"><?php echo esc_html(ucfirst($item['type'])); ?></span>
                                <a href="<?php echo esc_url($item['edit_url']); ?>" target="_blank" class="usage-title">
                                    <?php echo esc_html($item['title']); ?>
                                </a>
                                <a href="<?php echo esc_url($item['url']); ?>" target="_blank" class="usage-view">
                                    <?php _e('View', 'filebird-frontend-docs'); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($last_scan): ?>
                        <p class="usage-last-scan">
                            <small><?php printf(__('Last scanned: %s', 'filebird-frontend-docs'), date('M j, Y g:i A', strtotime($last_scan))); ?></small>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="no-usage"><?php _e('No usage found. This library may not be used anywhere yet.', 'filebird-frontend-docs'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="usage-actions">
                <button type="button" id="scan-usage" class="button button-small">
                    <?php _e('Scan for Usage', 'filebird-frontend-docs'); ?>
                </button>
                <span id="scan-status" style="display: none;">
                    <span class="spinner is-active"></span>
                    <?php _e('Scanning...', 'filebird-frontend-docs'); ?>
                </span>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#scan-usage').on('click', function() {
                var $button = $(this);
                var $status = $('#scan-status');
                var $usageList = $('#usage-list');
                
                $button.prop('disabled', true);
                $status.show();
                
                $.ajax({
                    url: filebird_fd_order.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'filebird_fd_scan_library_usage',
                        library_id: <?php echo $post->ID; ?>,
                        nonce: filebird_fd_order.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            var usage = response.data;
                            var html = '';
                            
                            if (usage.length > 0) {
                                html = '<ul class="usage-list">';
                                usage.forEach(function(item) {
                                    html += '<li>';
                                    html += '<span class="usage-type">' + item.type.charAt(0).toUpperCase() + item.type.slice(1) + '</span>';
                                    html += '<a href="' + item.edit_url + '" target="_blank" class="usage-title">' + item.title + '</a>';
                                    html += '<a href="' + item.url + '" target="_blank" class="usage-view">View</a>';
                                    html += '</li>';
                                });
                                html += '</ul>';
                                html += '<p class="usage-last-scan"><small>Last scanned: ' + new Date().toLocaleString() + '</small></p>';
                            } else {
                                html = '<p class="no-usage">No usage found. This library may not be used anywhere yet.</p>';
                            }
                            
                            $usageList.html(html);
                        } else {
                            alert('Error scanning for usage: ' + (response.data || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error scanning usage:', error);
                        alert('Error scanning for usage. Please try again.');
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                        $status.hide();
                    }
                });
            });
        });
        </script>
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
            'document_library_exclude_folders',
            'document_library_accordion_states',
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
        $exclude_folders = get_post_meta($post_id, '_document_library_exclude_folders', true);
        $custom_class = get_post_meta($post_id, '_document_library_custom_class', true);
        $accordion_states = get_post_meta($post_id, '_document_library_accordion_states', true) ?: '{}';
        
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
            'exclude_folders' => $exclude_folders,
            'accordion_states' => $accordion_states,
            'class' => $custom_class
        );
        
        // Build shortcode string
        $shortcode_parts = array();
        foreach ($shortcode_atts as $key => $value) {
            if ($value !== '' && $value !== null) {
                // Base64 encode JSON values to avoid shortcode parsing issues
                if ($key === 'accordion_states') {
                    $shortcode_parts[] = $key . '="' . base64_encode($value) . '"';
                } else {
                    $shortcode_parts[] = $key . '="' . esc_attr($value) . '"';
                }
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
        

    }
    
    /**
     * AJAX handler for scanning library usage
     */
    public function ajaxScanLibraryUsage() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_order_nonce') || 
            !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'filebird-frontend-docs'));
        }
        
        $library_id = intval($_POST['library_id']);
        
        if (!$library_id) {
            wp_send_json_error(__('Invalid library ID.', 'filebird-frontend-docs'));
        }
        
        // Scan for usage
        $usage = $this->scanLibraryUsage($library_id);
        
        // Update the meta fields
        update_post_meta($library_id, '_document_library_usage', $usage);
        update_post_meta($library_id, '_document_library_usage_last_scan', current_time('mysql'));
        
        wp_send_json_success($usage);
    }
    
    /**
     * Scan for library usage across posts and pages
     */
    private function scanLibraryUsage($library_id) {
        $usage = array();
        $shortcode = '[render_document_library id="' . $library_id . '"]';
        
        // Search in posts
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_thumbnail_id',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        foreach ($posts as $post) {
            if (strpos($post->post_content, $shortcode) !== false) {
                $usage[] = array(
                    'type' => 'post',
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'url' => get_permalink($post->ID),
                    'edit_url' => get_edit_post_link($post->ID)
                );
            }
        }
        
        // Search in pages
        $pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ));
        
        foreach ($pages as $page) {
            if (strpos($page->post_content, $shortcode) !== false) {
                $usage[] = array(
                    'type' => 'page',
                    'id' => $page->ID,
                    'title' => $page->post_title,
                    'url' => get_permalink($page->ID),
                    'edit_url' => get_edit_post_link($page->ID)
                );
            }
        }
        
        // Search in custom post types (excluding document_library itself)
        $custom_post_types = get_post_types(array('public' => true, '_builtin' => false));
        unset($custom_post_types['document_library']);
        
        foreach ($custom_post_types as $post_type) {
            $custom_posts = get_posts(array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                'posts_per_page' => -1
            ));
            
            foreach ($custom_posts as $custom_post) {
                if (strpos($custom_post->post_content, $shortcode) !== false) {
                    $usage[] = array(
                        'type' => $post_type,
                        'id' => $custom_post->ID,
                        'title' => $custom_post->post_title,
                        'url' => get_permalink($custom_post->ID),
                        'edit_url' => get_edit_post_link($custom_post->ID)
                    );
                }
            }
        }
        
        return $usage;
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
            
            // Parse exclude folders if provided
            if (!empty($_POST['exclude_folders'])) {
                $exclude_folders = array_map('intval', explode(',', sanitize_text_field($_POST['exclude_folders'])));
            }
            
            if (empty($folder_ids_input)) {
                wp_send_json_error(__('No folders specified.', 'filebird-frontend-docs'));
            }
            
            // Parse folder IDs
            $folder_ids = array_map('intval', explode(',', $folder_ids_input));
            $folder_ids = array_filter($folder_ids); // Remove empty values
            
            if (empty($folder_ids)) {
                wp_send_json_error(__('Invalid folder IDs provided.', 'filebird-frontend-docs'));
            }
            
            // Get documents from all specified folders
            $all_documents = array();
            $folder_info = array();
            
            foreach ($folder_ids as $folder_id) {
                if (!FileBird_FD_Helper::folderExists($folder_id)) {
                    continue; // Skip non-existent folders
                }
                
                // Get folder name for parent folder
                $parent_folder_object = FileBird_FD_Helper::getFolderById($folder_id);
                $parent_folder_name = ($parent_folder_object && isset($parent_folder_object->name)) ? $parent_folder_object->name : 'Unknown Folder (ID: ' . $folder_id . ')';
                
                // Get all subfolder IDs recursively
                $all_subfolder_ids = FileBird_FD_Helper::getSubfolderIds($folder_id);
                
                // Filter out excluded subfolders
                $included_subfolder_ids = $all_subfolder_ids;
                if (!empty($exclude_folders)) {
                    // First, get all subfolders of excluded folders
                    $excluded_subfolders = array();
                    foreach ($exclude_folders as $excluded_folder_id) {
                        $excluded_subfolders = array_merge($excluded_subfolders, FileBird_FD_Helper::getSubfolderIds($excluded_folder_id));
                    }
                    
                    // Combine original excluded folders with their subfolders
                    $all_excluded_folders = array_merge($exclude_folders, $excluded_subfolders);
                    
                    // Filter out all excluded folders and their subfolders
                    $included_subfolder_ids = array_diff($all_subfolder_ids, $all_excluded_folders);
                }
                
                // Create list of all folders to process (parent + included subfolders)
                $folders_to_process = array($folder_id);
                if ($include_subfolders) {
                    $folders_to_process = array_merge($folders_to_process, $included_subfolder_ids);
                }
                
                // Get documents from each folder individually
                foreach ($folders_to_process as $current_folder_id) {
                    // Get documents from this specific folder (not recursively)
                    $documents = FileBird_FD_Helper::getAttachmentsByFolderId($current_folder_id, array(
                        'orderby' => 'menu_order',
                        'order' => 'ASC',
                        'limit' => -1,
                        'include_metadata' => true
                    ));
                    
                    // Get folder name for this specific folder
                    $current_folder_object = FileBird_FD_Helper::getFolderById($current_folder_id);
                    $current_folder_name = ($current_folder_object && isset($current_folder_object->name)) ? $current_folder_object->name : 'Unknown Folder (ID: ' . $current_folder_id . ')';
                    
                    // Add folder information to each document
                    foreach ($documents as $doc) {
                        $doc->source_folder_id = $current_folder_id;
                        $doc->source_folder_name = $current_folder_name;
                        $all_documents[] = $doc;
                    }
                    
                    // Store folder info for display
                    $folder_info[$current_folder_id] = array(
                        'id' => $current_folder_id,
                        'name' => $current_folder_name,
                        'count' => count($documents)
                    );
                }
            }
            
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
            
            wp_send_json_success($response_data);
            
        } catch (Exception $e) {
            error_log('FileBird FD Error - Document Library CPT ajaxGetDocumentsForOrdering exception: ' . $e->getMessage());
            error_log('FileBird FD Error - Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Server error: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('FileBird FD Error - Document Library CPT ajaxGetDocumentsForOrdering fatal error: ' . $e->getMessage());
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
     * Update document order across multiple folders
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