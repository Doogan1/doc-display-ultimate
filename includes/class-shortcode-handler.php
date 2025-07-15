<?php
/**
 * Shortcode Handler Class
 * 
 * Handles the [filebird_docs] shortcode registration and processing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FileBird_FD_Shortcode_Handler {
    
    public function __construct() {
        add_action('init', array($this, 'registerShortcode'));
    }
    
    /**
     * Register the shortcode
     */
    public function registerShortcode() {
        add_shortcode('filebird_docs', array($this, 'renderShortcode'));
    }
    
    /**
     * Render the shortcode
     */
    public function renderShortcode($atts) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'folder' => '',
            'orderby' => 'date',
            'order' => 'DESC',
            'limit' => -1,
            'layout' => 'grid',
            'show_title' => 'true',
            'show_size' => 'false',
            'show_date' => 'false',
            'show_thumbnail' => 'true',
            'columns' => 3,
            'class' => ''
        ), $atts, 'filebird_docs');
        
        // Validate folder parameter
        if (empty($atts['folder'])) {
            return '<p class="filebird-docs-error">' . __('Error: No folder specified. Use folder="folder_id" attribute.', 'filebird-frontend-docs') . '</p>';
        }
        
        // Convert string values to appropriate types
        $atts['limit'] = intval($atts['limit']);
        $atts['columns'] = intval($atts['columns']);
        $atts['show_title'] = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_size'] = filter_var($atts['show_size'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_date'] = filter_var($atts['show_date'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_thumbnail'] = filter_var($atts['show_thumbnail'], FILTER_VALIDATE_BOOLEAN);
        
        // Check if FileBird is available
        if (!FileBird_FD_Helper::isFileBirdAvailable()) {
            return '<p class="filebird-docs-error">' . __('Error: FileBird plugin is not available.', 'filebird-frontend-docs') . '</p>';
        }
        
        // Get folder information
        $folder = FileBird_FD_Helper::getFolderById($atts['folder']);
        if (!$folder) {
            return '<p class="filebird-docs-error">' . sprintf(__('Error: Folder with ID %s not found.', 'filebird-frontend-docs'), $atts['folder']) . '</p>';
        }
        
        // Get attachments
        $attachments = FileBird_FD_Helper::getAttachmentsByFolderId($atts['folder'], array(
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'limit' => $atts['limit']
        ));
        
        if (empty($attachments)) {
            return '<p class="filebird-docs-empty">' . __('No documents found in this folder.', 'filebird-frontend-docs') . '</p>';
        }
        
        // Build CSS classes
        $container_classes = array(
            'filebird-docs-container',
            'filebird-docs-layout-' . sanitize_html_class($atts['layout']),
            'filebird-docs-columns-' . $atts['columns']
        );
        
        if (!empty($atts['class'])) {
            $container_classes[] = sanitize_html_class($atts['class']);
        }
        
        // Start output buffering
        ob_start();
        
        // Include template
        $this->renderTemplate($atts['layout'], array(
            'folder' => $folder,
            'attachments' => $attachments,
            'atts' => $atts,
            'container_classes' => implode(' ', $container_classes)
        ));
        
        return ob_get_clean();
    }
    
    /**
     * Render the appropriate template
     */
    private function renderTemplate($layout, $data) {
        $template_path = FB_FD_PLUGIN_PATH . 'templates/document-' . sanitize_file_name($layout) . '.php';
        
        // Check if custom template exists
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback to default template
            $default_template = FB_FD_PLUGIN_PATH . 'templates/document-grid.php';
            if (file_exists($default_template)) {
                include $default_template;
            } else {
                // Emergency fallback
                $this->renderDefaultTemplate($data);
            }
        }
    }
    
    /**
     * Render default template as fallback
     */
    private function renderDefaultTemplate($data) {
        extract($data);
        ?>
        <div class="<?php echo esc_attr($container_classes); ?>">
            <h3 class="filebird-docs-folder-title"><?php echo esc_html($folder->name); ?></h3>
            <ul class="filebird-docs-list">
                <?php foreach ($attachments as $attachment): ?>
                    <li class="filebird-docs-item">
                        <a href="<?php echo esc_url($attachment->file_url); ?>" target="_blank" class="filebird-docs-link">
                            <?php if ($atts['show_thumbnail'] && $attachment->thumbnail_url): ?>
                                <img src="<?php echo esc_url($attachment->thumbnail_url); ?>" alt="<?php echo esc_attr($attachment->post_title); ?>" class="filebird-docs-thumbnail">
                            <?php endif; ?>
                            
                            <?php if ($atts['show_title']): ?>
                                <span class="filebird-docs-title"><?php echo esc_html($attachment->post_title); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($atts['show_size'] && $attachment->file_size): ?>
                                <span class="filebird-docs-size">(<?php echo esc_html($attachment->file_size); ?>)</span>
                            <?php endif; ?>
                            
                            <?php if ($atts['show_date']): ?>
                                <span class="filebird-docs-date"><?php echo esc_html(get_the_date('', $attachment->ID)); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Get shortcode documentation
     */
    public static function getShortcodeDocs() {
        return array(
            'folder' => array(
                'type' => 'string',
                'required' => true,
                'description' => __('Folder ID from FileBird', 'filebird-frontend-docs')
            ),
            'orderby' => array(
                'type' => 'string',
                'required' => false,
                'default' => 'date',
                'options' => array('date', 'title', 'menu_order', 'ID'),
                'description' => __('Order by field', 'filebird-frontend-docs')
            ),
            'order' => array(
                'type' => 'string',
                'required' => false,
                'default' => 'DESC',
                'options' => array('ASC', 'DESC'),
                'description' => __('Sort order', 'filebird-frontend-docs')
            ),
            'limit' => array(
                'type' => 'integer',
                'required' => false,
                'default' => -1,
                'description' => __('Number of documents to display (-1 for all)', 'filebird-frontend-docs')
            ),
            'layout' => array(
                'type' => 'string',
                'required' => false,
                'default' => 'grid',
                'options' => array('grid', 'list', 'table'),
                'description' => __('Display layout', 'filebird-frontend-docs')
            ),
            'show_title' => array(
                'type' => 'boolean',
                'required' => false,
                'default' => 'true',
                'description' => __('Show document title', 'filebird-frontend-docs')
            ),
            'show_size' => array(
                'type' => 'boolean',
                'required' => false,
                'default' => 'false',
                'description' => __('Show file size', 'filebird-frontend-docs')
            ),
            'show_date' => array(
                'type' => 'boolean',
                'required' => false,
                'default' => 'false',
                'description' => __('Show upload date', 'filebird-frontend-docs')
            ),
            'show_thumbnail' => array(
                'type' => 'boolean',
                'required' => false,
                'default' => 'true',
                'description' => __('Show thumbnail for images', 'filebird-frontend-docs')
            ),
            'columns' => array(
                'type' => 'integer',
                'required' => false,
                'default' => 3,
                'description' => __('Number of columns for grid layout', 'filebird-frontend-docs')
            ),
            'class' => array(
                'type' => 'string',
                'required' => false,
                'default' => '',
                'description' => __('Additional CSS classes', 'filebird-frontend-docs')
            )
        );
    }
} 