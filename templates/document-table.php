<?php
/**
 * Table Layout Template
 * 
 * Template for displaying documents in a table layout
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
            <?php echo esc_html($folder->name); ?>
            <span class="filebird-docs-count">(<?php echo count($attachments); ?> <?php _e('documents', 'filebird-frontend-docs'); ?>)</span>
        </h3>
    <?php endif; ?>
    
    <?php if (!empty($attachments)): ?>
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
                    <?php foreach ($attachments as $attachment): ?>
                        <tr class="filebird-docs-table-row">
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
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="filebird-docs-empty">
            <p><?php _e('No documents found in this folder.', 'filebird-frontend-docs'); ?></p>
        </div>
    <?php endif; ?>
</div> 