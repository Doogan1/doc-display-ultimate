<?php
/**
 * Test Folder Selector Fix
 * 
 * This file can be used to test if the folder selector is working in the document library CPT
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Test function to check if admin scripts are loading
function test_document_library_admin_scripts() {
    global $post_type;
    
    echo "Current post type: " . ($post_type ?: 'none') . "\n";
    
    // Check if we're on a document_library page
    if ($post_type === 'document_library') {
        echo "✅ On document_library post type page\n";
        
        // Check if admin scripts are enqueued
        $enqueued_scripts = wp_scripts()->queue;
        $admin_script_found = false;
        
        foreach ($enqueued_scripts as $script) {
            if ($script === 'filebird-frontend-docs-admin') {
                $admin_script_found = true;
                break;
            }
        }
        
        if ($admin_script_found) {
            echo "✅ Admin script is enqueued\n";
        } else {
            echo "❌ Admin script is NOT enqueued\n";
        }
        
        // Check if admin styles are enqueued
        $enqueued_styles = wp_styles()->queue;
        $admin_style_found = false;
        
        foreach ($enqueued_styles as $style) {
            if ($style === 'filebird-frontend-docs-admin') {
                $admin_style_found = true;
                break;
            }
        }
        
        if ($admin_style_found) {
            echo "✅ Admin style is enqueued\n";
        } else {
            echo "❌ Admin style is NOT enqueued\n";
        }
        
    } else {
        echo "❌ Not on document_library post type page\n";
    }
}

// Test function to check AJAX endpoint
function test_ajax_endpoint() {
    // Simulate AJAX request
    $_POST['action'] = 'filebird_fd_get_folders_admin';
    $_POST['nonce'] = wp_create_nonce('filebird_fd_admin_nonce');
    
    // Call the AJAX handler
    ob_start();
    do_action('wp_ajax_filebird_fd_get_folders_admin');
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "✅ AJAX endpoint is working\n";
        echo "Output length: " . strlen($output) . " characters\n";
    } else {
        echo "❌ AJAX endpoint is not working\n";
    }
}

// Test function to check folder tree generation
function test_folder_tree() {
    if (class_exists('FileBird_FD_Helper')) {
        $folders = FileBird_FD_Helper::getFolderTree();
        
        if (!empty($folders)) {
            echo "✅ Folder tree generated successfully\n";
            echo "Number of root folders: " . count($folders) . "\n";
            
            // Show first few folders
            $count = 0;
            foreach ($folders as $folder) {
                if ($count >= 3) break;
                echo "  - " . $folder['name'] . " (ID: " . $folder['id'] . ")\n";
                $count++;
            }
        } else {
            echo "❌ Folder tree is empty\n";
        }
    } else {
        echo "❌ FileBird_FD_Helper class not found\n";
    }
}

// Run tests if this file is accessed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    echo "Testing Document Library Folder Selector...\n";
    echo "==========================================\n";
    
    test_document_library_admin_scripts();
    echo "\n";
    test_ajax_endpoint();
    echo "\n";
    test_folder_tree();
    
    echo "\nTests completed!\n";
}

// Hook to run tests on admin_init
add_action('admin_init', 'test_document_library_admin_scripts'); 