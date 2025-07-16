<?php
/**
 * Grouped List Layout Template
 * 
 * Template for displaying documents grouped by folder structure in a list layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
extract($data);

/**
 * Recursively render folder groups
 */
function renderFolderGroups($folder_groups, $atts, $level = 0, $accordion_states = array()) {
    foreach ($folder_groups as $folder_group) {
        $has_children = !empty($folder_group['children']);
        
        // Determine if this folder should be open by default
        $is_open = false;
        if (isset($accordion_states[$folder_group['folder_id']])) {
            $is_open = ($accordion_states[$folder_group['folder_id']] === 'open');
        } else {
            // Fallback to global accordion_default
            $is_open = $atts['accordion_default'] === 'open';
        }
        
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
                    <div class="filebird-docs-list">
                    <?php foreach ($folder_group['attachments'] as $attachment): ?>
                        <div class="filebird-docs-list-item">
                            <div class="filebird-docs-list-content">
                                <div class="filebird-docs-list-thumbnail">
                                    <?php if ($atts['show_thumbnail'] && $attachment->thumbnail_url): ?>
                                        <img src="<?php echo esc_url($attachment->thumbnail_url); ?>" 
                                             alt="<?php echo esc_attr($attachment->post_title); ?>"
                                             class="filebird-docs-thumbnail">
                                    <?php else: ?>
                                        <div class="filebird-docs-list-icon">
                                            <i class="<?php echo esc_attr(FileBird_FD_Document_Display::getFileTypeIcon($attachment->file_type)); ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="filebird-docs-list-details">
                                    <?php if ($atts['show_title']): ?>
                                        <h4 class="filebird-docs-list-title">
                                            <a href="<?php echo esc_url($attachment->file_url); ?>" 
                                               target="_blank" 
                                               class="filebird-docs-link"
                                               title="<?php echo esc_attr($attachment->post_title); ?>">
                                                <?php echo esc_html($attachment->post_title); ?>
                                            </a>
                                        </h4>
                                    <?php endif; ?>
                                    
                                    <div class="filebird-docs-list-meta">
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
                                        
                                        <span class="filebird-docs-meta-item filebird-docs-type">
                                            <i class="filebird-docs-icon filebird-docs-icon-file"></i>
                                            <?php echo esc_html(strtoupper(pathinfo($attachment->file_path, PATHINFO_EXTENSION))); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="filebird-docs-list-actions">
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
                        <?php renderFolderGroups($folder_group['children'], $atts, $level + 1, $accordion_states); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>
<div class="<?php echo esc_attr($container_classes); ?>">
    <?php if (!empty($folder)): ?>
        <h3 class="filebird-docs-folder-title">
            <?php echo esc_html(isset($folder->name) ? $folder->name : __('Documents', 'filebird-frontend-docs')); ?>
        </h3>
    <?php endif; ?>
    
    <?php if (!empty($attachments) && is_array($attachments)): ?>
        <?php renderFolderGroups($attachments, $atts, 0, $accordion_states); ?>
    <?php else: ?>
        <div class="filebird-docs-empty">
            <p><?php _e('No documents found in this folder.', 'filebird-frontend-docs'); ?></p>
        </div>
    <?php endif; ?>
</div> 