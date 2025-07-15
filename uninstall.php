<?php
/**
 * Uninstall FileBird Frontend Documents
 * 
 * This file is executed when the plugin is deleted.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up any plugin-specific options
delete_option('filebird_fd_version');

// Clean up any plugin-specific user meta
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'filebird_fd_%'");

// Clean up any plugin-specific post meta
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'filebird_fd_%'");

// Clean up any plugin-specific options
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'filebird_fd_%'");

// Remove any plugin-specific upload directories
$upload_dir = wp_upload_dir();
$plugin_upload_dir = $upload_dir['basedir'] . '/filebird-frontend-docs';

if (is_dir($plugin_upload_dir)) {
    // Recursively remove directory
    function removeDirectory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        removeDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    removeDirectory($plugin_upload_dir);
}