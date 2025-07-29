<?php
/**
 * List Layout Template
 * 
 * Template for displaying documents in a list layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
extract($data);
?>
<div class="<?php echo esc_attr($container_classes); ?>">
    
    <?php if (!empty($attachments)): ?>
        <div class="filebird-docs-list">
            <?php foreach ($attachments as $attachment): ?>
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
    <?php else: ?>
        <div class="filebird-docs-empty">
            <p><?php _e('No documents found in this folder.', 'filebird-frontend-docs'); ?></p>
        </div>
    <?php endif; ?>
</div> 