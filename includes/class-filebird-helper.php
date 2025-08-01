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
            error_log('FileBird FD Debug - FileBird not available in getFolderById');
            return null;
        }
        
        error_log('FileBird FD Debug - getFolderById called for folder ID: ' . $folder_id);
        
        try {
            $folder = \FileBird\Model\Folder::findById($folder_id);
            error_log('FileBird FD Debug - FileBird\Model\Folder::findById returned: ' . print_r($folder, true));
            
            // If the folder object doesn't have a name property, try to get it from the folder tree
            if ($folder && !isset($folder->name)) {
                $all_folders = self::getAllFolders();
                foreach ($all_folders as $f) {
                    if (isset($f->id) && $f->id == $folder_id) {
                        if (isset($f->name)) {
                            $folder->name = $f->name;
                            error_log('FileBird FD Debug - Found folder name from getAllFolders: ' . $f->name);
                        }
                        break;
                    }
                }
            }
            
            return $folder;
        } catch (Exception $e) {
            error_log('FileBird FD Debug - Error getting folder by ID: ' . $e->getMessage());
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
                // Check if required properties exist
                if (!isset($folder->id) || !isset($folder->name) || !isset($folder->parent)) {
                    continue; // Skip folders with missing properties
                }
                
                $folder_map[$folder->id] = array(
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'parent' => $folder->parent,
                    'children' => array(),
                    'count' => self::getAttachmentCountByFolderId($folder->id)
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
     * Get hierarchical folder options for select dropdown
     */
    public static function getHierarchicalFolderOptions($include_all = true, $level = 0) {
        $tree = self::getFolderTree();
        $options = array();
        
        if ($include_all) {
            $options[-1] = __('All Folders', 'filebird-frontend-docs');
        }
        
        self::buildHierarchicalOptions($tree, $options, $level);
        
        return $options;
    }
    
    /**
     * Build hierarchical options recursively
     */
    private static function buildHierarchicalOptions($folders, &$options, $level = 0) {
        foreach ($folders as $folder) {
            $prefix = str_repeat('— ', $level);
            $options[$folder['id']] = $prefix . $folder['name'] . ' (' . $folder['count'] . ')';
            
            if (!empty($folder['children'])) {
                self::buildHierarchicalOptions($folder['children'], $options, $level + 1);
            }
        }
    }

    /**
     * Get all subfolder IDs recursively
     */
    public static function getSubfolderIds($folder_id) {
        if (!self::isFileBirdAvailable()) {
            error_log('FileBird FD Debug - FileBird not available in getSubfolderIds');
            return array();
        }
        
        error_log('FileBird FD Debug - getSubfolderIds called for folder: ' . $folder_id);
        
        try {
            $all_folders = self::getAllFolders();
            $subfolder_ids = array();
            
            error_log('FileBird FD Debug - Total folders available: ' . count($all_folders));
            
            // FileBird uses a parent-child relationship, not a children property
            // We need to find all folders that have the target folder as their parent
            foreach ($all_folders as $folder) {
                if (isset($folder->parent) && $folder->parent == $folder_id) {
                    $subfolder_ids[] = $folder->id;
                    error_log('FileBird FD Debug - Found direct subfolder: ' . $folder->id);
                    
                    // Recursively get subfolders of this subfolder
                    $nested_subfolders = self::getSubfolderIds($folder->id);
                    $subfolder_ids = array_merge($subfolder_ids, $nested_subfolders);
                }
            }
            
            error_log('FileBird FD Debug - Subfolder IDs found: ' . print_r($subfolder_ids, true));
            
            return $subfolder_ids;
        } catch (Exception $e) {
            error_log('FileBird FD Debug - Error in getSubfolderIds: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get attachments from folder and all subfolders
     */
    public static function getAttachmentsByFolderIdRecursive($folder_id, $args = array()) {
        $defaults = array(
            'orderby' => 'date',
            'order' => 'DESC',
            'limit' => -1,
            'include_metadata' => true,
            'include_subfolders' => false,
            'group_by_folder' => false,
            'exclude_folders' => array()
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Debug logging
        error_log('FileBird FD Debug - getAttachmentsByFolderIdRecursive called for folder: ' . $folder_id);
        error_log('FileBird FD Debug - args: ' . print_r($args, true));
        
        if ($args['include_subfolders']) {
            error_log('FileBird FD Debug - Including subfolders');
            if ($args['group_by_folder']) {
                error_log('FileBird FD Debug - Using grouped by folder approach');
                return self::getAttachmentsGroupedByFolder($folder_id, $args);
            } else {
                // Get all subfolder IDs including the parent
                $all_folder_ids = array($folder_id);
                $subfolder_ids = self::getSubfolderIds($folder_id);
                error_log('FileBird FD Debug - Subfolder IDs found: ' . print_r($subfolder_ids, true));
                $all_folder_ids = array_merge($all_folder_ids, $subfolder_ids);
                error_log('FileBird FD Debug - All folder IDs (including parent): ' . print_r($all_folder_ids, true));
                
                // Filter out excluded folders
                if (!empty($args['exclude_folders'])) {
                    $all_folder_ids = array_diff($all_folder_ids, $args['exclude_folders']);
                    error_log('FileBird FD Debug - After excluding folders: ' . print_r($all_folder_ids, true));
                }
                
                // Get attachments from all folders
                $all_attachments = array();
                foreach ($all_folder_ids as $fid) {
                    error_log('FileBird FD Debug - Getting attachments for folder: ' . $fid);
                    $attachments = self::getAttachmentsByFolderId($fid, array(
                        'orderby' => $args['orderby'],
                        'order' => $args['order'],
                        'limit' => -1, // Get all from each folder
                        'include_metadata' => $args['include_metadata']
                    ));
                    error_log('FileBird FD Debug - Found ' . count($attachments) . ' attachments in folder ' . $fid);
                    $all_attachments = array_merge($all_attachments, $attachments);
                }
                
                error_log('FileBird FD Debug - Total attachments collected: ' . count($all_attachments));
                
                // Sort and limit the combined results
                if ($args['limit'] > 0) {
                    $all_attachments = array_slice($all_attachments, 0, $args['limit']);
                }
                
                return $all_attachments;
            }
        } else {
            error_log('FileBird FD Debug - Not including subfolders, getting direct folder attachments');
            // Check if the main folder is excluded
            if (!empty($args['exclude_folders']) && in_array($folder_id, $args['exclude_folders'])) {
                error_log('FileBird FD Debug - Main folder is excluded');
                return array();
            }
            $attachments = self::getAttachmentsByFolderId($folder_id, $args);
            error_log('FileBird FD Debug - Found ' . count($attachments) . ' attachments in main folder');
            return $attachments;
        }
    }

    /**
     * Get attachments grouped by folder structure
     */
    public static function getAttachmentsGroupedByFolder($folder_id, $args = array()) {
        $tree = self::getFolderTree();
        $grouped_attachments = array();
        
        // Find the target folder in the tree
        $target_folder = self::findFolderInTree($tree, $folder_id);
        
        if ($target_folder) {
            self::collectFolderAttachments($target_folder, $grouped_attachments, $args);
        }
        
        return $grouped_attachments;
    }
    
    /**
     * Find a specific folder in the tree
     */
    private static function findFolderInTree($folders, $folder_id) {
        foreach ($folders as $folder) {
            if ($folder['id'] == $folder_id) {
                return $folder;
            }
            if (!empty($folder['children'])) {
                $found = self::findFolderInTree($folder['children'], $folder_id);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
    }
    
    /**
     * Collect attachments from folder and its children
     */
    private static function collectFolderAttachments($folder, &$grouped_attachments, $args) {
        // Check if this folder is excluded
        if (!empty($args['exclude_folders']) && in_array($folder['id'], $args['exclude_folders'])) {
            return;
        }
        
        // Get attachments for current folder
        $attachments = self::getAttachmentsByFolderId($folder['id'], array(
            'orderby' => $args['orderby'],
            'order' => $args['order'],
            'limit' => -1,
            'include_metadata' => $args['include_metadata']
        ));
        
        // Create folder group for current folder
        $folder_group = array(
            'folder_id' => $folder['id'],
            'folder_name' => $folder['name'],
            'folder_path' => self::getFolderPath($folder['id']),
            'attachments' => $attachments,
            'count' => count($attachments),
            'children' => array()
        );
        
        // Recursively collect from children
        if (!empty($folder['children'])) {
            foreach ($folder['children'] as $child) {
                // Check if child is excluded
                if (!empty($args['exclude_folders']) && in_array($child['id'], $args['exclude_folders'])) {
                    continue;
                }
                
                // Get child attachments
                $child_attachments = self::getAttachmentsByFolderId($child['id'], array(
                    'orderby' => $args['orderby'],
                    'order' => $args['order'],
                    'limit' => -1,
                    'include_metadata' => $args['include_metadata']
                ));
                
                // Create child folder group
                $child_group = array(
                    'folder_id' => $child['id'],
                    'folder_name' => $child['name'],
                    'folder_path' => self::getFolderPath($child['id']),
                    'attachments' => $child_attachments,
                    'count' => count($child_attachments),
                    'children' => array()
                );
                
                // Recursively collect child's children
                if (!empty($child['children'])) {
                    self::collectChildFolderAttachments($child, $child_group, $args);
                }
                
                // Add child to current folder's children
                $folder_group['children'][] = $child_group;
            }
        }
        
        // Add current folder to grouped attachments
        $grouped_attachments[] = $folder_group;
    }
    
    /**
     * Recursively collect child folder attachments
     */
    private static function collectChildFolderAttachments($parent_folder, &$parent_group, $args) {
        if (!empty($parent_folder['children'])) {
            foreach ($parent_folder['children'] as $child) {
                // Check if child is excluded
                if (!empty($args['exclude_folders']) && in_array($child['id'], $args['exclude_folders'])) {
                    continue;
                }
                
                // Get child attachments
                $child_attachments = self::getAttachmentsByFolderId($child['id'], array(
                    'orderby' => $args['orderby'],
                    'order' => $args['order'],
                    'limit' => -1,
                    'include_metadata' => $args['include_metadata']
                ));
                
                // Create child folder group
                $child_group = array(
                    'folder_id' => $child['id'],
                    'folder_name' => $child['name'],
                    'folder_path' => self::getFolderPath($child['id']),
                    'attachments' => $child_attachments,
                    'count' => count($child_attachments),
                    'children' => array()
                );
                
                // Recursively collect child's children
                if (!empty($child['children'])) {
                    self::collectChildFolderAttachments($child, $child_group, $args);
                }
                
                // Add child to parent's children
                $parent_group['children'][] = $child_group;
            }
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
        
        error_log('FileBird FD Debug - getAttachmentsByFolderId called for folder: ' . $folder_id);
        error_log('FileBird FD Debug - args: ' . print_r($args, true));
        
        $attachment_ids = self::getAttachmentIdsByFolderId($folder_id);
        error_log('FileBird FD Debug - Found ' . count($attachment_ids) . ' attachment IDs for folder ' . $folder_id);
        
        if (empty($attachment_ids)) {
            error_log('FileBird FD Debug - No attachment IDs found for folder ' . $folder_id);
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
        
        error_log('FileBird FD Debug - Query args: ' . print_r($query_args, true));
        
        $attachments = get_posts($query_args);
        error_log('FileBird FD Debug - get_posts returned ' . count($attachments) . ' attachments');
        
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
        
        error_log('FileBird FD Debug - Returning ' . count($attachments) . ' attachments with metadata');
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
                if ($folder && isset($folder->name) && isset($folder->parent)) {
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
            // Check if required properties exist
            if (!isset($folder->id) || !isset($folder->name)) {
                continue; // Skip folders with missing properties
            }
            $options[$folder->id] = $folder->name;
        }
        
        return $options;
    }
} 