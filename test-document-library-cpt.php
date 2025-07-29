<?php
/**
 * Test Document Library Custom Post Type
 * 
 * This file can be used to test the document library CPT functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Test function to verify CPT registration
function test_document_library_cpt() {
    // Check if post type is registered
    $post_types = get_post_types(array(), 'names');
    
    if (in_array('document_library', $post_types)) {
        echo "✅ Document Library CPT is registered successfully\n";
    } else {
        echo "❌ Document Library CPT is NOT registered\n";
    }
    
    // Check if we can create a test post
    $test_post = array(
        'post_title' => 'Test Document Library',
        'post_type' => 'document_library',
        'post_status' => 'publish'
    );
    
    $post_id = wp_insert_post($test_post);
    
    if ($post_id && !is_wp_error($post_id)) {
        echo "✅ Test document library post created with ID: $post_id\n";
        
        // Test meta fields
        $meta_fields = array(
            '_document_library_folders' => '1,2,3',
            '_document_library_layout' => 'grid',
            '_document_library_columns' => '3',
            '_document_library_orderby' => 'date',
            '_document_library_order' => 'DESC',
            '_document_library_limit' => '-1',
            '_document_library_show_title' => 'true',
            '_document_library_show_size' => 'false',
            '_document_library_show_date' => 'false',
            '_document_library_show_thumbnail' => 'true',
            '_document_library_include_subfolders' => 'false',
            '_document_library_group_by_folder' => 'false',
            '_document_library_accordion_default' => 'closed',
            '_document_library_exclude_folders' => '',
            '_document_library_custom_class' => 'test-library'
        );
        
        foreach ($meta_fields as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
        
        echo "✅ Meta fields saved successfully\n";
        
        // Test shortcode generation
        $shortcode = '[render_document_library id="' . $post_id . '"]';
        echo "✅ Generated shortcode: $shortcode\n";
        
        // Clean up test post
        wp_delete_post($post_id, true);
        echo "✅ Test post cleaned up\n";
        
    } else {
        echo "❌ Failed to create test document library post\n";
    }
}

// Test function to check shortcode functionality
function test_document_library_shortcode() {
    // Create a test post
    $test_post = array(
        'post_title' => 'Test Document Library for Shortcode',
        'post_type' => 'document_library',
        'post_status' => 'publish'
    );
    
    $post_id = wp_insert_post($test_post);
    
    if ($post_id && !is_wp_error($post_id)) {
        // Set some test meta
        update_post_meta($post_id, '_document_library_folders', '1');
        update_post_meta($post_id, '_document_library_layout', 'grid');
        update_post_meta($post_id, '_document_library_columns', '3');
        
        // Test the shortcode
        $shortcode = '[render_document_library id="' . $post_id . '"]';
        $output = do_shortcode($shortcode);
        
        if (!empty($output)) {
            echo "✅ Shortcode renders successfully\n";
            echo "Output preview: " . substr($output, 0, 100) . "...\n";
        } else {
            echo "❌ Shortcode output is empty\n";
        }
        
        // Clean up
        wp_delete_post($post_id, true);
        
    } else {
        echo "❌ Failed to create test post for shortcode\n";
    }
}

// Run tests if this file is accessed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    echo "Testing Document Library Custom Post Type...\n";
    echo "==========================================\n";
    
    test_document_library_cpt();
    echo "\n";
    test_document_library_shortcode();
    
    echo "\nTests completed!\n";
} 