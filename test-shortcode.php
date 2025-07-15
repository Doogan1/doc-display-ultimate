<?php
/**
 * Test script for FileBird Frontend Documents
 * 
 * This script helps test the shortcode functionality and generate logs
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Test function to trigger shortcode
function filebird_fd_test_shortcode() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $logger = FileBird_FD_Logger::getInstance();
    $logger->info('Test shortcode execution started');
    
    // Test different shortcode variations
    $test_cases = array(
        'basic' => '[filebird_docs folder="1"]',
        'with_subfolders' => '[filebird_docs folder="1" include_subfolders="true"]',
        'grouped' => '[filebird_docs folder="1" include_subfolders="true" group_by_folder="true"]',
        'with_accordion' => '[filebird_docs folder="1" include_subfolders="true" group_by_folder="true" accordion_behavior="independent"]'
    );
    
    foreach ($test_cases as $test_name => $shortcode) {
        $logger->info('Testing shortcode', array('test_name' => $test_name, 'shortcode' => $shortcode));
        
        try {
            $result = do_shortcode($shortcode);
            $logger->info('Shortcode result', array(
                'test_name' => $test_name,
                'result_length' => strlen($result),
                'has_error' => strpos($result, 'error') !== false
            ));
        } catch (Exception $e) {
            $logger->exception($e, array('test_name' => $test_name));
        }
    }
    
    $logger->info('Test shortcode execution completed');
}

// Add test button to admin
add_action('admin_menu', function() {
    add_submenu_page(
        'upload.php',
        __('FileBird FD Test', 'doc-display-ultimate'),
        __('FD Test', 'doc-display-ultimate'),
        'manage_options',
        'filebird-fd-test',
        'filebird_fd_test_page'
    );
});

function filebird_fd_test_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('FileBird Frontend Documents - Test Page', 'doc-display-ultimate'); ?></h1>
        
        <div class="filebird-fd-test-controls">
            <button type="button" id="run-test" class="button button-primary">
                <?php _e('Run Test Shortcodes', 'doc-display-ultimate'); ?>
            </button>
            <button type="button" id="view-logs" class="button button-secondary">
                <?php _e('View Logs', 'doc-display-ultimate'); ?>
            </button>
        </div>
        
        <div class="filebird-fd-test-results">
            <h3><?php _e('Test Results', 'doc-display-ultimate'); ?></h3>
            <div id="test-output"></div>
        </div>
        
        <div class="filebird-fd-test-info">
            <h3><?php _e('System Information', 'doc-display-ultimate'); ?></h3>
            <ul>
                <li><strong>FileBird Available:</strong> <?php echo FileBird_FD_Helper::isFileBirdAvailable() ? 'Yes' : 'No'; ?></li>
                <li><strong>WordPress Version:</strong> <?php echo get_bloginfo('version'); ?></li>
                <li><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></li>
                <li><strong>Debug Mode:</strong> <?php echo defined('WP_DEBUG') && WP_DEBUG ? 'Yes' : 'No'; ?></li>
            </ul>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#run-test').on('click', function() {
            var $button = $(this);
            var $output = $('#test-output');
            
            $button.prop('disabled', true).text('Running tests...');
            $output.html('<p>Running tests...</p>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'filebird_fd_run_test',
                    nonce: '<?php echo wp_create_nonce('filebird_fd_test_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $output.html('<div class="notice notice-success"><p>Tests completed. Check the logs for details.</p></div>');
                    } else {
                        $output.html('<div class="notice notice-error"><p>Test failed: ' + response.data + '</p></div>');
                    }
                },
                error: function() {
                    $output.html('<div class="notice notice-error"><p>Test failed due to AJAX error.</p></div>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Run Test Shortcodes');
                }
            });
        });
        
        $('#view-logs').on('click', function() {
            window.open('<?php echo admin_url('admin.php?page=filebird-fd-logs'); ?>', '_blank');
        });
    });
    </script>
    
    <style>
    .filebird-fd-test-controls {
        margin: 20px 0;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }
    
    .filebird-fd-test-results,
    .filebird-fd-test-info {
        margin: 20px 0;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }
    </style>
    <?php
}

// AJAX handler for running tests
add_action('wp_ajax_filebird_fd_run_test', function() {
    if (!wp_verify_nonce($_POST['nonce'], 'filebird_fd_test_nonce') || 
        !current_user_can('manage_options')) {
        wp_die(__('Security check failed.', 'doc-display-ultimate'));
    }
    
    try {
        filebird_fd_test_shortcode();
        wp_send_json_success('Tests completed successfully');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}); 