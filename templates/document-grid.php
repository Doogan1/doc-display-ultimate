<?php
/**
 * Grid Layout Template
 * 
 * Default template for displaying documents in a grid layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
extract($data);
?>
<div class="<?php echo esc_attr($container_classes); ?>">
    <?php if (!empty($folder)): ?>
        <h3 class="filebird-docs-folder-title">
            <?php echo esc_html(isset($folder->name) ? $folder->name : __('Documents', 'filebird-frontend-docs')); ?>
        </h3>
    <?php endif; ?>
    
    <?php if (!empty($attachments)): ?>
        <div class="filebird-docs-grid filebird-docs-grid-<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($attachments as $attachment): ?>
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
    <?php else: ?>
        <div class="filebird-docs-empty">
            <p><?php _e('No documents found in this folder.', 'filebird-frontend-docs'); ?></p>
        </div>
    <?php endif; ?>
</div> 