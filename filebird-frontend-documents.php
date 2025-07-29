<?php
/**
 * Plugin Name: FileBird Frontend Documents
 * Description: Display FileBird documents on the frontend via shortcode or block.
 * Version: 0.1.0
 * Author: Your Name
 * Text Domain: filebird-frontend-docs
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FB_FD_VERSION', '0.1.0');
define('FB_FD_PLUGIN_FILE', __FILE__);
define('FB_FD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FB_FD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FB_FD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class FileBirdFrontendDocuments {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        // Check if FileBird is active
        if (!$this->isFileBirdActive()) {
            add_action('admin_notices', array($this, 'filebirdMissingNotice'));
            return;
        }
        
        // Load dependencies
        $this->loadDependencies();
        
        // Initialize components
        $this->initComponents();
        
        // Register hooks
        $this->registerHooks();
    }
    
    /**
     * Check if FileBird plugin is active
     */
    private function isFileBirdActive() {
        return class_exists('FileBird\Plugin');
    }
    
    /**
     * Display notice if FileBird is not active
     */
    public function filebirdMissingNotice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('FileBird Frontend Documents requires FileBird plugin to be installed and activated.', 'filebird-frontend-docs'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Load plugin dependencies
     */
    private function loadDependencies() {
        // Load classes
        require_once FB_FD_PLUGIN_PATH . 'includes/class-shortcode-handler.php';
        require_once FB_FD_PLUGIN_PATH . 'includes/class-document-display.php';
        require_once FB_FD_PLUGIN_PATH . 'includes/class-filebird-helper.php';
        require_once FB_FD_PLUGIN_PATH . 'includes/class-admin.php';
        require_once FB_FD_PLUGIN_PATH . 'includes/class-document-library-cpt.php';
    }
    
    /**
     * Initialize plugin components
     */
    private function initComponents() {
        // Initialize shortcode handler
        new FileBird_FD_Shortcode_Handler();
        
        // Initialize document display
        new FileBird_FD_Document_Display();
        
        // Initialize admin functionality
        new FileBird_FD_Admin();
        
        // Initialize document library custom post type
        new FileBird_FD_Document_Library_CPT();
    }
    
    /**
     * Register WordPress hooks
     */
    private function registerHooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_action('init', array($this, 'loadTextDomain'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueueScripts() {
        wp_enqueue_style(
            'filebird-frontend-docs',
            FB_FD_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            FB_FD_VERSION
        );
        
        wp_enqueue_style(
            'filebird-frontend-docs-editor',
            FB_FD_PLUGIN_URL . 'assets/css/editor.css',
            array(),
            FB_FD_VERSION
        );
        
        wp_enqueue_script(
            'filebird-frontend-docs',
            FB_FD_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            FB_FD_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script(
            'filebird-frontend-docs',
            'filebird_fd_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('filebird_fd_nonce')
            )
        );
    }
    
    /**
     * Load plugin text domain
     */
    public function loadTextDomain() {
        load_plugin_textdomain(
            'filebird-frontend-docs',
            false,
            dirname(FB_FD_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Plugin activation hook
     */
    public static function activate() {
        // Check if FileBird is active
        if (!class_exists('FileBird\Plugin')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('FileBird Frontend Documents requires FileBird plugin to be installed and activated.', 'filebird-frontend-docs'));
        }
        
        // Create necessary directories
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/filebird-frontend-docs';
        
        if (!file_exists($plugin_upload_dir)) {
            wp_mkdir_p($plugin_upload_dir);
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation hook
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function filebird_frontend_documents_init() {
    return FileBirdFrontendDocuments::getInstance();
}

// Hook into WordPress
add_action('plugins_loaded', 'filebird_frontend_documents_init');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('FileBirdFrontendDocuments', 'activate'));
register_deactivation_hook(__FILE__, array('FileBirdFrontendDocuments', 'deactivate')); 