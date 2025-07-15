<?php
/**
 * Debug Page for FileBird Frontend Documents
 * 
 * This file can be accessed directly to debug plugin issues
 * Access it at: /wp-content/plugins/doc-display-ultimate/debug.php
 */

// Prevent direct access in production
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    die('Debug mode not enabled');
}

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin capabilities
if (!current_user_can('manage_options')) {
    die('Access denied');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>FileBird FD Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        .test-result { margin: 10px 0; padding: 10px; border-left: 4px solid #0073aa; }
    </style>
</head>
<body>
    <h1>FileBird Frontend Documents - Debug Page</h1>
    
    <div class="debug-section">
        <h2>Plugin Status</h2>
        <?php
        // Check if plugin is active
        if (is_plugin_active('doc-display-ultimate/filebird-frontend-documents.php')) {
            echo '<p class="success">✓ Plugin is active</p>';
        } else {
            echo '<p class="error">✗ Plugin is not active</p>';
        }
        
        // Check if FileBird is active
        if (class_exists('FileBird\Plugin')) {
            echo '<p class="success">✓ FileBird plugin is active</p>';
        } else {
            echo '<p class="error">✗ FileBird plugin is not active</p>';
        }
        
        // Check if our classes exist
        if (class_exists('FileBird_FD_Helper')) {
            echo '<p class="success">✓ FileBird_FD_Helper class exists</p>';
        } else {
            echo '<p class="error">✗ FileBird_FD_Helper class does not exist</p>';
        }
        
        if (class_exists('FileBird_FD_Logger')) {
            echo '<p class="success">✓ FileBird_FD_Logger class exists</p>';
        } else {
            echo '<p class="error">✗ FileBird_FD_Logger class does not exist</p>';
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h2>FileBird Helper Tests</h2>
        <?php
        if (class_exists('FileBird_FD_Helper')) {
            // Test FileBird availability
            $is_available = FileBird_FD_Helper::isFileBirdAvailable();
            echo '<p>' . ($is_available ? '✓ FileBird is available' : '✗ FileBird is not available') . '</p>';
            
            if ($is_available) {
                // Test getting folders
                try {
                    $folders = FileBird_FD_Helper::getAllFolders();
                    echo '<p class="success">✓ Retrieved ' . count($folders) . ' folders</p>';
                    
                    if (!empty($folders)) {
                        echo '<h3>Available Folders:</h3>';
                        echo '<ul>';
                        foreach ($folders as $folder) {
                            $name = isset($folder->name) ? $folder->name : 'Unknown';
                            $id = isset($folder->id) ? $folder->id : 'Unknown';
                            echo '<li>ID: ' . $id . ' - Name: ' . $name . '</li>';
                        }
                        echo '</ul>';
                    }
                } catch (Exception $e) {
                    echo '<p class="error">✗ Error getting folders: ' . $e->getMessage() . '</p>';
                }
            }
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h2>Shortcode Test</h2>
        <form method="post">
            <p>
                <label>Folder ID: <input type="number" name="test_folder" value="1" min="1" /></label>
            </p>
            <p>
                <input type="submit" value="Test Shortcode" />
            </p>
        </form>
        
        <?php
        if (isset($_POST['test_folder'])) {
            $folder_id = intval($_POST['test_folder']);
            echo '<div class="test-result">';
            echo '<h3>Testing shortcode with folder ID: ' . $folder_id . '</h3>';
            
            $shortcode = '[filebird_docs folder="' . $folder_id . '" layout="grid"]';
            echo '<p><strong>Shortcode:</strong> <code>' . esc_html($shortcode) . '</code></p>';
            
            echo '<p><strong>Output:</strong></p>';
            echo '<div style="border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">';
            echo do_shortcode($shortcode);
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h2>Error Log</h2>
        <?php
        $log_file = WP_CONTENT_DIR . '/debug-filebird-frontend-docs.log';
        if (file_exists($log_file)) {
            $logs = file($log_file, FILE_IGNORE_NEW_LINES);
            if ($logs) {
                echo '<pre>';
                foreach (array_slice($logs, -20) as $log) {
                    echo esc_html($log) . "\n";
                }
                echo '</pre>';
            } else {
                echo '<p>No log entries found.</p>';
            }
        } else {
            echo '<p>Log file does not exist.</p>';
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h2>PHP Info</h2>
        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
        <p><strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?></p>
        <p><strong>Plugin Directory:</strong> <?php echo plugin_dir_path(__FILE__); ?></p>
        <p><strong>Upload Directory:</strong> <?php echo wp_upload_dir()['basedir']; ?></p>
    </div>
</body>
</html> 