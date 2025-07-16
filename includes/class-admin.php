<?php
/**
 * Admin Class
 * 
 * Handles admin functionality and settings page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FileBird_FD_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
        add_action('wp_ajax_filebird_fd_get_folders_admin', array($this, 'ajaxGetFoldersAdmin'));
    }
    
    /**
     * Add admin menu
     */
    public function addAdminMenu() {
        add_submenu_page(
            'upload.php', // Parent slug (Media menu)
            __('FileBird Frontend Documents', 'filebird-frontend-docs'),
            __('Frontend Documents', 'filebird-frontend-docs'),
            'manage_options',
            'filebird-frontend-docs',
            array($this, 'adminPage')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueueAdminScripts($hook) {
        if ($hook !== 'media_page_filebird-frontend-docs') {
            return;
        }
        
        // Enqueue WordPress dashicons for folder tree icons
        wp_enqueue_style('dashicons');
        
        wp_enqueue_style(
            'filebird-frontend-docs-admin',
            FB_FD_PLUGIN_URL . 'assets/css/admin.css',
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
        
        wp_localize_script(
            'filebird-frontend-docs-admin',
            'filebird_fd_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('filebird_fd_admin_nonce')
            )
        );
    }
    
    /**
     * AJAX handler for getting folders in admin
     */
    public function ajaxGetFoldersAdmin() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_admin_nonce') || 
            !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'filebird-frontend-docs'));
        }
        
        $folders_tree = FileBird_FD_Helper::getFolderTree();
        
        wp_send_json_success($folders_tree);
    }
    
    /**
     * Admin page content
     */
    public function adminPage() {
        ?>
        <div class="wrap">
            <h1><?php _e('FileBird Frontend Documents', 'filebird-frontend-docs'); ?></h1>
            
            <?php if (!FileBird_FD_Helper::isFileBirdAvailable()): ?>
                <div class="notice notice-error">
                    <p><?php _e('FileBird plugin is not active. Please install and activate FileBird first.', 'filebird-frontend-docs'); ?></p>
                </div>
            <?php else: ?>
                
                <div class="filebird-fd-admin-container">
                    <div class="filebird-fd-admin-section">
                        <h2><?php _e('Shortcode Generator', 'filebird-frontend-docs'); ?></h2>
                        <p><?php _e('Generate shortcodes to display your FileBird documents on the frontend.', 'filebird-frontend-docs'); ?></p>
                        
                        <div class="filebird-fd-shortcode-generator">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="folder-selector"><?php _e('Select Folder', 'filebird-frontend-docs'); ?></label>
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
                                                <label><?php _e('Selected Folder:', 'filebird-frontend-docs'); ?></label>
                                                <div id="selected-folder-display">
                                                    <span class="no-folder-selected"><?php _e('No folder selected', 'filebird-frontend-docs'); ?></span>
                                                </div>
                                                <input type="hidden" id="selected-folder-id" value="">
                                                
                                                <div id="subfolder-controls" style="display: none;">
                                                    <label><?php _e('Subfolder Selection:', 'filebird-frontend-docs'); ?></label>
                                                    <p class="description"><?php _e('Uncheck folders you want to exclude from the display. The selected parent folder is always included.', 'filebird-frontend-docs'); ?></p>
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
                                            </div>
                                        </div>
                                        <p class="description"><?php _e('Click on a folder to select it for the shortcode.', 'filebird-frontend-docs'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="layout-select"><?php _e('Layout', 'filebird-frontend-docs'); ?></label>
                                    </th>
                                    <td>
                                        <select id="layout-select" class="regular-text">
                                            <option value="grid"><?php _e('Grid', 'filebird-frontend-docs'); ?></option>
                                            <option value="list"><?php _e('List', 'filebird-frontend-docs'); ?></option>
                                            <option value="table"><?php _e('Table', 'filebird-frontend-docs'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="columns-input"><?php _e('Columns (Grid only)', 'filebird-frontend-docs'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="columns-input" class="small-text" value="3" min="1" max="6">
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row"><?php _e('Display Options', 'filebird-frontend-docs'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label>
                                                <input type="checkbox" id="show-title" checked>
                                                <?php _e('Show title', 'filebird-frontend-docs'); ?>
                                            </label><br>
                                            
                                            <label>
                                                <input type="checkbox" id="show-size">
                                                <?php _e('Show file size', 'filebird-frontend-docs'); ?>
                                            </label><br>
                                            
                                            <label>
                                                <input type="checkbox" id="show-date">
                                                <?php _e('Show date', 'filebird-frontend-docs'); ?>
                                            </label><br>
                                            
                                            <label>
                                                <input type="checkbox" id="show-thumbnail" checked>
                                                <?php _e('Show thumbnail', 'filebird-frontend-docs'); ?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="orderby-select"><?php _e('Order by', 'filebird-frontend-docs'); ?></label>
                                    </th>
                                    <td>
                                        <select id="orderby-select" class="regular-text">
                                            <option value="date"><?php _e('Date', 'filebird-frontend-docs'); ?></option>
                                            <option value="title"><?php _e('Title', 'filebird-frontend-docs'); ?></option>
                                            <option value="menu_order"><?php _e('Menu Order', 'filebird-frontend-docs'); ?></option>
                                            <option value="ID"><?php _e('ID', 'filebird-frontend-docs'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="order-select"><?php _e('Order', 'filebird-frontend-docs'); ?></label>
                                    </th>
                                    <td>
                                        <select id="order-select" class="regular-text">
                                            <option value="DESC"><?php _e('Descending', 'filebird-frontend-docs'); ?></option>
                                            <option value="ASC"><?php _e('Ascending', 'filebird-frontend-docs'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="limit-input"><?php _e('Limit', 'filebird-frontend-docs'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="limit-input" class="small-text" value="-1" min="-1">
                                        <p class="description"><?php _e('Number of documents to display (-1 for all)', 'filebird-frontend-docs'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row"><?php _e('Subfolder Options', 'filebird-frontend-docs'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label>
                                                <input type="checkbox" id="include-subfolders">
                                                <?php _e('Include documents from subfolders', 'filebird-frontend-docs'); ?>
                                            </label>
                                            <p class="description"><?php _e('When enabled, documents from all subfolders will be included in the display.', 'filebird-frontend-docs'); ?></p>
                                            
                                            <br>
                                            
                                            <label>
                                                <input type="checkbox" id="group-by-folder">
                                                <?php _e('Group documents by folder structure', 'filebird-frontend-docs'); ?>
                                            </label>
                                            <p class="description"><?php _e('When enabled, documents will be organized by folder with folder names as section headings.', 'filebird-frontend-docs'); ?></p>
                                            
                                            <br>
                                            
                                            <label for="accordion-default"><?php _e('Accordion Default State', 'filebird-frontend-docs'); ?></label>
                                            <select id="accordion-default" class="regular-text">
                                                <option value="closed"><?php _e('Closed (folders collapsed)', 'filebird-frontend-docs'); ?></option>
                                                <option value="open"><?php _e('Open (folders expanded)', 'filebird-frontend-docs'); ?></option>
                                            </select>
                                            <p class="description"><?php _e('Default state for accordion folders when grouping is enabled.', 'filebird-frontend-docs'); ?></p>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                            
                            <div class="filebird-fd-shortcode-output">
                                <h3><?php _e('Generated Shortcode', 'filebird-frontend-docs'); ?></h3>
                                <div class="filebird-fd-shortcode-display">
                                    <code id="shortcode-output">[filebird_docs folder=""]</code>
                                    <button type="button" id="copy-shortcode" class="button button-secondary">
                                        <?php _e('Copy', 'filebird-frontend-docs'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filebird-fd-admin-section">
                        <h2><?php _e('Usage Examples', 'filebird-frontend-docs'); ?></h2>
                        <div class="filebird-fd-examples">
                            <h3><?php _e('Basic Usage', 'filebird-frontend-docs'); ?></h3>
                            <code>[filebird_docs folder="123"]</code>
                            
                            <h3><?php _e('Grid Layout with 4 Columns', 'filebird-frontend-docs'); ?></h3>
                            <code>[filebird_docs folder="123" layout="grid" columns="4"]</code>
                            
                            <h3><?php _e('List Layout with File Information', 'filebird-frontend-docs'); ?></h3>
                            <code>[filebird_docs folder="123" layout="list" show_size="true" show_date="true"]</code>
                            
                            <h3><?php _e('Table Layout with All Details', 'filebird-frontend-docs'); ?></h3>
                            <code>[filebird_docs folder="123" layout="table" show_size="true" show_date="true" show_thumbnail="true"]</code>
                            
                            <h3><?php _e('Include Subfolders', 'filebird-frontend-docs'); ?></h3>
                            <code>[filebird_docs folder="123" include_subfolders="true"]</code>
                            
                            <h3><?php _e('Group by Folder Structure', 'filebird-frontend-docs'); ?></h3>
                            <code>[filebird_docs folder="123" include_subfolders="true" group_by_folder="true"]</code>
                            
                            <h3><?php _e('Accordion Folders (Default Closed)', 'filebird-frontend-docs'); ?></h3>
                            <code>[filebird_docs folder="123" include_subfolders="true" group_by_folder="true" accordion_default="closed"]</code>
                            
                            <h3><?php _e('Accordion Folders (Default Open)', 'filebird-frontend-docs'); ?></h3>
                            <code>[filebird_docs folder="123" include_subfolders="true" group_by_folder="true" accordion_default="open"]</code>
                        </div>
                    </div>
                </div>
                
            <?php endif; ?>
        </div>
        <?php
    }
} 