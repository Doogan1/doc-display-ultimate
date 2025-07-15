<?php
/**
 * Test File for FileBird Frontend Documents Folder Selector
 * 
 * This file demonstrates the new tree-based folder selector functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // For testing purposes, define ABSPATH if not set
    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(__FILE__) . '/../../../');
    }
}

// Include WordPress
require_once(ABSPATH . 'wp-load.php');

// Check if user is logged in and has admin privileges
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>FileBird Folder Selector Test</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 20px;
            background: #f1f1f1;
        }
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .test-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .test-section h3 {
            margin-top: 0;
            color: #23282d;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>FileBird Frontend Documents - Folder Selector Test</h1>
            <p>This page demonstrates the new tree-based folder selector functionality.</p>
        </div>

        <div class="test-section">
            <h3>New Folder Selector Features</h3>
            <ul>
                <li><strong>Tree Structure:</strong> Hierarchical display of folders with expand/collapse functionality</li>
                <li><strong>Search:</strong> Real-time search through folder names</li>
                <li><strong>Visual Selection:</strong> Clear indication of selected folder with folder name display</li>
                <li><strong>Expand/Collapse All:</strong> Buttons to expand or collapse all folders at once</li>
                <li><strong>Responsive Design:</strong> Works on all screen sizes</li>
                <li><strong>Better UX:</strong> Similar to FileBird's media library interface</li>
            </ul>
        </div>

        <div class="test-section">
            <h3>How to Use</h3>
            <ol>
                <li>Navigate to <strong>Media → Frontend Documents</strong> in your WordPress admin</li>
                <li>Use the search box to find specific folders</li>
                <li>Click on folder names to select them</li>
                <li>Use the expand/collapse arrows to navigate the folder structure</li>
                <li>Use "Expand All" or "Collapse All" buttons for quick navigation</li>
                <li>The selected folder will be displayed in the right panel</li>
                <li>The shortcode will automatically update with your selection</li>
            </ol>
        </div>

        <div class="test-section">
            <h3>Technical Implementation</h3>
            <ul>
                <li><strong>Backend:</strong> Uses FileBird's folder tree API to get hierarchical data</li>
                <li><strong>Frontend:</strong> JavaScript handles tree rendering, search, and selection</li>
                <li><strong>Styling:</strong> CSS provides responsive design with hover and selection states</li>
                <li><strong>Icons:</strong> Uses WordPress Dashicons for consistent UI</li>
                <li><strong>AJAX:</strong> Loads folder data dynamically without page refresh</li>
            </ul>
        </div>

        <div class="test-section">
            <h3>Benefits Over Previous Dropdown</h3>
            <ul>
                <li><strong>Better Navigation:</strong> See folder hierarchy at a glance</li>
                <li><strong>Faster Selection:</strong> No need to scroll through long dropdown lists</li>
                <li><strong>Search Capability:</strong> Quickly find folders by name</li>
                <li><strong>Visual Feedback:</strong> Clear indication of selected folder</li>
                <li><strong>Familiar Interface:</strong> Similar to FileBird's own media library</li>
                <li><strong>Mobile Friendly:</strong> Works well on all devices</li>
            </ul>
        </div>

        <div class="test-section">
            <h3>Next Steps</h3>
            <p>To test the new folder selector:</p>
            <ol>
                <li>Go to your WordPress admin panel</li>
                <li>Navigate to <strong>Media → Frontend Documents</strong></li>
                <li>Try the new folder selector interface</li>
                <li>Test the search functionality</li>
                <li>Try expanding and collapsing folders</li>
                <li>Select different folders and see the shortcode update</li>
            </ol>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html> 