<?php
/**
 * FileBird Helper Class
 * 
 * Provides safe interaction with FileBird plugin classes and methods
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FileBird_FD_Helper {
    
    /**
     * Check if FileBird is active and accessible
     */
    public static function isFileBirdAvailable() {
        return class_exists('FileBird\Plugin') && 
               class_exists('FileBird\Classes\Helpers') && 
               class_exists('FileBird\Model\Folder');
    }
    
    /**
     * Get all folders from FileBird
     */
    public static function getAllFolders($prepend_default = null) {
        if (!self::isFileBirdAvailable()) {
            return array();
        }
        
        try {
            return \FileBird\Model\Folder::allFolders('*', $prepend_default);
        } catch (Exception $e) {
            error_log('FileBird Frontend Documents: Error getting folders - ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get folder by ID
     */
    public static function getFolderById($folder_id) {
        if (!self::isFileBirdAvailable()) {
            return null;
        }
        
        try {
            return \FileBird\Model\Folder::findById($folder_id);
        } catch (Exception $e) {
            error_log('FileBird Frontend Documents: Error getting folder by ID - ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get attachment IDs by folder ID
     */
    public static function getAttachmentIdsByFolderId($folder_id) {
        if (!self::isFileBirdAvailable()) {
            return array();
        }
        
        try {
            return \FileBird\Classes\Helpers::getAttachmentIdsByFolderId($folder_id);
        } catch (Exception $e) {
            error_log('FileBird Frontend Documents: Error getting attachments by folder ID - ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get attachment count by folder ID
     */
    public static function getAttachmentCountByFolderId($folder_id) {
        if (!self::isFileBirdAvailable()) {
            return 0;
        }
        
        try {
            return \FileBird\Classes\Helpers::getAttachmentCountByFolderId($folder_id);
        } catch (Exception $e) {
            error_log('FileBird Frontend Documents: Error getting attachment count - ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get folder tree structure
     */
    public static function getFolderTree() {
        if (!self::isFileBirdAvailable()) {
            return array();
        }
        
        try {
            // Get all folders
            $folders = self::getAllFolders();
            
            // Build tree structure
            $tree = array();
            $folder_map = array();
            
            // First pass: create map
            foreach ($folders as $folder) {
                $folder_map[$folder->id] = array(
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'parent' => $folder->parent,
                    'children' => array()
                );
            }
            
            // Second pass: build tree
            foreach ($folder_map as $id => $folder) {
                if ($folder['parent'] == 0) {
                    $tree[] = &$folder_map[$id];
                } else {
                    if (isset($folder_map[$folder['parent']])) {
                        $folder_map[$folder['parent']]['children'][] = &$folder_map[$id];
                    }
                }
            }
            
            return $tree;
        } catch (Exception $e) {
            error_log('FileBird Frontend Documents: Error building folder tree - ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get attachments with metadata by folder ID
     */
    public static function getAttachmentsByFolderId($folder_id, $args = array()) {
        $defaults = array(
            'orderby' => 'date',
            'order' => 'DESC',
            'limit' => -1,
            'include_metadata' => true
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $attachment_ids = self::getAttachmentIdsByFolderId($folder_id);
        
        if (empty($attachment_ids)) {
            return array();
        }
        
        $query_args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post__in' => $attachment_ids,
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            'posts_per_page' => $args['limit']
        );
        
        $attachments = get_posts($query_args);
        
        if (!$args['include_metadata']) {
            return $attachments;
        }
        
        // Add metadata to each attachment
        foreach ($attachments as &$attachment) {
            $attachment->file_url = wp_get_attachment_url($attachment->ID);
            $attachment->file_path = get_attached_file($attachment->ID);
            $attachment->file_type = get_post_mime_type($attachment->ID);
            $attachment->file_size = self::getFileSize($attachment->ID);
            $attachment->thumbnail_url = wp_get_attachment_image_url($attachment->ID, 'thumbnail');
            $attachment->medium_url = wp_get_attachment_image_url($attachment->ID, 'medium');
        }
        
        return $attachments;
    }
    
    /**
     * Get file size in human readable format
     */
    public static function getFileSize($attachment_id) {
        $file_path = get_attached_file($attachment_id);
        
        if (!file_exists($file_path)) {
            return '';
        }
        
        $bytes = filesize($file_path);
        
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get folder path as string
     */
    public static function getFolderPath($folder_id) {
        if (!self::isFileBirdAvailable()) {
            return '';
        }
        
        try {
            $path = array();
            $current_id = $folder_id;
            
            while ($current_id > 0) {
                $folder = \FileBird\Model\Folder::findById($current_id);
                if ($folder) {
                    array_unshift($path, $folder->name);
                    $current_id = $folder->parent;
                } else {
                    break;
                }
            }
            
            return implode(' / ', $path);
        } catch (Exception $e) {
            error_log('FileBird Frontend Documents: Error getting folder path - ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Check if folder exists
     */
    public static function folderExists($folder_id) {
        if (!self::isFileBirdAvailable()) {
            return false;
        }
        
        try {
            return \FileBird\Model\Folder::isFolderExist($folder_id);
        } catch (Exception $e) {
            error_log('FileBird Frontend Documents: Error checking folder existence - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get folder options for select dropdown
     */
    public static function getFolderOptions($include_all = true) {
        $folders = self::getAllFolders();
        $options = array();
        
        if ($include_all) {
            $options[-1] = __('All Folders', 'filebird-frontend-docs');
        }
        
        foreach ($folders as $folder) {
            $options[$folder->id] = $folder->name;
        }
        
        return $options;
    }
} 