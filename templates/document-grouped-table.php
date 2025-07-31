<?php
/**
 * Grouped Table Layout Template
 * 
 * Template for displaying documents grouped by folder structure in a table layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
extract($data);

/**
 * Recursively render folder groups for table layout
 */
if (!function_exists('renderFolderGroupsTable')) {
    function renderFolderGroupsTable($folder_groups, $atts, $level = 0, $accordion_states = array()) {
        foreach ($folder_groups as $folder_group) {
            $has_children = !empty($folder_group['children']);
            
            // Determine if this folder should be open by default
            $is_open = false;
            if (isset($accordion_states[$folder_group['folder_id']])) {
                $is_open = ($accordion_states[$folder_group['folder_id']] === 'open');
            }
            // Default to closed if no accordion state is set
            
            ?>
            <div class="filebird-docs-folder-section filebird-docs-accordion-section" data-level="<?php echo $level; ?>">
                <div class="filebird-docs-accordion-header" data-folder-id="<?php echo esc_attr($folder_group['folder_id']); ?>">
                    <h4 class="filebird-docs-folder-section-title">
                        <button class="filebird-docs-accordion-toggle" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
                            <span class="filebird-docs-accordion-icon"></span>
                            <?php echo esc_html($folder_group['folder_name']); ?>
                        </button>
                    </h4>
                    
                    <?php if (!empty($folder_group['folder_path']) && $folder_group['folder_path'] !== $folder_group['folder_name']): ?>
                        <p class="filebird-docs-folder-path"><?php echo esc_html($folder_group['folder_path']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="filebird-docs-accordion-content <?php echo $is_open ? 'filebird-docs-accordion-open' : ''; ?>" <?php echo !$is_open ? 'style="display: none;"' : ''; ?>>
                    <?php if (!empty($folder_group['attachments'])): ?>
                        <div class="filebird-docs-table-wrapper">
                        <table class="filebird-docs-table">
                            <thead>
                                <tr>
                                    <?php if ($atts['show_thumbnail']): ?>
                                        <th class="filebird-docs-table-thumbnail">
                                            <?php _e('Preview', 'filebird-frontend-docs'); ?>
                                        </th>
                                    <?php endif; ?>
                                    
                                    <?php if ($atts['show_title']): ?>
                                        <th class="filebird-docs-table-title">
                                            <?php _e('Title', 'filebird-frontend-docs'); ?>
                                        </th>
                                    <?php endif; ?>
                                    
                                    <th class="filebird-docs-table-type">
                                        <?php _e('Type', 'filebird-frontend-docs'); ?>
                                    </th>
                                    
                                    <?php if ($atts['show_size']): ?>
                                        <th class="filebird-docs-table-size">
                                            <?php _e('Size', 'filebird-frontend-docs'); ?>
                                        </th>
                                    <?php endif; ?>
                                    
                                    <?php if ($atts['show_date']): ?>
                                        <th class="filebird-docs-table-date">
                                            <?php _e('Date', 'filebird-frontend-docs'); ?>
                                        </th>
                                    <?php endif; ?>
                                    
                                    <th class="filebird-docs-table-actions">
                                        <?php _e('Actions', 'filebird-frontend-docs'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($folder_group['attachments'] as $attachment): ?>
                                    <tr class="filebird-docs-table-row" data-attachment-id="<?php echo esc_attr($attachment->ID); ?>">
                                        <?php if ($atts['show_thumbnail']): ?>
                                            <td class="filebird-docs-table-thumbnail">
                                                <?php if ($attachment->thumbnail_url): ?>
                                                    <img src="<?php echo esc_url($attachment->thumbnail_url); ?>" 
                                                         alt="<?php echo esc_attr($attachment->post_title); ?>"
                                                         class="filebird-docs-thumbnail">
                                                <?php else: ?>
                                                    <div class="filebird-docs-table-icon">
                                                        <i class="<?php echo esc_attr(FileBird_FD_Document_Display::getFileTypeIcon($attachment->file_type)); ?>"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        
                                        <?php if ($atts['show_title']): ?>
                                            <td class="filebird-docs-table-title">
                                                <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                                   target="_blank" 
                                                   class="filebird-docs-link"
                                                   title="<?php echo esc_attr($attachment->post_title); ?>">
                                                    <?php echo esc_html($attachment->post_title); ?>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                        
                                        <td class="filebird-docs-table-type">
                                            <span class="filebird-docs-file-type">
                                                <i class="<?php echo esc_attr(FileBird_FD_Document_Display::getFileTypeIcon($attachment->file_type)); ?>"></i>
                                                <?php echo esc_html(strtoupper(pathinfo($attachment->file_path, PATHINFO_EXTENSION))); ?>
                                            </span>
                                        </td>
                                        
                                        <?php if ($atts['show_size']): ?>
                                            <td class="filebird-docs-table-size">
                                                <?php echo esc_html($attachment->file_size); ?>
                                            </td>
                                        <?php endif; ?>
                                        
                                        <?php if ($atts['show_date']): ?>
                                            <td class="filebird-docs-table-date">
                                                <?php echo esc_html(get_the_date('', $attachment->ID)); ?>
                                            </td>
                                        <?php endif; ?>
                                        
                                        <td class="filebird-docs-table-actions">
                                            <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                               target="_blank" 
                                               class="filebird-docs-download-btn"
                                               download
                                               title="<?php _e('Download', 'filebird-frontend-docs'); ?>">
                                                <i class="filebird-docs-icon filebird-docs-icon-download"></i>
                                                <?php _e('Download', 'filebird-frontend-docs'); ?>
                                            </a>
                                            <?php echo FileBird_FD_Document_Display::getEditButton($attachment->ID, $attachment->post_title); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($folder_group['children'])): ?>
                        <div class="filebird-docs-nested-folders">
                            <?php renderFolderGroupsTable($folder_group['children'], $atts, $level + 1, $accordion_states); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }
}
?>
<div class="<?php echo esc_attr($container_classes); ?>">
    
    <?php if (!empty($attachments) && is_array($attachments)): ?>
        <?php renderFolderGroupsTable($attachments, $atts, 0, $accordion_states); ?>
    <?php else: ?>
        <div class="filebird-docs-empty">
            <p><?php _e('No documents found in this folder.', 'filebird-frontend-docs'); ?></p>
        </div>
    <?php endif; ?>
</div> 