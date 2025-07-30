<?php
/**
 * Grouped Grid Layout Template
 * 
 * Template for displaying documents grouped by folder structure in a grid layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
extract($data);

/**
 * Recursively render folder groups for grid layout
 */
if (!function_exists('renderFolderGroupsGrid')) {
    function renderFolderGroupsGrid($folder_groups, $atts, $level = 0, $accordion_states = array()) {
        foreach ($folder_groups as $folder_group) {
            $has_children = !empty($folder_group['children']);
            

            
            // Determine if this folder should be open by default
            $is_open = false;
            $folder_id = $folder_group['folder_id'];
            
            // Try both string and integer versions of the folder ID
            if (isset($accordion_states[$folder_id])) {
                $is_open = ($accordion_states[$folder_id] === 'open');
            } elseif (isset($accordion_states[(string)$folder_id])) {
                $is_open = ($accordion_states[(string)$folder_id] === 'open');
            } elseif (isset($accordion_states[(int)$folder_id])) {
                $is_open = ($accordion_states[(int)$folder_id] === 'open');
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
                        <div class="filebird-docs-grid filebird-docs-grid-<?php echo esc_attr($atts['columns']); ?>">
                        <?php foreach ($folder_group['attachments'] as $attachment): ?>
                            <div class="filebird-docs-grid-item">
                                <div class="filebird-docs-card">
                                    <div class="filebird-docs-card-header">
                                        <?php if ($atts['show_thumbnail'] && $attachment->thumbnail_url): ?>
                                            <div class="filebird-docs-card-image">
                                                <img src="<?php echo esc_url($attachment->thumbnail_url); ?>" 
                                                     alt="<?php echo esc_attr($attachment->post_title); ?>"
                                                     class="filebird-docs-thumbnail">
                                            </div>
                                        <?php else: ?>
                                            <div class="filebird-docs-card-icon">
                                                <i class="<?php echo esc_attr(FileBird_FD_Document_Display::getFileTypeIcon($attachment->file_type)); ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="filebird-docs-card-body">
                                        <?php if ($atts['show_title']): ?>
                                            <h4 class="filebird-docs-card-title">
                                                <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                                   target="_blank" 
                                                   class="filebird-docs-link"
                                                   title="<?php echo esc_attr($attachment->post_title); ?>">
                                                    <?php echo esc_html($attachment->post_title); ?>
                                                </a>
                                            </h4>
                                        <?php endif; ?>
                                        
                                        <div class="filebird-docs-card-meta">
                                            <?php if ($atts['show_size'] && $attachment->file_size): ?>
                                                <span class="filebird-docs-meta-item filebird-docs-size">
                                                    <i class="filebird-docs-icon filebird-docs-icon-size"></i>
                                                    <?php echo esc_html($attachment->file_size); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($atts['show_date']): ?>
                                                <span class="filebird-docs-meta-item filebird-docs-date">
                                                    <i class="filebird-docs-icon filebird-docs-icon-date"></i>
                                                    <?php echo esc_html(get_the_date('', $attachment->ID)); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="filebird-docs-card-footer">
                                        <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                           target="_blank" 
                                           class="filebird-docs-download-btn"
                                           download>
                                            <i class="filebird-docs-icon filebird-docs-icon-download"></i>
                                            <?php _e('Download', 'filebird-frontend-docs'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($folder_group['children'])): ?>
                        <div class="filebird-docs-nested-folders">
                            <?php renderFolderGroupsGrid($folder_group['children'], $atts, $level + 1, $accordion_states); ?>
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
        <?php renderFolderGroupsGrid($attachments, $atts, 0, $accordion_states); ?>
    <?php else: ?>
        <div class="filebird-docs-empty">
            <p><?php _e('No documents found in this folder.', 'filebird-frontend-docs'); ?></p>
        </div>
    <?php endif; ?>
</div> 