<?php
/**
 * Logger Class
 * 
 * Handles debugging and error logging for the plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FileBird_FD_Logger {
    
    private static $instance = null;
    private $log_file;
    private $debug_mode;
    
    public function __construct() {
        $this->debug_mode = defined('WP_DEBUG') && WP_DEBUG;
        $this->log_file = WP_CONTENT_DIR . '/debug-filebird-frontend-docs.log';
    }
    
    /**
     * Initialize admin hooks (called after WordPress is fully loaded)
     */
    public function initAdminHooks() {
        // Add admin menu for log viewer
        add_action('admin_menu', array($this, 'addLogViewerMenu'));
        
        // Add AJAX handler for clearing logs
        add_action('wp_ajax_filebird_fd_clear_logs', array($this, 'ajaxClearLogs'));
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log a message
     */
    public function log($message, $level = 'INFO', $context = array()) {
        if (!$this->debug_mode) {
            return;
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $level = strtoupper($level);
        
        $log_entry = sprintf(
            "[%s] [%s] %s",
            $timestamp,
            $level,
            $this->formatMessage($message, $context)
        );
        
        // Write to log file
        $this->writeToFile($log_entry);
        
        // Also log to WordPress debug log if available
        if (function_exists('error_log')) {
            error_log("FileBird FD: " . $log_entry);
        }
    }
    
    /**
     * Log info message
     */
    public function info($message, $context = array()) {
        $this->log($message, 'INFO', $context);
    }
    
    /**
     * Log warning message
     */
    public function warning($message, $context = array()) {
        $this->log($message, 'WARNING', $context);
    }
    
    /**
     * Log error message
     */
    public function error($message, $context = array()) {
        $this->log($message, 'ERROR', $context);
    }
    
    /**
     * Log debug message
     */
    public function debug($message, $context = array()) {
        $this->log($message, 'DEBUG', $context);
    }
    
    /**
     * Log exception
     */
    public function exception($exception, $context = array()) {
        $message = sprintf(
            "Exception: %s in %s:%d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        $this->error($message, $context);
    }
    
    /**
     * Format message with context
     */
    private function formatMessage($message, $context = array()) {
        if (empty($context)) {
            return $message;
        }
        
        $context_str = '';
        foreach ($context as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $context_str .= " {$key}=" . json_encode($value);
            } else {
                $context_str .= " {$key}=" . $value;
            }
        }
        
        return $message . $context_str;
    }
    
    /**
     * Write to log file
     */
    private function writeToFile($log_entry) {
        $log_entry .= "\n";
        
        try {
            // Create log directory if it doesn't exist
            $log_dir = dirname($this->log_file);
            if (!is_dir($log_dir)) {
                wp_mkdir_p($log_dir);
            }
            
            // Write to file
            file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Silently fail to prevent any output
            return;
        }
    }
    
    /**
     * Get log contents
     */
    public function getLogContents($lines = 100) {
        if (!file_exists($this->log_file)) {
            return array();
        }
        
        $contents = file($this->log_file, FILE_IGNORE_NEW_LINES);
        if ($contents === false) {
            return array();
        }
        
        // Return last N lines
        return array_slice($contents, -$lines);
    }
    
    /**
     * Clear log file
     */
    public function clearLog() {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
    }
    
    /**
     * Get log file size
     */
    public function getLogFileSize() {
        if (!file_exists($this->log_file)) {
            return 0;
        }
        
        return filesize($this->log_file);
    }
    
    /**
     * Add log viewer menu
     */
    public function addLogViewerMenu() {
        // Add main menu page
        add_menu_page(
            __('FileBird FD', 'doc-display-ultimate'),
            __('FileBird FD', 'doc-display-ultimate'),
            'manage_options',
            'filebird-fd',
            array($this, 'mainAdminPage'),
            'dashicons-media-document',
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'filebird-fd',
            __('Debug Logs', 'doc-display-ultimate'),
            __('Debug Logs', 'doc-display-ultimate'),
            'manage_options',
            'filebird-fd-logs',
            array($this, 'logViewerPage')
        );
        
        add_submenu_page(
            'filebird-fd',
            __('Test Shortcode', 'doc-display-ultimate'),
            __('Test Shortcode', 'doc-display-ultimate'),
            'manage_options',
            'filebird-fd-test',
            array($this, 'testPage')
        );
    }
    
    /**
     * Main admin page
     */
    public function mainAdminPage() {
        ?>
        <div class="wrap">
            <h1><?php _e('FileBird Frontend Documents', 'doc-display-ultimate'); ?></h1>
            
            <div class="filebird-fd-admin-overview">
                <div class="filebird-fd-card">
                    <h2><?php _e('Plugin Status', 'doc-display-ultimate'); ?></h2>
                    <p><?php _e('FileBird Frontend Documents is active and ready to use.', 'doc-display-ultimate'); ?></p>
                    
                    <h3><?php _e('Quick Links', 'doc-display-ultimate'); ?></h3>
                    <ul>
                        <li><a href="<?php echo admin_url('admin.php?page=filebird-fd-logs'); ?>"><?php _e('View Debug Logs', 'doc-display-ultimate'); ?></a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=filebird-fd-test'); ?>"><?php _e('Test Shortcode', 'doc-display-ultimate'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Test page
     */
    public function testPage() {
        ?>
        <div class="wrap">
            <h1><?php _e('Test Shortcode', 'doc-display-ultimate'); ?></h1>
            
            <div class="filebird-fd-test-container">
                <h2><?php _e('Test the FileBird Frontend Documents Shortcode', 'doc-display-ultimate'); ?></h2>
                
                <form method="post" action="">
                    <?php wp_nonce_field('filebird_fd_test', 'filebird_fd_test_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Folder ID', 'doc-display-ultimate'); ?></th>
                            <td>
                                <input type="number" name="test_folder_id" value="1" min="1" />
                                <p class="description"><?php _e('Enter a FileBird folder ID to test', 'doc-display-ultimate'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Layout', 'doc-display-ultimate'); ?></th>
                            <td>
                                <select name="test_layout">
                                    <option value="grid"><?php _e('Grid', 'doc-display-ultimate'); ?></option>
                                    <option value="list"><?php _e('List', 'doc-display-ultimate'); ?></option>
                                    <option value="table"><?php _e('Table', 'doc-display-ultimate'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Include Subfolders', 'doc-display-ultimate'); ?></th>
                            <td>
                                <input type="checkbox" name="test_include_subfolders" value="1" />
                                <p class="description"><?php _e('Include documents from subfolders', 'doc-display-ultimate'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Group by Folder', 'doc-display-ultimate'); ?></th>
                            <td>
                                <input type="checkbox" name="test_group_by_folder" value="1" />
                                <p class="description"><?php _e('Group documents by folder structure', 'doc-display-ultimate'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="test_shortcode" class="button button-primary" value="<?php _e('Test Shortcode', 'doc-display-ultimate'); ?>" />
                    </p>
                </form>
                
                <?php if (isset($_POST['test_shortcode']) && wp_verify_nonce($_POST['filebird_fd_test_nonce'], 'filebird_fd_test')): ?>
                    <div class="filebird-fd-test-results">
                        <h3><?php _e('Test Results', 'doc-display-ultimate'); ?></h3>
                        
                        <?php
                        $folder_id = intval($_POST['test_folder_id']);
                        $layout = sanitize_text_field($_POST['test_layout']);
                        $include_subfolders = isset($_POST['test_include_subfolders']) ? 'true' : 'false';
                        $group_by_folder = isset($_POST['test_group_by_folder']) ? 'true' : 'false';
                        
                        $shortcode = sprintf(
                            '[filebird_docs folder="%d" layout="%s" include_subfolders="%s" group_by_folder="%s"]',
                            $folder_id,
                            $layout,
                            $include_subfolders,
                            $group_by_folder
                        );
                        ?>
                        
                        <div class="filebird-fd-shortcode-preview">
                            <h4><?php _e('Generated Shortcode:', 'doc-display-ultimate'); ?></h4>
                            <code><?php echo esc_html($shortcode); ?></code>
                        </div>
                        
                        <div class="filebird-fd-shortcode-output">
                            <h4><?php _e('Output:', 'doc-display-ultimate'); ?></h4>
                            <div class="filebird-fd-output-container">
                                <?php echo do_shortcode($shortcode); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .filebird-fd-test-container {
            max-width: 800px;
        }
        .filebird-fd-test-results {
            margin-top: 30px;
            padding: 20px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .filebird-fd-shortcode-preview {
            margin-bottom: 20px;
        }
        .filebird-fd-shortcode-preview code {
            display: block;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 3px;
            font-family: monospace;
        }
        .filebird-fd-output-container {
            border: 1px solid #ddd;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 3px;
        }
        </style>
        <?php
    }
    
    /**
     * Log viewer page
     */
    public function logViewerPage() {
        ?>
        <div class="wrap">
            <h1><?php _e('FileBird Frontend Documents - Debug Logs', 'doc-display-ultimate'); ?></h1>
            
            <div class="filebird-fd-log-controls">
                <button type="button" id="refresh-logs" class="button button-primary">
                    <?php _e('Refresh Logs', 'doc-display-ultimate'); ?>
                </button>
                <button type="button" id="clear-logs" class="button button-secondary">
                    <?php _e('Clear Logs', 'doc-display-ultimate'); ?>
                </button>
                <span class="filebird-fd-log-info">
                    <?php 
                    $size = $this->getLogFileSize();
                    printf(__('Log file size: %s', 'doc-display-ultimate'), size_format($size));
                    ?>
                </span>
            </div>
            
            <div class="filebird-fd-log-container">
                <pre id="log-content" class="filebird-fd-log-content"><?php 
                    $logs = $this->getLogContents(200);
                    echo esc_html(implode("\n", $logs));
                ?></pre>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#refresh-logs').on('click', function() {
                location.reload();
            });
            
            $('#clear-logs').on('click', function() {
                if (confirm('<?php _e('Are you sure you want to clear all logs?', 'doc-display-ultimate'); ?>')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'filebird_fd_clear_logs',
                            nonce: '<?php echo wp_create_nonce('filebird_fd_logs_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('<?php _e('Failed to clear logs.', 'doc-display-ultimate'); ?>');
                            }
                        }
                    });
                }
            });
        });
        </script>
        
        <style>
        .filebird-fd-log-controls {
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .filebird-fd-log-info {
            margin-left: 15px;
            color: #666;
        }
        
        .filebird-fd-log-container {
            background: #1e1e1e;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .filebird-fd-log-content {
            color: #f8f8f2;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 600px;
            overflow-y: auto;
        }
        </style>
        <?php
    }
    
    /**
     * AJAX handler for clearing logs
     */
    public function ajaxClearLogs() {
        if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_logs_nonce') || 
            !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'doc-display-ultimate'));
        }
        
        $this->clearLog();
        wp_send_json_success();
    }
} 